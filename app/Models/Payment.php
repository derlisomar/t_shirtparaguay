<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id', 'gateway', 'method', 'amount', 'currency', 'status', 'transaction_id',
        'authorization_code', 'card_brand', 'card_last4', 'payload', 'paid_at',
    ];

    protected $casts = ['payload' => 'array', 'paid_at' => 'datetime'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
