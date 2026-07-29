<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    /**
     * Display the cart
     */
    public function index()
    {
        $cart = $this->getCart();
        $cartItems = $cart ? $cart->cartItems()->with('product.media')->get() : collect();

        $subtotal = $cartItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        $total = $subtotal;

        return view('shop.cart', compact('cartItems', 'subtotal', 'total'));
    }

    /**
     * Add item to cart
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $product = Product::findOrFail($request->product_id);
        $cart = $this->getOrCreateCart();
        $requestedQuantity = (int) $request->quantity;

        if ((int) $product->stock <= 0) {
            return response()->json([
                'success' => false,
                'message' => "Insufficient stock for {$product->name}. Available stock: 0",
                'out_of_stock' => true,
                'available_stock' => 0,
                'cart_count' => $cart->cartItems()->count()
            ], 422);
        }

        if ($requestedQuantity > (int) $product->stock) {
            return response()->json([
                'success' => false,
                'message' => "Insufficient stock for {$product->name}. Available stock: {$product->stock}",
                'out_of_stock' => true,
                'available_stock' => (int) $product->stock,
                'cart_count' => $cart->cartItems()->count()
            ], 422);
        }

        // Check if item already exists in cart
        $existingItem = $cart->cartItems()->where('product_id', $request->product_id)->first();

        if ($existingItem) {
            // Product already in cart - don't add again, just return info
            return response()->json([
                'success' => false,
                'message' => 'This product is already in your cart',
                'already_in_cart' => true,
                'cart_count' => $cart->cartItems()->count()
            ]);
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $request->product_id,
                'quantity' => $requestedQuantity,
                'price' => $product->new_price
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Product added to cart successfully',
                'cart_count' => $cart->cartItems()->count()
            ]);
        }
    }

    /**
     * Update cart item
     */
    public function update(Request $request, CartItem $cartItem)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:99'
        ]);

        // Check if cart belongs to current user/session
        if (!$this->cartBelongsToUser($cartItem->cart)) {
            abort(403);
        }

        $cartItem->update(['quantity' => $request->quantity]);

        return response()->json([
            'success' => true,
            'message' => 'Cart updated successfully'
        ]);
    }

    /**
     * Remove item from cart
     */
    public function remove(CartItem $cartItem)
    {
        // Check if cart belongs to current user/session
        if (!$this->cartBelongsToUser($cartItem->cart)) {
            abort(403);
        }

        $cartItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart'
        ]);
    }

    /**
     * Clear entire cart
     */
    public function clear()
    {
        $cart = $this->getCart();
        if ($cart) {
            $cart->cartItems()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully'
        ]);
    }

    /**
     * Get or create cart for current user/session
     */
    private function getOrCreateCart()
    {
        if (Auth::check()) {
            return Cart::firstOrCreate(['user_id' => Auth::id()]);
        } else {
            $sessionId = Session::getId();
            return Cart::firstOrCreate(['session_id' => $sessionId]);
        }
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
     * Get cart item count
     */
    public function getCartCount()
    {
        $cart = $this->getCart();
        $count = $cart ? $cart->cartItems()->count() : 0;

        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    }

    /**
     * Check if cart belongs to current user/session
     */
    private function cartBelongsToUser(Cart $cart)
    {
        if (Auth::check()) {
            return $cart->user_id === Auth::id();
        } else {
            return $cart->session_id === Session::getId();
        }
    }
}
