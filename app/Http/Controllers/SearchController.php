<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Services\Catalog\ProductFilter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Страница поиска товаров по названию (query-параметр q).
     *
     * Сайдбар содержит фасетные фильтры: категории и бренды,
     * представленные в текущем отборе, с количеством товаров.
     * Выбранная категория/бренд передаются query-параметрами
     * category и brand.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q'));

        // Базовый отбор: поиск по названию + цена (без категорий и брендов).
        $base = ProductFilter::baseQuery()
            ->when($search !== '', fn ($q) => $q->where('name', 'like', '%' . $search . '%'));

        ProductFilter::applyPriceRange($base, $request);

        // Фасеты: какие категории и бренды есть в отборе, и сколько товаров.
        $categoryCounts = ProductFilter::facetCounts($base, 'category_id');

        $categories = Category::query()
            ->whereIn('id', $categoryCounts->keys())
            ->with('parent')
            ->get()
            ->sortBy(fn (Category $c) => [
                $c->parent?->position ?? $c->position,
                $c->parent ? $c->position : 0,
                $c->name,
            ])
            ->values()
            ->each(fn (Category $c) => $c->products_count = $categoryCounts[$c->id]);

        $brands = ProductFilter::brandsInSelection($base);

        // Применяем выбранные категорию и бренд к основному отбору.
        $query = (clone $base)
            ->when($request->filled('category'), function ($q) use ($request) {
                $category = Category::query()
                    ->where('slug', $request->category)
                    ->with('children')
                    ->first();

                $q->whereIn('category_id', $category?->descendantIds() ?? [0]);
            })
            ->when($request->filled('brand'), function ($q) use ($request) {
                $brand = Brand::query()->where('slug', $request->brand)->first();

                $q->where('brand_id', $brand?->id ?? 0);
            });

        ProductFilter::applySort($query, $request);

        return view('search.index', [
            'search' => $search,
            'products' => ProductFilter::paginate($query),
            'categories' => $categories,
            'brands' => $brands,
        ]);
    }
}
