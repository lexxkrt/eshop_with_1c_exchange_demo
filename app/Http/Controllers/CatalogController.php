<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Product;
use App\Services\Catalog\ProductFilter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    /**
     * Основной каталог: все активные товары с фильтрами
     * по бренду и цене, и сортировкой.
     */
    public function index(Request $request): View
    {
        $query = ProductFilter::baseQuery()
            ->when($request->filled('brand'), function ($q) use ($request) {
                $q->whereHas('brand', fn ($b) => $b->where('slug', $request->brand));
            });

        ProductFilter::applyPriceRange($query, $request);
        ProductFilter::applySort($query, $request);

        return view('catalog.index', [
            'products' => ProductFilter::paginate($query),
            'brands' => Brand::withCount('products')->orderBy('name')->get(),
        ]);
    }

    /**
     * Карточка товара.
     */
    public function show(string $slug): View
    {
        $product = Product::active()
            ->with(['category', 'brand', 'images', 'properties', 'reviews'])
            ->where('slug', $slug)
            ->firstOrFail();

        $related = Product::active()
            ->ordered()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->limit(4)
            ->get();

        return view('catalog.show', compact('product', 'related'));
    }
}
