<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slides', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('text')->nullable();
            $table->string('tagline')->nullable();           // frase chica abajo al centro
            $table->string('button_text', 60)->nullable();
            $table->string('button_link')->nullable();       // si queda vacío, va al producto
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete(); // precio y talles
            $table->string('main_image')->nullable();        // prenda principal (PNG sin fondo)
            $table->string('secondary_image')->nullable();   // prenda flotante de atrás
            $table->string('thumb_image')->nullable();       // miniatura abajo a la derecha
            $table->string('bg_color', 7)->default('#2b211c');
            $table->string('accent_color', 7)->default('#ff7a2f');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->string('key', 80)->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('slides');
    }
};
