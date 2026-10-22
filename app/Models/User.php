<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'username', 'last_name', 'email', 'password', 'phone', 'document', 'city', 'country', 'address', 'is_admin', 'is_wholesale', 'purchase_limit', 'admin_notes'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_wholesale' => 'boolean',
            'purchase_limit' => 'integer',
        ];
    }

    /** Nombre y apellido (las cuentas viejas tienen todo en «name») */
    public function fullName(): string
    {
        return trim($this->name.' '.($this->last_name ?? ''));
    }

    /** Solo el nombre de pila, para saludar */
    public function firstName(): string
    {
        return $this->last_name ? $this->name : \Illuminate\Support\Str::before($this->name, ' ');
    }

    public function orders()
    {
        return $this->hasMany(Order::class)->latest();
    }

    public function favorites()
    {
        return $this->belongsToMany(Product::class, 'favorites')->withTimestamps();
    }
}
