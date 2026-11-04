<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Storage;

/**
 * Удаление физических файлов картинок при удалении модели.
 *
 * Модель задаёт список атрибутов с путями к картинкам (и/или сами пути)
 * методом imageAttributes(). Пути считаются относительными на диске local.
 */
trait HasImage
{
    protected static function bootHasImage(): void
    {
        static::deleting(function ($model) {
            $model->deleteImageFiles();
        });
    }

    /**
     * Атрибуты модели, содержащие пути к файлам картинок,
     * и/или готовые пути к файлам (например, пути картинок из связи).
     */
    protected function imageAttributes(): array
    {
        return ['image'];
    }

    /**
     * Удаляет физические файлы картинок модели с диска.
     */
    public function deleteImageFiles(): void
    {
        $paths = [];

        foreach ($this->imageAttributes() as $item) {
            // Имя атрибута модели — берём значение из атрибута,
            // иначе считаем элемент готовым путём к файлу.
            $value = $this->isFillable($item) || array_key_exists($item, $this->getAttributes())
                ? $this->getRawOriginal($item)
                : $item;

            if (is_string($value) && $value !== '') {
                $paths[] = $value;
            }
        }

        if ($paths !== []) {
            Storage::disk('local')->delete($paths);
        }
    }
}
