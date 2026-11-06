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
        $trail->push($item->name, route('categories.show', $item->slug));
    }
};

// Загружает категорию по slug из параметра маршрута.
$findCategory = fn (string $slug): Category => Category::query()->where('slug', $slug)->firstOrFail();

// Каталог (учитывает фильтр brand из query-параметров — сайдбар фильтров).
// На главной (без фильтров) trail остаётся пустым — крошки не выводятся.
Breadcrumbs::for('catalog.index', function ($trail) {
    if ($brandSlug = request('brand')) {
        $trail->parent('home');

        $name = Brand::where('slug', $brandSlug)->value('name');
        $trail->push($name ? "Бренд: {$name}" : 'Каталог', url()->current());
    }
});

// Страница категории: Главная / ... / Категория.
// diglactic/breadcrumbs передаёт параметры маршрута как есть,
// поэтому здесь приходит строка slug, а не модель Category.
Breadcrumbs::for('categories.show', function ($trail, string $slug) use ($pushCategoryChain, $findCategory) {
    $trail->parent('home');
    $pushCategoryChain($trail, $findCategory($slug));
});

// Страница бренда: Главная / Бренды / Бренд.
// В генератор приходит строка slug из параметра маршрута.
Breadcrumbs::for('brands.show', function ($trail, string $slug) {
    $brand = Brand::query()->where('slug', $slug)->firstOrFail();

    $trail->parent('brands.index');
    $trail->push($brand->name, route('brands.show', $brand->slug));
});

// Поиск: Главная / Поиск: {запрос}.
Breadcrumbs::for('search.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Поиск: ' . request('q'), route('search.index'));
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
