{{-- Сетка товаров с состоянием «ничего не найдено» и пагинацией. --}}
@props(['products'])

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
