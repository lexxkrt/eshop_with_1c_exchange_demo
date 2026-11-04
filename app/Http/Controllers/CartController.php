<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private CartService $cart)
    {
    }

    public function index(): View
    {
        return view('cart.index', [
            'items' => $this->cart->items(),
            'total' => $this->cart->total(),
        ]);
    }

    public function add(Request $request, Product $product): RedirectResponse
    {
        $quantity = max(1, (int) $request->input('quantity', 1));
        $quantity = min($quantity, max(1, $product->stock));

        $this->cart->add($product->id, $quantity);

        return redirect()
            ->route('cart.index')
            ->with('success', "Товар «{$product->name}» добавлен в корзину");
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $quantity = (int) $request->input('quantity', 1);
        $quantity = min($quantity, max(1, $product->stock));

        $this->cart->setQuantity($product->id, $quantity);

        return redirect()->route('cart.index')->with('success', 'Корзина обновлена');
    }

    public function remove(Product $product): RedirectResponse
    {
        $this->cart->remove($product->id);

        return redirect()->route('cart.index')->with('success', 'Товар удалён из корзины');
    }
}
