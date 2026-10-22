<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\Size;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request, ?Category $category = null)
    {
        $q = Product::active()->with(['images', 'variants.color', 'variants.size']);

        $lines = collect();
        if ($category) {
            abort_unless($category->is_active && (! $category->parent || $category->parent->is_active), 404);
            // Una categoría principal muestra también los productos de sus líneas
            $q->whereIn('category_id', $category->familyIds());
            $root = $category->parent ?? $category;
            $lines = $root->children()->where('is_active', true)->get();
        }
        if ($term = trim((string) $request->input('q'))) {
            $q->where(function ($w) use ($term) {
                $w->where('name', 'ilike', "%$term%")->orWhere('description', 'ilike', "%$term%");
            });
        }
        $sizes = array_filter((array) $request->input('talle'));
        $colors = array_filter((array) $request->input('color'));
        if ($sizes || $colors) {
            $q->whereHas('variants', function ($v) use ($sizes, $colors) {
                $v->where('stock', '>', 0);
                if ($sizes) {
                    $v->whereIn('size_id', $sizes);
                }
                if ($colors) {
                    $v->whereIn('color_id', $colors);
                }
            });
        }
        if ($request->filled('min')) {
            $q->where('price', '>=', (int) preg_replace('/\D/', '', $request->input('min')));
        }
        if ($request->filled('max')) {
            $q->where('price', '<=', (int) preg_replace('/\D/', '', $request->input('max')));
        }
        if ($request->boolean('oferta')) {
            $q->whereNotNull('compare_price')->whereColumn('compare_price', '>', 'price');
        }

        match ($request->input('orden')) {
            'precio-asc' => $q->orderBy('price'),
            'precio-desc' => $q->orderByDesc('price'),
            'mas-vendidos' => $q->orderByDesc('sold_count'),
            default => $q->latest('id'),
        };

        // En una categoría con líneas y sin filtros, los productos se muestran agrupados por línea
        $groups = null;
        $filtered = $request->hasAny(['q', 'talle', 'color', 'min', 'max', 'oferta', 'orden', 'page']);
        if ($category && ! $category->parent_id && $lines->isNotEmpty() && ! $filtered) {
            $all = $q->get();
            $groups = $lines->map(fn ($l) => (object) ['category' => $l, 'products' => $all->where('category_id', $l->id)->values()])
                ->push((object) ['category' => null, 'products' => $all->where('category_id', $category->id)->values()])
                ->filter(fn ($g) => $g->products->isNotEmpty())->values();
            $products = new \Illuminate\Pagination\LengthAwarePaginator($all, $all->count(), max(1, $all->count()));
        } else {
            $products = $q->paginate(12)->withQueryString();
        }

        return view('shop.catalog', [
            'products' => $products,
            'category' => $category,
            'categories' => Category::active()->roots()->get(),
            'lines' => $lines,
            'groups' => $groups,
            'allSizes' => Size::orderBy('sort_order')->get(),
            'allColors' => Color::orderBy('sort_order')->get(),
            'selSizes' => array_map('intval', $sizes),
            'selColors' => array_map('intval', $colors),
        ]);
    }

    public function show(Product $product)
    {
        abort_unless($product->is_active, 404);
        $product->load(['images', 'variants.size', 'variants.color', 'category.parent']);

        $related = Product::active()->with(['images', 'variants.color'])
            ->where('id', '!=', $product->id)
            ->when($product->category_id, fn ($q) => $q->where('category_id', $product->category_id))
            ->inRandomOrder()->take(4)->get();

        // Datos para el selector de talle/color en JavaScript
        $variants = $product->variants->map(fn ($v) => [
            'id' => $v->id, 'size' => $v->size_id, 'color' => $v->color_id, 'stock' => $v->available(),
        ])->values();

        $isFavorite = auth()->check() && auth()->user()->favorites()->whereKey($product->id)->exists();

        return view('shop.product', compact('product', 'related', 'variants', 'isFavorite'));
    }

    /** Búsqueda rápida para el buscador del encabezado */
    public function search(Request $request)
    {
        $term = trim((string) $request->input('q'));
        if (mb_strlen($term) < 2) {
            return response()->json([]);
        }
        $items = Product::active()->with('images')
            ->where('name', 'ilike', "%$term%")->take(6)->get()
            ->map(fn ($p) => [
                'name' => $p->name,
                'price' => money($p->price),
                'url' => route('product.show', $p),
                'image' => $p->mainImageUrl(),
            ]);

        return response()->json($items);
    }
}
