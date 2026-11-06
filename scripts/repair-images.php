<?php

/**
 * Одноразовый скрипт восстановления картинок товаров.
 *
 * Проблема: файлы из обмена 1С лежат в 1c/products/<xx>/<guid>_<guid>.jpg,
 * а импортёр искал их в 1c/import_files/... — поэтому файлы не были
 * перенесены в products/<xx>/<slug>.jpg, и DB-пути (products.image,
 * product_images.path) указывают на несуществующие файлы.
 *
 * Первая часть имени файла — GUID товара без дефисов, поэтому файлы
 * сопоставляются с товарами по products.uuid напрямую (import.xml
 * не требуется). Порядок картинок товара: products.image — первая,
 * затем product_images по position; при нескольких файлах источника
 * порядок берётся по имени файла (GUID второй части).
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;
use Illuminate\Support\Facades\Storage;

$disk = Storage::disk('local');
$storagePath = config('onec.storage_path');   // 1c
$imagesPath = config('onec.images_path');     // products

$moved = 0;
$mismatched = [];

foreach (Product::query()->with('images')->cursor() as $product) {
    // Ожидаемые имена файлов в порядке отображения:
    // products.image, затем product_images по position.
    $expected = [$product->image];

    foreach ($product->images->sortBy('position') as $image) {
        $expected[] = $image->path;
    }

    $expected = array_values(array_filter(array_map(
        fn (?string $path) => $path !== null ? basename($path) : null,
        $expected,
    )));

    // Файлы товара в каталоге обмена: <guid-без-дефисов>_*.jpg
    $guidNoDash = str_replace('-', '', $product->uuid);
    $exchangeDir = $storagePath . '/products/' . substr($guidNoDash, 0, 2);
    $sources = [];

    foreach ($disk->files($exchangeDir) as $file) {
        if (str_starts_with(basename($file), $guidNoDash . '_')) {
            $sources[] = $file;
        }
    }

    sort($sources);

    if ($sources === []) {
        continue;
    }

    if (count($sources) !== count($expected)) {
        $mismatched[] = sprintf(
            '%s: файлов %d, ожидается DB %d',
            $product->slug,
            count($sources),
            count($expected),
        );
    }

    // Переносим все найденные файлы; имена задаём по порядку отображения,
    // лишние файлы (если источников больше) получают продолжение нумерации.
    foreach ($sources as $index => $source) {
        $extension = strtolower(pathinfo($source, PATHINFO_EXTENSION));
        $extension = $extension !== '' ? '.' . $extension : '';

        if ($index < count($expected)) {
            // Используем точное имя из DB (сохранив исходное расширение файла).
            $filename = pathinfo($expected[$index], PATHINFO_FILENAME) . $extension;
        } else {
            $filename = $product->slug . '-' . ($index + 1) . $extension;
        }

        $subdir = substr($product->slug, 0, 2);
        $target = $imagesPath . '/' . $subdir . '/' . $filename;

        if ($disk->exists($target)) {
            continue;
        }

        if (! $disk->exists($imagesPath . '/' . $subdir)) {
            $disk->makeDirectory($imagesPath . '/' . $subdir);
        }

        $disk->move($source, $target);
        $moved++;
    }
}

echo "Перенесено файлов: {$moved}\n";

if ($mismatched !== []) {
    echo "Расхождение количества файлов:\n";

    foreach ($mismatched as $line) {
        echo "  {$line}\n";
    }
}
