<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Денежный каст: в БД сумма хранится в копейках (int),
 * в приложении доступна как рубли (float с двумя знаками).
 */
class MoneyCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): float
    {
        return round(((int) $value) / 100, 2);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): int
    {
        $normalized = str_replace([' ', ','], ['', '.'], (string) $value);

        return (int) round(((float) $normalized) * 100);
    }
}
