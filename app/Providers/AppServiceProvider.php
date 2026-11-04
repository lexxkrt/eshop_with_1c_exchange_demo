<?php

namespace App\Providers;

use App\Models\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::automaticallyEagerLoadRelationships();

        // Дерево категорий для меню в шапке и сайдбара каталога.
        // Composer (а не View::share), чтобы запрос выполнялся лениво — при рендере вида,
        // а не при загрузке приложения (важно для тестов: сначала миграции, потом запросы).
        View::composer('*', function ($view) {
            $view->with('menuCategories', Category::root()->with('children')->get());
        });
    }
}
