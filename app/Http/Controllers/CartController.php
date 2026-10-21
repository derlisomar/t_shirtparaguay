<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use App\Support\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $lines = Cart::lines();
        $subtotal = Cart::subtotal($lines);

        return view('shop.cart', [
            'lines' => $lines,
            'subtotal' => $subtotal,
            'shipping' => Cart::shippingFor($subtotal),
        ]);
    }

    /** Contenido del carrito lateral (se carga por JavaScript) */
    public function drawer()
    {
        $lines = Cart::lines();

        return response()->json([
            'html' => view('shop.partials.cart-drawer', [
                'lines' => $lines,
                'subtotal' => Cart::subtotal($lines),
            ])->render(),
            'count' => Cart::count(),
        ]);
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'variant_id' => 'required|integer|exists:product_variants,id',
            'qty' => 'nullable|integer|min:1|max:50',
        ], ['variant_id.required' => 'Elegí un talle.']);

        $variant = ProductVariant::with('product')->findOrFail($data['variant_id']);
        $qty = $data['qty'] ?? 1;
        $inCart = Cart::raw()[$variant->id] ?? 0;

        if (! $variant->product->is_active || $variant->stock < $inCart + $qty) {
            $msg = $variant->stock > 0
                ? "Solo hay {$variant->stock} en stock de este talle."
                : 'Ese talle está agotado.';

            return $request->expectsJson()
                ? response()->json(['message' => $msg], 422)
                : back()->withErrors(['cart' => $msg]);
        }

        Cart::add($variant->id, $qty);

        if ($request->expectsJson()) {
            return response()->json(['count' => Cart::count(), 'message' => 'Agregado al carrito']);
        }

        return redirect()->route('cart')->with('ok', 'Agregado al carrito.');
    }

    public function update(Request $request, ProductVariant $variant)
    {
        $qty = max(0, min((int) $request->input('qty'), $variant->stock));
        Cart::set($variant->id, $qty);

        return $request->expectsJson()
            ? $this->drawer()
            : back();
    }

    public function remove(Request $request, ProductVariant $variant)
    {
        Cart::set($variant->id, 0);

        return $request->expectsJson() ? $this->drawer() : back();
    }
}
