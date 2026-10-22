<?php

namespace App\Providers;

use App\Models\Category;
use App\Support\Cart;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::defaultView('partials.pager');

        View::composer('layouts.shop', function ($view) {
            $view->with([
                'navCategories' => Category::active()->roots()->with(['children' => fn ($q) => $q->where('is_active', true)])->get(),
                'cartCount' => Cart::count(),
            ]);
        });

        // Ids de favoritos del cliente (para pintar el corazón en las tarjetas)
        View::composer('shop.partials.card', function ($view) {
            static $ids = null;
            if ($ids === null) {
                $ids = auth()->check() ? auth()->user()->favorites()->pluck('products.id')->all() : [];
            }
            $view->with('favIds', $ids);
        });
    }
}
