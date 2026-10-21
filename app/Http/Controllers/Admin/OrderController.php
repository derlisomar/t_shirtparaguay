<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\Receipts;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $view = $request->input('vista', 'en-curso');
        $q = Order::withCount('items')->latest();
        match ($view) {
            'finalizados' => $q->where('status', 'finalizado')->reorder()->orderByDesc('finished_at')->orderByDesc('id'),
            'cancelados' => $q->where('status', 'cancelado'),
            'todos' => null,
            default => $q->whereIn('status', Order::OPEN_STATUSES),
        };
        if ($s = $request->input('estado')) {
            $q->where('status', $s);
        }
        if ($request->input('comprobante') === 'si') {
            $q->whereNotNull('receipt_path');
        }
        if ($c = $request->input('canal')) {
            $q->where('channel', $c);
        }
        if ($t = $request->input('q')) {
            $q->where(fn ($w) => $w->where('number', 'ilike', "%$t%")
                ->orWhere('customer_name', 'ilike', "%$t%")
                ->orWhere('customer_phone', 'ilike', "%$t%"));
        }

        return view('admin.orders.index', [
            'orders' => $q->paginate(25)->withQueryString(),
            'view' => $view,
            'counts' => [
                'en-curso' => Order::whereIn('status', Order::OPEN_STATUSES)->count(),
                'finalizados' => Order::where('status', 'finalizado')->count(),
                'cancelados' => Order::where('status', 'cancelado')->count(),
            ],
        ]);
    }

    public function show(Order $order)
    {
        return view('admin.orders.show', ['order' => $order->load(['items.product.images', 'items.variant', 'payments', 'user', 'seller'])]);
    }

    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Order::STATUSES))],
            'payment_status' => ['required', Rule::in(array_keys(Order::PAYMENT_STATUSES))],
        ]);

        if ($order->status === 'cancelado' && $data['status'] !== 'cancelado') {
            return back()->withErrors(['status' => 'Un pedido cancelado no se puede reabrir. Cargalo de nuevo como venta.']);
        }
        if ($data['status'] === 'cancelado') {
            $order->cancel();
        } elseif ($data['status'] === 'pendiente') {
            if ($order->stock_applied) {
                $order->backToPending();
            }
        } elseif ($data['status'] === 'finalizado') {
            // Finalizado = entregado y pagado conforme
            $order->finish();

            return back()->with('ok', "Pedido {$order->number} finalizado. Ya está en «Finalizados».");
        } elseif (! $order->stock_applied) {
            // Procesado, enviado o entregado: recién acá se descuenta el stock
            $order->process($data['status']);
        }
        $order->update($data + ['finished_at' => null]);
        if ($data['payment_status'] === 'pagado') {
            $order->payments()->where('status', 'pendiente')->update(['status' => 'pagado', 'paid_at' => now()]);
        }

        return back()->with('ok', 'Pedido actualizado.');
    }

    /** Botón «Procesar pedido»: descuenta el stock y, si se marca, da el pago por recibido. */
    public function process(Request $request, Order $order)
    {
        if ($order->status !== 'pendiente') {
            return back()->withErrors(['status' => 'Este pedido ya no está pendiente.']);
        }
        $order->process();
        if ($request->boolean('paid') && $order->payment_status !== 'pagado') {
            $order->update(['payment_status' => 'pagado']);
            $order->payments()->where('status', 'pendiente')->update(['status' => 'pagado', 'paid_at' => now()]);
        }

        return back()->with('ok', "Pedido {$order->number} procesado. El stock ya se descontó.");
    }

    /** Ajustar cantidades o quitar prendas antes de finalizar (por ejemplo, si no hay todo lo que se pidió). */
    public function updateItems(Request $request, Order $order)
    {
        $data = $request->validate([
            'qty' => 'required|array',
            'qty.*' => 'required|integer|min:0|max:999',
        ]);
        $quantities = collect($data['qty'])->only($order->items()->pluck('id')->all())->all();
        $changed = $order->adjustItems($quantities);

        return back()->with('ok', $changed
            ? 'Prendas actualizadas. Nuevo total: '.money($order->fresh()->total).'.'
            : 'No cambiaste ninguna cantidad.');
    }

    /** Botón «Confirmar entrega y pago»: el pedido pasa a Finalizados. */
    public function finish(Order $order)
    {
        if (! in_array($order->status, Order::OPEN_STATUSES)) {
            return back()->withErrors(['status' => 'Este pedido ya está finalizado o cancelado.']);
        }
        $order->finish();

        return back()->with('ok', "Pedido {$order->number} finalizado. Ya está en «Finalizados».");
    }

    public function receipt(Order $order)
    {
        return Receipts::response($order);
    }

    public function ticket(Request $request, Order $order)
    {
        return view($request->input('formato') === 'a4' ? 'admin.orders.print' : 'admin.orders.ticket', ['order' => $order->load(['items', 'seller'])]);
    }
}
