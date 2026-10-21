<?php

namespace App\Support;

/** Ayudas para calcular el precio mayorista según las unidades de cada producto. */
class Pricing
{
    /**
     * Suma las unidades de cada producto (todos sus talles y colores juntos),
     * porque la cantidad mínima mayorista se cuenta por producto.
     *
     * @param  iterable<array{0:\App\Models\ProductVariant,1:int}>  $lines
     * @return array<int,int> [product_id => unidades]
     */
    public static function quantitiesByProduct(iterable $lines): array
    {
        $out = [];
        foreach ($lines as $line) {
            [$v, $qty] = $line;
            $out[$v->product_id] = ($out[$v->product_id] ?? 0) + $qty;
        }

        return $out;
    }
}
