<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 64)->nullable()->unique()->comment('GUID из 1С');
            $table->string('sku')->nullable()->unique()->comment('Артикул');
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('price')->default(0)->comment('Цена в копейках; в приложении — рубли (MoneyCast)');
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedInteger('position')->default(0)->comment('Порядок сортировки');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
