@extends('layouts.shop')

@php($breadcrumbs = [
    ['label' => 'Главная', 'url' => route('catalog.index')],
    ['label' => 'Бренды'],
])

@section('title', 'Бренды')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Бренды</h1>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
        @foreach ($brands as $brand)
            <a href="{{ route('catalog.index', ['brand' => $brand->slug]) }}"
               class="bg-white dark:bg-gray-800 rounded-xl shadow hover:shadow-lg transition p-6 flex flex-col items-center justify-center text-center gap-2">
                <span class="text-3xl font-bold text-indigo-600">{{ mb_substr($brand->name, 0, 1) }}</span>
                <span class="font-semibold text-lg">{{ $brand->name }}</span>
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $brand->products_count }} товаров</span>
            </a>
        @endforeach
    </div>
@endsection
