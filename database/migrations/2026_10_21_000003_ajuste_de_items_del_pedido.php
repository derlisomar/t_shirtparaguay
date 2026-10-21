<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Actualización 4: ajustar cantidades o quitar prendas de un pedido antes de finalizarlo. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $t) {
            // Cantidad que pidió el cliente, si después se cambió desde el panel (0 = prenda quitada)
            $t->unsignedInteger('original_quantity')->nullable()->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', fn (Blueprint $t) => $t->dropColumn('original_quantity'));
    }
};
