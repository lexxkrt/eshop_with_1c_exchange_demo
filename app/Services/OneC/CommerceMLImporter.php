<?php

namespace App\Services\OneC;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use SimpleXMLElement;

/**
 * Импорт файлов CommerceML 2 (import.xml / offers.xml) из 1С.
 *
 * Поддерживается структура:
 *  - Классификатор > Группы (дерево категорий, любая вложенность);
 *  - Каталог > Товары (товары, бренд из узла Изготовитель, картинки — узлы Картинка);
 *  - ПакетПредложений > Предложения (цены и остатки).
 */
class CommerceMLImporter
{
    private int $categoriesImported = 0;

    private int $brandsImported = 0;

    private int $productsImported = 0;

    private int $imagesImported = 0;

    public function importFile(string $path): void
    {
        $xml = simplexml_load_file($path);

        if ($xml === false) {
            throw new \RuntimeException('Не удалось разобрать XML: ' . $path);
        }

        $this->importClassifier($xml->Классификатор ?? null);
        $this->importCatalog($xml->Каталог ?? null);
        $this->importOffers($xml->ПакетПредложений ?? null);
    }

    public function stats(): string
    {
        return "categories: {$this->categoriesImported}, brands: {$this->brandsImported}, products: {$this->productsImported}, images: {$this->imagesImported}";
    }

    private function importClassifier(?SimpleXMLElement $classifier): void
    {
        if ($classifier === null) {
            return;
        }

        foreach ($classifier->Группы?->Группа ?? [] as $group) {
            $this->importGroup($group);
        }
    }

    private function importGroup(SimpleXMLElement $group, ?int $parentId = null): void
    {
        $uuid = (string) ($group->Ид ?? '');
        $name = (string) ($group->Наименование ?? '');

        if ($uuid === '' || $name === '') {
            return;
        }

        // uuid присваивается явно (не через mass assignment — поле убрано из fillable).
        $category = Category::findByUuid($uuid) ?? new Category();

        if (! $category->exists) {
            $category->uuid = $uuid;
            $this->categoriesImported++;
        }

        $category->name = $name;
        $category->parent_id = $parentId;
        $category->save();

        foreach ($group->Группы?->Группа ?? [] as $child) {
            $this->importGroup($child, $category->id);
        }
    }

    private function importCatalog(?SimpleXMLElement $catalog): void
    {
        if ($catalog === null) {
            return;
        }

        foreach ($catalog->Товары?->Товар ?? [] as $item) {
            $externalId = (string) ($item->Ид ?? '');
            $name = (string) ($item->Наименование ?? '');

            if ($externalId === '' || $name === '') {
                continue;
            }

            $category = null;
            $categoryUuid = (string) ($item->Группы?->Ид ?? '');

            if ($categoryUuid !== '') {
                $category = Category::where('uuid', $categoryUuid)->first();
            }

            // Бренд из узла Изготовитель (или Изготовитель/Наименование).
            $brandName = (string) ($item->Изготовитель?->Наименование ?? $item->Изготовитель ?? '');
            $brandId = null;

            if ($brandName !== '') {
                $brand = Brand::firstOrCreate(['name' => $brandName]);

                if ($brand->wasRecentlyCreated) {
                    $this->brandsImported++;
                }

                $brandId = $brand->id;
            }

            // uuid присваивается явно (не через mass assignment — поле убрано из fillable).
            $product = Product::findByUuid($externalId) ?? new Product();

            if (! $product->exists) {
                $product->uuid = $externalId;
            }

            $product->name = $name;
            $product->sku = trim((string) ($item->Артикул ?? '')) ?: $product->sku;
            $product->description = trim((string) ($item->Описание ?? '')) ?: null;
            $product->category_id = $category?->id;
            $product->brand_id = $brandId;
            $product->status = true;
            // Цена придёт позже в ПакетПредложений (offers.xml).
            $product->price ??= 0;
            $product->stock ??= 0;
            $product->save();

            $this->importProductImages($product, $item->Картинка ?? []);

            $this->productsImported++;
        }
    }

