<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slide extends Model
{
    protected $fillable = [
        'title', 'text', 'tagline', 'button_text', 'button_link', 'product_id',
        'main_image', 'secondary_image', 'thumb_image', 'bg_color', 'accent_color', 'sort_order', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function link(): string
    {
        if ($this->button_link) {
            return $this->button_link;
        }

        return $this->product ? route('product.show', $this->product) : route('catalog');
    }
}
