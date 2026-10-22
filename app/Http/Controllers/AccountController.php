<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Support\Accounts;
use App\Support\Receipts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return view('shop.account.index', [
            'user' => $user,
            'orders' => $user->orders()->withCount('items')->take(20)->get(),
            'favCount' => $user->favorites()->count(),
        ]);
    }

    public function order(Order $order)
    {
        abort_unless($order->user_id === auth()->id(), 404);

        return view('shop.account.order', ['order' => $order->load('items.product.images')]);
    }

    public function uploadReceipt(Request $request, Order $order)
    {
        abort_unless($order->user_id === auth()->id(), 404);
        if ($order->status === 'cancelado') {
            return back()->withErrors(['receipt' => 'Este pedido está cancelado.']);
        }
        $request->validate(['receipt' => 'required|'.Receipts::RULE], Receipts::MESSAGES + [
            'receipt.required' => 'Elegí la foto o el PDF del comprobante.',
        ]);
        Receipts::attach($order, $request->file('receipt'));

        return back()->with('ok', '¡Listo! Recibimos tu comprobante. Te avisamos cuando procesemos el pedido.');
    }

    public function receipt(Order $order)
    {
        abort_unless($order->user_id === auth()->id(), 404);

        return Receipts::response($order);
    }

    public function update(Request $request)
    {
        Accounts::normalize($request);
        $data = $request->validate(Accounts::rules($request->user(), false), Accounts::messages());
        $request->user()->update(Accounts::attributes($data));

        return back()->with('ok', 'Tus datos se guardaron.');
    }

    public function password(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::min(6)],
        ], ['password.confirmed' => 'Las contraseñas nuevas no coinciden.', 'password.min' => 'La contraseña tiene que tener al menos 6 caracteres.']);

        if (! Hash::check($request->input('current_password'), $request->user()->password)) {
            return back()->withErrors(['current_password' => 'La contraseña actual no es correcta.']);
        }
        $request->user()->update(['password' => $request->input('password')]);

        return back()->with('ok', 'Cambiaste tu contraseña.');
    }
}