    /**
     * Импорт картинок товара из узлов Картинка (относительные пути к файлам).
     *
     * Первая картинка становится основным изображением товара (products.image),
     * остальные сохраняются в product_images. Идемпотентно: изображения,
     * отсутствующие в файле обмена, удаляются; порядок задаётся последовательностью узлов.
     */
    private function importProductImages(Product $product, iterable $nodes): void
    {
        $paths = [];

        foreach ($nodes as $node) {
            $path = trim((string) $node);

            if ($path !== '') {
                $paths[] = $this->importImageFile($product, $path, count($paths));
            }
        }

        $paths = array_values(array_unique($paths));

        // Первая картинка — основное изображение товара.
        $product->image = $paths[0] ?? null;
        $product->save();

        // Остальные картинки — в product_images.
        $rest = array_slice($paths, 1);

        // Удаляем изображения, которых больше нет в файле обмена.
        if ($rest === []) {
            $product->images()->delete();
        } else {
            $product->images()->whereNotIn('path', $rest)->delete();
        }

        foreach ($rest as $index => $path) {
            $wasNew = ! $product->images()->where('path', $path)->exists();

            $product->images()->updateOrCreate(
                ['path' => $path],
                ['position' => $index + 1]
            );

            if ($wasNew) {
                $this->imagesImported++;
            }
        }
    }

    private function importOffers(?SimpleXMLElement $offers): void
    {
        if ($offers === null) {
            return;
        }

        foreach ($offers->Предложения?->Предложение ?? [] as $offer) {
            $productGuid = (string) ($offer->Ид ?? '');

            if ($productGuid === '') {
                continue;
            }

            $product = Product::where('uuid', $productGuid)->first();

            if (! $product) {
                continue;
            }

            // Цена берётся из первого типа цен.
            $price = (float) ($offer->Цены?->Цена?->ЦенаЗаЕдиницу ?? 0);

            if ($price > 0) {
                $product->price = $price;
            }

            // Остаток: Количество или Остатки > Остаток > ... > Количество.
            $quantity = $offer->Количество ?? $offer->Остатки?->Остаток?->Количество ?? null;

            if ($quantity !== null) {
                $product->stock = max(0, (int) round((float) $quantity));
            }

            $product->save();
        }
    }

    /**
     * Переносит файл картинки, выгруженный 1С, из каталога обмена
     * в каталог хранения картинок (config('onec.images_path')).
     *
     * Файл кладётся в подпапку из первых двух символов слага товара
     * и переименовывается в слаг с сохранением расширения:
     * первая картинка — <images_path>/<xx>/<slug>.<ext>,
     * последующие — <images_path>/<xx>/<slug>-2.<ext>, <slug>-3.<ext>...
     * Пути вида import_files/<subdir>/<file> теряют префикс import_files/.
     */
    private function importImageFile(Product $product, string $path, int $index): string
    {
        $prefix = 'import_files';

        if (! str_starts_with($path, $prefix)) {
            return $path;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $extension = $extension !== '' ? '.' . $extension : '';
        $filename = $index === 0
            ? $product->slug . $extension
            : $product->slug . '-' . ($index + 1) . $extension;

        $subdir = substr($product->slug, 0, 2);
        $relativePath = $subdir . '/' . $filename;

        $disk = Storage::disk('local');
        $source = config('onec.storage_path') . '/' . $path;
        $target = config('onec.images_path') . '/' . $relativePath;

        if ($disk->exists($source) && ! $disk->exists($target)) {
            if (! $disk->exists(config('onec.images_path') . '/' . $subdir)) {
                $disk->makeDirectory(config('onec.images_path') . '/' . $subdir);
            }

            $disk->move($source, $target);
        }

        return $target;
    }
}
