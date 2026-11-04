<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->string('external_id', 64)->nullable()->unique()->after('slug')->comment('GUID из 1С');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->string('external_id', 64)->nullable()->unique()->after('slug')->comment('GUID из 1С');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('external_id', 64)->nullable()->unique()->after('slug')->comment('GUID из 1С');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('external_id', 64)->nullable()->unique()->after('id')->comment('GUID заказа из 1С');
            $table->timestamp('exported_at')->nullable()->after('status')->comment('Дата выгрузки заказа в 1С');
        });
    }

    public function down(): void
    {
        Schema::table('brands', fn (Blueprint $table) => $table->dropColumn('external_id'));
        Schema::table('categories', fn (Blueprint $table) => $table->dropColumn('external_id'));
        Schema::table('products', fn (Blueprint $table) => $table->dropColumn('external_id'));
        Schema::table('orders', fn (Blueprint $table) => $table->dropColumn(['external_id', 'exported_at']));
    }
};
