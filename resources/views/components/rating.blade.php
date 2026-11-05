@props([
    // Оценка (средняя или конкретного отзыва), от 1 до 5.
    'rating' => 0,
    // Количество отзывов — показывается рядом с оценкой (если задано).
    'count' => null,
    // Ссылка-обёртка (например, на секцию отзывов товара).
    'href' => null,
])

@php $avg = round($rating, 1); @endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400 hover:text-indigo-600 transition']) }}>
        <span class="text-amber-500 tracking-wide" aria-label="Средняя оценка {{ $avg }} из 5">
            <x-rating.stars :avg="$avg" />
        </span>
        @if ($count !== null) {{ $avg }} ({{ $count }}) @endif
    </a>
@else
    <span {{ $attributes->merge(['class' => 'text-amber-500']) }} aria-label="Оценка {{ $avg }} из 5">
        <x-rating.stars :avg="$avg" />
    </span>
@endif
