<?php

namespace App\Services\OneC;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use SimpleXMLElement;

/**
 * Импорт файлов CommerceML 2 (import.xml / offers.xml) из 1С.
 *
 * Поддерживается структура:
 *  - Классификатор > Группы (дерево категорий, любая вложенность);
 *  - Каталог > Товары (товары, бренд из узла Изготовитель);
 *  - ПакетПредложений > Предложения (цены и остатки).
 */
class CommerceMLImporter
{
    private int $categoriesImported = 0;

    private int $brandsImported = 0;

    private int $productsImported = 0;

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
        return "categories: {$this->categoriesImported}, brands: {$this->brandsImported}, products: {$this->productsImported}";
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

        Category::updateOrCreate(
            ['uuid' => $uuid],
            [
                'name' => $name,
                'parent_id' => $parentId,
            ]
        );

        $this->categoriesImported++;

        $category = Category::where('uuid', $uuid)->first();

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

            $product = Product::firstOrNew(['uuid' => $externalId]);
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

            $this->productsImported++;
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

}
