@props([
    // Модель Product (для рейтинга ожидаются агрегаты reviews_count / reviews_avg_rating).
    'product',
    // Высота блока картинки (Tailwind-класс).
    'imageHeight' => 'h-44',
    // Показывать строку «Бренд · Категория» над названием.
    'showMeta' => false,
])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800 rounded-xl shadow hover:shadow-lg transition overflow-hidden flex flex-col']) }}>
    <a href="{{ route('catalog.show', $product->slug) }}"
       class="{{ $imageHeight }} flex items-center justify-center overflow-hidden bg-gradient-to-br from-indigo-100 to-indigo-200 dark:from-indigo-900/40 dark:to-indigo-800/40">
        @if ($product->imageUrl('medium'))
            <img src="{{ $product->imageUrl('medium') }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
        @else
            <span class="text-5xl">📦</span>
        @endif
    </a>
    <div class="p-3 sm:p-4 flex flex-col flex-1">
        @if ($showMeta)
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1 truncate">
                {{ $product->brand?->name }} · {{ $product->category->name }}
            </p>
        @endif
        <a href="{{ route('catalog.show', $product->slug) }}"
           class="text-sm sm:text-base font-semibold hover:text-indigo-600 line-clamp-2">{{ $product->name }}</a>
        @if (($product->reviews_count ?? 0) > 0)
            <x-rating
                :rating="$product->reviews_avg_rating"
                :count="$product->reviews_count"
                :href="route('catalog.show', $product->slug) . '#reviews'"
                class="mt-1"
            />
        @endif
        <div class="mt-auto pt-3 flex flex-wrap items-center justify-between gap-2">
            <span class="text-base sm:text-lg font-bold">{{ $product->priceRub() }}</span>
            <form action="{{ route('cart.add', $product) }}" method="POST">
                @csrf
                <button type="submit"
                        class="px-2 sm:px-3 py-2 rounded-lg bg-indigo-600 text-white text-xs sm:text-sm font-medium hover:bg-indigo-700 transition">
                    В корзину
                </button>
            </form>
        </div>
    </div>
</div>
