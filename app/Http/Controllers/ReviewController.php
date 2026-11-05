<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'author_name' => ['required', 'string', 'max:100'],
            'rating' => ['required', 'integer', 'min:' . Review::MIN_RATING, 'max:' . Review::MAX_RATING],
            'comment' => ['required', 'string', 'max:2000'],
        ]);

        $product->reviews()->create($validated);

        return back()->with('success', 'Спасибо! Ваш отзыв опубликован.');
    }
}
