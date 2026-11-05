# Changelog

## [Unreleased] — 2026-11-05

### Свойства товаров

- **Модель [`Property`](app/Models/Property.php)** + миграция `properties` (uuid GUID из 1С, name, slug, unit, type — тип значений из 1С, position, status); связь `products()` (belongsToMany) в модели `Property` и `properties()` в модели `Product`.
- **Миграция [`product_property`](database/migrations/2026_01_01_000007_create_product_property_table.php)**: сводная таблица с составным первичным ключом `(product_id, property_id)`, колонкой `value` (значение свойства у товара) и `position` (порядок), без id и timestamps.
- **Модель [`PropertyValue`](app/Models/PropertyValue.php)** + миграция: таблица `property_values` — варианты значений свойств-справочников (property_id FK cascade, uuid — GUID варианта из 1С, value, position; unique `(property_id, uuid)`); связь `values()` в `Property`, хелпер `dictionaryValue(uuid)`.
- **Импорт свойств из 1С** ([`CommerceMLImporter`](app/Services/OneC/CommerceMLImporter.php)):
  - определения свойств из `Классификатор > Свойства > Свойство` (поиск по GUID, обновление наименования и типа);
  - варианты справочников из `ВариантыЗначений > Справочник` (`ИдЗначения`/`Значение`) — идемпотентно, отсутствующие в файле удаляются;
  - значения свойств товара из `ЗначенияСвойств > ЗначенияСвойства` через `sync` pivot; для свойств-справочников GUID варианта заменяется на значение из `property_values`;
  - наименования свойств очищаются от пояснения в скобках в конце («Форма вафель (Вафельницы)» → «Форма вафель»);
  - единица измерения извлекается из наименования после запятой («Мощность, Вт» → name «Мощность», unit «Вт») и дописывается к значению товара («600» → «600 Вт»; кроме флагов «есть»/«нет»);
  - значения-флаги заменяются на читаемые: `true` → «есть», `false` → «нет»;
  - статистика импорта дополнена счётчиком `properties`.
- Разовое исправление существующих данных: 27 наименований очищены от скобок (slug перегенерирован), 23 значения `true` → «есть», unit извлечён и дописан к значениям.
- **Свойства в карточке товара** ([`catalog/show.blade.php`](resources/views/catalog/show.blade.php)): блок «Характеристики» (имя + единица измерения + значение), сортировка по pivot `position`; eager load `properties`/`images` в [`CatalogController::show`](app/Http/Controllers/CatalogController.php).
- Разовое исправление существующих данных: 27 наименований очищены от скобок (slug перегенерирован), 23 значения `true` → «есть».

### Изображения товаров

- **Новый контроллер [`ImageController`](app/Http/Controllers/ImageController.php)** — универсальный вывод изображений моделей (product/category/brand) с изменением размера:
  - маршрут `GET /image/{size}/{type}/{model}/{image?}` (`image.show`), пресеты `size`: `small` (160), `medium` (400), `large` (800), `original` (без ресайза); `type` ограничен `whereIn`;
  - обработка через **Intervention Image** (`intervention/image-laravel`): вписывание с сохранением пропорций, выходной формат — всегда **WebP** (качество 85);
  - кэш отрендеренных файлов на диске `public` (`storage/app/public/image-cache/<size>/...`) — доступен с сайта напрямую по `/storage/image-cache/...`, повторные запросы без пересоздания;
  - защита от выхода за каталог картинок (`basename`), 404 при отсутствии товара/файла, `Cache-Control: public, max-age=86400`.
- Метод `imageUrl(size, path?)` вынесен в trait [`HasImage`](app/Models/Concerns/HasImage.php) и доступен любой модели с трейтом (тип модели в URL определяется автоматически через `class_basename`); для моделей без slug возвращается `null`.

### Отзывы

- **Модель [`Review`](app/Models/Review.php)** + миграция `reviews` (product_id FK cascade, author_name, rating 1–5, comment), константы `MIN_RATING`/`MAX_RATING`; связь `reviews()` в `Product`.
- **[`ReviewController`](app/Http/Controllers/ReviewController.php)**: публикация отзыва (`reviews.store`) с валидацией (имя ≤100, rating integer 1–5, комментарий ≤2000), флеш-сообщение.
- **Блок отзывов в карточке товара** ([`catalog/show.blade.php`](resources/views/catalog/show.blade.php)): список (автор, дата, звёзды, текст), форма «Оставить отзыв» с сохранением `old()`, пустое состояние; заголовок с якорем `#reviews`.
- **Рейтинг в карточке товара**: звёзды по средней оценке + «N отзывов» (русская плюрализация) со ссылкой-якорем на отзывы.
- **Рейтинг в списке товаров** ([`catalog/index.blade.php`](resources/views/catalog/index.blade.php)): звёзды и «4.5 (2)» в карточках, агрегаты `withCount`/`withAvg` одним запросом, ссылка на `#reviews` карточки.

