@extends('layouts.shop')

{{-- Страница категории: товары категории и её подкатегорий.
     В сайдбаре — список подкатегорий текущей категории. --}}

@section('title', $category->name)

@section('content')
    <div class="flex flex-col lg:flex-row gap-8">
        <aside class="lg:w-60 shrink-0 space-y-6">
            <x-price-filter />

            <div>
                <h2 class="font-bold text-lg mb-3">Подкатегории</h2>
                @if ($category->children->isNotEmpty())
                    <nav class="flex lg:flex-col gap-2 overflow-x-auto pb-2">
                        @foreach ($category->children as $child)
                            <a href="{{ route('categories.show', $child->slug) }}"
                               class="px-4 py-2 rounded-lg whitespace-nowrap bg-white hover:bg-indigo-50 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-100">
                                {{ $child->name }}
                            </a>
                        @endforeach
                    </nav>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">Нет подкатегорий</p>
                @endif
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
            <x-product-toolbar :heading="$category->name" />
            <x-product-listing :products="$products" />
        </div>
    </div>
@endsection
