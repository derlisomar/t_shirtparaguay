<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Payments\PaymentManager;
use App\Support\Cart;
use App\Support\OrderBuilder;
use App\Support\Receipts;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller
{
    public function show(PaymentManager $payments)
    {
        $lines = Cart::lines();
        if ($lines->isEmpty()) {
            return redirect()->route('cart');
        }
        $subtotal = Cart::subtotal($lines);

        return view('shop.checkout', [
            'lines' => $lines,
            'subtotal' => $subtotal,
            'shippingCost' => Cart::shippingFor($subtotal, 'envio'),
            'methods' => $payments->webMethods(),
            'user' => auth()->user(),
            'limit' => auth()->user()->purchase_limit,
        ]);
    }

    public function store(Request $request, PaymentManager $payments)
    {
        $available = array_keys(array_filter($payments->webMethods(), fn ($m) => $m['available']));

        $data = $request->validate([
            'customer_name' => 'required|string|max:120',
            'customer_phone' => 'required|string|max:40',
            'customer_document' => 'nullable|string|max:30',
            'delivery_method' => 'required|in:envio,retiro',
            'address' => 'required_if:delivery_method,envio|nullable|string|max:255',
            'city' => 'required_if:delivery_method,envio|nullable|string|max:80',
            'notes' => 'nullable|string|max:500',
            'payment_method' => ['required', Rule::in($available)],
            'save_data' => 'nullable|boolean',
            'receipt' => Receipts::RULE,
        ], Receipts::MESSAGES + [
            'address.required_if' => 'Poné la dirección de entrega.',
            'city.required_if' => 'Poné la ciudad.',
            'payment_method.in' => 'Ese medio de pago todavía no está disponible.',
        ]);

        $items = Cart::raw();
        if (! $items) {
            return redirect()->route('cart');
        }
        $subtotal = Cart::subtotal();
        $user = $request->user();

        $order = OrderBuilder::create($items, [
            'user_id' => $user->id,
            'channel' => 'web',
            'status' => 'pendiente',
            'payment_status' => 'pendiente',
            'customer_email' => $user->email,
        ] + collect($data)->except(['save_data', 'receipt'])->all(), Cart::shippingFor($subtotal, $data['delivery_method']), 0, [
            // El stock se descuenta cuando el pedido se procesa desde el panel
            'applyStock' => false,
            'customer' => $user,
            'limitMessage' => 'Tu límite por compra es :limit y este pedido suma :total. Sacá algún producto o escribinos por WhatsApp.',
        ]);

        if ($request->hasFile('receipt')) {
            Receipts::attach($order, $request->file('receipt'));
        }

        if ($request->boolean('save_data')) {
            $user->update([
                'phone' => $data['customer_phone'],
                'document' => $data['customer_document'] ?? $user->document,
                'address' => $data['address'] ?? $user->address,
                'city' => $data['city'] ?? $user->city,
            ]);
        }

        Cart::clear();

        return redirect($payments->forMethod($order->payment_method)->start($order));
    }

    public function thanks(Order $order)
    {
        abort_unless($order->user_id === auth()->id(), 404);

        return view('shop.thanks', ['order' => $order->load('items')]);
    }
}
