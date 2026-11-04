<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Автоматическая генерация уникального slug для модели.
 *
 * Ожидается наличие колонки `slug` (unique) в таблице модели.
 * Slug строится из исходного поля (по умолчанию `name`) и генерируется:
 *  - при создании записи, если slug не задан;
 *  - при обновлении, если исходное поле изменилось;
 * при коллизии к slug добавляется числовой суффикс (-2, -3, ...).
 */
trait HasSlug
{
    protected static function bootHasSlug(): void
    {
        static::saving(function ($model) {
            if (empty($model->{$model->slugColumn()}) || $model->isDirty($model->slugSourceColumn())) {
                $model->generateSlug();
            }
        });
    }

    /**
     * Колонка, из которой берётся строка для slug.
     */
    protected function slugSourceColumn(): string
    {
        return 'name';
    }

    /**
     * Колонка, в которую записывается slug.
     */
    protected function slugColumn(): string
    {
        return 'slug';
    }

    /**
     * Генерирует уникальный slug; при коллизии добавляет суффикс.
     */
    public function generateSlug(): void
    {
        $column = $this->slugColumn();

        $base = Str::slug($this->{$this->slugSourceColumn()}) ?: 'n-' . Str::random(8);
        $slug = $base;
        $i = 1;

        while ($this->slugExists($slug)) {
            $slug = $base . '-' . ++$i;
        }

        $this->{$column} = $slug;
    }

    /**
     * Проверка занятости slug другими записями (кроме текущей).
     */
    protected function slugExists(string $slug): bool
    {
        $query = static::query()->where($this->slugColumn(), $slug);

        if ($this->exists) {
            $query->where($this->getKeyName(), '!=', $this->getKey());
        }

        return $query->exists();
    }
}
