<?php

namespace App\Services\OneC;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Property;
use Illuminate\Support\Facades\Storage;
use SimpleXMLElement;

/**
 * Импорт файлов CommerceML 2 (import.xml / offers.xml) из 1С.
 *
 * Поддерживается структура:
 *  - Классификатор > Группы (дерево категорий, любая вложенность);
 *  - Классификатор > Свойства > Свойство (определения свойств товаров);
 *  - Каталог > Товары (товары, бренд из реквизита "Производитель" узла
 *    ЗначенияРеквизитов с fallback на узел Изготовитель, картинки — узлы Картинка,
 *    значения свойств — узел ЗначенияСвойств);
 *  - ПакетПредложений > Предложения (цены и остатки).
 */
class CommerceMLImporter
{
    private int $categoriesImported = 0;

    private int $brandsImported = 0;

    private int $propertiesImported = 0;

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
        return "categories: {$this->categoriesImported}, brands: {$this->brandsImported}, properties: {$this->propertiesImported}, products: {$this->productsImported}, images: {$this->imagesImported}";
    }

    private function importClassifier(?SimpleXMLElement $classifier): void
    {
        if ($classifier === null) {
            return;
        }

        foreach ($classifier->Группы?->Группа ?? [] as $group) {
            $this->importGroup($group);
        }

        $this->importPropertyDefinitions($classifier->Свойства?->Свойство ?? []);
    }

    /**
     * Импорт определений свойств из Классификатор > Свойства > Свойство.
     *
     * Свойство ищется по GUID из 1С (Ид); новые свойства создаются,
     * существующие обновляются (наименование, тип). Slug генерируется
     * автоматически (HasSlug). Для свойств-справочников дополнительно
     * импортируются варианты значений (ВариантыЗначений > Справочник).
     */
    private function importPropertyDefinitions(iterable $nodes): void
    {
        foreach ($nodes as $node) {
            $uuid = (string) ($node->Ид ?? '');
            $name = (string) ($node->Наименование ?? '');

            if ($uuid === '' || $name === '') {
                continue;
            }

            // 1С добавляет в конец наименования пояснение в скобках
            // (например, "Форма вафель (Вафельницы)") — убираем его.
            $name = trim((string) preg_replace('/\s*\([^()]*\)\s*$/u', '', $name));

            // uuid присваивается явно (не через mass assignment — поле убрано из fillable).
            $property = Property::findByUuid($uuid) ?? new Property();

            if (! $property->exists) {
                $property->uuid = $uuid;
                $this->propertiesImported++;
            }

            $property->name = $name;
            $property->type = trim((string) ($node->ТипЗначений ?? '')) ?: null;
            $property->status = true;
            $property->save();

            if ($property->type === Property::TYPE_DICTIONARY) {
                $this->importPropertyDictionary($property, $node->ВариантыЗначений?->Справочник ?? []);
            }
        }
    }

    /**
     * Импорт вариантов значений свойства-справочника.
     *
     * Каждый вариант ищется по GUID из 1С (ИдЗначения); новые создаются,
     * существующие обновляются (значение, порядок). Идемпотентно: варианты,
     * отсутствующие в файле обмена, удаляются; порядок задаётся
     * последовательностью узлов.
     */
    private function importPropertyDictionary(Property $property, iterable $options): void
    {
        $uuids = [];

        foreach ($options as $index => $option) {
            $uuid = trim((string) ($option->ИдЗначения ?? ''));
            $value = trim((string) ($option->Значение ?? ''));

            if ($value === '') {
                continue;
            }

            $property->values()->updateOrCreate(
                ['uuid' => $uuid !== '' ? $uuid : null],
                ['value' => $value, 'position' => count($uuids)]
            );

            $uuids[] = $uuid;
        }

        // Удаляем варианты, которых больше нет в файле обмена.
        if ($uuids === []) {
            $property->values()->delete();
        } else {
            $property->values()->whereNotIn('uuid', $uuids)->delete();
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

            // Реквизиты товара из узла ЗначенияРеквизитов > ЗначениеРеквизита.
            $brandName = '';

            foreach ($item->ЗначенияРеквизитов?->ЗначениеРеквизита ?? [] as $rekvit) {
                switch (trim((string) $rekvit->Наименование)) {
                    case 'Производитель':
                        $brandName = trim((string) $rekvit->Значение);
                        break;

                    // Другие реквизиты добавляются здесь новыми case.
                    default:
                        break;
                }
            }

            // Fallback: узел Изготовитель (или Изготовитель/Наименование).
            if ($brandName === '') {
                $brandName = trim((string) ($item->Изготовитель?->Наименование ?? $item->Изготовитель ?? ''));
            }

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

            $this->importProductProperties($product, $item->ЗначенияСвойств?->ЗначенияСвойства ?? []);

            $this->productsImported++;
        }
    }

    /**
     * Импорт значений свойств товара из узла ЗначенияСвойств > ЗначенияСвойства.
     *
     * Каждое значение привязывается к товару через pivot product_property
     * (property_id ищется по GUID из 1С, Ид узла). Для свойств-справочников
     * в Значении приходит GUID варианта (ИдЗначения) — он заменяется
     * на значение из property_values. Идемпотентно: sync заменяет
     * полный набор свойств товара — свойства, отсутствующие в файле обмена,
     * отвязываются; порядок задаётся последовательностью узлов.
     */
    private function importProductProperties(Product $product, iterable $nodes): void
    {
        $values = [];
        $position = 0;

        foreach ($nodes as $node) {
            $propertyUuid = (string) ($node->Ид ?? '');
            $value = trim((string) ($node->Значение ?? ''));

            if ($propertyUuid === '' || $value === '') {
                continue;
            }

            // Значения-флаги из 1С заменяем на читаемые.
            $value = match ($value) {
                'true' => 'есть',
                'false' => 'нет',
                default => $value,
            };

            $property = Property::findByUuid($propertyUuid);

            if (! $property) {
                continue;
            }

            if ($property->type === Property::TYPE_DICTIONARY) {
                // Значение справочника приходит GUID'ом варианта — берём текст из property_values.
                $value = $property->dictionaryValue($value) ?? $value;
            }

            $values[$property->id] = ['value' => $value, 'position' => $position++];
        }

        $product->properties()->sync($values);
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
