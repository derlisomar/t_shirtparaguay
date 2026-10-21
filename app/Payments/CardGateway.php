<?php

namespace App\Payments;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Tarjeta de crédito / débito — PREPARADO PARA LA SEGUNDA ACTUALIZACIÓN.
 *
 * Qué falta para activarlo:
 *  1. Contratar el procesador (en Paraguay: Bancard vPOS, Pagopar, etc.) y pedir las claves.
 *  2. Cargar en .env: PAYMENTS_CARD_ENABLED=true, PAYMENTS_CARD_PUBLIC_KEY, PAYMENTS_CARD_PRIVATE_KEY.
 *  3. Completar start() para crear la operación en la pasarela y devolver su URL de pago.
 *  4. Completar handleWebhook() para validar la firma y marcar el pago.
 *  La URL del webhook para dar al procesador es: /pagos/card/webhook
 */
class CardGateway implements PaymentGateway
{
    public function key(): string
    {
        return 'card';
    }

    public function isAvailable(): bool
    {
        return (bool) config('payments.card.enabled')
            && config('payments.card.public_key')
            && config('payments.card.private_key');
    }

    public function start(Order $order): string
    {
        $payment = $order->payments()->create([
            'gateway' => config('payments.card.provider'),
            'method' => 'tarjeta',
            'amount' => $order->total,
            'status' => 'pendiente',
        ]);

        // TODO (2ª actualización): llamar a la API del procesador con
        //   $payment->id como identificador de la operación, $order->total y
        //   route('payments.return', ['gateway' => 'card', 'payment' => $payment->id])
        // y devolver la URL de pago que responde la pasarela.

        return route('checkout.thanks', $order);
    }

    public function handleWebhook(Request $request): void
    {
        // TODO (2ª actualización): validar la firma con la clave privada.
        Log::info('Webhook de tarjeta recibido', $request->all());

        $payment = Payment::find($request->input('payment_id'));
        if (! $payment) {
            return;
        }

        $ok = $request->input('status') === 'approved';
        $payment->update([
            'status' => $ok ? 'pagado' : 'fallido',
            'transaction_id' => $request->input('transaction_id'),
            'authorization_code' => $request->input('authorization_code'),
            'card_brand' => $request->input('card_brand'),
            'card_last4' => $request->input('card_last4'),
            'payload' => $request->all(),
            'paid_at' => $ok ? now() : null,
        ]);
        // El pedido sigue «pendiente»: el stock se descuenta cuando se procesa desde el panel
        $payment->order->update(['payment_status' => $ok ? 'pagado' : 'fallido']);
    }
}
