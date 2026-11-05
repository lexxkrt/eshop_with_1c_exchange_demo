<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Property;
use Illuminate\Database\Seeder;

/**
 * Тестовые данные свойств товаров: свойства, справочники значений
 * и привязка значений к конкретным товарам.
 */
class PropertySeeder extends Seeder
{
    public function run(): void
    {
        $properties = $this->createProperties();
        $this->attachToProducts($properties);
    }

    /**
     * Создаёт свойства и варианты значений справочников.
     *
     * @return array<string, Property>
     */
    private function createProperties(): array
    {
        $definitions = [
            'screen' => [
                'name' => 'Экран',
                'unit' => 'дюйм',
                'type' => 'Число',
            ],
            'memory' => [
                'name' => 'Встроенная память',
                'unit' => 'ГБ',
                'type' => 'Число',
            ],
            'processor' => [
                'name' => 'Процессор',
                'type' => 'Строка',
            ],
            'color' => [
                'name' => 'Цвет',
                'type' => Property::TYPE_DICTIONARY,
                'values' => ['Чёрный', 'Белый', 'Серебристый', 'Синий'],
            ],
            'material' => [
                'name' => 'Материал корпуса',
                'type' => Property::TYPE_DICTIONARY,
                'values' => ['Титан', 'Алюминий', 'Пластик', 'Силикон'],
            ],
            'connectivity' => [
                'name' => 'Тип подключения',
                'type' => Property::TYPE_DICTIONARY,
                'values' => ['Проводные', 'Беспроводные'],
            ],
            'anc' => [
                'name' => 'Шумоподавление',
                'type' => Property::TYPE_DICTIONARY,
                'values' => ['Есть', 'Нет'],
            ],
            'runtime' => [
                'name' => 'Время работы',
                'unit' => 'ч',
                'type' => 'Число',
            ],
            'battery' => [
                'name' => 'Ёмкость аккумулятора',
                'unit' => 'мА·ч',
                'type' => 'Число',
            ],
            'power' => [
                'name' => 'Мощность',
                'unit' => 'Вт',
                'type' => 'Число',
            ],
        ];

        $properties = [];

        foreach ($definitions as $slug => $definition) {
            $values = $definition['values'] ?? [];
            unset($definition['values']);

            $property = Property::create($definition + ['position' => count($properties) + 1]);

            foreach ($values as $index => $value) {
                $property->values()->create([
                    'value' => $value,
                    'position' => $index + 1,
                ]);
            }

            $properties[$slug] = $property;
        }

        return $properties;
    }

