<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Size;
use App\Models\Slide;
use App\Models\User;
use App\Support\OrderBuilder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $data = json_decode(file_get_contents(__DIR__.'/data/catalog.json'), true);

        User::updateOrCreate(['email' => 'admin@tshirtparaguay.com'], [
            'name' => 'Administrador',
            'password' => 'admin123',
            'is_admin' => true,
        ]);
        $demo = User::updateOrCreate(['email' => 'cliente@ejemplo.com'], [
            'name' => 'Lucía',
            'last_name' => 'Benítez',
            'username' => 'lucia',
            'document' => '4567890',
            'password' => 'cliente123',
            'phone' => '0981 123 456',
            'country' => 'Paraguay',
            'city' => 'Asunción',
            'address' => 'Av. España 1234',
        ]);

        Setting::put([
            'store_name' => 't_shirtparaguay',
            'store_tagline' => 'Ropa urbana hecha para el calor de acá',
            'currency' => 'Gs.',
            'whatsapp' => '595981000000',
            'instagram' => 'https://instagram.com/tshirtparaguay',
            'facebook' => '',
            'tiktok' => '',
            'email' => 'hola@tshirtparaguay.com',
            'address' => 'Asunción, Paraguay',
            'pickup_address' => 'Av. Mcal. López 1234, Asunción (lunes a sábado de 9 a 19 h)',
            'shipping_cost' => '25000',
            'free_shipping_from' => '400000',
            'low_stock' => '3',
            'bank_details' => "Banco: Itaú\nCuenta: 123456789\nTitular: t_shirtparaguay\nRUC: 80000000-0\nAlias: tshirtpy",
            'announcement' => 'Envío gratis en Asunción y Central desde Gs. 400.000',
        ]);

        $order = 0;
        $sizes = [];
        foreach (['S', 'M', 'L', 'XL', 'XXL', 'Único'] as $name) {
            $sizes[$name] = Size::firstOrCreate(['name' => $name], ['sort_order' => $order++]);
        }

        $colors = [];
        $order = 0;
        foreach ($data['colors'] as $key => [$name, $hex]) {
            $colors[$key] = Color::firstOrCreate(['name' => $name], ['hex' => $hex, 'sort_order' => $order++]);
        }

        $cats = [];
        foreach ($data['categories'] as $i => $c) {
            $cats[$c['slug']] = Category::firstOrCreate(['slug' => $c['slug']], [
                'name' => $c['name'], 'description' => $c['desc'], 'sort_order' => $i,
            ]);
        }

        // Líneas dentro de las categorías (se ven agrupadas al entrar a Remeras o Pantalones y shorts)
        $lines = [
            'remeras' => [
                'remeras-clasicas-40-1' => ['Clásicas hilo 40.1 peinado', 'Corte clásico, algodón peinado hilo 40.1.'],
                'remeras-clasicas-pima-50-1' => ['Clásicas Cotton Pima hilo 50.1', 'Algodón Pima original hilo 50.1, más suave y fresco.'],
                'remeras-oversize-30-1' => ['Oversize algodón peinado 30.1', 'Calce holgado, algodón peinado 30.1.'],
                'remeras-oversize-50-1-suedin' => ['Oversize 50.1 peinado suedin', 'Horma grande, tela suedin hilo 50.1.'],
            ],
            'pantalones' => [
                'shorts-dry-fit-premium' => ['Línea deportiva Dry Fit Premium', 'Shorts livianos de secado rápido.'],
                'shorts-dry-fit-elastizado' => ['Línea deportiva Dry Fit elastizado', 'Con elastano para moverte cómodo.'],
                'shorts-de-lino' => ['Shorts de lino', 'Frescos para el verano.'],
                'joggers' => ['Joggers', 'Frisa liviana con puño.'],
            ],
        ];
        foreach ($lines as $parent => $children) {
            $o = 0;
            foreach ($children as $slug => [$name, $desc]) {
                $cats[$slug] = Category::firstOrCreate(['slug' => $slug], [
                    'parent_id' => $cats[$parent]->id, 'name' => $name, 'description' => $desc, 'sort_order' => $o++,
                ]);
            }
        }
        $cats['pantalones']->update(['name' => 'Pantalones y shorts']);
        $lineOf = [
            'remera-terere-club' => 'remeras-clasicas-40-1',
            'remera-basica-algodon' => 'remeras-clasicas-40-1',
            'remera-nanduti' => 'remeras-clasicas-pima-50-1',
            'remera-mbarete-oversize' => 'remeras-oversize-30-1',
            'remera-asu-city' => 'remeras-oversize-50-1-suedin',
            'short-deportivo' => 'shorts-dry-fit-premium',
            'jogger-urbano' => 'joggers',
        ];

        $products = [];
        foreach ($data['products'] as $i => $p) {
            $product = Product::updateOrCreate(['slug' => $p['slug']], [
                'category_id' => $cats[$lineOf[$p['slug']] ?? $p['cat']]->id,
                'name' => $p['name'],
                'description' => $p['desc'],
                'sku' => 'TS-'.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
                'price' => $p['price'],
                'compare_price' => $p['compare'] ?? null,
                'is_active' => true,
                'is_featured' => $p['featured'] ?? false,
                'is_new' => $p['isNew'] ?? false,
                // Precio mayorista de ejemplo en remeras y gorras: 20% menos llevando 6 o más
                'wholesale_price' => in_array($p['cat'], ['remeras', 'accesorios']) ? (int) (round($p['price'] * 0.8 / 1000) * 1000) : null,
                'wholesale_min_qty' => in_array($p['cat'], ['remeras', 'accesorios']) ? 6 : null,
            ]);
            $product->images()->delete();
            $sort = 0;
            foreach ($p['colors'] as $ck) {
                $product->images()->create(['path' => "seed/products/{$p['slug']}--{$ck}.webp", 'color_id' => $colors[$ck]->id, 'sort_order' => $sort++]);
                $product->images()->create(['path' => "seed/products/{$p['slug']}--{$ck}--detalle.webp", 'color_id' => $colors[$ck]->id, 'sort_order' => $sort++]);
            }
            // La foto de detalle del primer color va segunda (se ve al pasar el mouse)
            foreach ($p['colors'] as $ci => $ck) {
                foreach ($p['sizes'] as $si => $sn) {
                    // Stock variado: algunos talles agotados o con poco stock para ver cómo se ve
                    $stock = (($i * 7 + $ci * 3 + $si * 5) % 11);
                    $product->variants()->updateOrCreate(
                        ['size_id' => $sizes[$sn]->id, 'color_id' => $colors[$ck]->id],
                        ['stock' => $stock, 'sku' => "TS-".($i + 1)."-$sn-".strtoupper(substr($ck, 0, 3))]
                    );
                }
            }
            $products[$p['slug']] = $product;
        }

        Slide::query()->delete();
        foreach ($data['slides'] as $i => $s) {
            Slide::create([
                'title' => $s['title'],
                'text' => $s['text'],
                'tagline' => $s['tagline'],
                'button_text' => $s['button'],
                'product_id' => $products[$s['product']]->id,
                'main_image' => "seed/cut/{$s['main'][0]}--{$s['main'][1]}.webp",
                'secondary_image' => "seed/cut/{$s['second'][0]}--{$s['second'][1]}.webp",
                'thumb_image' => "seed/cut/{$s['thumb'][0]}--{$s['thumb'][1]}.webp",
                'bg_color' => $s['bg'],
                'accent_color' => $s['accent'],
                'sort_order' => $i,
                'is_active' => true,
            ]);
        }

        // Ventas de ejemplo (solo si en .env está DEMO_ORDERS=true), para probar el resumen del panel
        if (env('DEMO_ORDERS', false) && ! \App\Models\Order::exists()) {
            $admin = User::where('email', 'admin@tshirtparaguay.com')->first();
            $variants = \App\Models\ProductVariant::where('stock', '>', 2)->inRandomOrder()->take(14)->get()->values();
            foreach ($variants->chunk(2) as $k => $pair) {
                $local = $k % 2 === 1;
                $o = OrderBuilder::create($pair->mapWithKeys(fn ($v) => [$v->id => 1])->all(), [
                    'user_id' => $local ? null : $demo->id,
                    'seller_id' => $local ? $admin->id : null,
                    'channel' => $local ? 'local' : 'web',
                    'status' => $status = ($local ? 'finalizado' : ['pendiente', 'procesado', 'enviado', 'finalizado'][intdiv($k, 2) % 4]),
                    'payment_method' => $local ? 'efectivo' : 'transferencia',
                    'payment_status' => $local || $k > 2 ? 'pagado' : 'pendiente',
                    'customer_name' => $local ? 'Cliente de mostrador' : $demo->fullName(),
                    'customer_email' => $local ? null : $demo->email,
                    'customer_phone' => $local ? null : $demo->phone,
                    'delivery_method' => $local ? 'retiro' : 'envio',
                    'address' => $local ? null : $demo->address,
                    'city' => $local ? null : $demo->city,
                ], $local ? 0 : 25000, 0, ['applyStock' => $status !== 'pendiente']);
                $o->created_at = now()->subDays(13 - $k * 2)->setTime(10 + $k, 15);
                $o->processed_at = $o->stock_applied ? $o->created_at : null;
                $o->finished_at = $status === 'finalizado' ? $o->created_at : null;
                $o->save();
            }
        }
    }
}
