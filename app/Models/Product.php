<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Models\Concerns\HasSlug;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'category_id',
    'brand_id',
    'name',
    'sku',
    'description',
    'price',
    'stock',
    'position',
    'image',
    'status',
])]
class Product extends Model
{
    use HasSlug;
    use HasUuid;

    protected function casts(): array
    {
        return [
            'price' => MoneyCast::class,
            'position' => 'integer',
            'status' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position')->orderBy('name');
    }

    public function priceRub(): string
    {
        return number_format($this->price, 2, ',', ' ') . ' ₽';
    }
}
