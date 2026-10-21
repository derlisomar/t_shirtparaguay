<?php

namespace App\Payments;

use App\Models\Order;
use Illuminate\Http\Request;

/** Transferencia y efectivo: el pedido queda pendiente y se confirma desde el panel. */
class ManualGateway implements PaymentGateway
{
    public function key(): string
    {
        return 'manual';
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function start(Order $order): string
    {
        $order->payments()->create([
            'gateway' => $this->key(),
            'method' => $order->payment_method,
            'amount' => $order->total,
            'status' => $order->payment_status,
            'paid_at' => $order->payment_status === 'pagado' ? now() : null,
        ]);

        return route('checkout.thanks', $order);
    }

    public function handleWebhook(Request $request): void
    {
        abort(404);
    }
}
