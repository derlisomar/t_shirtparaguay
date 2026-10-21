<?php

namespace App\Payments;

use App\Models\Order;
use Illuminate\Http\Request;

/**
 * Contrato que cumple cada forma de cobro. Para agregar tarjeta de crédito
 * en la segunda actualización alcanza con completar CardGateway.
 */
interface PaymentGateway
{
    /** Nombre corto: manual, card… */
    public function key(): string;

    /** ¿Se puede ofrecer en el checkout ahora mismo? */
    public function isAvailable(): bool;

    /**
     * Inicia el cobro de un pedido recién creado.
     * Devuelve la URL a la que hay que mandar al cliente (la de la pasarela
     * o la de "gracias por tu compra").
     */
    public function start(Order $order): string;

    /** Procesa el aviso de la pasarela (webhook) y marca el pago. */
    public function handleWebhook(Request $request): void;
}
