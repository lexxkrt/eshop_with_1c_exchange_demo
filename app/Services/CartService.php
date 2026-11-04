<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class CartService
{
    private const SESSION_KEY = 'cart';

    /**
     * Корзина хранится в сессии как [product_id => quantity].
     * Цены — в рублях (float) благодаря MoneyCast.
     *
     * @return Collection<int, array{id: int, name: string, slug: string, price: float, quantity: int, subtotal: float}>
     */
    public function items(): Collection
    {
        $cart = Session::get(self::SESSION_KEY, []);

        if (empty($cart)) {
            return collect();
        }

        $products = Product::whereIn('id', array_keys($cart))->get();

        return $products->map(function (Product $product) use ($cart) {
            $quantity = (int) $cart[$product->id];

            return [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => $product->price,
                'quantity' => $quantity,
                'subtotal' => round($product->price * $quantity, 2),
            ];
        });
    }

    public function add(int $productId, int $quantity = 1): void
    {
        $cart = Session::get(self::SESSION_KEY, []);
        $cart[$productId] = ($cart[$productId] ?? 0) + max(1, $quantity);
        Session::put(self::SESSION_KEY, $cart);
    }

    public function setQuantity(int $productId, int $quantity): void
    {
        $cart = Session::get(self::SESSION_KEY, []);

        if ($quantity <= 0) {
            unset($cart[$productId]);
        } else {
            $cart[$productId] = $quantity;
        }

        Session::put(self::SESSION_KEY, $cart);
    }

    public function remove(int $productId): void
    {
        $cart = Session::get(self::SESSION_KEY, []);
        unset($cart[$productId]);
        Session::put(self::SESSION_KEY, $cart);
    }

    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    public function count(): int
    {
        return array_sum(Session::get(self::SESSION_KEY, []));
    }

    public function total(): float
    {
        return round($this->items()->sum('subtotal'), 2);
    }
}
