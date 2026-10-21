<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $products = $request->user()->favorites()->active()->with(['images', 'variants.color'])->get();

        return view('shop.favorites', compact('products'));
    }

    public function toggle(Request $request, Product $product)
    {
        $res = $request->user()->favorites()->toggle($product->id);
        $on = count($res['attached']) > 0;

        return $request->expectsJson() ? response()->json(['favorite' => $on]) : back();
    }
}