    /**
     * Привязывает значения свойств к товарам по названию.
     *
     * @param array<string, Property> $properties
     */
    private function attachToProducts(array $properties): void
    {
        $map = [
            'iPhone 15 Pro' => ['screen' => '6,1', 'memory' => '256', 'processor' => 'Apple A17 Pro', 'material' => 'Титан', 'color' => 'Чёрный'],
            'Samsung Galaxy S24 Ultra' => ['screen' => '6,8', 'memory' => '512', 'processor' => 'Snapdragon 8 Gen 3', 'material' => 'Титан', 'color' => 'Серебристый'],
            'Google Pixel 8 Pro' => ['screen' => '6,7', 'memory' => '256', 'processor' => 'Google Tensor G3', 'material' => 'Алюминий', 'color' => 'Синий'],
            'Xiaomi 14 Ultra' => ['screen' => '6,7', 'memory' => '512', 'processor' => 'Snapdragon 8 Gen 3', 'material' => 'Алюминий', 'color' => 'Чёрный'],

            'Xiaomi Redmi Note 13' => ['screen' => '6,7', 'memory' => '256', 'processor' => 'Snapdragon 685', 'material' => 'Пластик', 'color' => 'Синий'],
            'Samsung Galaxy A55' => ['screen' => '6,6', 'memory' => '256', 'processor' => 'Exynos 1480', 'material' => 'Алюминий', 'color' => 'Чёрный'],
            'Honor Magic 6 Lite' => ['screen' => '6,8', 'memory' => '256', 'processor' => 'Snapdragon 6 Gen 1', 'material' => 'Пластик', 'color' => 'Серебристый'],
            'Google Pixel 8a' => ['screen' => '6,1', 'memory' => '128', 'processor' => 'Google Tensor G3', 'material' => 'Пластик', 'color' => 'Белый'],

            'ASUS ROG Zephyrus G14' => ['screen' => '14', 'memory' => '1024', 'processor' => 'AMD Ryzen 9 8945HS'],
            'Lenovo Legion Pro 5' => ['screen' => '16', 'memory' => '1024', 'processor' => 'Intel Core i9-14900HX'],
            'ASUS TUF Gaming F15' => ['screen' => '15,6', 'memory' => '512', 'processor' => 'Intel Core i5-12500H'],
            'MacBook Air 13 M3' => ['screen' => '13,6', 'memory' => '512', 'processor' => 'Apple M3', 'material' => 'Алюминий', 'color' => 'Серебристый'],
            'Dell XPS 15' => ['screen' => '15,6', 'memory' => '1024', 'processor' => 'Intel Core i7-13700H'],
            'Lenovo ThinkPad X1 Carbon' => ['screen' => '14', 'memory' => '512', 'processor' => 'Intel Core Ultra 7 155H'],
            'MacBook Pro 14 M3 Pro' => ['screen' => '14,2', 'memory' => '1024', 'processor' => 'Apple M3 Pro', 'material' => 'Алюминий', 'color' => 'Чёрный'],

            'iPad Air M2' => ['screen' => '11', 'memory' => '128', 'processor' => 'Apple M2', 'material' => 'Алюминий', 'color' => 'Синий'],
            'iPad Pro 11 M4' => ['screen' => '11', 'memory' => '256', 'processor' => 'Apple M4', 'material' => 'Алюминий', 'color' => 'Чёрный'],
            'iPad 10' => ['screen' => '10,9', 'memory' => '64', 'processor' => 'Apple A14 Bionic', 'color' => 'Синий'],
            'Samsung Galaxy Tab S9' => ['screen' => '11', 'memory' => '256', 'processor' => 'Snapdragon 8 Gen 2', 'color' => 'Серебристый'],
            'Xiaomi Pad 6' => ['screen' => '11', 'memory' => '128', 'processor' => 'Snapdragon 870', 'color' => 'Чёрный'],
            'Honor Pad 9' => ['screen' => '12,1', 'memory' => '256', 'processor' => 'Snapdragon 6 Gen 1', 'color' => 'Серебристый'],

            'Sony WH-1000XM5' => ['connectivity' => 'Беспроводные', 'anc' => 'Есть', 'runtime' => '30'],
            'Apple AirPods Max' => ['connectivity' => 'Беспроводные', 'anc' => 'Есть', 'runtime' => '20', 'color' => 'Серебристый'],
            'JBL Tune 770NC' => ['connectivity' => 'Беспроводные', 'anc' => 'Есть', 'runtime' => '70', 'color' => 'Чёрный'],
            'Apple AirPods Pro 2' => ['connectivity' => 'Беспроводные', 'anc' => 'Есть', 'runtime' => '6', 'color' => 'Белый'],
            'Samsung Galaxy Buds 3' => ['connectivity' => 'Беспроводные', 'anc' => 'Есть', 'runtime' => '6', 'color' => 'Серебристый'],
            'Xiaomi Redmi Buds 5' => ['connectivity' => 'Беспроводные', 'anc' => 'Есть', 'runtime' => '10', 'color' => 'Чёрный'],
            'Huawei FreeBuds Pro 3' => ['connectivity' => 'Беспроводные', 'anc' => 'Есть', 'runtime' => '6,5', 'color' => 'Белый'],

            'Apple Watch Series 9' => ['screen' => '1,9', 'battery' => '308', 'material' => 'Алюминий', 'color' => 'Чёрный'],
            'Samsung Galaxy Watch 6' => ['screen' => '1,5', 'battery' => '300', 'material' => 'Алюминий', 'color' => 'Серебристый'],
            'Huawei Watch GT 4' => ['screen' => '1,43', 'battery' => '524', 'runtime' => '336', 'material' => 'Пластик', 'color' => 'Синий'],
            'Xiaomi Smart Band 8' => ['screen' => '1,62', 'battery' => '190', 'runtime' => '336', 'color' => 'Чёрный'],
            'Honor Band 9' => ['screen' => '1,47', 'battery' => '180', 'runtime' => '336', 'color' => 'Серебристый'],

            'Anker 737 Power Bank 24000' => ['battery' => '24000', 'power' => '140', 'color' => 'Чёрный'],
            'Apple 20W USB-C Adapter' => ['power' => '20', 'color' => 'Белый'],
            'Anker 65W GaN Charger' => ['power' => '65', 'color' => 'Чёрный'],
            'Apple Silicone Case для iPhone 15' => ['material' => 'Силикон', 'color' => 'Чёрный'],
            'Samsung Clear Gadget Case' => ['material' => 'Пластик', 'color' => 'Чёрный'],

            'LG OLED C4 55"' => ['screen' => '55', 'processor' => 'α9 AI Processor 4K Gen7'],
            'Samsung QLED Q80D 50"' => ['screen' => '50', 'processor' => 'Quantum Processor 4K'],
            'Philips 55OLED809' => ['screen' => '55', 'processor' => 'P5 Perfect Experience'],
            'Apple TV 4K' => ['processor' => 'Apple A15 Bionic', 'memory' => '64'],
            'Xiaomi TV Box S' => ['processor' => 'Amlogic S905Y4', 'memory' => '16'],
        ];

        foreach ($map as $productName => $propertyValues) {
            $product = Product::where('name', $productName)->first();

            if ($product === null) {
                continue;
            }

            $position = 0;

            foreach ($propertyValues as $slug => $value) {
                if (! isset($properties[$slug])) {
                    continue;
                }

                $property = $properties[$slug];

                // Значение храним вместе с единицей измерения: «6,1 дюйм», «256 ГБ».
                $storedValue = $property->unit !== null ? $value . ' ' . $property->unit : $value;

                $product->properties()->attach($property->id, [
                    'value' => $storedValue,
                    'position' => ++$position,
                ]);
            }
        }
    }
}
