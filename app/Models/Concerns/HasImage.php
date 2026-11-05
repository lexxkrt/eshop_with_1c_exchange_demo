<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Работа с картинками модели.
 *
 * Удаление физических файлов картинок при удалении модели: модель задаёт
 * список атрибутов с путями к картинкам (и/или сами пути) методом
 * imageAttributes(). Пути считаются относительными на диске local.
 *
 * URL картинки через контроллер изображений (image.show): модель должна
 * иметь slug; основной атрибут с путём картинки — `image`.
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
     * URL картинки модели через контроллер изображений.
     *
     * $size — пресет из ImageController::SIZES (small, medium, large, original);
     * $path — путь к картинке (основное изображение, если не задан).
     * Выходной формат — WebP.
     */
    public function imageUrl(string $size = 'medium', ?string $path = null): ?string
    {
        $file = $path ?? $this->getAttribute('image');

        if (! $file || ! isset($this->slug)) {
            return null;
        }

        return route('image.show', [
            'size' => $size,
            'type' => Str::snake(class_basename($this)),
            'model' => $this->slug,
            'image' => basename($file),
        ]);
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
