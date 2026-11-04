# eshop_with_1c_exchange_demo — интернет-магазин на Laravel

Демонстрационный интернет-магазин, созданный на **Laravel 13** и **PHP 8.4** с базой данных **SQLite**.

## Возможности

### Витрина
- 📦 Каталог товаров с фильтрацией по категориям и брендам, поиском, сортировкой (цена, название) и пагинацией
- 🗂️ Иерархия категорий с подкатегориями (дерево любой вложенности)
- 🏷️ Страница брендов со списком и переходом к товарам бренда
- 📄 Карточка товара: описание, цена, остаток на складе, похожие товары
- 🍞 Хлебные крошки на всех страницах, кроме главной
- 🛒 Корзина на сессиях: добавление, изменение количества, удаление, счётчик в шапке
- ✅ Оформление заказа с валидацией и страницей подтверждения; списание остатков со склада
- 🎨 Адаптивная вёрстка на Tailwind CSS 4 (сборка через Vite, `@tailwindcss/vite`) с поддержкой тёмной темы

### Модуль обмена с 1С (CommerceML 2)
Стандартный протокол обмена «1С:Предприятие ↔ сайт» на эндпоинте `/api/1c/exchange`.
Авторизация — HTTP Basic (логин и пароль из `ONEC_LOGIN` / `ONEC_PASSWORD`).

| Режим | Описание |
|---|---|
| `type=catalog&mode=checkauth` | Проверка авторизации |
| `type=catalog&mode=init` | Параметры обмена (zip, file_limit) |
| `type=catalog&mode=file` | Приём файлов обмена (import.xml, offers.xml) |
| `type=catalog&mode=import` | Импорт: категории (дерево любой вложенности), бренды, товары, цены, остатки |
| `type=sale&mode=orders` | Выгрузка заказов в CommerceML XML |
| `type=sale&mode=success` | Подтверждение загрузки заказов в 1С (пометка `exported_at`) |

Импорт идемпотентный:
- категории сопоставляются по GUID из 1С (поле `uuid`, трейт [HasUuid](app/Models/Concerns/HasUuid.php)), дерево любой вложенности;
- товары сопоставляются по GUID, артикул берётся из узла «Артикул» (`sku`);
- цены и остатки обновляются из «ПакетПредложений» (offers.xml);
- бренды создаются из узла «Изготовитель»;
- slug для категорий, брендов и товаров генерируется автоматически (трейт [HasSlug](app/Models/Concerns/HasSlug.php)) — при переименовании сущности slug обновляется с гарантией уникальности.

## Технологии
- Laravel 13, PHP 8.4
- SQLite (без внешних зависимостей)
- Tailwind CSS 4, установленный в проект и собираемый через Vite (`@tailwindcss/vite`), без CDN
- Денежные значения: каст [MoneyCast](app/Casts/MoneyCast.php) — в БД копейки (int), в приложении рубли (float)
- Современный синтаксис Eloquent: атрибут `#[Fillable]`, метод `casts()`

## Структура кода

```
app/
├── Casts/MoneyCast.php              # Каст «копейки ↔ рубли»
├── Http/Controllers/
│   ├── CatalogController.php        # Каталог и карточка товара
│   ├── BrandController.php          # Список брендов
│   ├── CartController.php           # Корзина
│   ├── CheckoutController.php       # Оформление заказа
│   └── Api/OneCExchangeController.php # Обмен с 1С
├── Models/                          # Category, Brand, Product, Order, OrderItem
└── Services/
    ├── CartService.php              # Логика корзины (сессия)
    └── OneC/
        ├── CommerceMLImporter.php   # Импорт CommerceML из 1С
        └── OrdersExporter.php       # Экспорт заказов в CommerceML
```

## Быстрый старт

Требования: PHP 8.4+, Composer.

```sh
composer install
copy .env.example .env          # Windows (Linux/macOS: cp .env.example .env)
php artisan key:generate
php artisan migrate --seed      # создаст БД и наполнит демо-данными
php artisan serve               # http://127.0.0.1:8000
```

Демо-данные: 7 категорий, 14 подкатегорий, 14 брендов, 40 товаров.

## Настройка обмена с 1С

Задайте в `.env` (значения по умолчанию — `1c_user` / `1c_secret`):

```env
ONEC_LOGIN=1c_user
ONEC_PASSWORD=1c_secret
ONEC_ZIP=true
```

Настройте в 1С адрес обмена: `https://ваш-домен/api/1c/exchange`, логин и пароль из конфигурации.

## Лицензия

Проект создан в демонстрационных целях.
