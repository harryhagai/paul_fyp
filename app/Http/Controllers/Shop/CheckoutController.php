<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CheckoutInformation;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\ClickPesaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use RuntimeException;

class CheckoutController extends Controller
{
    /**
     * Display the checkout page
     */
    public function index()
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            session(['intended' => route('checkout.index')]);
            return redirect()->route('register')
                ->with('info', 'Please register or login to proceed with checkout');
        }

        $user = Auth::user();
        $savedCheckoutInfo = $user->checkoutInformation()->first();
        $checkoutPhoneNumber = $this->formatTzPhoneForDisplay($savedCheckoutInfo?->phone_number ?: $user->phone_number);
        $cart = $this->getCart();

        if (!$cart || $cart->cartItems->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Your cart is empty');
        }

        // Calculate totals
        $cartItems = $cart->cartItems()->with('product.media')->get();
        $subtotal = $cartItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        $total = $subtotal;

        return view('shop.checkout', compact(
            'cart',
            'cartItems',
            'subtotal',
            'total',
            'user',
            'checkoutPhoneNumber',
            'savedCheckoutInfo'
        ));
    }

    /**
     * Process the checkout
     */
    public function store(Request $request, ClickPesaService $clickPesaService)
    {
        // Normalize phone number:
        // accepts 0XXXXXXXXX, XXXXXXXXX, 255XXXXXXXXX, or +255XXXXXXXXX
        $rawPhone = (string) $request->input('phone_number', '');
        $digitsOnly = $this->normalizeTzPhoneToLocal9($rawPhone);
        $request->merge(['phone_number' => $digitsOnly]);

        try {
            // Validate required info form
            $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email',
                'phone_number' => 'required|string|regex:/^[0-9]{9}$/',
                'save_required_information' => 'nullable|boolean',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Please fill in all required fields correctly.',
                'errors' => $e->errors(),
                'error_code' => 'VALIDATION_FAILED',
            ], 422);
        }

        $user = Auth::user();
        $cart = $this->getCart();
        $formattedPhone = '+255' . $digitsOnly;

        if (!$cart || $cart->cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Your cart is empty. Please add items to your cart before checkout.',
                'error_code' => 'CART_EMPTY',
            ], 400);
        }

        $cartItems = $cart->cartItems()->with('product')->get();

        // Check stock availability
        foreach ($cartItems as $item) {
            if (!$item->product) {
                return response()->json([
                    'success' => false,
                    'message' => 'One item in your cart is no longer available. Please remove it from cart and try again.',
                    'error_code' => 'PRODUCT_NOT_FOUND',
                ], 400);
            }

            if ($item->quantity > $item->product->stock) {
                return response()->json([
                    'success' => false,
                    'message' => "Insufficient stock for {$item->product->name}. Available stock: {$item->product->stock}",
                    'error_code' => 'INSUFFICIENT_STOCK',
                ], 400);
            }
        }

        // Calculate totals
        $subtotal = $cartItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        $totalAmount = $subtotal;

        if (!$this->amountIsAllowedForClickPesa($totalAmount)) {
            return response()->json([
                'success' => false,
                'message' => $this->clickPesaAmountLimitMessage(),
                'error_code' => 'AMOUNT_OUT_OF_RANGE',
                'action' => 'back_to_cart',
                'cart_url' => route('cart.index'),
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'status' => 'pending',
                'subtotal' => $subtotal,
                'total_amount' => $totalAmount,
                'currency' => 'TZS',
                'ordered_at' => now(),

                // Store customer info directly on order
                'customer_name' => $request->first_name . ' ' . $request->last_name,
                'customer_email' => $request->email,
                'customer_phone' => $formattedPhone,
                'payment_provider' => 'clickpesa',
                'payment_status' => 'pending',
                'stock_deducted_at' => null,
            ]);

            // Create order items and reserve stock
            foreach ($cartItems as $cartItem) {
                $product = $cartItem->product()->lockForUpdate()->first();

                if (!$product || $cartItem->quantity > $product->stock) {
                    throw new RuntimeException("Insufficient stock for {$cartItem->product->name}.");
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku ?? null,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $cartItem->price,
                    'total_price' => $cartItem->price * $cartItem->quantity,
                ]);

                $product->decrement('stock', $cartItem->quantity);
            }

            $order->update(['stock_deducted_at' => now()]);

            if ($request->boolean('save_required_information')) {
                CheckoutInformation::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'first_name' => $request->first_name,
                        'last_name' => $request->last_name,
                        'email' => $request->email,
                        'phone_number' => $formattedPhone,
                    ]
                );
            }

            DB::commit();

            try {
                $payment = $clickPesaService->initiateUssdPush($order->fresh('user'));

                $order->update([
                    'payment_status' => strtolower($payment['status'] ?? 'processing'),
                    'clickpesa_payment_id' => $payment['id'] ?? null,
                    'clickpesa_client_id' => $payment['clientId'] ?? null,
                    'clickpesa_channel' => $payment['channel'] ?? null,
                    'clickpesa_payload' => $payment,
                    'payment_message' => $payment['message'] ?? 'Payment prompt sent to customer phone.',
                ]);
            } catch (\Throwable $e) {
                $amountLimitFailure = $this->isClickPesaAmountLimitFailure($e);

                $order->update([
                    'payment_status' => 'failed',
                    'payment_message' => 'Could not start ClickPesa payment.',
                    'payment_failed_at' => now(),
                ]);

                Log::error('ClickPesa USSD push initiation failed', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'message' => $e->getMessage(),
                ]);

                if ($amountLimitFailure) {
                    $this->deleteOrderAndRestoreStock($order);

                    return response()->json([
                        'success' => false,
                        'message' => $this->clickPesaAmountLimitMessage(),
                        'error_code' => 'AMOUNT_OUT_OF_RANGE',
                        'action' => 'back_to_cart',
                        'cart_url' => route('cart.index'),
                    ], 422);
                }

                return response()->json([
                    'success' => false,
                    'message' => $this->friendlyPaymentPromptFailureMessage($e) . ' Your order is still pending, so you can retry payment from My Orders.',
                    'error_code' => 'PAYMENT_PROMPT_FAILED',
                    'order_id' => $order->public_id,
                ], 502);
            }

            // Clear the cart only after ClickPesa has accepted the checkout request.
            $cart->cartItems()->delete();
            $cart->delete();

            return response()->json([
                'success' => true,
                'message' => 'A mobile money payment request has been sent to ' . $formattedPhone . '. Please check your phone, enter your PIN, and confirm the payment for order ' . ($order->order_number ?: $order->public_id) . '.',
                'order_id' => $order->public_id,
                'payment_status' => $order->payment_status,
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Checkout failed while creating order', [
                'user_id' => Auth::id(),
                'email' => $request->email,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'We could not place your order right now. Please review your details and try again.',
                'error_code' => 'ORDER_CREATION_FAILED',
            ], 500);
        }
    }

    /**
     * Show checkout success page
     */
    public function success(Order $order)
    {
        // Ensure user owns this order
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $order->load('orderItems.product', 'orderAddresses');

        return view('shop.checkout-success', compact('order'));
    }

    /**
     * Get current user's cart
     */
    private function getCart()
    {
        if (Auth::check()) {
            return Cart::where('user_id', Auth::id())->first();
        } else {
            $sessionId = Session::getId();
            return Cart::where('session_id', $sessionId)->first();
        }
    }

    /**
     * Normalize Tanzania phone into local 9 digits (e.g. 622070303).
     */
    private function normalizeTzPhoneToLocal9(?string $phone): string
    {
        $digitsOnly = preg_replace('/\D+/', '', (string) $phone);

        if (str_starts_with($digitsOnly, '255')) {
            $digitsOnly = substr($digitsOnly, 3);
        }

        if (strlen($digitsOnly) === 10 && str_starts_with($digitsOnly, '0')) {
            $digitsOnly = substr($digitsOnly, 1);
        }

        return $digitsOnly;
    }

    /**
     * Display phone as +255XXXXXXXXX for checkout UI.
     */
    private function formatTzPhoneForDisplay(?string $phone): string
    {
        $local9 = $this->normalizeTzPhoneToLocal9($phone);

        if (!preg_match('/^[0-9]{9}$/', $local9)) {
            return '';
        }

        return '+255' . $local9;
    }

    private function friendlyPaymentPromptFailureMessage(\Throwable $e): string
    {
        $message = strtolower($e->getMessage());

        if (str_contains($message, 'amount must be between')) {
            return $this->clickPesaAmountLimitMessage();
        }

        if (str_contains($message, 'phone') || str_contains($message, 'msisdn')) {
            return 'We could not send the mobile money request. Please check that your phone number is correct and active for mobile money, then try again.';
        }

        if (str_contains($message, 'credentials') || str_contains($message, 'token')) {
            return 'We could not start the payment right now. Please try again in a few minutes.';
        }

        return 'We could not send the mobile money request to your phone. Please check your number and try again.';
    }

    private function amountIsAllowedForClickPesa(float|int|string $amount): bool
    {
        $amount = (float) $amount;

        return $amount >= (float) config('clickpesa.min_amount', 500)
            && $amount <= (float) config('clickpesa.max_amount', 3000000);
    }

    private function clickPesaAmountLimitMessage(): string
    {
        return 'This order amount is outside the mobile money payment limit. Please go back to your cart and adjust the quantity or items before placing the order.';
    }

    private function isClickPesaAmountLimitFailure(\Throwable $e): bool
    {
        return str_contains(strtolower($e->getMessage()), 'amount must be between');
    }

    private function deleteOrderAndRestoreStock(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            $order->loadMissing('orderItems.product');

            if ($order->stock_deducted_at !== null) {
                foreach ($order->orderItems as $item) {
                    if ($item->product) {
                        $item->product->increment('stock', $item->quantity);
                    }
                }
            }

            $order->orderItems()->delete();
            $order->delete();
        });
    }
}
