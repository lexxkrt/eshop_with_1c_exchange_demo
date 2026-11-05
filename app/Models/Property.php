<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'slug',
    'unit',
    'type',
    'position',
    'status',
])]
class Property extends Model
{
    use HasSlug;
    use HasUuid;

    public const TYPE_DICTIONARY = 'Справочник';

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'status' => 'boolean',
        ];
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->withPivot('value', 'position');
    }

    public function values(): HasMany
    {
        return $this->hasMany(PropertyValue::class)
            ->orderBy('position')
            ->orderBy('id');
    }

    /**
     * Значение из справочника по GUID варианта из 1С (ИдЗначения).
     */
    public function dictionaryValue(string $uuid): ?string
    {
        return $this->values()->where('uuid', $uuid)->value('value');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position')->orderBy('name');
    }
}
