<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'parent_id', 'position', 'external_id'])]
class Category extends Model
{
    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'parent_id' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('position')->orderBy('name');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function scopeRoot(Builder $query): Builder
    {
        return $query->whereNull('parent_id')->orderBy('position')->orderBy('name');
    }

    /**
     * ID категории и всех её подкатегорий (2 уровня вложенности).
     *
     * @return array<int, int>
     */
    public function descendantIds(): array
    {
        return $this->children->pluck('id')->push($this->id)->all();
    }
}
