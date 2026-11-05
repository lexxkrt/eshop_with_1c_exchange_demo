<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->string('type')->nullable()->comment('Тип значений из 1С (Строка, Число, Справочник и т.п.)')->after('unit');
        });

        Schema::create('property_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('uuid', 64)->nullable()->comment('GUID варианта значения из 1С (ИдЗначения)');
            $table->string('value')->comment('Значение справочника');
            $table->unsignedInteger('position')->default(0)->comment('Порядок сортировки');
            $table->timestamps();

            $table->unique(['property_id', 'uuid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_values');

        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
