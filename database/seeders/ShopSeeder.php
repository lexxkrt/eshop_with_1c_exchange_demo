<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ShopSeeder extends Seeder
{
    public function run(): void
    {
        $brands = collect(['Apple', 'Samsung', 'Google', 'Xiaomi', 'Lenovo', 'ASUS', 'Dell', 'Sony', 'JBL', 'Huawei', 'Honor', 'Anker', 'LG', 'Philips'])
            ->mapWithKeys(fn (string $name) => [
                $name => Brand::create(['name' => $name]),
            ]);

        /*
         * Структура: категория => [позиция, подкатегории, товары].
         * Товар: [название, бренд, цена (руб.), остаток, описание].
         */
        $tree = [
            'Смартфоны' => [
                'position' => 1,
                'subs' => [
                    'Флагманы' => [
                        ['iPhone 15 Pro', 'Apple', 8999.90, 10, 'Флагманский смартфон Apple с титановым корпусом, чипом A17 Pro и камерой 48 Мп.'],
                        ['Samsung Galaxy S24 Ultra', 'Samsung', 11999.90, 6, 'Топовый Samsung с S Pen, камерой 200 Мп и ИИ-функциями Galaxy AI.'],
                        ['Google Pixel 8 Pro', 'Google', 8499.90, 8, 'Флагман Google с лучшей камерой на Android и 7 годами обновлений.'],
                        ['Xiaomi 14 Ultra', 'Xiaomi', 8999.90, 5, 'Камерофон с оптикой Leica и процессором Snapdragon 8 Gen 3.'],
                    ],
                    'Бюджетные' => [
                        ['Xiaomi Redmi Note 13', 'Xiaomi', 1799.90, 40, 'Народный смартфон с AMOLED-экраном 120 Гц и камерой 108 Мп.'],
                        ['Samsung Galaxy A55', 'Samsung', 2499.90, 35, 'Стильный средний класс с металлическим корпусом и долгой батареей.'],
                        ['Honor Magic 6 Lite', 'Honor', 1999.90, 25, 'Бюджетник с изогнутым AMOLED-дисплеем и ёмкой батареей.'],
                        ['Google Pixel 8a', 'Google', 3999.90, 15, 'Компактный смартфон с флагманским чипом Tensor G3.'],
                    ],
                ],
            ],
            'Ноутбуки' => [
                'position' => 2,
                'subs' => [
                    'Игровые' => [
                        ['ASUS ROG Zephyrus G14', 'ASUS', 14999.90, 5, 'Игровой ноутбук с RTX 4070 и дисплеем 240 Гц.'],
                        ['Lenovo Legion Pro 5', 'Lenovo', 15999.90, 4, 'Мощный игровой ноутбук с RTX 4080 и системой охлаждения ColdFront.'],
                        ['ASUS TUF Gaming F15', 'ASUS', 8999.90, 10, 'Надёжный игровой ноутбук с защитой MIL-STD и RTX 4060.'],
                    ],
                    'Ультрабуки' => [
                        ['MacBook Air 13 M3', 'Apple', 12999.90, 12, 'Лёгкий ноутбук Apple с чипом M3, до 18 часов работы от батареи.'],
                        ['Dell XPS 15', 'Dell', 13499.90, 6, 'Премиальный ноутбук с 4K-дисплеем InfinityEdge.'],
                        ['Lenovo ThinkPad X1 Carbon', 'Lenovo', 11999.90, 7, 'Легендарный бизнес-ноутбук с лучшей клавиатурой и защитой MIL-STD.'],
                        ['MacBook Pro 14 M3 Pro', 'Apple', 19999.90, 4, 'Профессиональный ноутбук с чипом M3 Pro и дисплеем Liquid Retina XDR.'],
                    ],
                ],
            ],
            'Планшеты' => [
                'position' => 3,
                'subs' => [
                    'На iOS и iPadOS' => [
                        ['iPad Air M2', 'Apple', 5999.90, 14, 'Универсальный планшет Apple с чипом M2 и поддержкой Apple Pencil Pro.'],
                        ['iPad Pro 11 M4', 'Apple', 9999.90, 6, 'Тончайший планшет Apple с чипом M4 и дисплеем Ultra Retina XDR.'],
                        ['iPad 10', 'Apple', 3399.90, 20, 'Базовый iPad для учёбы и развлечений с дисплеем 10,9".'],
                    ],
                    'На Android' => [
                        ['Samsung Galaxy Tab S9', 'Samsung', 6499.90, 9, 'Флагманский планшет с AMOLED-экраном и S Pen в комплекте.'],
                        ['Xiaomi Pad 6', 'Xiaomi', 2499.90, 35, 'Доступный планшет с дисплеем 144 Гц и большой батареей.'],
                        ['Honor Pad 9', 'Honor', 1999.90, 18, 'Планшет с экраном 12,1" и 8 динамиками.'],
                    ],
                ],
            ],
            'Наушники' => [
                'position' => 4,
                'subs' => [
                    'Накладные' => [
                        ['Sony WH-1000XM5', 'Sony', 2999.90, 25, 'Беспроводные наушники с лидирующим шумоподавлением на рынке.'],
                        ['Apple AirPods Max', 'Apple', 4499.90, 8, 'Премиальные накладные наушники Apple с пространственным звуком.'],
                        ['JBL Tune 770NC', 'JBL', 999.90, 30, 'Доступные накладные наушники с адаптивным шумоподавлением.'],
                    ],
                    'TWS' => [
                        ['Apple AirPods Pro 2', 'Apple', 2199.90, 30, 'Наушники с адаптивным шумоподавлением и аудио на базе H2.'],
                        ['Samsung Galaxy Buds 3', 'Samsung', 1299.90, 40, 'Лёгкие TWS-наушники с активным шумоподавлением.'],
                        ['Xiaomi Redmi Buds 5', 'Xiaomi', 349.90, 60, 'Бюджетные TWS-наушники до 40 часов автономности.'],
                        ['Huawei FreeBuds Pro 3', 'Huawei', 1499.90, 20, 'Флагманские TWS с технологией Dual Driver.'],
                    ],
                ],
            ],
            'Умные часы' => [
                'position' => 5,
                'subs' => [
                    'Смарт-часы' => [
                        ['Apple Watch Series 9', 'Apple', 3499.90, 15, 'Умные часы Apple с дисплеем Always-On и жестом Double Tap.'],
                        ['Samsung Galaxy Watch 6', 'Samsung', 2299.90, 12, 'Часы с датчиками здоровья и интерфейсом Wear OS.'],
                        ['Huawei Watch GT 4', 'Huawei', 1499.90, 18, 'Часы с до 14 дней автономности и 100+ режимами тренировок.'],
                    ],
                    'Фитнес-браслеты' => [
                        ['Xiaomi Smart Band 8', 'Xiaomi', 399.90, 50, 'Популярный браслет с 150+ спортивными режимами.'],
                        ['Honor Band 9', 'Honor', 299.90, 45, 'Лёгкий браслет с AMOLED-экраном и отслеживанием сна.'],
                    ],
                ],
            ],
            'Аксессуары' => [
                'position' => 6,
                'subs' => [
                    'Зарядные устройства' => [
                        ['Anker 737 Power Bank 24000', 'Anker', 1299.90, 20, 'Мощный повербанк с зарядкой 140 Вт и дисплеем.'],
                        ['Apple 20W USB-C Adapter', 'Apple', 199.90, 80, 'Компактный адаптер быстрой зарядки от Apple.'],
                        ['Anker 65W GaN Charger', 'Anker', 499.90, 35, 'Зарядка GaN II с тремя портами для ноутбука и телефона.'],
                    ],
                    'Чехлы и защита' => [
                        ['Apple Silicone Case для iPhone 15', 'Apple', 449.90, 40, 'Оригинальный силиконовый чехол с мягкой подкладкой.'],
                        ['Samsung Clear Gadget Case', 'Samsung', 349.90, 25, 'Прозрачный чехол с креплениями для гаджетов.'],
                    ],
                ],
            ],
            'ТВ и медиа' => [
                'position' => 7,
                'subs' => [
                    'Телевизоры' => [
                        ['LG OLED C4 55"', 'LG', 7999.90, 5, 'OLED-телевизор с идеальным чёрным и частотой 144 Гц.'],
                        ['Samsung QLED Q80D 50"', 'Samsung', 5499.90, 7, 'QLED-телевизор с ИИ-процессором и Dolby Atmos.'],
                        ['Philips 55OLED809', 'Philips', 6499.90, 4, 'OLED-телевизор с уникальной подсветкой Ambilight.'],
                    ],
                    'Приставки' => [
                        ['Apple TV 4K', 'Apple', 1499.90, 15, 'Медиаприставка с чипом A15 и поддержкой 4K HDR.'],
                        ['Xiaomi TV Box S', 'Xiaomi', 449.90, 30, 'Доступная Android TV-приставка с 4K.'],
                    ],
                ],
            ],
        ];

        foreach ($tree as $categoryName => $categoryData) {
            $category = Category::create([
                'name' => $categoryName,
                'position' => $categoryData['position'],
            ]);

            foreach ($categoryData['subs'] as $subName => $products) {
                $sub = Category::create([
                    'name' => $subName,
                    'parent_id' => $category->id,
                    'position' => array_key_first($categoryData['subs']) === $subName ? 1 : 2,
                ]);

                foreach ($products as $index => [$name, $brandName, $price, $stock, $description]) {
                    Product::create([
                        'category_id' => $sub->id,
                        'brand_id' => $brands[$brandName]->id,
                        'name' => $name,
                        'sku' => 'SKU-' . strtoupper(Str::slug($name)),
                        'description' => $description,
                        'price' => $price,
                        'stock' => $stock,
                        'position' => $index + 1,
                        'status' => true,
                    ]);
                }
            }
        }
    }
}
