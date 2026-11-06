{{-- Фильтр по цене. Подставляет скрытые поля, чтобы не потерять
     активные фильтры (поиск, бренд, сортировку) при отправке формы. --}}
<div>
    <h2 class="font-bold text-lg mb-3">Цена</h2>
    <form method="GET" action="{{ url()->current() }}"
          class="bg-white dark:bg-gray-800 rounded-lg p-4 space-y-3">
        @if (request('q'))<input type="hidden" name="q" value="{{ request('q') }}">@endif
        @if (request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif
        @if (request('brand'))<input type="hidden" name="brand" value="{{ request('brand') }}">@endif
        @if (request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}">@endif

        <div class="flex items-center gap-2">
            <input type="number" name="price_min" min="0" step="0.01" placeholder="От"
                   value="{{ request('price_min') }}"
                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 border px-3 py-2 text-sm">
            <span class="text-gray-400 dark:text-gray-500">—</span>
            <input type="number" name="price_max" min="0" step="0.01" placeholder="До"
                   value="{{ request('price_max') }}"
                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 border px-3 py-2 text-sm">
        </div>

        <div class="flex items-center gap-2">
            <button type="submit"
                    class="flex-1 px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">
                Применить
            </button>
            @if (request('price_min') || request('price_max'))
                <a href="{{ request()->fullUrlWithoutQuery(['price_min', 'price_max', 'page']) }}"
                   class="px-4 py-2 rounded-lg text-sm bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-100">
                    Сбросить
                </a>
            @endif
        </div>
    </form>
</div>
