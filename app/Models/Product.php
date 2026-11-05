<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Models\Concerns\HasImage;
use App\Models\Concerns\HasSlug;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    use HasImage;
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

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('position')->orderBy('id');
    }

    /**
     * Файлы картинок товара: основное изображение и все картинки галереи.
     */
    protected function imageAttributes(): array
    {
        return ['image', ...$this->images->pluck('path')->all()];
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class, 'product_property')
            ->withPivot('value', 'position')
            ->orderBy('product_property.position')
            ->orderBy('name');
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
        return number_format($this->price ?? 0, 2, ',', ' ') . ' ₽';
    }

    /**
     * URL картинки товара через контроллер изображений.
     *
     * $size — пресет из ImageController::SIZES (small, medium, large, original);
     * $path — путь к картинке (основное изображение, если не задан).
     * Выходной формат — WebP.
     */
    public function imageUrl(string $size = 'medium', ?string $path = null): ?string
    {
        $file = $path ?? $this->image;

        if (! $file) {
            return null;
        }

        return route('image.show', [
            'size' => $size,
            'product' => $this->slug,
            'image' => basename($file),
        ]);
    }
}
