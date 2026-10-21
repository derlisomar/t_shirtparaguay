<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Order extends Model
{
    public const STATUSES = [
        'pendiente' => 'Pendiente',
        'procesado' => 'Procesado',
        'enviado' => 'Enviado',
        'entregado' => 'Entregado',
        'finalizado' => 'Finalizado',
        'cancelado' => 'Cancelado',
    ];

    /** Pedidos que todavía hay que atender (ni finalizados ni cancelados) */
    public const OPEN_STATUSES = ['pendiente', 'procesado', 'enviado', 'entregado'];

    public const PAYMENT_STATUSES = [
        'pendiente' => 'Pendiente',
        'pagado' => 'Pagado',
        'fallido' => 'Fallido',
        'reembolsado' => 'Reembolsado',
    ];

    protected $fillable = [
        'number', 'user_id', 'seller_id', 'channel', 'status', 'payment_method', 'payment_status',
        'customer_name', 'customer_email', 'customer_phone', 'customer_document',
        'delivery_method', 'address', 'city', 'notes',
        'subtotal', 'discount', 'shipping', 'total', 'cash_received',
        'receipt_path', 'receipt_uploaded_at', 'stock_applied', 'processed_at', 'finished_at', 'is_wholesale',
    ];

    protected $casts = [
        'stock_applied' => 'boolean',
        'is_wholesale' => 'boolean',
        'processed_at' => 'datetime',
        'finished_at' => 'datetime',
        'receipt_uploaded_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class)->orderBy('id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class)->latest();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function getRouteKeyName(): string
    {
        return 'number';
    }

    public static function nextNumber(string $prefix = 'TS'): string
    {
        $last = (int) static::max('id');

        return $prefix.'-'.str_pad((string) ($last + 1001), 6, '0', STR_PAD_LEFT);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function paymentLabel(): string
    {
        return config('payments.methods.'.$this->payment_method.'.label', ucfirst($this->payment_method));
    }

    public function paymentStatusLabel(): string
    {
        return self::PAYMENT_STATUSES[$this->payment_status] ?? ucfirst($this->payment_status);
    }

    /**
     * Finaliza el pedido: entregado y con el pago recibido conforme.
     * Si todavía no se había procesado, primero descuenta el stock.
     */
    public function finish(): void
    {
        DB::transaction(function () {
            if (! $this->stock_applied) {
                $this->process('finalizado');
            }
            $this->update(['status' => 'finalizado', 'payment_status' => 'pagado', 'finished_at' => now()]);
            $this->payments()->where('status', 'pendiente')->update(['status' => 'pagado', 'paid_at' => now()]);
        });
    }

    public function canEditItems(): bool
    {
        return in_array($this->status, self::OPEN_STATUSES);
    }

    /**
     * Cambia las cantidades de las prendas (0 = quitarla) y recalcula los totales.
     * Si el stock ya se había descontado, devuelve o descuenta la diferencia.
     *
     * @param  array<int,int>  $quantities  [order_item_id => cantidad nueva]
     * @return int cuántas prendas cambiaron
     */
    public function adjustItems(array $quantities): int
    {
        return DB::transaction(function () use ($quantities) {
            $order = static::whereKey($this->id)->lockForUpdate()->first();
            if (! $order->canEditItems()) {
                throw ValidationException::withMessages(['items' => 'Este pedido ya está finalizado o cancelado; no se puede modificar.']);
            }
            $items = $this->items()->get()->keyBy('id');
            $changed = 0;

            $final = $items->map(fn ($it) => array_key_exists($it->id, $quantities) ? max(0, (int) $quantities[$it->id]) : $it->quantity);
            if ($final->sum() === 0) {
                throw ValidationException::withMessages(['items' => 'El pedido no puede quedar sin prendas. Si no tenés nada de lo que pidió, cancelalo.']);
            }

            foreach ($items as $it) {
                $new = $final[$it->id];
                $delta = $new - $it->quantity;
                if ($delta === 0) {
                    continue;
                }
                if ($order->stock_applied && $it->product_variant_id) {
                    $v = ProductVariant::with('product')->whereKey($it->product_variant_id)->lockForUpdate()->first();
                    if ($v && $delta > 0 && $v->stock < $delta) {
                        throw ValidationException::withMessages(['items' => "No alcanza el stock de {$it->product_name} ({$it->size}): hay {$v->stock} y querés sumar {$delta}."]);
                    }
                    if ($v) {
                        $v->decrement('stock', $delta);
                    }
                    if ($it->product_id) {
                        Product::whereKey($it->product_id)->increment('sold_count', $delta);
                    }
                }
                $it->update([
                    'original_quantity' => $it->original_quantity ?? $it->quantity,
                    'quantity' => $new,
                    'total' => $it->price * $new,
                ]);
                $changed++;
            }

            if ($changed) {
                $subtotal = (int) $this->items()->sum('total');
                $discount = min($this->discount, $subtotal);
                $this->update(['subtotal' => $subtotal, 'discount' => $discount, 'total' => $subtotal - $discount + $this->shipping]);
                $this->payments()->where('status', 'pendiente')->update(['amount' => $this->total]);
            }

            return $changed;
        });
    }

    public function hasReceipt(): bool
    {
        return (bool) $this->receipt_path;
    }

    public function receiptIsPdf(): bool
    {
        return str_ends_with(strtolower((string) $this->receipt_path), '.pdf');
    }

    /**
     * Procesa el pedido: descuenta el stock de cada prenda.
     * Si alguna prenda ya no tiene stock suficiente, no descuenta nada y avisa cuál.
     */
    public function process(string $status = 'procesado'): void
    {
        DB::transaction(function () use ($status) {
            $order = static::whereKey($this->id)->lockForUpdate()->first();
            if (! $order->stock_applied) {
                $items = $this->items()->get();
                $variants = ProductVariant::with(['product', 'size', 'color'])
                    ->whereIn('id', $items->pluck('product_variant_id')->filter())->lockForUpdate()->get()->keyBy('id');
                $need = $items->groupBy('product_variant_id')->map->sum('quantity');
                $missing = [];
                foreach ($need as $vid => $qty) {
                    $v = $variants->get($vid);
                    if ($v && $v->stock < $qty) {
                        $missing[] = "{$v->product->name} ({$v->label()}): hay {$v->stock}, pide {$qty}";
                    }
                }
                if ($missing) {
                    throw ValidationException::withMessages(['status' => 'No alcanza el stock para procesarlo. '.implode('; ', $missing).'.']);
                }
                foreach ($items as $item) {
                    if ($variants->has($item->product_variant_id)) {
                        ProductVariant::whereKey($item->product_variant_id)->decrement('stock', $item->quantity);
                    }
                    if ($item->product_id) {
                        Product::whereKey($item->product_id)->increment('sold_count', $item->quantity);
                    }
                }
            }
            $this->update(['status' => $status, 'stock_applied' => true, 'processed_at' => $this->processed_at ?? now()]);
        });
    }

    /** Cancela el pedido y, si ya se había descontado, devuelve el stock de cada prenda. */
    public function cancel(): void
    {
        if ($this->status === 'cancelado') {
            return;
        }
        DB::transaction(function () {
            $this->restoreStock();
            $this->update(['status' => 'cancelado']);
        });
    }

    /** Vuelve un pedido procesado a «pendiente» y devuelve el stock que se había descontado. */
    public function backToPending(): void
    {
        DB::transaction(function () {
            $this->restoreStock();
            $this->update(['status' => 'pendiente', 'processed_at' => null]);
        });
    }

    private function restoreStock(): void
    {
        $order = static::whereKey($this->id)->lockForUpdate()->first();
        if (! $order->stock_applied) {
            return;
        }
        foreach ($this->items as $item) {
            if ($item->product_variant_id) {
                ProductVariant::whereKey($item->product_variant_id)->increment('stock', $item->quantity);
            }
            if ($item->product_id) {
                Product::whereKey($item->product_id)->where('sold_count', '>=', $item->quantity)->decrement('sold_count', $item->quantity);
            }
        }
        $this->update(['stock_applied' => false]);
    }
}
