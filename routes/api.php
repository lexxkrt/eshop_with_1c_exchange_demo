<?php

use App\Http\Controllers\Api\OneCExchangeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Модуль обмена с 1С (CommerceML 2)
|--------------------------------------------------------------------------
|
| Точка обмена: /api/1c/exchange?type=catalog|sale&mode=...
| Авторизация: Basic (ONEC_LOGIN / ONEC_PASSWORD).
|
*/

Route::match(['get', 'post'], '/1c/exchange', OneCExchangeController::class)
    ->name('api.1c.exchange');
