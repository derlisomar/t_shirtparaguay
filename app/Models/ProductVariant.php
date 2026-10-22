<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = ['product_id', 'size_id', 'color_id', 'sku', 'stock'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function size()
    {
        return $this->belongsTo(Size::class);
    }

    public function color()
    {
        return $this->belongsTo(Color::class);
    }

    /**
     * Lo que la tienda web puede vender: el stock menos lo que ya pidieron otros
     * clientes en pedidos que todavía no se procesaron.
     */
    public function available(): int
    {
        return max(0, $this->stock - \App\Support\Stock::reserved($this->id));
    }

    public function label(): string
    {
        return trim(($this->size?->name ?? '').($this->color ? ' · '.$this->color->name : ''));
    }
}
