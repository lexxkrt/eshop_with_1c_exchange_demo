<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Services\Catalog\ProductFilter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    /**
     * Список всех брендов.
     */
    public function index(): View
    {
        return view('brands.index', [
            'brands' => Brand::withCount(['products' => fn ($q) => $q->where('status', true)])
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Страница бренда: товары выбранного бренда.
     */
    public function show(Request $request, string $slug): View
    {
        $brand = Brand::where('slug', $slug)->firstOrFail();

        $query = ProductFilter::baseQuery()
            ->where('brand_id', $brand->id);

        ProductFilter::applyPriceRange($query, $request);
        ProductFilter::applySort($query, $request);

        return view('brands.show', [
            'brand' => $brand,
            'products' => ProductFilter::paginate($query),
            'brands' => Brand::withCount(['products' => fn ($q) => $q->where('status', true)])
                ->orderBy('name')
                ->get(),
        ]);
    }
}
