<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('number', 20)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('seller_id')->nullable()->constrained('users')->nullOnDelete(); // quién vendió (venta en el local)
            $table->string('channel', 10)->default('web');           // web | local
            $table->string('status', 20)->default('pendiente');      // ver App\Models\Order::STATUSES
            $table->string('payment_method', 30);                    // ver config/payments.php
            $table->string('payment_status', 20)->default('pendiente'); // pendiente | pagado | fallido | reembolsado
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone', 40)->nullable();
            $table->string('customer_document', 30)->nullable();
            $table->string('delivery_method', 10)->default('envio'); // envio | retiro
            $table->string('address')->nullable();
            $table->string('city', 80)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('subtotal');
            $table->unsignedBigInteger('discount')->default(0);
            $table->unsignedBigInteger('shipping')->default(0);
            $table->unsignedBigInteger('total');
            $table->unsignedBigInteger('cash_received')->nullable(); // efectivo entregado (para el vuelto)
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->string('size', 30)->nullable();
            $table->string('color', 60)->nullable();
            $table->unsignedBigInteger('price');
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('total');
            $table->timestamps();
        });

        // Preparado para cobrar con tarjeta (segunda actualización):
        // cada intento de pago queda registrado con la respuesta de la pasarela.
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('gateway', 30);                 // manual | bancard | pagopar | …
            $table->string('method', 30);                  // transferencia | efectivo | tarjeta | …
            $table->unsignedBigInteger('amount');
            $table->string('currency', 3)->default('PYG');
            $table->string('status', 20)->default('pendiente');
            $table->string('transaction_id')->nullable();  // id que devuelve la pasarela
            $table->string('authorization_code')->nullable();
            $table->string('card_brand', 30)->nullable();
            $table->string('card_last4', 4)->nullable();
            $table->json('payload')->nullable();           // respuesta completa, para auditoría
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->index(['gateway', 'transaction_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
