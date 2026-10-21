<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Actualización 3:
 * - Clientes cargados desde el panel (el correo pasa a ser opcional) y límite de compra.
 * - Estado «Finalizado»: entregado y con el pago confirmado.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->string('email')->nullable()->change();
            $t->unsignedBigInteger('purchase_limit')->nullable()->after('is_wholesale');
            $t->text('admin_notes')->nullable()->after('purchase_limit');
        });

        Schema::table('orders', function (Blueprint $t) {
            $t->timestamp('finished_at')->nullable()->after('processed_at');
        });

        // Lo que ya estaba entregado y pagado pasa a «Finalizado»
        DB::table('orders')->where('status', 'entregado')->where('payment_status', 'pagado')
            ->update(['status' => 'finalizado', 'finished_at' => DB::raw('updated_at')]);
    }

    public function down(): void
    {
        DB::table('orders')->where('status', 'finalizado')->update(['status' => 'entregado']);
        Schema::table('orders', fn (Blueprint $t) => $t->dropColumn('finished_at'));
        Schema::table('users', function (Blueprint $t) {
            $t->dropColumn(['purchase_limit', 'admin_notes']);
        });
    }
};
