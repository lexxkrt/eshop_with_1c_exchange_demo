<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Illuminate\Support\Facades\Route;

// Blade-вид для генерации HTML хлебных крошек.
config(['breadcrumbs.view' => 'partials.breadcrumbs']);

// Главная.
Breadcrumbs::for('home', function ($trail) {
    $trail->push('Главная', route('catalog.index'));
});

// Цепочка категорий от корня до заданной (Главная / ... / Категория).
$pushCategoryChain = function ($trail, ?Category $category): void {
    $chain = [];

    while ($category) {
        array_unshift($chain, $category);
        $category = $category->parent;
    }

    foreach ($chain as $item) {
        $trail->push($item->name, route('catalog.index', ['category' => $item->slug]));
    }
};

// Каталог (учитывает фильтры category/brand/search из query-параметров).
// На главной (без фильтров) trail остаётся пустым — крошки не выводятся.
Breadcrumbs::for('catalog.index', function ($trail) use ($pushCategoryChain) {
    if ($categorySlug = request('category')) {
        $trail->parent('home');
        $pushCategoryChain($trail, Category::where('slug', $categorySlug)->first());

        return;
    }

    if ($brandSlug = request('brand')) {
        $trail->parent('home');

        $name = Brand::where('slug', $brandSlug)->value('name');
        $trail->push($name ? "Бренд: {$name}" : 'Каталог', url()->current());

        return;
    }

    if ($search = request('search')) {
        $trail->parent('home');
        $trail->push("Поиск: {$search}", url()->current());

        return;
    }
});

// Карточка товара: Главная / ... / Категория / Товар.
Breadcrumbs::for('catalog.show', function ($trail, string $slug) use ($pushCategoryChain) {
    $trail->parent('home');

    $product = Product::query()->where('slug', $slug)->firstOrFail();
    $pushCategoryChain($trail, $product->category);

    $trail->push($product->name, route('catalog.show', $product->slug));
});

// Бренды.
Breadcrumbs::for('brands.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Бренды', route('brands.index'));
});

// Корзина.
Breadcrumbs::for('cart.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Корзина', route('cart.index'));
});

// Оформление заказа: Главная / Корзина / Оформление заказа.
Breadcrumbs::for('checkout.form', function ($trail) {
    $trail->parent('cart.index');
    $trail->push('Оформление заказа', route('checkout.form'));
});

// Заказ оформлен: Главная / Корзина / Оформление заказа / Заказ №N.
Breadcrumbs::for('checkout.success', function ($trail, Order $order) {
    $trail->parent('checkout.form');
    $trail->push("Заказ №{$order->id}", route('checkout.success', $order));
});
