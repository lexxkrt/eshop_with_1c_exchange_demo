# Changelog

## [Unreleased] — 2026-11-04

### Миграции и структура БД

- **Консолидация миграций**: промежуточные миграции `2026_01_01_000005_add_1c_exchange_fields.php` и `2026_01_01_000006_rename_external_id_to_uuid_add_sku.php` удалены; их изменения влиты в миграции создания таблиц.
- **Переупорядочивание миграций**: таблица `brands` теперь создаётся раньше `products` (`000002_create_brands_table` → `000003_create_products_table` → `000004_create_orders_table`), что позволило объявить FK при создании таблиц.
- **`products`**: поле `brand_id` перенесено в миграцию создания таблицы (`foreignId('brand_id')->nullable()->constrained()->nullOnDelete()`); добавлены `uuid` (GUID из 1С, unique) и `sku` (артикул, unique).
- **`categories`**: добавлены `uuid` (GUID из 1С, unique), `image` (string, nullable), `status` (boolean, default `true`).
- **`brands`**: добавлены `uuid` (GUID из 1С, unique), `image` (string, nullable), `status` (boolean, default `true`), `position` (unsigned int, default `0`).
- **`orders`**: добавлены `external_id` (GUID заказа из 1С, unique) и `exported_at` (дата выгрузки заказа в 1С).

### Модели

- **Новый trait [`HasSlug`](app/Models/Concerns/HasSlug.php)**: автоматическая генерация уникального slug из поля `name` на событии `saving`; при коллизии добавляется числовой суффикс (`-2`, `-3`, ...). Подключён к `Category`, `Brand`, `Product`.
- **Trait `HasUuid`** используется во всех моделях с `uuid`; UUID генерируется автоматически.
- **`Category`**: добавлены `status` (каст `boolean`) и `image` в fillable.
- **`Brand`**: добавлены `status` (каст `boolean`), `position` (каст `integer`), `image` в fillable.
- **`uuid` и `slug` удалены из fillable** всех моделей — ручное массовое присвоение запрещено (slug генерирует `HasSlug`, uuid — `HasUuid`).
- [`AppServiceProvider`](app/Providers/AppServiceProvider.php): меню категорий вынесено из `View::share` в `View::composer` — запрос выполняется лениво при рендере вида, а не при загрузке приложения (важно для тестов).

### Сервисы

- **[`CommerceMLImporter`](app/Services/OneC/CommerceMLImporter.php)**: удалено ручное присвоение slug и метод `uniqueSlug()` — slug генерируется автоматически трейтом `HasSlug`; для брендов используется `firstOrCreate`.
- **[`OrdersExporter`](app/Services/OneC/OrdersExporter.php)**: выгрузка заказа в 1С использует `product->uuid` вместо удалённого `external_id`.

### Сеялок

- [`ShopSeeder`](database/seeders/ShopSeeder.php): убрано ручное присвоение `slug` у брендов, категорий и товаров — генерируется трейтом.
- [`DatabaseSeeder`](database/seeders/DatabaseSeeder.php): удалён trait `WithoutModelEvents`, из-за которого события моделей (и трейты `HasSlug`/`HasUuid`) не срабатывали при `db:seed`.

### Представления и фронтенд

- **Исправлено отображение цены** на странице успеха заказа ([`checkout/success.blade.php`](resources/views/checkout/success.blade.php)): убрано лишнее деление `subtotal()` на 100 — `MoneyCast` уже возвращает сумму в рублях, из-за деления цена отображалась в 100 раз заниженной.
- **Тёмная тема**: добавлен вариант `@custom-variant dark` в [`app.css`](resources/css/app.css), классы `dark:` во все представления (каталог, корзина, оформление, бренды, крошки, макет), переключатель темы в шапке с сохранением выбора в `localStorage`.

### Тесты

- [`ExampleTest`](tests/Feature/ExampleTest.php): подключён `RefreshDatabase`; добавлен тест, что модели получают `uuid` автоматически, а товар — `sku`.
