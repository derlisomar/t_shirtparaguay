<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Stock disponible para la tienda web.
 * Los pedidos web descuentan el stock real recién cuando se procesan desde el panel,
 * pero mientras están pendientes esas prendas quedan «reservadas»: la web ya no las
 * ofrece, así dos clientes no compran la misma última remera.
 */
class Stock
{
    /** @var array<int,int>|null [variant_id => unidades reservadas] */
    private static ?array $reserved = null;

    /** Unidades reservadas por pedidos pendientes (sin procesar) */
    public static function reserved(int $variantId): int
    {
        if (self::$reserved === null) {
            self::$reserved = DB::table('order_items')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->where('orders.status', 'pendiente')
                ->where('orders.stock_applied', false)
                ->whereNotNull('order_items.product_variant_id')
                ->groupBy('order_items.product_variant_id')
                ->selectRaw('order_items.product_variant_id as vid, sum(order_items.quantity) as qty')
                ->pluck('qty', 'vid')
                ->map(fn ($q) => (int) $q)->all();
        }

        return self::$reserved[$variantId] ?? 0;
    }

    /** Se llama cuando cambia un pedido, para volver a calcular en la misma petición */
    public static function forget(): void
    {
        self::$reserved = null;
    }
}
