<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 64)->nullable()->unique()->comment('GUID из 1С');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('unit')->nullable()->comment('Единица измерения значения (кг, см и т.п.)');
            $table->unsignedInteger('position')->default(0)->comment('Порядок сортировки');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
