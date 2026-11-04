@extends('layouts.shop')

@php
    // На главной (каталог без фильтров) хлебные крошки не показываем.
    $isHome = !request('category') && !request('brand') && !request('search');

    if (! $isHome) {
        $breadcrumbs = [['label' => 'Главная', 'url' => route('catalog.index')]];

        if ($activeCategory = \App\Models\Category::where('slug', request('category'))->with('parent')->first()) {
            if ($activeCategory->parent) {
                $breadcrumbs[] = ['label' => $activeCategory->parent->name, 'url' => route('catalog.index', ['category' => $activeCategory->parent->slug])];
            }
            $breadcrumbs[] = ['label' => $activeCategory->name];
        } elseif (request('brand')) {
            $brandName = \App\Models\Brand::where('slug', request('brand'))->value('name');
            $breadcrumbs[] = ['label' => $brandName ? "Бренд: {$brandName}" : 'Каталог'];
        } else {
            $breadcrumbs[] = ['label' => 'Каталог'];
        }
    }
@endphp

@section('title', 'Каталог товаров')

@section('content')
    <div class="flex flex-col lg:flex-row gap-8">
        <aside class="lg:w-60 shrink-0 space-y-6">
            <div>
                <h2 class="font-bold text-lg mb-3">Категории</h2>
                <nav class="flex lg:flex-col gap-2 overflow-x-auto pb-2">
                    <a href="{{ request()->only(['brand', 'search']) ? route('catalog.index', request()->only(['brand', 'search'])) : route('catalog.index') }}"
                       class="px-4 py-2 rounded-lg whitespace-nowrap {{ !request('category') ? 'bg-indigo-600 text-white' : 'bg-white hover:bg-indigo-50' }}">
                        Все товары
                    </a>
                    @foreach ($menuCategories as $category)
                        @php $params = ['category' => $category->slug] + request()->only(['brand', 'search']); @endphp
                        <a href="{{ route('catalog.index', $params) }}"
                           class="px-4 py-2 rounded-lg font-medium whitespace-nowrap {{ request('category') === $category->slug ? 'bg-indigo-600 text-white' : 'bg-white hover:bg-indigo-50' }}">
                            {{ $category->name }}
                        </a>
                        @foreach ($category->children as $child)
                            @php $childParams = ['category' => $child->slug] + request()->only(['brand', 'search']); @endphp
                            <a href="{{ route('catalog.index', $childParams) }}"
                               class="pl-6 pr-4 py-2 rounded-lg text-sm whitespace-nowrap {{ request('category') === $child->slug ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 hover:bg-indigo-50' }}">
                                — {{ $child->name }}
                            </a>
                        @endforeach
                    @endforeach
                </nav>
            </div>

            <div>
                <h2 class="font-bold text-lg mb-3">Бренды</h2>
                <nav class="flex lg:flex-col gap-2 overflow-x-auto pb-2">
                    <a href="{{ request()->only(['category', 'search']) ? route('catalog.index', request()->only(['category', 'search'])) : route('catalog.index') }}"
                       class="px-4 py-2 rounded-lg whitespace-nowrap {{ !request('brand') ? 'bg-indigo-600 text-white' : 'bg-white hover:bg-indigo-50' }}">
                        Все бренды
                    </a>
                    @foreach ($brands as $brand)
                        @php $params = ['brand' => $brand->slug] + request()->only(['category', 'search']); @endphp
                        <a href="{{ route('catalog.index', $params) }}"
                           class="px-4 py-2 rounded-lg flex items-center justify-between whitespace-nowrap {{ request('brand') === $brand->slug ? 'bg-indigo-600 text-white' : 'bg-white hover:bg-indigo-50' }}">
                            <span>{{ $brand->name }}</span>
                            <span class="text-xs {{ request('brand') === $brand->slug ? 'text-indigo-200' : 'text-gray-400' }}">
                                {{ $brand->products_count }}
                            </span>
                        </a>
                    @endforeach
                </nav>
            </div>
        </aside>

        <div class="flex-1">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold">Каталог товаров</h1>
                <form method="GET" class="flex items-center gap-2">
                    @if (request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif
                    @if (request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
                    <select name="sort" onchange="this.form.submit()"
                            class="rounded-lg border-gray-300 border px-3 py-2 text-sm">
                        <option value="" @selected(!request('sort'))>По умолчанию</option>
                        <option value="price_asc" @selected(request('sort') === 'price_asc')>Цена: сначала дешёвые</option>
                        <option value="price_desc" @selected(request('sort') === 'price_desc')>Цена: сначала дорогие</option>
                        <option value="name" @selected(request('sort') === 'name')>По названию</option>
                    </select>
                </form>
            </div>

            @if ($products->isEmpty())
                <div class="bg-white rounded-xl p-12 text-center text-gray-500">
                    Товары не найдены. Попробуйте изменить параметры поиска.
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach ($products as $product)
                        <div class="bg-white rounded-xl shadow hover:shadow-lg transition overflow-hidden flex flex-col">
                            <a href="{{ route('catalog.show', $product->slug) }}"
                               class="h-44 bg-gradient-to-br from-indigo-100 to-indigo-200 flex items-center justify-center text-6xl">
                                📦
                            </a>
                            <div class="p-4 flex flex-col flex-1">
                                <p class="text-xs text-gray-500 mb-1">
                                    {{ $product->brand?->name }} · {{ $product->category->name }}
                                </p>
                                <a href="{{ route('catalog.show', $product->slug) }}"
                                   class="font-semibold hover:text-indigo-600 line-clamp-2">{{ $product->name }}</a>
                                <div class="mt-auto pt-3 flex items-center justify-between">
                                    <span class="text-lg font-bold">{{ $product->priceRub() }}</span>
                                    <form action="{{ route('cart.add', $product) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                                class="px-3 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
                                            В корзину
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8">{{ $products->links() }}</div>
            @endif
        </div>
    </div>
@endsection
