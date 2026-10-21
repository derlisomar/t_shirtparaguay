<?php

namespace App\Support;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Crea pedidos (web o venta en el local).
 * Las ventas del local descuentan el stock en el momento; los pedidos web quedan
 * pendientes y el stock se descuenta recién cuando se procesan desde el panel.
 */
class OrderBuilder
{
    /**
     * @param  array<int,int>  $items  [variant_id => cantidad]
     * @param  array  $data  datos del pedido (cliente, pago, envío…)
     * @param  array  $options  applyStock (bool), customer (User para el precio y el límite de compra),
     *                         wholesale (forzar precio mayorista), limitMessage (texto si supera el límite)
     */
    public static function create(array $items, array $data, int $shipping = 0, int $discount = 0, array $options = []): Order
    {
        $applyStock = $options['applyStock'] ?? true;
        $customer = $options['customer'] ?? null;
        $forceWholesale = (bool) ($options['wholesale'] ?? false);
        $limitMessage = $options['limitMessage'] ?? 'El límite por compra de este cliente es :limit y el pedido suma :total.';

        return DB::transaction(function () use ($items, $data, $shipping, $discount, $applyStock, $customer, $forceWholesale, $limitMessage) {
            $variants = ProductVariant::with(['product', 'size', 'color'])
                ->whereIn('id', array_keys($items))->lockForUpdate()->get()->keyBy('id');

            $lines = [];
            foreach ($items as $id => $qty) {
                $v = $variants->get($id);
                if (! $v || ! $v->product) {
                    throw ValidationException::withMessages(['cart' => 'Un producto del carrito ya no está disponible.']);
                }
                if ($qty > $v->stock) {
                    throw ValidationException::withMessages([
                        'cart' => "Solo quedan {$v->stock} de «{$v->product->name}» ({$v->label()}).",
                    ]);
                }
                $lines[] = [$v, $qty];
            }
            if (! $lines) {
                throw ValidationException::withMessages(['cart' => 'No hay productos.']);
            }

            $perProduct = Pricing::quantitiesByProduct($lines);
            $subtotal = 0;
            $anyWholesale = false;
            foreach ($lines as $i => [$v, $qty]) {
                $wholesale = $v->product->wholesaleApplies($perProduct[$v->product_id], $customer, $forceWholesale);
                $price = $wholesale ? $v->product->wholesale_price : $v->product->price;
                $lines[$i][] = $price;
                $lines[$i][] = $wholesale;
                $subtotal += $price * $qty;
                $anyWholesale = $anyWholesale || $wholesale;
            }

            $discount = min($discount, $subtotal);
            $total = $subtotal - $discount + $shipping;
            if ($customer?->purchase_limit && $total > $customer->purchase_limit) {
                throw ValidationException::withMessages([
                    'cart' => strtr($limitMessage, [':limit' => money($customer->purchase_limit), ':total' => money($total)]),
                ]);
            }

            $order = Order::create($data + [
                'number' => Order::nextNumber(),
                'subtotal' => $subtotal,
                'discount' => $discount,
                'shipping' => $shipping,
                'total' => $total,
                'is_wholesale' => $anyWholesale,
                'stock_applied' => $applyStock,
                'processed_at' => $applyStock ? now() : null,
            ]);

            foreach ($lines as [$v, $qty, $price, $wholesale]) {
                $order->items()->create([
                    'product_id' => $v->product_id,
                    'product_variant_id' => $v->id,
                    'product_name' => $v->product->name,
                    'size' => $v->size?->name,
                    'color' => $v->color?->name,
                    'price' => $price,
                    'quantity' => $qty,
                    'total' => $price * $qty,
                    'is_wholesale' => $wholesale,
                ]);
                if ($applyStock) {
                    $v->decrement('stock', $qty);
                    Product::whereKey($v->product_id)->increment('sold_count', $qty);
                }
            }

            return $order;
        });
    }
}
