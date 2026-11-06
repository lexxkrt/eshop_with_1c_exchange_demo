<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Services\Catalog\ProductFilter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Страница категории: товары категории и всех её подкатегорий.
     *
     * Сайдбар содержит фасетный фильтр по производителям — только
     * бренды, представленные в текущем отборе (категория + цена),
     * с количеством товаров. Выбранный бренд передаётся
     * query-параметром brand.
     */
    public function show(Request $request, string $slug): View
    {
        $category = Category::query()
            ->where('slug', $slug)
            ->with('children')
            ->firstOrFail();

        // Базовый отбор: категория (с подкатегориями) + цена.
        $base = ProductFilter::baseQuery()
            ->whereIn('category_id', $category->descendantIds());

        ProductFilter::applyPriceRange($base, $request);

        // Фасеты: какие бренды есть в отборе, и сколько товаров.
        $brands = ProductFilter::brandsInSelection($base);

        // Применяем выбранный бренд к основному отбору.
        $query = (clone $base)
            ->when($request->filled('brand'), function ($q) use ($request) {
                $brand = Brand::query()->where('slug', $request->brand)->first();

                $q->where('brand_id', $brand?->id ?? 0);
            });

        ProductFilter::applySort($query, $request);

        return view('categories.show', [
            'category' => $category,
            'products' => ProductFilter::paginate($query),
            'brands' => $brands,
        ]);
    }
}
