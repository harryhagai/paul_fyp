<?php

namespace App\Support;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PendingShopAction
{
    public const SESSION_KEY = 'auth.pending_shop_action';

    private const ALLOWED_ACTIONS = [
        'add_to_cart',
        'rate_product',
    ];

    public function capture(Request $request): ?array
    {
        $action = (string) $request->query('action', '');
        if (!in_array($action, self::ALLOWED_ACTIONS, true)) {
            return null;
        }

        $productId = (int) $request->query('product_id', 0);
        if ($productId <= 0) {
            return null;
        }

        $pending = [
            'action' => $action,
            'product_id' => $productId,
            'return_url' => $this->safeReturnUrl(
                (string) $request->query('redirect', ''),
                route('shop', [], false)
            ),
        ];

        if ($action === 'add_to_cart') {
            $quantity = (int) $request->query('quantity', 1);
            if ($quantity < 1 || $quantity > 99) {
                return null;
            }
            $pending['quantity'] = $quantity;
        }

        $request->session()->put(self::SESSION_KEY, $pending);

        return $pending;
    }

    public function has(Request $request): bool
    {
        return is_array($request->session()->get(self::SESSION_KEY));
    }

    public function rememberIntendedUrl(Request $request): void
    {
        $redirect = $this->safeReturnUrl((string) $request->query('redirect', ''), '');
        if ($redirect !== '') {
            $request->session()->put('url.intended', $redirect);
        }
    }

    public function process(Request $request): ?array
    {
        $pending = $request->session()->pull(self::SESSION_KEY);
        if (!is_array($pending)) {
            return null;
        }

        $action = (string) ($pending['action'] ?? '');
        $productId = (int) ($pending['product_id'] ?? 0);
        $returnUrl = $this->safeReturnUrl(
            (string) ($pending['return_url'] ?? ''),
            route('shop', [], false)
        );

        if (!in_array($action, self::ALLOWED_ACTIONS, true) || $productId <= 0) {
            return $this->result($returnUrl, 'error', 'The requested shop action is no longer valid.');
        }

        $product = Product::query()->find($productId);
        if (!$product || (int) $product->stock <= 0) {
            return $this->result($returnUrl, 'error', 'Selected product is currently unavailable.');
        }

        return match ($action) {
            'add_to_cart' => $this->addToCart($product, (int) ($pending['quantity'] ?? 0), $returnUrl),
            'rate_product' => $this->result(
                $this->withQueryParameter($returnUrl, 'open_rating', '1'),
                null,
                null
            ),
        };
    }

    private function addToCart(Product $product, int $quantity, string $returnUrl): array
    {
        if ($quantity < 1 || $quantity > 99) {
            return $this->result($returnUrl, 'error', 'The selected quantity is invalid.');
        }

        if ($quantity > (int) $product->stock) {
            return $this->result(
                $returnUrl,
                'error',
                "Insufficient stock for {$product->name}. Available stock: {$product->stock}"
            );
        }

        $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
        $existingItem = $cart->cartItems()->where('product_id', $product->id)->first();

        if ($existingItem) {
            return $this->result($returnUrl, 'success', 'This product is already in your cart');
        }

        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'price' => $product->new_price,
        ]);

        return $this->result($returnUrl, 'success', 'Product added to cart successfully');
    }

    private function safeReturnUrl(string $url, string $fallback): string
    {
        $decodedUrl = rawurldecode($url);
        if (
            $url === ''
            || !str_starts_with($url, '/')
            || str_starts_with($url, '//')
            || str_starts_with($decodedUrl, '//')
            || str_contains($decodedUrl, '\\')
            || preg_match('/[\x00-\x1F\x7F]/', $decodedUrl)
        ) {
            return $fallback;
        }

        $parts = parse_url($url);
        if (
            $parts === false
            || isset($parts['scheme'])
            || isset($parts['host'])
            || isset($parts['user'])
            || isset($parts['pass'])
        ) {
            return $fallback;
        }

        return $url;
    }

    private function withQueryParameter(string $url, string $key, string $value): string
    {
        $parts = parse_url($url);
        if ($parts === false) {
            return route('shop', [], false);
        }

        parse_str($parts['query'] ?? '', $query);
        $query[$key] = $value;

        $path = $parts['path'] ?? '/shop';
        $fragment = isset($parts['fragment']) ? '#'.$parts['fragment'] : '';

        return $path.'?'.http_build_query($query).$fragment;
    }

    private function result(string $redirectUrl, ?string $flashType, ?string $message): array
    {
        return [
            'redirect_url' => $redirectUrl,
            'flash_type' => $flashType,
            'message' => $message,
        ];
    }
}
