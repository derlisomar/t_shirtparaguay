<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Payments\PaymentManager;
use Illuminate\Http\Request;

/** Rutas de retorno y aviso de la pasarela de tarjeta (segunda actualización). */
class PaymentController extends Controller
{
    public function return(Request $request, string $gateway)
    {
        $payment = Payment::with('order')->findOrFail($request->input('payment'));

        return redirect()->route('checkout.thanks', $payment->order);
    }

    public function webhook(Request $request, string $gateway, PaymentManager $payments)
    {
        $payments->gateway($gateway)->handleWebhook($request);

        return response()->json(['ok' => true]);
    }
}
