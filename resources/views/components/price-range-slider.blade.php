@props([
    'min' => 0,
    'max' => 100,
    'minValue' => null,
    'maxValue' => null,
])

@php
    $min = (int) floor((float) $min);
    $max = (int) ceil((float) $max);
    $minValue = (int) max($minValue ?? $min, $min);
    $maxValue = (int) min($maxValue ?? $max, $max);

    $rangeInputClasses = 'absolute w-full h-6 appearance-none bg-transparent pointer-events-none ' .
        '[&::-webkit-slider-thumb]:pointer-events-auto [&::-webkit-slider-thumb]:appearance-none ' .
        '[&::-webkit-slider-thumb]:w-4 [&::-webkit-slider-thumb]:h-4 [&::-webkit-slider-thumb]:rounded-full ' .
        '[&::-webkit-slider-thumb]:bg-brand [&::-webkit-slider-thumb]:cursor-pointer [&::-webkit-slider-thumb]:border-0 ' .
        '[&::-moz-range-thumb]:pointer-events-auto [&::-moz-range-thumb]:appearance-none ' .
        '[&::-moz-range-thumb]:w-4 [&::-moz-range-thumb]:h-4 [&::-moz-range-thumb]:rounded-full ' .
        '[&::-moz-range-thumb]:bg-brand [&::-moz-range-thumb]:cursor-pointer [&::-moz-range-thumb]:border-0';

    $numberInputClasses = 'w-full min-w-0 px-2 py-1 border border-gray-300 dark:border-gray-600 ' .
        'bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 rounded text-sm ' .
        'focus:outline-none focus:ring-2 focus:ring-brand/40';
@endphp

<div x-data="{
        min: {{ $min }},
        max: {{ $max }},
        valueMin: {{ $minValue }},
        valueMax: {{ $maxValue }},
        apply() {
            this.$wire.set('minPrice', this.valueMin > this.min ? this.valueMin : null);
            this.$wire.set('maxPrice', this.valueMax < this.max ? this.valueMax : null);
        },
        reset() {
            this.valueMin = this.min;
            this.valueMax = this.max;
            this.apply();
        }
    }">
    <div class="relative h-6 flex items-center">
        {{-- Трек --}}
        <div class="absolute w-full h-1 rounded bg-gray-200 dark:bg-gray-700"></div>

        {{-- Выбранный диапазон --}}
        <div class="absolute h-1 rounded bg-brand"
             :style="'left: ' + ((valueMin - min) / ((max - min) || 1) * 100) + '%; width: ' + ((valueMax - valueMin) / ((max - min) || 1) * 100) + '%'"></div>

        {{-- Ползунок «от» --}}
        <input type="range" :min="min" :max="max" step="1" x-model.number="valueMin"
               x-on:input="if (valueMin > valueMax) valueMin = valueMax"
               x-on:change="apply()"
               class="{{ $rangeInputClasses }}">
        {{-- Ползунок «до» --}}
        <input type="range" :min="min" :max="max" step="1" x-model.number="valueMax"
               x-on:input="if (valueMax < valueMin) valueMax = valueMin"
               x-on:change="apply()"
               class="{{ $rangeInputClasses }}">
    </div>

    <div class="flex items-center gap-2 mt-3">
        <input type="number" x-model.number="valueMin" x-on:change="apply()" placeholder="{{ $min }}"
               aria-label="Цена от"
               class="{{ $numberInputClasses }}">
        <span class="text-gray-400 dark:text-gray-500">—</span>
        <input type="number" x-model.number="valueMax" x-on:change="apply()" placeholder="{{ $max }}"
               aria-label="Цена до"
               class="{{ $numberInputClasses }}">
    </div>

    <button type="button" x-on:click="reset()"
            class="mt-3 w-full text-sm text-brand dark:text-sky-300 hover:underline cursor-pointer">
        Сбросить
    </button>
</div>
