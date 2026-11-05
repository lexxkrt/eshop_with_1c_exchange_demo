@extends('layouts.shop')

@section('title', 'Регистрация')

@section('content')
    <div class="max-w-md mx-auto bg-white dark:bg-gray-800 rounded-xl shadow p-8">
        <h1 class="text-2xl font-bold mb-6 text-center">Регистрация</h1>

        <form action="{{ route('register') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm mb-1">Имя</label>
                <input type="text" name="name" value="{{ old('name') }}" required maxlength="255" autofocus
                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 border px-3 py-2">
                @error('name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm mb-1">E-mail</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 border px-3 py-2">
                @error('email')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm mb-1">Пароль</label>
                <input type="password" name="password" required
                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 border px-3 py-2">
                @error('password')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm mb-1">Подтверждение пароля</label>
                <input type="password" name="password_confirmation" required
                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 border px-3 py-2">
            </div>
            <button type="submit"
                    class="w-full px-6 py-3 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700 transition">
                Зарегистрироваться
            </button>
        </form>

        <p class="mt-4 text-sm text-center text-gray-500 dark:text-gray-400">
            Уже есть аккаунт? <a href="{{ route('login') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Войдите</a>
        </p>
    </div>
@endsection
