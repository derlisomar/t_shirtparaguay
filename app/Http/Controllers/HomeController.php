<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Slide;

class HomeController extends Controller
{
    public function index()
    {
        $slides = Slide::with(['product.variants.size', 'product.variants.color'])
            ->where('is_active', true)->orderBy('sort_order')->orderBy('id')->get();

        $with = ['images', 'variants.color', 'variants.size'];
        $categories = Category::active()->roots()->get();
        $new = Product::active()->with($with)->where('is_new', true)->latest()->take(8)->get();
        $featured = Product::active()->with($with)->where('is_featured', true)->latest()->take(8)->get();
        $best = Product::active()->with($with)->orderByDesc('sold_count')->take(4)->get();
        $sale = Product::active()->with($with)->whereNotNull('compare_price')->whereColumn('compare_price', '>', 'price')->take(4)->get();

        return view('shop.home', compact('slides', 'categories', 'new', 'featured', 'best', 'sale'));
    }

    public function help()
    {
        return view('shop.help');
    }
}
