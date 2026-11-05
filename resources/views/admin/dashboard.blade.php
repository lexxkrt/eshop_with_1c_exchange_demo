@extends('layouts.shop')

@section('title', 'Панель администратора')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Панель администратора</h1>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
            <p class="text-sm text-gray-500 dark:text-gray-400">Всего заказов</p>
            <p class="text-3xl font-bold">{{ $ordersTotal }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
            <p class="text-sm text-gray-500 dark:text-gray-400">Новых заказов</p>
            <p class="text-3xl font-bold text-indigo-600">{{ $ordersNew }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
            <p class="text-sm text-gray-500 dark:text-gray-400">Ваша роль</p>
            <p class="text-3xl font-bold">admin</p>
        </div>
    </div>

    <h2 class="text-xl font-bold mb-4">Заказы</h2>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                <tr>
                    <th class="px-4 py-3">№</th>
                    <th class="px-4 py-3">Клиент</th>
                    <th class="px-4 py-3">E-mail</th>
                    <th class="px-4 py-3">Сумма</th>
                    <th class="px-4 py-3">Позиций</th>
                    <th class="px-4 py-3">Статус</th>
                    <th class="px-4 py-3">Дата</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr class="border-b border-gray-100 dark:border-gray-700">
                        <td class="px-4 py-3 font-medium">{{ $order->id }}</td>
                        <td class="px-4 py-3">{{ $order->customer_name }}</td>
                        <td class="px-4 py-3">{{ $order->customer_email }}</td>
                        <td class="px-4 py-3">{{ $order->totalRub() }}</td>
                        <td class="px-4 py-3">{{ $order->items_count }}</td>
                        <td class="px-4 py-3">{{ $order->status }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $order->created_at->format('d.m.Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                            Заказов пока нет
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $orders->links() }}</div>
@endsection
