<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Size extends Model
{
    protected $fillable = ['name', 'sort_order'];

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
}
