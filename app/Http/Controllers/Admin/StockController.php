<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

/** Planilla rápida para actualizar el stock de todas las prendas de una vez. */
class StockController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with(['variants.size', 'variants.color', 'images'])
            ->when($request->input('q'), fn ($q, $t) => $q->where('name', 'ilike', "%$t%"))
            ->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.stock', compact('products'));
    }

    public function update(Request $request)
    {
        foreach ((array) $request->input('stock', []) as $id => $qty) {
            if ($qty !== null && $qty !== '') {
                ProductVariant::whereKey($id)->update(['stock' => max(0, (int) $qty)]);
            }
        }

        return back()->with('ok', 'Stock actualizado.');
    }
}
