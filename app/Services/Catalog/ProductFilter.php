<?php

namespace App\Services\Catalog;

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Общая логика выборки товаров для страниц каталога, категории,
 * бренда и поиска.
 */
class ProductFilter
{
    /**
     * Базовый запрос: активные товары с сортировкой по позиции,
     * связями и агрегатами отзывов.
     */
    public static function baseQuery(): Builder
    {
        return Product::active()
            ->ordered()
            ->with(['category', 'brand'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating');
    }

    /**
     * Применяет фильтр по ценовому диапазону из query-параметров
     * price_min / price_max (значения в рублях).
     */
    public static function applyPriceRange(Builder $query, Request $request): Builder
    {
        $min = $request->filled('price_min') ? self::toKopecks($request->price_min) : null;
        $max = $request->filled('price_max') ? self::toKopecks($request->price_max) : null;

        // Если границы перепутаны местами, меняем их между собой.
        if ($min !== null && $max !== null && $min > $max) {
            [$min, $max] = [$max, $min];
        }

        return $query
            ->when($min !== null, fn ($q) => $q->where('price', '>=', $min))
            ->when($max !== null, fn ($q) => $q->where('price', '<=', $max));
    }

    /**
     * Применяет сортировку из query-параметра sort
     * (price_asc, price_desc, name).
     */
    public static function applySort(Builder $query, Request $request): Builder
    {
        return $query
            ->when($request->input('sort') === 'price_asc', fn ($q) => $q->reorder('price')->orderBy('position'))
            ->when($request->input('sort') === 'price_desc', fn ($q) => $q->reorder('price', 'desc')->orderBy('position'))
            ->when($request->input('sort') === 'name', fn ($q) => $q->reorder('name')->orderBy('position'));
    }

    /**
     * Пагинация с сохранением query-параметров.
     */
    public static function paginate(Builder $query): LengthAwarePaginator
    {
        return $query->paginate(12)->withQueryString();
    }

    /**
     * Считает количество товаров по значению колонки
     * (category_id / brand_id) в рамках заданного отбора.
     *
     * @return Collection<int, int> значение колонки => количество
     */
    public static function facetCounts(Builder $query, string $column): Collection
    {
        return (clone $query)
            ->select($column, DB::raw('count(*) as products_count'))
            ->whereNotNull($column)
            ->groupBy($column)
            ->pluck('products_count', $column);
    }

    /**
     * Бренды, присутствующие в заданном отборе, с количеством
     * товаров каждого бренда (атрибут products_count).
     */
    public static function brandsInSelection(Builder $query): Collection
    {
        $counts = self::facetCounts($query, 'brand_id');

        return Brand::query()
            ->whereIn('id', $counts->keys())
            ->orderBy('name')
            ->get()
            ->each(fn (Brand $brand) => $brand->products_count = $counts[$brand->id]);
    }

    /**
     * Переводит введённую пользователем сумму (в рублях) в копейки,
     * как это делает MoneyCast при записи в БД.
     */
    public static function toKopecks(mixed $value): int
    {
        $normalized = str_replace([' ', ','], ['', '.'], (string) $value);

        return (int) round(max(0, (float) $normalized) * 100);
    }
}
