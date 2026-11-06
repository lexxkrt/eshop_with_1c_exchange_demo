@extends('layouts.shop')

{{-- Страница бренда: товары выбранного бренда.
     В сайдбаре — навигация по всем брендам (ссылки на их страницы). --}}

@section('title', $brand->name)

@section('content')
    <div class="flex flex-col lg:flex-row gap-8">
        <aside class="lg:w-60 shrink-0 space-y-6">
            <x-price-filter />

            <div>
                <h2 class="font-bold text-lg mb-3">Бренды</h2>
                <nav class="flex lg:flex-col gap-2 overflow-x-auto pb-2">
                    <a href="{{ route('brands.index') }}"
                       class="px-4 py-2 rounded-lg whitespace-nowrap bg-white hover:bg-indigo-50 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-100">
                        Все бренды
                    </a>
                    @foreach ($brands as $item)
                        <a href="{{ route('brands.show', $item->slug) }}"
                           class="px-4 py-2 rounded-lg flex items-center justify-between whitespace-nowrap {{ $item->slug === $brand->slug ? 'bg-indigo-600 text-white' : 'bg-white hover:bg-indigo-50 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-100' }}">
                            <span>{{ $item->name }}</span>
                            <span class="text-xs {{ $item->slug === $brand->slug ? 'text-indigo-200' : 'text-gray-400 dark:text-gray-500' }}">
                                {{ $item->products_count }}
                            </span>
                        </a>
                    @endforeach
                </nav>
            </div>
        </aside>

        <div class="flex-1">
            <x-product-toolbar :heading="$brand->name" />
            <x-product-listing :products="$products" />
        </div>
    </div>
@endsection