### Blade-компоненты

- Дублирующиеся блоки view вынесены в анонимные компоненты:
  - [`<x-rating>`](resources/views/components/rating.blade.php) — звёздный рейтинг (props: rating, count, href), вложенный [`<x-rating.stars>`](resources/views/components/rating/stars.blade.php) рендерит 5 звёзд;
  - [`<x-product-card>`](resources/views/components/product-card.blade.php) — карточка товара для списков (props: product, imageHeight, showMeta): картинка с заглушкой, мета, название, рейтинг, цена и форма «В корзину»; используется в каталоге и «Похожих товарах».
- `@include` в проекте не используется.

### Авторизация и роли

- **Роли пользователей**: колонка `role` в `users` (`user` / `admin`), константы и метод `isAdmin()` в модели [`User`](app/Models/User.php); роль присваивается явно (вне fillable — по конвенции проекта).
- **[`AuthController`](app/Http/Controllers/AuthController.php)**: регистрация (пароль `Password::min(8)` + подтверждение), вход (`Auth::attempt` + remember), выход (инвалидация сессии).
- **Middleware [`EnsureUserIsAdmin`](app/Http/Middleware/EnsureUserIsAdmin.php)** (алиас `admin`) — 403 для не-администраторов.
- **Панель администратора** (`/admin`, [`Admin/DashboardController`](app/Http/Controllers/Admin/DashboardController.php)): статистика заказов и таблица заказов; доступ `auth` + `admin`.
- **Шапка сайта**: для гостей — «Войти»/«Регистрация», для авторизованных — имя, кнопка «Админ» (для админов) и «Выйти».
- **Сеялка [`AdminUserSeeder`](database/seeders/AdminUserSeeder.php)**: админ по умолчанию (конфигурируется `ADMIN_EMAIL`/`ADMIN_PASSWORD`, по умолчанию `admin@shop.local` / `admin-password`).

### Навигация

- **Хлебные крошки переведены на [diglactic/laravel-breadcrumbs](https://github.com/diglactic/laravel-breadcrumbs)**:
  - определения в [`routes/breadcrumbs.php`](routes/breadcrumbs.php) для всех страниц (главная, каталог с фильтрами категория/бренд/поиск, товар, бренды, корзина, оформление, успех заказа);
  - авто-режим по текущему маршруту в [`layouts/shop.blade.php`](resources/views/layouts/shop.blade.php); ручные массивы `$breadcrumbs` удалены из всех представлений;
  - кастомный Tailwind-вид [`partials/breadcrumbs.blade.php`](resources/views/partials/breadcrumbs.blade.php); на главной странице крошки скрыты.

## [Unreleased] — 2026-11-04 (часть 2)

### Картинки товаров

- **Модель [`ProductImage`](app/Models/ProductImage.php)** + миграция `product_images` (product_id FK cascade, path, position); связь `images()` в модели `Product`.
- **Импорт картинок из 1С** ([`CommerceMLImporter`](app/Services/OneC/CommerceMLImporter.php)):
  - узлы `Картинка` из import.xml; первая картинка становится основным изображением товара (`products.image`), остальные — галереей (`product_images`);
  - синхронизация идемпотентна: изображения, отсутствующие в файле обмена, удаляются;
  - файлы переносятся из каталога обмена (`storage/app/1c/import_files/...`) в каталог из конфига `ONEC_IMAGES_PATH` (по умолчанию `products`), префикс `import_files/` удаляется;
  - файл переименовывается в слаг товара с сохранением расширения: `<slug>.<ext>` — основная, `<slug>-2.<ext>`, `<slug>-3.<ext>`... — галерея;
  - файл кладётся в подпапку из первых двух символов слага: `products/<xx>/<файл>`;
  - статистика импорта дополнена счётчиком `images`.
- **Новый trait [`HasImage`](app/Models/Concerns/HasImage.php)**: при удалении модели удаляются физические файлы картинок; подключён к `Product` (основное изображение + галерея), `ProductImage`, `Category`, `Brand`.

### Исправления

- В [`CommerceMLImporter`](app/Services/OneC/CommerceMLImporter.php) GUID из 1С присваивается явно (`$model->uuid = $guid`), а не через mass assignment (поле `uuid` убрано из fillable) — без этого записи получали случайные uuid и дублировались при повторном импорте.

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
