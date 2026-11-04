<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::active()
            ->ordered()
            ->with(['category', 'brand'])
            ->when($request->filled('category'), function ($query) use ($request) {
                $category = Category::where('slug', $request->category)
                    ->with('children')
                    ->first();

                $query->whereIn('category_id', $category?->descendantIds() ?? [0]);
            })
            ->when($request->filled('brand'), function ($query) use ($request) {
                $query->whereHas('brand', fn ($q) => $q->where('slug', $request->brand));
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%');
            })
            ->when($request->input('sort') === 'price_asc', fn ($q) => $q->reorder('price')->orderBy('position'))
            ->when($request->input('sort') === 'price_desc', fn ($q) => $q->reorder('price', 'desc')->orderBy('position'))
            ->when($request->input('sort') === 'name', fn ($q) => $q->reorder('name')->orderBy('position'))
            ->paginate(12)
            ->withQueryString();

        return view('catalog.index', [
            'products' => $products,
            'brands' => Brand::withCount('products')->orderBy('name')->get(),
        ]);
    }

    public function show(string $slug): View
    {
        $product = Product::active()
            ->with(['category', 'brand'])
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
