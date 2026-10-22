<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Payments\PaymentManager;
use App\Support\Accounts;
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
        $user = auth()->user();
        if (! $user) {
            // Si elige «Ingresá», al entrar vuelve acá con su carrito
            session(['url.intended' => route('checkout')]);
        }

        return view('shop.checkout', [
            'lines' => $lines,
            'subtotal' => $subtotal,
            'shippingCost' => Cart::shippingFor($subtotal, 'envio'),
            'methods' => $payments->webMethods(),
            'user' => $user,
            'limit' => $user?->purchase_limit,
            'countries' => Accounts::COUNTRIES,
        ]);
    }

    public function store(Request $request, PaymentManager $payments)
    {
        $available = array_keys(array_filter($payments->webMethods(), fn ($m) => $m['available']));

        $user = $request->user();
        $orderRules = [
            'delivery_method' => 'required|in:envio,retiro',
            'notes' => 'nullable|string|max:500',
            'payment_method' => ['required', Rule::in($available)],
            'receipt' => Receipts::RULE,
        ];
        $messages = Receipts::MESSAGES + [
            'address.required_if' => 'Poné la dirección de entrega.',
            'city.required_if' => 'Poné la ciudad.',
            'payment_method.in' => 'Ese medio de pago todavía no está disponible.',
        ];

        if ($user) {
            $data = $request->validate($orderRules + [
                'customer_name' => 'required|string|max:120',
                'customer_phone' => 'required|string|max:40',
                'customer_document' => 'nullable|string|max:30',
                'address' => 'required_if:delivery_method,envio|nullable|string|max:255',
                'city' => 'required_if:delivery_method,envio|nullable|string|max:80',
                'save_data' => 'nullable|boolean',
            ], $messages);
        } else {
            // Compra sin cuenta: con sus datos se crea la cuenta (el correo es opcional)
            Accounts::normalize($request);
            $account = $request->validate($orderRules + Accounts::rules(), $messages + Accounts::messages());
            if (! Cart::raw()) {
                return redirect()->route('cart');
            }
            $user = Accounts::register($request, $account);
            $data = collect($account)->only(array_keys($orderRules))->all() + [
                'customer_name' => $user->fullName(),
                'customer_phone' => $user->phone,
                'customer_document' => $user->document,
                'address' => $user->address,
                'city' => $user->city,
            ];
        }

        $items = Cart::raw();
        if (! $items) {
            return redirect()->route('cart');
        }
        $subtotal = Cart::subtotal();

        $order = OrderBuilder::create($items, [
            'user_id' => $user->id,
            'channel' => 'web',
            'status' => 'pendiente',
            'payment_status' => 'pendiente',
            'customer_email' => $user->email,
        ] + collect($data)->only(['customer_name', 'customer_phone', 'customer_document', 'delivery_method', 'address', 'city', 'notes', 'payment_method'])->all(), Cart::shippingFor($subtotal, $data['delivery_method']), 0, [
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

    /** Boleta del pedido para imprimir o guardar como PDF */
    public function invoice(Order $order)
    {
        abort_unless($order->user_id === auth()->id(), 404);

        return view('admin.orders.print', ['order' => $order->load(['items', 'user']), 'forCustomer' => true]);
    }
}
