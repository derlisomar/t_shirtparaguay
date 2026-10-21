<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Todo el resumen muestra solo pedidos confirmados: procesados (stock descontado) y no cancelados,
        // contados el día en que se confirmaron. Los pendientes solo aparecen en «Pedidos por confirmar».
        $sales = fn () => Order::where('status', '!=', 'cancelado')->where('stock_applied', true);
        $byMonth = $request->input('ver') === 'mes';

        $buckets = $this->buckets($byMonth);
        $from = $buckets[0]['start'];
        $fmt = $byMonth ? 'YYYY-MM' : 'YYYY-MM-DD';

        $salesRows = $sales()->where('processed_at', '>=', $from)
            ->selectRaw("to_char(processed_at, '$fmt') as k, channel, SUM(total) as t, COUNT(*) as n")
            ->groupBy('k', 'channel')->get();

        $salesChart = [];
        $ordersChart = [];
        foreach ($buckets as $b) {
            $s = $salesRows->where('k', $b['key']);
            $salesChart[] = $b + [
                'web' => (int) $s->where('channel', 'web')->sum('t'),
                'local' => (int) $s->where('channel', 'local')->sum('t'),
                'count' => (int) $s->sum('n'),
            ];
            $ordersChart[] = $b + [
                'web' => (int) $s->where('channel', 'web')->sum('n'),
                'local' => (int) $s->where('channel', 'local')->sum('n'),
            ];
        }

        $top = OrderItem::select('product_name', DB::raw('SUM(quantity) as qty'), DB::raw('SUM(order_items.total) as total'))
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', '!=', 'cancelado')->where('orders.stock_applied', true)
            ->where('orders.processed_at', '>=', now()->subDays(30))
            ->groupBy('product_name')->orderByDesc('qty')->take(6)->get();

        $today = $sales()->where('processed_at', '>=', today());
        $month = $sales()->where('processed_at', '>=', now()->startOfMonth());

        return view('admin.dashboard', [
            'byMonth' => $byMonth,
            'todayTotal' => (clone $today)->sum('total'),
            'todayCount' => (clone $today)->count(),
            'monthTotal' => (clone $month)->sum('total'),
            'monthWeb' => (clone $month)->where('channel', 'web')->sum('total'),
            'monthLocal' => (clone $month)->where('channel', 'local')->sum('total'),
            'finishedMonth' => Order::where('status', 'finalizado')->where('finished_at', '>=', now()->startOfMonth())->count(),
            'inProgress' => Order::whereIn('status', ['procesado', 'enviado', 'entregado'])->count(),
            'pending' => Order::where('status', 'pendiente')->count(),
            'pendingWithReceipt' => Order::where('status', 'pendiente')->whereNotNull('receipt_path')->count(),
            'customers' => User::where('is_admin', false)->count(),
            'lowStock' => ProductVariant::with(['product', 'size', 'color'])->where('stock', '<=', (int) setting('low_stock', 3))
                ->whereHas('product', fn ($q) => $q->where('is_active', true))->orderBy('stock')->take(8)->get(),
            'latest' => $sales()->orderByDesc('processed_at')->take(7)->get(),
            'salesChart' => $salesChart,
            'ordersChart' => $ordersChart,
            'top' => $top,
        ]);
    }

    /** Días (últimos 30) o meses (últimos 12) del gráfico, del más viejo al más nuevo. */
    private function buckets(bool $byMonth): array
    {
        $months = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
        $out = [];
        if ($byMonth) {
            $start = now()->startOfMonth()->subMonths(11);
            for ($i = 0; $i < 12; $i++) {
                $d = $start->copy()->addMonths($i);
                $out[] = ['start' => $d, 'key' => $d->format('Y-m'), 'label' => $months[$d->month - 1], 'long' => $months[$d->month - 1].' '.$d->year];
            }
        } else {
            $start = today()->subDays(29);
            for ($i = 0; $i < 30; $i++) {
                $d = $start->copy()->addDays($i);
                $out[] = ['start' => $d, 'key' => $d->format('Y-m-d'), 'label' => $d->format('d/m'), 'long' => $this->dayName($d).' '.$d->format('d/m')];
            }
        }

        return $out;
    }

    private function dayName(Carbon $d): string
    {
        return ['dom', 'lun', 'mar', 'mié', 'jue', 'vie', 'sáb'][$d->dayOfWeek];
    }
}
