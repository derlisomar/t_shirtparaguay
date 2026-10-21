<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'name', 'slug', 'description', 'sku', 'price', 'compare_price', 'wholesale_price', 'wholesale_min_qty',
        'is_active', 'is_featured', 'is_new',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_new' => 'boolean',
        'price' => 'integer',
        'compare_price' => 'integer',
        'wholesale_price' => 'integer',
        'wholesale_min_qty' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Product $p) {
            if (! $p->slug) {
                $base = Str::slug($p->name) ?: 'producto';
                $slug = $base;
                $i = 2;
                while (static::where('slug', $slug)->where('id', '!=', $p->id ?? 0)->exists()) {
                    $slug = $base.'-'.$i++;
                }
                $p->slug = $slug;
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    public function totalStock(): int
    {
        return (int) ($this->relationLoaded('variants') ? $this->variants->sum('stock') : $this->variants()->sum('stock'));
    }

    public function isOnSale(): bool
    {
        return $this->compare_price && $this->compare_price > $this->price;
    }

    public function discountPercent(): int
    {
        return $this->isOnSale() ? (int) round(100 - $this->price * 100 / $this->compare_price) : 0;
    }

    public function hasWholesale(): bool
    {
        return $this->wholesale_price > 0 && $this->wholesale_price < $this->price;
    }

    /**
     * Precio por unidad según quién compra y cuántas unidades lleva del producto.
     * Un cliente mayorista paga siempre el precio mayorista; los demás, al llegar a la cantidad mínima.
     */
    public function unitPrice(int $qty = 1, ?User $user = null, bool $forceWholesale = false): int
    {
        return $this->wholesaleApplies($qty, $user, $forceWholesale) ? $this->wholesale_price : $this->price;
    }

    public function wholesaleApplies(int $qty = 1, ?User $user = null, bool $forceWholesale = false): bool
    {
        if (! $this->hasWholesale()) {
            return false;
        }

        return $forceWholesale || $user?->is_wholesale || ($this->wholesale_min_qty > 0 && $qty >= $this->wholesale_min_qty);
    }

    public function mainImageUrl(): string
    {
        $img = $this->relationLoaded('images') ? $this->images->first() : $this->images()->first();

        return $img ? $img->url : asset('img/sin-foto.svg');
    }

    public function secondImageUrl(): ?string
    {
        $imgs = $this->relationLoaded('images') ? $this->images : $this->images()->get();

        return $imgs->count() > 1 ? $imgs[1]->url : null;
    }

    /** Colores con stock o sin stock, en el orden de la carta */
    public function colorOptions()
    {
        return $this->variants->pluck('color')->filter()->unique('id')->sortBy('sort_order')->values();
    }

    public function sizeOptions()
    {
        return $this->variants->pluck('size')->filter()->unique('id')->sortBy('sort_order')->values();
    }
}
