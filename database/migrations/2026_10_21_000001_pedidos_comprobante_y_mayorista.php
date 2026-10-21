<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Actualización 2:
 * - Comprobante de pago opcional en los pedidos web.
 * - El stock de los pedidos web se descuenta recién al procesarlos.
 * - Precio mayorista por producto y clientes mayoristas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $t) {
            $t->string('receipt_path')->nullable()->after('cash_received');
            $t->timestamp('receipt_uploaded_at')->nullable()->after('receipt_path');
            $t->boolean('stock_applied')->default(false)->after('receipt_uploaded_at');
            $t->timestamp('processed_at')->nullable()->after('stock_applied');
            $t->boolean('is_wholesale')->default(false)->after('processed_at');
            $t->index('processed_at');
        });

        // Los pedidos que ya existían descontaron stock al crearse
        DB::table('orders')->update(['stock_applied' => true]);
        DB::table('orders')->where('status', '!=', 'cancelado')->update(['processed_at' => DB::raw('created_at')]);
        DB::table('orders')->whereIn('status', ['confirmado', 'preparando'])->update(['status' => 'procesado']);

        Schema::table('order_items', function (Blueprint $t) {
            $t->boolean('is_wholesale')->default(false)->after('total');
        });

        Schema::table('products', function (Blueprint $t) {
            $t->unsignedBigInteger('wholesale_price')->nullable()->after('compare_price');
            $t->unsignedInteger('wholesale_min_qty')->nullable()->after('wholesale_price');
        });

        Schema::table('users', function (Blueprint $t) {
            $t->boolean('is_wholesale')->default(false)->after('is_admin');
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('is_wholesale'));
        Schema::table('products', fn (Blueprint $t) => $t->dropColumn(['wholesale_price', 'wholesale_min_qty']));
        Schema::table('order_items', fn (Blueprint $t) => $t->dropColumn('is_wholesale'));
        DB::table('orders')->where('status', 'procesado')->update(['status' => 'confirmado']);
        Schema::table('orders', function (Blueprint $t) {
            $t->dropIndex(['processed_at']);
            $t->dropColumn(['receipt_path', 'receipt_uploaded_at', 'stock_applied', 'processed_at', 'is_wholesale']);
        });
    }
};
