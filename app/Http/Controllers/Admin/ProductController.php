<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Size;
use App\Support\Uploads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $q = Product::with(['category', 'images', 'variants'])->latest('id');
        if ($t = $request->input('q')) {
            $q->where(fn ($w) => $w->where('name', 'ilike', "%$t%")->orWhere('sku', 'ilike', "%$t%"));
        }
        if ($c = \App\Models\Category::find($request->input('categoria'))) {
            $q->whereIn('category_id', $c->familyIds(false)); // incluye sus líneas
        }
        if ($request->input('estado') === 'ocultos') {
            $q->where('is_active', false);
        } elseif ($request->input('estado') === 'sin-stock') {
            $q->whereDoesntHave('variants', fn ($v) => $v->where('stock', '>', 0));
        }

        return view('admin.products.index', [
            'products' => $q->paginate(20)->withQueryString(),
            'categories' => Category::tree(),
        ]);
    }

    public function create()
    {
        return $this->form(new Product(['is_active' => true]));
    }

    public function edit(Product $product)
    {
        return $this->form($product->load(['images', 'variants']));
    }

    private function form(Product $product)
    {
        return view('admin.products.form', [
            'product' => $product,
            'categories' => Category::tree(),
            'sizes' => Size::orderBy('sort_order')->get(),
            'colors' => Color::orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $product = new Product;
        $this->save($request, $product);

        return redirect()->route('admin.products.edit', $product)->with('ok', 'Producto creado.');
    }

    public function update(Request $request, Product $product)
    {
        $this->save($request, $product);

        return redirect()->route('admin.products.edit', $product)->with('ok', 'Cambios guardados.');
    }

    private function save(Request $request, Product $product): void
    {
        $data = $request->validate([
            'name' => 'required|string|max:160',
            'category_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string|max:5000',
            'sku' => 'nullable|string|max:60',
            'unit_price' => 'required',
            'offer_price' => 'nullable',
            'wholesale_price' => 'nullable',
            'wholesale_min_qty' => 'nullable|integer|min:0|max:100000',
            'slug' => 'nullable|alpha_dash|max:180|unique:products,slug,'.($product->id ?? 'NULL'),
            'stock' => 'array',
            'stock.*.*' => 'nullable|integer|min:0',
            'variant_sku' => 'array',
            'photos' => 'array',
            'photos.*' => 'image|mimes:jpg,jpeg,png,webp|max:6144',
            'photo_color' => 'array',
            'image_color' => 'array',
            'image_order' => 'array',
        ], [
            'photos.*.image' => 'Las fotos tienen que ser JPG, PNG o WEBP.',
            'photos.*.max' => 'Cada foto puede pesar hasta 6 MB.',
        ]);

        // Tres precios: por unidad, de oferta (opcional) y mayorista (opcional).
        // Con oferta, «price» es lo que paga el cliente y «compare_price» el precio por unidad tachado.
        $unit = (int) preg_replace('/\D/', '', (string) $data['unit_price']);
        $offer = (int) preg_replace('/\D/', '', (string) ($data['offer_price'] ?? ''));
        $wholesale = (int) preg_replace('/\D/', '', (string) ($data['wholesale_price'] ?? ''));
        if ($unit <= 0) {
            throw \Illuminate\Validation\ValidationException::withMessages(['unit_price' => 'Poné el precio por unidad.']);
        }
        if ($offer > 0 && $offer >= $unit) {
            throw \Illuminate\Validation\ValidationException::withMessages(['offer_price' => 'El precio de oferta tiene que ser menor que el precio por unidad.']);
        }
        $price = $offer > 0 ? $offer : $unit;
        $compare = $offer > 0 ? $unit : 0;
        if ($wholesale > 0 && $wholesale >= $price) {
            throw \Illuminate\Validation\ValidationException::withMessages(['wholesale_price' => $offer > 0
                ? 'El precio mayorista tiene que ser menor que el precio de oferta.'
                : 'El precio mayorista tiene que ser menor que el precio por unidad.']);
        }

        DB::transaction(function () use ($request, $product, $data, $price, $compare, $wholesale) {
            $product->fill([
                'name' => $data['name'],
                'category_id' => $data['category_id'] ?? null,
                'description' => $data['description'] ?? null,
                'sku' => $data['sku'] ?? null,
                'price' => $price,
                'compare_price' => $compare > 0 ? $compare : null,
                'wholesale_price' => $wholesale > 0 ? $wholesale : null,
                'wholesale_min_qty' => $wholesale > 0 && ! empty($data['wholesale_min_qty']) ? (int) $data['wholesale_min_qty'] : null,
                'is_active' => $request->boolean('is_active'),
                'is_featured' => $request->boolean('is_featured'),
                'is_new' => $request->boolean('is_new'),
            ]);
            if (! empty($data['slug'])) {
                $product->slug = $data['slug'];
            }
            $product->save();

            // Variantes: una grilla talle × color con el stock de cada combinación.
            // stock[size_id][color_id|0] = cantidad (vacío = no existe esa combinación)
            $keep = [];
            foreach ($request->input('stock', []) as $sizeId => $byColor) {
                foreach ((array) $byColor as $colorId => $qty) {
                    if ($qty === null || $qty === '') {
                        continue;
                    }
                    $colorId = (int) $colorId ?: null;
                    $variant = $product->variants()->firstOrNew(['size_id' => (int) $sizeId, 'color_id' => $colorId]);
                    $variant->stock = max(0, (int) $qty);
                    $variant->sku = $request->input("variant_sku.$sizeId.".($colorId ?? 0)) ?: $variant->sku;
                    $variant->save();
                    $keep[] = $variant->id;
                }
            }
            // Las combinaciones que se vaciaron se borran (si ya se vendieron, quedan con stock 0)
            $product->variants()->whereNotIn('id', $keep ?: [0])->get()->each(function ($v) {
                DB::table('order_items')->where('product_variant_id', $v->id)->exists()
                    ? $v->update(['stock' => 0]) : $v->delete();
            });

            // Fotos existentes: color y orden
            foreach ((array) $request->input('image_color', []) as $imgId => $colorId) {
                $product->images()->whereKey($imgId)->update([
                    'color_id' => $colorId ?: null,
                    'sort_order' => (int) $request->input("image_order.$imgId", 0),
                ]);
            }

            // Fotos nuevas
            $next = (int) $product->images()->max('sort_order') + 1;
            foreach ($request->file('photos', []) as $i => $file) {
                $product->images()->create([
                    'path' => Uploads::store($file, 'products'),
                    'color_id' => $request->input("photo_color.$i") ?: null,
                    'sort_order' => $next++,
                ]);
            }
        });
    }

    public function destroyImage(Product $product, ProductImage $image)
    {
        abort_unless($image->product_id === $product->id, 404);
        Uploads::delete($image->path);
        $image->delete();

        return back()->with('ok', 'Foto eliminada.');
    }

    public function duplicate(Product $product)
    {
        $copy = $product->replicate(['slug', 'sold_count']);
        $copy->name = $product->name.' (copia)';
        $copy->is_active = false;
        $copy->save();
        foreach ($product->variants as $v) {
            $copy->variants()->create($v->only('size_id', 'color_id') + ['stock' => 0]);
        }
        foreach ($product->images as $img) {
            $copy->images()->create($img->only('path', 'color_id', 'sort_order'));
        }

        return redirect()->route('admin.products.edit', $copy)->with('ok', 'Copia creada (oculta). Revisala y activala.');
    }

    public function destroy(Product $product)
    {
        if (DB::table('order_items')->where('product_id', $product->id)->exists()) {
            $product->update(['is_active' => false]);

            return redirect()->route('admin.products.index')->with('ok', 'El producto tiene ventas, así que se ocultó en lugar de borrarse.');
        }
        foreach ($product->images as $img) {
            if (! ProductImage::where('path', $img->path)->where('id', '!=', $img->id)->exists()) {
                Uploads::delete($img->path);
            }
        }
        $product->delete();

        return redirect()->route('admin.products.index')->with('ok', 'Producto borrado.');
    }
}
