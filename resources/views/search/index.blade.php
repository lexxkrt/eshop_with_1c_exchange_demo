@extends('layouts.shop')

{{-- Страница поиска по названию товара (query-параметр q).
     В сайдбаре — фильтр по цене, категории и производителям,
     присутствующим в текущем отборе (с количеством товаров). --}}

@section('title', $search !== '' ? "Поиск: {$search}" : 'Поиск')

@section('content')
    <div class="flex flex-col lg:flex-row gap-8">
        <aside class="lg:w-60 shrink-0 space-y-6">
            <x-price-filter />

            <div>
                <h2 class="font-bold text-lg mb-3">Категории</h2>
                <nav class="flex lg:flex-col gap-2 overflow-x-auto pb-2">
                    @foreach ($categories as $category)
                        @php $active = request('category') === $category->slug; @endphp
                        <a href="{{ $active
                                ? request()->fullUrlWithoutQuery(['category', 'page'])
                                : request()->fullUrlWithQuery(['category' => $category->slug]) }}"
                           class="px-4 py-2 rounded-lg flex items-center justify-between gap-2 whitespace-nowrap {{ $active ? 'bg-indigo-600 text-white' : 'bg-white hover:bg-indigo-50 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-100' }}">
                            <span class="truncate" title="{{ $category->name }}">
                                {{ $category->parent?->name ? $category->parent->name . ' / ' . $category->name : $category->name }}
                            </span>
                            <span class="text-xs {{ $active ? 'text-indigo-200' : 'text-gray-400 dark:text-gray-500' }}">
                                {{ $category->products_count }}
                            </span>
                        </a>
                    @endforeach
                </nav>
            </div>

            <div>
                <h2 class="font-bold text-lg mb-3">Производители</h2>
                <nav class="flex lg:flex-col gap-2 overflow-x-auto pb-2">
                    @foreach ($brands as $brand)
                        @php $active = request('brand') === $brand->slug; @endphp
                        <a href="{{ $active
                                ? request()->fullUrlWithoutQuery(['brand', 'page'])
                                : request()->fullUrlWithQuery(['brand' => $brand->slug]) }}"
                           class="px-4 py-2 rounded-lg flex items-center justify-between gap-2 whitespace-nowrap {{ $active ? 'bg-indigo-600 text-white' : 'bg-white hover:bg-indigo-50 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-100' }}">
                            <span class="truncate">{{ $brand->name }}</span>
                            <span class="text-xs {{ $active ? 'text-indigo-200' : 'text-gray-400 dark:text-gray-500' }}">
                                {{ $brand->products_count }}
                            </span>
                        </a>
                    @endforeach
                </nav>
            </div>
        </aside>

        <div class="flex-1">
            <x-product-toolbar :heading="$search !== '' ? "Поиск: {$search}" : 'Поиск'" />
            <x-product-listing :products="$products" />
        </div>
    </div>
@endsection
