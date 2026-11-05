@extends('layouts.shop')


@section('title', 'Каталог товаров')

@section('content')
    <div class="flex flex-col lg:flex-row gap-8">
        <aside class="lg:w-60 shrink-0 space-y-6">
            <div>
                <h2 class="font-bold text-lg mb-3">Цена</h2>
                <form method="GET" action="{{ route('catalog.index') }}"
                      class="bg-white dark:bg-gray-800 rounded-lg p-4 space-y-3">
                    @if (request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif
                    @if (request('brand'))<input type="hidden" name="brand" value="{{ request('brand') }}">@endif
                    @if (request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
                    @if (request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}">@endif

                    <div class="flex items-center gap-2">
                        <input type="number" name="price_min" min="0" step="0.01" placeholder="От"
                               value="{{ request('price_min') }}"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 border px-3 py-2 text-sm">
                        <span class="text-gray-400 dark:text-gray-500">—</span>
                        <input type="number" name="price_max" min="0" step="0.01" placeholder="До"
                               value="{{ request('price_max') }}"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 border px-3 py-2 text-sm">
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="submit"
                                class="flex-1 px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">
                            Применить
                        </button>
                        @if (request('price_min') || request('price_max'))
                            <a href="{{ route('catalog.index', request()->except(['price_min', 'price_max', 'page'])) }}"
                               class="px-4 py-2 rounded-lg text-sm bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-100">
                                Сбросить
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <div>
                <h2 class="font-bold text-lg mb-3">Бренды</h2>
                <nav class="flex lg:flex-col gap-2 overflow-x-auto pb-2">
                    <a href="{{ request()->except(['brand', 'page']) ? route('catalog.index', request()->except(['brand', 'page'])) : route('catalog.index') }}"
                       class="px-4 py-2 rounded-lg whitespace-nowrap {{ !request('brand') ? 'bg-indigo-600 text-white' : 'bg-white hover:bg-indigo-50 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-100' }}">
                        Все бренды
                    </a>
                    @foreach ($brands as $brand)
                        @php $params = ['brand' => $brand->slug] + request()->except(['brand', 'page']); @endphp
                        <a href="{{ route('catalog.index', $params) }}"
                           class="px-4 py-2 rounded-lg flex items-center justify-between whitespace-nowrap {{ request('brand') === $brand->slug ? 'bg-indigo-600 text-white' : 'bg-white hover:bg-indigo-50 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-100' }}">
                            <span>{{ $brand->name }}</span>
                            <span class="text-xs {{ request('brand') === $brand->slug ? 'text-indigo-200' : 'text-gray-400 dark:text-gray-500' }}">
                                {{ $brand->products_count }}
                            </span>
                        </a>
                    @endforeach
                </nav>
            </div>
        </aside>

        <div class="flex-1">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                <h1 class="text-xl sm:text-2xl font-bold">Каталог товаров</h1>
                <form method="GET" class="flex items-center gap-2">
                    @if (request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif
                    @if (request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
                    @if (request('brand'))<input type="hidden" name="brand" value="{{ request('brand') }}">@endif
                    @if (request('price_min'))<input type="hidden" name="price_min" value="{{ request('price_min') }}">@endif
                    @if (request('price_max'))<input type="hidden" name="price_max" value="{{ request('price_max') }}">@endif
                    <select name="sort" onchange="this.form.submit()"
                            class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 border px-3 py-2 text-sm">
                        <option value="" @selected(!request('sort'))>По умолчанию</option>
                        <option value="price_asc" @selected(request('sort') === 'price_asc')>Цена: сначала дешёвые</option>
                        <option value="price_desc" @selected(request('sort') === 'price_desc')>Цена: сначала дорогие</option>
                        <option value="name" @selected(request('sort') === 'name')>По названию</option>
                    </select>
                </form>
            </div>

            @if ($products->isEmpty())
                <div class="bg-white dark:bg-gray-800 rounded-xl p-12 text-center text-gray-500 dark:text-gray-400">
                    Товары не найдены. Попробуйте изменить параметры поиска.
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach ($products as $product)
                        <x-product-card :product="$product" :show-meta="true" />
                    @endforeach
                </div>

                <div class="mt-8">{{ $products->links() }}</div>
            @endif
        </div>
    </div>
@endsection
