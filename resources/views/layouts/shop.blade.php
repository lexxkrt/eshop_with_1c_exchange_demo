<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Интернет-магазин')</title>
    {{-- Стили подключаются через Vite (@tailwindcss/vite, Tailwind v4).
        Тёмная тема: variant dark работает по классу .dark на <html> (@custom-variant в resources/css/app.css). --}}
    @vite(['resources/css/app.css','resources/js/app.js'])
    {{-- Применяем сохранённую тему до отрисовки страницы, чтобы избежать "мигания" --}}
    <script>
        (function () {
            const theme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (theme === 'dark' || (!theme && prefersDark)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
</head>
<body class="bg-gray-50 text-gray-800 dark:bg-gray-900 dark:text-gray-100 min-h-screen flex flex-col transition-colors">
    <header class="bg-white shadow dark:bg-gray-800 dark:border-b dark:border-gray-700 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between gap-4">
            <div class="flex items-center gap-6">
                <a href="{{ route('catalog.index') }}" class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">🛒 Shop</a>

                {{-- Выпадающее меню категорий --}}
                <nav class="relative group hidden md:block">
                    <button type="button"
                            class="flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 transition">
                        Каталог
                        <span class="text-xs transition group-hover:rotate-180">▼</span>
                    </button>

                    <div class="invisible opacity-0 group-hover:visible group-hover:opacity-100 transition
                                absolute left-0 top-full pt-2 w-72">
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-100 dark:border-gray-700 py-2 max-h-[70vh] overflow-y-auto">
                            <a href="{{ route('catalog.index') }}"
                               class="block px-4 py-2 font-semibold hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-gray-700 dark:hover:text-indigo-400">
                                Все товары
                            </a>
                            @foreach ($menuCategories as $category)
                                <div class="px-4 py-2 hover:bg-indigo-50 dark:hover:bg-gray-700">
                                    <a href="{{ route('catalog.index', ['category' => $category->slug]) }}"
                                       class="block font-semibold hover:text-indigo-600 dark:hover:text-indigo-400">
                                        {{ $category->name }}
                                    </a>
                                    @if ($category->children->isNotEmpty())
                                        <div class="mt-1 space-y-1">
                                            @foreach ($category->children as $child)
                                                <a href="{{ route('catalog.index', ['category' => $child->slug]) }}"
                                                   class="block pl-4 text-sm text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400">
                                                    — {{ $child->name }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </nav>
            </div>

            <form action="{{ route('catalog.index') }}" method="GET" class="flex-1 max-w-md hidden sm:block">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Поиск товаров..."
                       class="w-full rounded-lg bg-white dark:bg-gray-900 dark:text-gray-100 border-gray-300 dark:border-gray-600 border px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </form>

            <div class="flex items-center gap-3">
                {{-- Переключатель темы --}}
                <button type="button" id="theme-toggle" title="Переключить тему"
                        class="w-10 h-10 flex items-center justify-center rounded-lg border border-gray-200 dark:border-gray-600 text-lg hover:bg-indigo-50 dark:hover:bg-gray-700 transition">
                    <span class="hidden dark:inline" title="Светлая тема">☀️</span>
                    <span class="dark:hidden" title="Тёмная тема">🌙</span>
                </button>
                <a href="{{ route('brands.index') }}"
                   class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 font-medium hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-gray-700 dark:hover:text-indigo-400 transition">
                    Бренды
                </a>
                @auth
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}"
                           class="px-4 py-2 rounded-lg border border-amber-400 text-amber-600 dark:text-amber-400 font-medium hover:bg-amber-50 dark:hover:bg-amber-900/30 transition">
                            Админ
                        </a>
                    @endif
                    <div class="flex items-center gap-2">
                        <span class="font-medium">{{ auth()->user()->name }}</span>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" title="Выйти"
                                    class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/30 transition">
                                Выйти
                            </button>
                        </form>
                    </div>
                @endauth
                @guest
                    <a href="{{ route('login') }}"
                       class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 font-medium hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-gray-700 dark:hover:text-indigo-400 transition">
                        Войти
                    </a>
                    <a href="{{ route('register') }}"
                       class="px-4 py-2 rounded-lg bg-gray-800 dark:bg-white text-white dark:text-gray-900 font-medium hover:opacity-90 transition">
                        Регистрация
                    </a>
                @endguest
                <a href="{{ route('cart.index') }}" class="relative px-4 py-2 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 transition">
                Корзина
                @php $cartCount = app(\App\Services\CartService::class)->count(); @endphp
                @if ($cartCount > 0)
                    <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center">
                        {{ $cartCount }}
                    </span>
                @endif
                </a>
            </div>
        </div>
    </header>

    @if (Breadcrumbs::exists())
        {!! Breadcrumbs::render() !!}
    @endif

    @if (session('success'))
        <div class="max-w-7xl mx-auto px-4 mt-4 w-full">
            <div class="bg-green-100 dark:bg-green-900/40 border border-green-300 dark:border-green-700 text-green-800 dark:text-green-300 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        </div>
    @endif
    @if (session('error'))
        <div class="max-w-7xl mx-auto px-4 mt-4 w-full">
            <div class="bg-red-100 dark:bg-red-900/40 border border-red-300 dark:border-red-700 text-red-800 dark:text-red-300 px-4 py-3 rounded-lg">
                {{ session('error') }}
            </div>
        </div>
    @endif

    <main class="max-w-7xl mx-auto px-4 py-8 w-full flex-1">
        @yield('content')
    </main>

    <footer class="bg-white dark:bg-gray-800 border-t dark:border-gray-700 mt-auto">
        <div class="max-w-7xl mx-auto px-4 py-6 text-center text-gray-500 dark:text-gray-400 text-sm">
            © {{ date('Y') }} Shop — демонстрационный интернет-магазин на Laravel
        </div>
    </footer>

    <script>
        document.getElementById('theme-toggle')?.addEventListener('click', function () {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        });
    </script>
</body>
</html>
