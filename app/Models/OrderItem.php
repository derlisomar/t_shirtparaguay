<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'product_id', 'product_variant_id', 'product_name', 'size', 'color', 'price', 'quantity', 'original_quantity', 'total', 'is_wholesale'];

    protected $casts = ['is_wholesale' => 'boolean'];

    public function wasAdjusted(): bool
    {
        return $this->original_quantity !== null && $this->original_quantity !== $this->quantity;
    }

    public function isRemoved(): bool
    {
        return $this->quantity === 0;
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
