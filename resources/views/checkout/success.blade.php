@extends('layouts.shop')

@php($breadcrumbs = [
    ['label' => 'Главная', 'url' => route('catalog.index')],
    ['label' => 'Корзина', 'url' => route('cart.index')],
    ['label' => 'Оформление заказа', 'url' => route('checkout.form')],
    ['label' => "Заказ №{$order->id}"],
])

@section('title', 'Заказ оформлен')

@section('content')
    <div class="max-w-2xl mx-auto bg-white rounded-xl shadow p-8 text-center">
        <p class="text-6xl mb-4">✅</p>
        <h1 class="text-2xl font-bold mb-2">Заказ №{{ $order->id }} успешно оформлен!</h1>
        <p class="text-gray-500 mb-6">Мы свяжемся с вами в ближайшее время для подтверждения.</p>

        <div class="text-left bg-gray-50 rounded-lg p-4 mb-6">
            <p><span class="text-gray-500">Получатель:</span> {{ $order->customer_name }}</p>
            <p><span class="text-gray-500">Телефон:</span> {{ $order->customer_phone }}</p>
            <p><span class="text-gray-500">Email:</span> {{ $order->customer_email }}</p>
            <p><span class="text-gray-500">Адрес доставки:</span> {{ $order->address }}</p>

            <h2 class="font-bold mt-4 mb-2">Состав заказа:</h2>
            <ul class="divide-y divide-gray-200">
                @foreach ($order->items as $item)
                    <li class="py-2 flex justify-between text-sm">
                        <span>{{ $item->product_name }} × {{ $item->quantity }}</span>
                        <span>{{ number_format($item->subtotal() / 100, 2, ',', ' ') }} ₽</span>
                    </li>
                @endforeach
            </ul>
            <p class="flex justify-between font-bold text-lg pt-3 border-t mt-2">
                <span>Итого:</span>
                <span class="text-indigo-600">{{ $order->totalRub() }}</span>
            </p>
        </div>

        <a href="{{ route('catalog.index') }}" class="px-6 py-3 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700">
            Вернуться в каталог
        </a>
    </div>
@endsection
