<?php

namespace App\Support;

use App\Models\ProductVariant;
use Illuminate\Support\Collection;

/** Carrito guardado en la sesión: [variant_id => cantidad] */
class Cart
{
    const KEY = 'cart';

    public static function raw(): array
    {
        return session(self::KEY, []);
    }

    public static function add(int $variantId, int $qty = 1): void
    {
        $cart = self::raw();
        $cart[$variantId] = ($cart[$variantId] ?? 0) + $qty;
        session([self::KEY => $cart]);
    }

    public static function set(int $variantId, int $qty): void
    {
        $cart = self::raw();
        if ($qty <= 0) {
            unset($cart[$variantId]);
        } else {
            $cart[$variantId] = $qty;
        }
        session([self::KEY => $cart]);
    }

    public static function clear(): void
    {
        session()->forget(self::KEY);
    }

    public static function count(): int
    {
        return array_sum(self::raw());
    }

    /** Líneas del carrito con su variante, producto y subtotal. Quita lo que ya no existe. */
    public static function lines(): Collection
    {
        $raw = self::raw();
        if (! $raw) {
            return collect();
        }
        $variants = ProductVariant::with(['product.images', 'size', 'color'])
            ->whereIn('id', array_keys($raw))->get()->keyBy('id');

        $valid = [];
        foreach ($raw as $id => $qty) {
            $v = $variants->get($id);
            if (! $v || ! $v->product || ! $v->product->is_active) {
                self::set((int) $id, 0);
                continue;
            }
            $valid[] = [$v, $qty];
        }

        // El precio mayorista se calcula con las unidades de cada producto (todos sus talles y colores)
        $perProduct = Pricing::quantitiesByProduct($valid);
        $user = auth()->user();

        $lines = collect();
        foreach ($valid as [$v, $qty]) {
            $wholesale = $v->product->wholesaleApplies($perProduct[$v->product_id], $user);
            $price = $wholesale ? $v->product->wholesale_price : $v->product->price;
            $image = $v->color_id ? $v->product->images->firstWhere('color_id', $v->color_id) : null;
            $lines->push((object) [
                'variant' => $v,
                'product' => $v->product,
                'qty' => $qty,
                'price' => $price,
                'regular_price' => $v->product->price,
                'wholesale' => $wholesale,
                'total' => $price * $qty,
                'image' => $image ? $image->url : $v->product->mainImageUrl(),
                'over_stock' => $qty > $v->stock,
            ]);
        }

        return $lines;
    }

    public static function subtotal(?Collection $lines = null): int
    {
        return (int) ($lines ?? self::lines())->sum('total');
    }

    public static function shippingFor(int $subtotal, string $method = 'envio'): int
    {
        if ($method === 'retiro' || $subtotal === 0) {
            return 0;
        }
        $free = (int) setting('free_shipping_from', 400000);
        if ($free > 0 && $subtotal >= $free) {
            return 0;
        }

        return (int) setting('shipping_cost', 25000);
    }
}
