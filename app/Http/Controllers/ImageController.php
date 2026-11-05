<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Вывод изображения модели (товар, категория, бренд) в заданном размере.
 *
 * Маршрут: /image/{size}/{type}/{model}/{image?}, где
 *  size — small, medium, large или original;
 *  type — тип модели (product, category, brand);
 *  model — слаг модели;
 *  image — имя файла картинки (не задано — основное изображение модели).
 *
 * Файлы картинок лежат на диске local в каталоге config('onec.images_path')
 * в подпапке из первых двух символов слага модели. Выходной формат — WebP.
 * Результат кэшируется на диске public в image-cache/{size}/...
 * (доступен с сайта напрямую по /storage/image-cache/...), поэтому
 * повторные запросы отдаются без пересоздания.
 */
class ImageController extends Controller
{
    /**
     * Пресеты размеров: имя => максимальная сторона, px (null — без ресайза).
     */
    private const SIZES = [
        'small' => 160,
        'medium' => 400,
        'large' => 800,
        'original' => null,
    ];

    /**
     * Поддерживаемые типы моделей с картинками (trait HasImage и slug).
     */
    private const IMAGEABLE = [
        'product' => Product::class,
        'category' => Category::class,
        'brand' => Brand::class,
    ];

    /**
     * Качество кодирования WebP.
     */
    private const WEBP_QUALITY = 85;

    public function __invoke(string $size, string $type, string $model, ?string $image = null): BinaryFileResponse
    {
        abort_unless(array_key_exists($size, self::SIZES), 404);
        abort_unless(array_key_exists($type, self::IMAGEABLE), 404);

        $maxSide = self::SIZES[$size];

        $modelInstance = self::IMAGEABLE[$type]::query()
            ->where('slug', $model)
            ->firstOrFail();

        // basename защищает от выхода за пределы каталога картинок.
        $file = basename($image ?? $modelInstance->image ?? '');

        abort_if($file === '' || $file === '.', 404);

        $disk = Storage::disk('local');
        $sourcePath = config('onec.images_path') . '/' . substr($modelInstance->slug, 0, 2) . '/' . $file;

        abort_unless($disk->exists($sourcePath), 404);

        // Кэш на диске public — файлы доступны с сайта по /storage/image-cache/...
        $cacheDisk = Storage::disk('public');
        $cachePath = 'image-cache/' . $size . '/' . substr($modelInstance->slug, 0, 2)
            . '/' . pathinfo($file, PATHINFO_FILENAME) . '.webp';

        if (! $cacheDisk->exists($cachePath)) {
            $this->convert($disk->path($sourcePath), $cacheDisk->path($cachePath), $maxSide);
        }

        return response()->file($cacheDisk->path($cachePath), ['Cache-Control' => 'public, max-age=86400']);
    }

    /**
     * Вписывает изображение в рамку maxSide x maxSide с сохранением пропорций
     * и сохраняет результат в WebP (для original — только конвертация).
     */
    private function convert(string $sourcePath, string $targetPath, ?int $maxSide): void
    {
        $image = Image::decodePath($sourcePath);

        if ($maxSide !== null) {
            $image->scaleDown($maxSide, $maxSide);
        }

        $directory = dirname($targetPath);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // Расширение .webp целевого файла задаёт выходной формат кодирования.
        $image->save($targetPath, ['quality' => self::WEBP_QUALITY]);
    }
}
