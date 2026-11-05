@extends('layouts.shop')

@section('title', 'Вход')

@section('content')
    <div class="max-w-md mx-auto bg-white dark:bg-gray-800 rounded-xl shadow p-8">
        <h1 class="text-2xl font-bold mb-6 text-center">Вход</h1>

        <form action="{{ route('login') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm mb-1">E-mail</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 border px-3 py-2">
                @error('email')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm mb-1">Пароль</label>
                <input type="password" name="password" required
                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 border px-3 py-2">
                @error('password')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="remember" class="rounded border-gray-300 dark:border-gray-600">
                Запомнить меня
            </label>
            <button type="submit"
                    class="w-full px-6 py-3 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700 transition">
                Войти
            </button>
        </form>

        <p class="mt-4 text-sm text-center text-gray-500 dark:text-gray-400">
            Нет аккаунта? <a href="{{ route('register') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Зарегистрируйтесь</a>
        </p>
    </div>
@endsection
