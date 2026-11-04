@extends('layouts.shop')

@php($breadcrumbs = [
    ['label' => 'Главная', 'url' => route('catalog.index')],
    ['label' => 'Корзина', 'url' => route('cart.index')],
    ['label' => 'Оформление заказа'],
])

@section('title', 'Оформление заказа')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Оформление заказа</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <form action="{{ route('checkout.submit') }}" method="POST" class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow p-6 space-y-4">
            @csrf

            <div>
                <label class="block font-medium mb-1">Ваше имя</label>
                <input type="text" name="customer_name" value="{{ old('customer_name') }}" required
                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 border px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                @error('customer_name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block font-medium mb-1">Email</label>
                <input type="email" name="customer_email" value="{{ old('customer_email') }}" required
                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 border px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                @error('customer_email')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block font-medium mb-1">Телефон</label>
                <input type="tel" name="customer_phone" value="{{ old('customer_phone') }}" placeholder="+7 (___) ___-__-__" required
                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 border px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                @error('customer_phone')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block font-medium mb-1">Адрес доставки</label>
                <textarea name="address" rows="3" required
                          class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 border px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">{{ old('address') }}</textarea>
                @error('address')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <button type="submit"
                    class="w-full px-6 py-3 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700 transition">
                Подтвердить заказ
            </button>
        </form>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 h-fit">
            <h2 class="text-lg font-bold mb-4">Ваш заказ</h2>
            <ul class="divide-y divide-gray-100 dark:divide-gray-700 mb-4">
                @foreach ($items as $item)
                    <li class="py-2 flex justify-between gap-2 text-sm">
                        <span>{{ $item['name'] }} × {{ $item['quantity'] }}</span>
                        <span class="font-medium whitespace-nowrap">{{ number_format($item['subtotal'], 2, ',', ' ') }} ₽</span>
                    </li>
                @endforeach
            </ul>
            <p class="flex justify-between font-bold text-lg border-t pt-3">
                <span>Итого:</span>
                <span class="text-indigo-600">{{ number_format($total, 2, ',', ' ') }} ₽</span>
            </p>
        </div>
    </div>
@endsection
