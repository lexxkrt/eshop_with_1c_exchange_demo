{{-- Заголовок раздела и панель сортировки. Скрытые поля сохраняют
     активные фильтры при смене сортировки. --}}
@props(['heading'])

<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <h1 class="text-xl sm:text-2xl font-bold">{{ $heading }}</h1>

    <form method="GET" class="flex items-center gap-2">
        @if (request('q'))<input type="hidden" name="q" value="{{ request('q') }}">@endif
        @if (request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif
        @if (request('brand'))<input type="hidden" name="brand" value="{{ request('brand') }}">@endif
        @if (request('price_min'))<input type="hidden" name="price_min" value="{{ request('price_min') }}">@endif
        @if (request('price_max'))<input type="hidden" name="price_max" value="{{ request('price_max') }}">@endif

        <select name="sort" onchange="this.form.submit()"
                class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 border px-3 py-2 text-sm">
            <option value="" @selected(!request('sort'))>По умолчанию</option>
            <option value="price_asc" @selected(request('sort') === 'price_asc')>Цена: сначала дешёвые</option>
            <option value="price_desc" @selected(request('sort') === 'price_desc')>Цена: сначала дорогие</option>
            <option value="name" @selected(request('sort') === 'name')>По названию</option>
        </select>
    </form>
</div>
