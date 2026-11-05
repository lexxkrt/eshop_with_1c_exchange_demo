@extends('layouts.shop')


@section('title', $product->name)

@section('content')
    @php
        $reviewsCount = $product->reviews->count();
        $avgRating = $reviewsCount ? round($product->reviews->avg('rating'), 1) : null;
        $reviewsWord = match (true) {
            $reviewsCount % 10 === 1 && $reviewsCount % 100 !== 11 => 'отзыв',
            in_array($reviewsCount % 10, [2, 3, 4]) && ! in_array($reviewsCount % 100, [12, 13, 14]) => 'отзыва',
            default => 'отзывов',
        };
    @endphp

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
            @if ($reviewsCount)
                <a href="#reviews" class="inline-flex items-center gap-2 mb-6 text-sm group">
                    <x-rating :rating="$avgRating" class="text-base tracking-wider" />
                    <span class="text-gray-500 dark:text-gray-400 group-hover:text-indigo-600 transition">
                        {{ $avgRating }} · {{ $reviewsCount }} {{ $reviewsWord }}
                    </span>
                </a>
            @endif

            <p class="text-gray-600 dark:text-gray-300 mb-6">{{ $product->description }}</p>

            @if ($product->properties->isNotEmpty())
                <div class="mb-6 border-t border-gray-200 dark:border-gray-700 pt-4">
                    <h2 class="font-semibold mb-3">Характеристики</h2>
                    <dl class="space-y-1.5 text-sm">
                        @foreach ($product->properties as $property)
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500 dark:text-gray-400">{{ $property->name }}</dt>
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

    <h2 id="reviews" class="text-2xl font-bold mt-12 mb-2 flex items-center gap-3">
        Отзывы
        @if ($reviewsCount)
            <span class="text-base font-normal text-gray-500 dark:text-gray-400">
                {{ $reviewsCount }} @if ($avgRating) — средняя оценка
                <span class="text-amber-500 font-semibold">{{ $avgRating }} ★</span>
                @endif
            </span>
        @endif
    </h2>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-6">
            @if ($reviewsCount)
                <div class="space-y-4">
                    @foreach ($product->reviews as $review)
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-5">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-semibold">{{ $review->author_name }}</span>
                                <span class="text-sm text-gray-400">{{ $review->created_at->format('d.m.Y') }}</span>
                            </div>
                            <x-rating :rating="$review->rating" class="mb-2" />
                            <p class="text-gray-600 dark:text-gray-300 whitespace-pre-line">{{ $review->comment }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 dark:text-gray-400">Пока нет отзывов. Станьте первым!</p>
            @endif

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 h-fit">
                <h3 class="font-semibold mb-4">Оставить отзыв</h3>
                <form action="{{ route('reviews.store', $product) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm mb-1">Ваше имя</label>
                        <input type="text" name="author_name" value="{{ old('author_name') }}" required maxlength="100"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 border px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Оценка</label>
                        <select name="rating" required
                                class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 border px-3 py-2">
                            @for ($i = 5; $i >= 1; $i--)
                                <option value="{{ $i }}" @selected(old('rating') == $i)>{{ $i }} {{ str_repeat('★', $i) }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Отзыв</label>
                        <textarea name="comment" rows="4" required maxlength="2000"
                                  class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 border px-3 py-2">{{ old('comment') }}</textarea>
                    </div>
                    <button type="submit"
                            class="px-6 py-2 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700 transition">
                        Отправить
                    </button>
                </form>
            </div>
        </div>

    @if ($related->isNotEmpty())
        <h2 class="text-2xl font-bold mt-12 mb-6">Похожие товары</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach ($related as $item)
                <x-product-card :product="$item" image-height="h-36" />
            @endforeach
        </div>
    @endif
@endsection
