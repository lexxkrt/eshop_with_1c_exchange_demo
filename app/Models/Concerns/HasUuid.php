<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * Добавляет модели UUID-поле.
 *
 * Ожидается наличие колонки `uuid` (unique, nullable) в таблице модели.
 * UUID генерируется автоматически при создании записи, если не задан,
 * что позволяет использовать его как внешний идентификатор (в т.ч. для обмена с 1С).
 */
trait HasUuid
{
    protected static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Резервная генерация uuid прямо перед вставкой — не зависит от событий модели
     * (например, при запуске сеялок через artisan db:seed).
     */
    public function save(array $options = [])
    {
        if (! $this->exists && empty($this->uuid)) {
            $this->uuid = (string) Str::uuid();
        }

        return parent::save($options);
    }

    public function scopeWhereUuid(Builder $query, string $uuid): Builder
    {
        return $query->where('uuid', $uuid);
    }

    public static function findByUuid(string $uuid): ?static
    {
        return static::query()->whereUuid($uuid)->first();
    }
}
