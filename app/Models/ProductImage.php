<?php

namespace App\Models;

use App\Models\Concerns\HasImage;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Изображение товара. У товара может быть несколько изображений,
 * порядок отображения задаётся полем position.
 */
#[Fillable(['product_id', 'path', 'position'])]
class ProductImage extends Model
{
    use HasImage;

    protected function casts(): array
    {
        return [
            'position' => 'integer',
        ];
    }

    protected function imageAttributes(): array
    {
        return ['path'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
