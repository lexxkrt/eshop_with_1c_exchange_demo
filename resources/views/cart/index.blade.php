@extends('layouts.shop')

@section('title', 'Корзина')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Корзина</h1>

    @if ($items->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-xl p-12 text-center">
            <p class="text-5xl mb-4">🛒</p>
            <p class="text-gray-500 dark:text-gray-400 mb-4">Ваша корзина пуста</p>
            <a href="{{ route('catalog.index') }}" class="px-6 py-3 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700">
                Перейти в каталог
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-4">
                @foreach ($items as $item)
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 flex items-center gap-4">
                        <div class="w-20 h-20 bg-indigo-100 dark:bg-indigo-900/50 rounded-lg flex items-center justify-center text-4xl shrink-0">📦</div>
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('catalog.show', $item['slug']) }}" class="font-semibold hover:text-indigo-600">
                                {{ $item['name'] }}
                            </a>
                            <p class="text-gray-500 dark:text-gray-400 text-sm">{{ number_format($item['price'], 2, ',', ' ') }} ₽ / шт.</p>
                        </div>
                        <form action="{{ route('cart.update', $item['id']) }}" method="POST" class="flex items-center gap-2">
                            @csrf
                            <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1"
                                   class="w-16 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 border px-2 py-1 text-sm">
                            <button type="submit" class="px-3 py-1 rounded-lg border border-gray-300 dark:border-gray-600 text-sm hover:bg-gray-50 dark:hover:bg-gray-700">OK</button>
                        </form>
                        <span class="font-bold w-32 text-right">{{ number_format($item['subtotal'], 2, ',', ' ') }} ₽</span>
                        <form action="{{ route('cart.remove', $item['id']) }}" method="POST">
                            @csrf
                            <button type="submit" title="Удалить"
                                    class="text-red-500 hover:text-red-700 text-xl px-2">✕</button>
                        </form>
                    </div>
                @endforeach
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 h-fit">
                <h2 class="text-lg font-bold mb-4">Итого</h2>
                <p class="text-3xl font-bold text-indigo-600 mb-6">{{ number_format($total, 2, ',', ' ') }} ₽</p>
                <a href="{{ route('checkout.form') }}"
                   class="block w-full text-center px-6 py-3 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700 transition">
                    Оформить заказ
                </a>
                <a href="{{ route('catalog.index') }}"
                   class="block w-full text-center mt-3 px-6 py-3 rounded-lg border border-gray-300 dark:border-gray-600 font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    Продолжить покупки
                </a>
            </div>
        </div>
    @endif
@endsection
