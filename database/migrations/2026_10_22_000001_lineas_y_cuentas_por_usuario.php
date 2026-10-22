<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Líneas dentro de cada categoría (Remeras > Clásicas hilo 40.1, Oversize…)
 * y cuentas de clientes con nombre de usuario, apellido y país.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('categories')->nullOnDelete();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 40)->nullable()->unique()->after('name');
            $table->string('last_name', 120)->nullable()->after('username');
            $table->string('country', 60)->nullable()->after('city');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn(['username', 'last_name', 'country']);
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
        });
    }
};
