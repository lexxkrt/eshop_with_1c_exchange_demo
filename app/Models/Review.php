<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Отзыв на товар: автор, оценка от 1 до 5, текст.
 */
#[Fillable([
    'product_id',
    'author_name',
    'rating',
    'comment',
])]
class Review extends Model
{
    public const MIN_RATING = 1;

    public const MAX_RATING = 5;

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
