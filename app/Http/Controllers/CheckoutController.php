<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function __construct(private CartService $cart)
    {
    }

    public function form(): View
    {
        if ($this->cart->count() === 0) {
            redirect()->route('cart.index')->with('error', 'Корзина пуста')->send();
        }

        return view('checkout.form', [
            'items' => $this->cart->items(),
            'total' => $this->cart->total(),
        ]);
    }

    public function submit(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:1000'],
        ]);

        $items = $this->cart->items();

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Корзина пуста');
        }

        $order = DB::transaction(function () use ($validated, $items) {
            $order = Order::create([
                ...$validated,
                'total' => round($items->sum('subtotal'), 2),
                'status' => 'new',
            ]);

            foreach ($items as $item) {
                $order->items()->create([
                    'product_id' => $item['id'],
                    'product_name' => $item['name'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                ]);

                Product::where('id', $item['id'])->decrement('stock', $item['quantity']);
            }

            return $order;
        });

        $this->cart->clear();

        return redirect()
            ->route('checkout.success', $order)
            ->with('success', 'Заказ успешно оформлен!');
    }

    public function success(Order $order): View
    {
        return view('checkout.success', compact('order'));
    }
}
