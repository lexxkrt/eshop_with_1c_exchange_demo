<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Интернет-магазин')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col">
    <header class="bg-white shadow sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between gap-4">
            <div class="flex items-center gap-6">
                <a href="{{ route('catalog.index') }}" class="text-2xl font-bold text-indigo-600">🛒 Shop</a>

                {{-- Выпадающее меню категорий --}}
                <nav class="relative group hidden md:block">
                    <button type="button"
                            class="flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700 transition">
                        Каталог
                        <span class="text-xs transition group-hover:rotate-180">▼</span>
                    </button>

                    <div class="invisible opacity-0 group-hover:visible group-hover:opacity-100 transition
                                absolute left-0 top-full pt-2 w-72">
                        <div class="bg-white rounded-xl shadow-xl border border-gray-100 py-2 max-h-[70vh] overflow-y-auto">
                            <a href="{{ route('catalog.index') }}"
                               class="block px-4 py-2 font-semibold hover:bg-indigo-50 hover:text-indigo-600">
                                Все товары
                            </a>
                            @foreach ($menuCategories as $category)
                                <div class="px-4 py-2 hover:bg-indigo-50">
                                    <a href="{{ route('catalog.index', ['category' => $category->slug]) }}"
                                       class="block font-semibold hover:text-indigo-600">
                                        {{ $category->name }}
                                    </a>
                                    @if ($category->children->isNotEmpty())
                                        <div class="mt-1 space-y-1">
                                            @foreach ($category->children as $child)
                                                <a href="{{ route('catalog.index', ['category' => $child->slug]) }}"
                                                   class="block pl-4 text-sm text-gray-500 hover:text-indigo-600">
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
                       class="w-full rounded-lg border-gray-300 border px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </form>

            <div class="flex items-center gap-3">
                <a href="{{ route('brands.index') }}"
                   class="px-4 py-2 rounded-lg border font-medium hover:bg-indigo-50 hover:text-indigo-600 transition">
                    Бренды
                </a>
                <a href="{{ route('cart.index') }}" class="relative px-4 py-2 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700 transition">
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

    @isset($breadcrumbs)
        @include('partials.breadcrumbs')
    @endisset

    @if (session('success'))
        <div class="max-w-7xl mx-auto px-4 mt-4 w-full">
            <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        </div>
    @endif
    @if (session('error'))
        <div class="max-w-7xl mx-auto px-4 mt-4 w-full">
            <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded-lg">
                {{ session('error') }}
            </div>
        </div>
    @endif

    <main class="max-w-7xl mx-auto px-4 py-8 w-full flex-1">
        @yield('content')
    </main>

    <footer class="bg-white border-t mt-auto">
        <div class="max-w-7xl mx-auto px-4 py-6 text-center text-gray-500 text-sm">
            © {{ date('Y') }} Shop — демонстрационный интернет-магазин на Laravel
        </div>
    </footer>
</body>
</html>
