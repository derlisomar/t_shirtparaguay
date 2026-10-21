<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Payments\PaymentManager;
use App\Support\OrderBuilder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Venta en el local (punto de venta) desde el panel. */
class PosController extends Controller
{
    public function index(PaymentManager $payments)
    {
        return view('admin.pos', [
            'categories' => Category::orderBy('sort_order')->get(),
            'methods' => $payments->localMethods(),
        ]);
    }

    public function search(Request $request)
    {
        $q = Product::active()->with(['images', 'variants.size', 'variants.color'])->orderBy('name');
        if ($t = trim((string) $request->input('q'))) {
            $q->where(fn ($w) => $w->where('name', 'ilike', "%$t%")->orWhere('sku', 'ilike', "%$t%"));
        }
        if ($c = $request->input('categoria')) {
            $q->where('category_id', $c);
        }

        return response()->json($q->take(40)->get()->map(fn ($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'price' => $p->price,
            'price_text' => money($p->price),
            'wholesale_price' => $p->hasWholesale() ? $p->wholesale_price : null,
            'wholesale_min' => $p->hasWholesale() ? $p->wholesale_min_qty : null,
            'image' => $p->mainImageUrl(),
            'variants' => $p->variants->sortBy(fn ($v) => [$v->color?->sort_order, $v->size?->sort_order])->values()->map(fn ($v) => [
                'id' => $v->id,
                'size' => $v->size?->name,
                'color' => $v->color?->name,
                'hex' => $v->color?->hex,
                'stock' => $v->stock,
            ]),
        ]));
    }

    public function customers(Request $request)
    {
        $t = trim((string) $request->input('q'));
        if (mb_strlen($t) < 2) {
            return response()->json([]);
        }

        return response()->json(User::where('is_admin', false)
            ->where(fn ($w) => $w->where('name', 'ilike', "%$t%")->orWhere('email', 'ilike', "%$t%")
                ->orWhere('phone', 'ilike', "%$t%")->orWhere('document', 'ilike', "%$t%"))
            ->take(6)->get(['id', 'name', 'email', 'phone', 'document', 'is_wholesale', 'purchase_limit']));
    }

    public function store(Request $request, PaymentManager $payments)
    {
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.variant_id' => 'required|integer|exists:product_variants,id',
            'items.*.qty' => 'required|integer|min:1',
            'payment_method' => ['required', Rule::in(array_keys($payments->localMethods()))],
            'customer_id' => 'nullable|exists:users,id',
            'customer_name' => 'nullable|string|max:120',
            'customer_phone' => 'nullable|string|max:40',
            'customer_document' => 'nullable|string|max:30',
            'discount' => 'nullable|integer|min:0',
            'cash_received' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:500',
            'wholesale' => 'nullable|boolean',
        ]);

        $items = [];
        foreach ($data['items'] as $row) {
            $items[$row['variant_id']] = ($items[$row['variant_id']] ?? 0) + $row['qty'];
        }
        $customer = ! empty($data['customer_id']) ? User::find($data['customer_id']) : null;

        $order = OrderBuilder::create($items, [
            'user_id' => $customer?->id,
            'seller_id' => $request->user()->id,
            'channel' => 'local',
            'status' => 'finalizado',
            'finished_at' => now(),
            'payment_method' => $data['payment_method'],
            'payment_status' => 'pagado',
            'customer_name' => $customer?->name ?: ($data['customer_name'] ?? null) ?: 'Cliente de mostrador',
            'customer_email' => $customer?->email,
            'customer_phone' => $customer?->phone ?: ($data['customer_phone'] ?? null),
            'customer_document' => $customer?->document ?: ($data['customer_document'] ?? null),
            'delivery_method' => 'retiro',
            'notes' => $data['notes'] ?? null,
            'cash_received' => $data['cash_received'] ?? null,
        ], 0, (int) ($data['discount'] ?? 0), [
            'customer' => $customer,
            'wholesale' => $request->boolean('wholesale'),
        ]);

        $payments->forMethod($order->payment_method)->start($order);

        return response()->json([
            'number' => $order->number,
            'total' => money($order->total),
            'ticket' => route('admin.orders.ticket', $order),
            'show' => route('admin.orders.show', $order),
        ]);
    }
}
