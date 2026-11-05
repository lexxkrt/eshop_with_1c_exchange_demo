<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Администратор по умолчанию.
 *
 * Учётные данные задаются переменными окружения ADMIN_EMAIL / ADMIN_PASSWORD
 * (по умолчанию admin@shop.local / admin-password).
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Роль присваивается явно (поле role убрано из fillable — по конвенции проекта).
        $user = User::query()->firstOrNew(['email' => env('ADMIN_EMAIL', 'admin@shop.local')]);
        $user->name = 'Администратор';
        $user->password = env('ADMIN_PASSWORD', 'admin-password');
        $user->role = User::ROLE_ADMIN;
        $user->save();
    }
}
