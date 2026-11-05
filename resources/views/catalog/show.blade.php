@extends('layouts.shop')


@section('title', $product->name)

@section('content')
    @if ($product->brand)
        <a href="{{ route('catalog.index', ['brand' => $product->brand->slug]) }}"
           class="inline-block mb-4 px-3 py-1 rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 text-sm hover:bg-indigo-200 dark:hover:bg-indigo-800/60 transition">
            {{ $product->brand->name }}
        </a>
    @endif

    @php $mainImage = $product->imageUrl('large'); @endphp

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="space-y-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow h-96 flex items-center justify-center overflow-hidden">
                @if ($mainImage)
                    <img src="{{ $mainImage }}" alt="{{ $product->name }}"
                         class="max-h-full max-w-full object-contain">
                @else
                    <span class="text-9xl">📦</span>
                @endif
            </div>

            @if ($product->images->isNotEmpty())
                <div class="grid grid-cols-6 gap-2">
                    @foreach ($product->images as $galleryImage)
                        <img src="{{ $product->imageUrl('small', $galleryImage->path) }}"
                             alt="{{ $product->name }} — фото {{ $loop->iteration }}"
                             class="h-20 w-full rounded-lg object-cover bg-white dark:bg-gray-800 shadow cursor-zoom-in">
                    @endforeach
                </div>
            @endif
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-8">
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">{{ $product->category->name }}</p>
            <h1 class="text-3xl font-bold mb-4">{{ $product->name }}</h1>
            <p class="text-gray-600 dark:text-gray-300 mb-6">{{ $product->description }}</p>

            @if ($product->properties->isNotEmpty())
                <div class="mb-6 border-t border-gray-200 dark:border-gray-700 pt-4">
                    <h2 class="font-semibold mb-3">Характеристики</h2>
                    <dl class="space-y-1.5 text-sm">
                        @foreach ($product->properties as $property)
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500 dark:text-gray-400">
                                    {{ $property->name }}@if ($property->unit) <span class="text-gray-400 dark:text-gray-500">({{ $property->unit }})</span>@endif
                                </dt>
                                <dd class="font-medium text-right">{{ $property->pivot->value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            @endif

            <p class="text-4xl font-bold text-indigo-600 mb-2">{{ $product->priceRub() }}</p>
            <p class="text-sm mb-6 {{ $product->stock > 0 ? 'text-green-600' : 'text-red-500' }}">
                @if ($product->stock > 0)
                    В наличии: {{ $product->stock }} шт.
                @else
                    Нет в наличии
                @endif
            </p>

            <form action="{{ route('cart.add', $product) }}" method="POST" class="flex items-center gap-4">
                @csrf
                <label class="flex items-center gap-2">
                    Количество:
                    <input type="number" name="quantity" value="1" min="1" max="{{ max(1, $product->stock) }}"
                           class="w-20 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 border px-3 py-2">
                </label>
                <button type="submit" @disabled($product->stock === 0)
                        class="px-6 py-3 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    В корзину
                </button>
            </form>
        </div>
    </div>

    @if ($related->isNotEmpty())
        <h2 class="text-2xl font-bold mt-12 mb-6">Похожие товары</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach ($related as $item)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow hover:shadow-lg transition overflow-hidden flex flex-col">
                    <a href="{{ route('catalog.show', $item->slug) }}"
                       class="h-36 flex items-center justify-center overflow-hidden bg-gradient-to-br from-indigo-100 to-indigo-200 dark:from-indigo-900/40 dark:to-indigo-800/40">
                        @if ($item->imageUrl('medium'))
                            <img src="{{ $item->imageUrl('medium') }}" alt="{{ $item->name }}" class="h-full w-full object-cover">
                        @else
                            <span class="text-5xl">📦</span>
                        @endif
                    </a>
                    <div class="p-4 flex flex-col flex-1">
                        <a href="{{ route('catalog.show', $item->slug) }}" class="font-semibold hover:text-indigo-600">
                            {{ $item->name }}
                        </a>
                        <div class="mt-auto pt-3 flex items-center justify-between">
                            <span class="font-bold">{{ $item->priceRub() }}</span>
                            <form action="{{ route('cart.add', $item) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-3 py-2 rounded-lg bg-indigo-600 text-white text-sm hover:bg-indigo-700">
                                    В корзину
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
