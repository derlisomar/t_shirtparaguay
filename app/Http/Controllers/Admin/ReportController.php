<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\ProductVariant;
use Carbon\Carbon;
use App\Support\Pdf;
use App\Support\Xlsx;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Reportes de ventas. Igual que el resumen, solo cuentan los pedidos confirmados
 * (procesados, con el stock descontado, y no cancelados), por la fecha en que se confirmaron.
 */
class ReportController extends Controller
{
    public const PERIODS = [
        'hoy' => 'Hoy',
        'ayer' => 'Ayer',
        'semana' => 'Esta semana',
        'mes' => 'Este mes',
        'mes-anterior' => 'Mes anterior',
        'anio' => 'Este año',
        'rango' => 'Elegir fechas',
    ];

    private const MONTHS = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

    private const DAYS = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

    public function index(Request $request)
    {
        $d = $this->data($request);

        return view('admin.reports', $d + [
            'pending' => Order::where('status', 'pendiente')->count(),
            'pendingWithReceipt' => Order::where('status', 'pendiente')->whereNotNull('receipt_path')->count(),
            'sections' => self::SECTIONS,
        ]);
    }

    /** Reportes que se pueden descargar, en el orden en que aparecen en la pantalla. */
    public const SECTIONS = [
        'resumen' => 'Resumen del período',
        'ventas-por-fecha' => 'Ventas por fecha',
        'mas-vendidos' => 'Productos más vendidos',
        'lineas' => 'Ventas por línea y categoría',
        'canal' => 'Ventas por canal',
        'medio-de-pago' => 'Ventas por medio de pago',
        'vendedor' => 'Ventas por vendedor',
        'talles' => 'Talles más vendidos',
        'colores' => 'Colores más vendidos',
        'dias' => 'Ventas por día de la semana',
        'horarios' => 'Ventas por horario',
        'clientes' => 'Mejores clientes',
        'sin-ventas' => 'Productos sin ventas',
        'stock' => 'Stock actual y poco stock',
        'ventas' => 'Detalle de ventas',
        'prendas' => 'Detalle de prendas vendidas',
    ];

    /** Descarga un reporte (o todos) en Excel o en PDF, en formato planilla. */
    public function export(Request $request, string $type)
    {
        abort_unless($type === 'todo' || array_key_exists($type, self::SECTIONS), 404);
        $format = $request->input('formato') === 'pdf' ? 'pdf' : 'xlsx';
        $d = $this->data($request);

        $keys = $type === 'todo' ? array_keys(self::SECTIONS) : [$type];
        if ($type === 'todo' && $format === 'pdf') {
            $keys = array_values(array_diff($keys, ['prendas'])); // el detalle de prendas es muy largo para el PDF completo
        }
        $sections = array_map(fn ($k) => $this->section($k, $d), $keys);

        $store = setting('store_name', 't_shirtparaguay');
        $title = $type === 'todo' ? 'Reporte de ventas' : self::SECTIONS[$type];
        $subtitle = $store.' · '.$d['label'].($d['channel'] ? ' · '.($d['channel'] === 'local' ? 'Venta en el local' : 'Tienda online') : ' · Web y local')
            .' · Solo pedidos confirmados';
        $file = ($type === 'todo' ? 'reporte-de-ventas' : $type).'-'.$d['from']->format('Y-m-d').'-al-'.$d['to']->format('Y-m-d');

        if ($format === 'pdf') {
            $wide = in_array($type, ['ventas', 'prendas'], true) || $type === 'todo';

            return response(Pdf::build($title, $subtitle, $sections, $wide), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$file.'.pdf"',
            ]);
        }

        $sheets = array_map(fn ($s) => $s + ['name' => $s['sheet'], 'subtitle' => $subtitle], $sections);

        return response(Xlsx::build($sheets), 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$file.'.xlsx"',
        ]);
    }

    /** Todos los números del período elegido. */
    private function data(Request $request): array
    {
        [$period, $from, $to] = $this->period($request);
        $channel = in_array($request->input('canal'), ['web', 'local'], true) ? $request->input('canal') : null;

        $orders = $this->orders($from, $to, $channel);
        $items = $this->items($from, $to, $channel);

        $total = (int) (clone $orders)->sum('total');
        $count = (clone $orders)->count();
        $units = (int) (clone $items)->sum('order_items.quantity');

        // Mismo largo de período, inmediatamente antes, para comparar
        $days = $from->diffInDays($to) + 1;
        $prevTo = $from->copy()->subSecond();
        $prevFrom = $from->copy()->subDays((int) round($days));
        $prev = $this->orders($prevFrom, $prevTo, $channel);
        $byMonth = $days > 62;

        return [
            'period' => $period,
            'from' => $from,
            'to' => $to,
            'days' => (int) round($days),
            'channel' => $channel,
            'label' => $this->label($period, $from, $to),
            'total' => $total,
            'count' => $count,
            'units' => $units,
            'average' => $count ? (int) round($total / $count) : 0,
            'discount' => (int) (clone $orders)->sum('discount'),
            'shipping' => (int) (clone $orders)->sum('shipping'),
            'wholesale' => (clone $orders)->where('is_wholesale', true)->selectRaw('COUNT(*) as n, COALESCE(SUM(total),0) as t')->first(),
            'cancelled' => Order::where('status', 'cancelado')->whereBetween('updated_at', [$from, $to])
                ->when($channel, fn ($q) => $q->where('channel', $channel))->count(),
            'prevTotal' => (int) (clone $prev)->sum('total'),
            'prevCount' => (clone $prev)->count(),
            'prevLabel' => $prevFrom->format('d/m/Y').' al '.$prevTo->format('d/m/Y'),
            'byMonth' => $byMonth,
            'chart' => $this->chart($from, $to, $channel, $byMonth),
            'byChannel' => (clone $orders)->selectRaw('channel as k, COUNT(*) as n, SUM(total) as t')->groupBy('channel')->orderByDesc('t')->get(),
            'byPayment' => (clone $orders)->selectRaw('payment_method as k, COUNT(*) as n, SUM(total) as t')->groupBy('payment_method')->orderByDesc('t')->get(),
            'bySeller' => (clone $orders)->leftJoin('users', 'users.id', '=', 'orders.seller_id')
                ->selectRaw("COALESCE(NULLIF(TRIM(CONCAT(users.name, ' ', COALESCE(users.last_name, ''))), ''), 'Tienda online') as k, COUNT(*) as n, SUM(orders.total) as t")
                ->groupBy('k')->orderByDesc('t')->get(),
            'products' => $this->products($items)->get(),
            'byCategory' => $this->byCategory($items),
            'bySize' => (clone $items)->selectRaw("COALESCE(NULLIF(order_items.size, ''), 'Sin talle') as k, SUM(order_items.quantity) as q, SUM(order_items.total) as t")
                ->groupBy('k')->orderByDesc('q')->get(),
            'byColor' => (clone $items)->selectRaw("COALESCE(NULLIF(order_items.color, ''), 'Sin color') as k, SUM(order_items.quantity) as q, SUM(order_items.total) as t")
                ->groupBy('k')->orderByDesc('q')->get(),
            'customers' => (clone $orders)
                ->selectRaw("COALESCE(CAST(user_id AS varchar), NULLIF(customer_document, ''), customer_name) as k, MAX(customer_name) as name, MAX(customer_phone) as phone, MAX(user_id) as uid, COUNT(*) as n, SUM(total) as t, MAX(processed_at) as last")
                ->groupBy('k')->orderByDesc('t')->get(),
            'byWeekday' => $this->byWeekday($orders),
            'byHour' => (clone $orders)->selectRaw('EXTRACT(HOUR FROM processed_at)::int as h, COUNT(*) as n, SUM(total) as t')->groupBy('h')->orderBy('h')->get(),
            'noSales' => $this->noSales($from, $to),
            'stock' => ProductVariant::whereHas('product', fn ($q) => $q->where('is_active', true))
                ->join('products', 'products.id', '=', 'product_variants.product_id')
                ->selectRaw('COALESCE(SUM(GREATEST(product_variants.stock, 0)),0) as units, COALESCE(SUM(GREATEST(product_variants.stock, 0) * products.price),0) as value, SUM(CASE WHEN product_variants.stock <= 0 THEN 1 ELSE 0 END) as out')
                ->first(),
            'lowStock' => ProductVariant::with(['product', 'size', 'color'])->where('stock', '<=', (int) setting('low_stock', 3))
                ->whereHas('product', fn ($q) => $q->where('is_active', true))->orderBy('stock')->get(),
            'list' => (clone $orders)->with('seller')->withCount('items')->orderByDesc('processed_at')->get(),
            'orderItems' => fn () => $this->items($from, $to, $channel)
                ->select('orders.number', 'orders.processed_at', 'orders.customer_name', 'orders.channel', 'order_items.product_name',
                    'order_items.size', 'order_items.color', 'order_items.price', 'order_items.quantity', 'order_items.is_wholesale', 'order_items.total')
                ->orderBy('orders.processed_at')->orderBy('order_items.id')->get(),
        ];
    }

    /** Una planilla: título, columnas, filas y totales. */
    private function section(string $key, array $d): array
    {
        $date = fn ($v) => $v ? Carbon::parse($v)->format('d/m/Y H:i') : '';
        $pct = fn ($v, $of) => $of ? (int) round($v * 100 / $of) : 0;
        $chLabel = fn ($k) => $k === 'local' ? 'Venta en el local' : 'Tienda online';
        $total = $d['total'];
        $sumRows = fn ($rows, $label, $n, $t) => [$label, $n, $t, $total ? 100 : 0];

        $s = match ($key) {
            'resumen' => [
                'head' => ['Indicador', 'Este período', 'Período anterior', 'Variación'],
                'types' => ['text', 'money', 'money', 'text'],
                'note' => 'Período anterior: '.$d['prevLabel'],
                'rows' => [
                    ['Total vendido', ['v' => $total, 't' => 'money'], ['v' => $d['prevTotal'], 't' => 'money'], $this->variation($total, $d['prevTotal'])],
                    ['Cantidad de ventas', ['v' => $d['count'], 't' => 'int'], ['v' => $d['prevCount'], 't' => 'int'], $this->variation($d['count'], $d['prevCount'])],
                    ['Ticket promedio', ['v' => $d['average'], 't' => 'money'], '', ''],
                    ['Promedio vendido por día', ['v' => (int) round($total / max(1, $d['days'])), 't' => 'money'], '', ''],
                    ['Prendas vendidas', ['v' => $d['units'], 't' => 'int'], '', ''],
                    ['Prendas por venta', ['v' => $d['count'] ? round($d['units'] / $d['count'], 1) : 0, 't' => 'dec'], '', ''],
                    ['Ventas mayoristas', ['v' => (int) ($d['wholesale']->t ?? 0), 't' => 'money'], '', ''],
                    ['Cantidad de ventas mayoristas', ['v' => (int) ($d['wholesale']->n ?? 0), 't' => 'int'], '', ''],
                    ['Descuentos dados', ['v' => $d['discount'], 't' => 'money'], '', ''],
                    ['Cobrado por envíos', ['v' => $d['shipping'], 't' => 'money'], '', ''],
                    ['Pedidos cancelados', ['v' => $d['cancelled'], 't' => 'int'], '', ''],
                ],
            ],
            'ventas-por-fecha' => [
                'head' => [$d['byMonth'] ? 'Mes' : 'Fecha', 'Tienda online', 'Venta en el local', 'Total', 'Ventas'],
                'types' => ['text', 'money', 'money', 'money', 'int'],
                'rows' => array_map(fn ($c) => [$d['byMonth'] ? ucfirst($c['long']) : Carbon::parse($c['key'])->format('d/m/Y').' '.mb_substr($c['long'], 0, 3), $c['web'], $c['local'], $c['web'] + $c['local'], $c['count']], $d['chart']),
                'total' => ['Total', array_sum(array_column($d['chart'], 'web')), array_sum(array_column($d['chart'], 'local')), $total, $d['count']],
            ],
            'mas-vendidos' => [
                'head' => ['#', 'Producto', 'Unidades', 'Pedidos', 'Total vendido', '% del total'],
                'types' => ['int', 'text', 'int', 'int', 'money', 'pct'],
                'rows' => $d['products']->values()->map(fn ($p, $i) => [$i + 1, $p->product_name, (int) $p->qty, (int) $p->orders, (int) $p->total, $pct($p->total, $total)])->all(),
                'total' => ['', 'Total', $d['units'], $d['count'], $total, $total ? 100 : 0],
            ],
            'lineas' => [
                'head' => ['Línea / categoría', 'Unidades', 'Total vendido', '% del total'],
                'types' => ['text', 'int', 'money', 'pct'],
                'rows' => $d['byCategory']->map(fn ($r) => [$r->k, $r->q, $r->t, $pct($r->t, $total)])->all(),
                'total' => ['Total', $d['units'], $total, $total ? 100 : 0],
            ],
            'canal' => [
                'head' => ['Canal', 'Ventas', 'Total vendido', '% del total'],
                'types' => ['text', 'int', 'money', 'pct'],
                'rows' => $d['byChannel']->map(fn ($r) => [$chLabel($r->k), (int) $r->n, (int) $r->t, $pct($r->t, $total)])->all(),
                'total' => $sumRows(null, 'Total', $d['count'], $total),
            ],
            'medio-de-pago' => [
                'head' => ['Medio de pago', 'Ventas', 'Total vendido', '% del total'],
                'types' => ['text', 'int', 'money', 'pct'],
                'rows' => $d['byPayment']->map(fn ($r) => [config('payments.methods.'.$r->k.'.label', ucfirst((string) $r->k)), (int) $r->n, (int) $r->t, $pct($r->t, $total)])->all(),
                'total' => $sumRows(null, 'Total', $d['count'], $total),
            ],
            'vendedor' => [
                'head' => ['Vendedor', 'Ventas', 'Total vendido', '% del total'],
                'types' => ['text', 'int', 'money', 'pct'],
                'rows' => $d['bySeller']->map(fn ($r) => [$r->k, (int) $r->n, (int) $r->t, $pct($r->t, $total)])->all(),
                'total' => $sumRows(null, 'Total', $d['count'], $total),
            ],
            'talles' => [
                'head' => ['Talle', 'Unidades', 'Total vendido', '% de las prendas'],
                'types' => ['text', 'int', 'money', 'pct'],
                'rows' => $d['bySize']->map(fn ($r) => [$r->k, (int) $r->q, (int) $r->t, $pct($r->q, $d['units'])])->all(),
                'total' => ['Total', $d['units'], $total, $d['units'] ? 100 : 0],
            ],
            'colores' => [
                'head' => ['Color', 'Unidades', 'Total vendido', '% de las prendas'],
                'types' => ['text', 'int', 'money', 'pct'],
                'rows' => $d['byColor']->map(fn ($r) => [$r->k, (int) $r->q, (int) $r->t, $pct($r->q, $d['units'])])->all(),
                'total' => ['Total', $d['units'], $total, $d['units'] ? 100 : 0],
            ],
            'dias' => [
                'head' => ['Día', 'Ventas', 'Total vendido', '% del total'],
                'types' => ['text', 'int', 'money', 'pct'],
                'rows' => $d['byWeekday']->map(fn ($r) => [$r->k, $r->n, $r->t, $pct($r->t, $total)])->all(),
                'total' => $sumRows(null, 'Total', $d['count'], $total),
            ],
            'horarios' => [
                'head' => ['Horario', 'Ventas', 'Total vendido', '% del total'],
                'types' => ['text', 'int', 'money', 'pct'],
                'rows' => $d['byHour']->map(fn ($r) => [sprintf('%02d:00 a %02d:00', $r->h, ($r->h + 1) % 24), (int) $r->n, (int) $r->t, $pct($r->t, $total)])->all(),
                'total' => $sumRows(null, 'Total', $d['count'], $total),
            ],
            'clientes' => [
                'head' => ['#', 'Cliente', 'Celular', 'Compras', 'Total comprado', 'Última compra'],
                'types' => ['int', 'text', 'text', 'int', 'money', 'text'],
                'rows' => $d['customers']->values()->map(fn ($c, $i) => [$i + 1, $c->name, $c->phone, (int) $c->n, (int) $c->t, Carbon::parse($c->last)->format('d/m/Y')])->all(),
            ],
            'sin-ventas' => [
                'head' => ['Producto', 'Prendas en stock'],
                'types' => ['text', 'int'],
                'note' => 'Productos activos con stock que no se vendieron en el período.',
                'rows' => $d['noSales']->map(fn ($p) => [$p->name, (int) $p->stock])->all(),
            ],
            'stock' => [
                'head' => ['Producto', 'Talle y color', 'Stock'],
                'types' => ['text', 'text', 'int'],
                'note' => 'Prendas en stock: '.number_format((int) $d['stock']->units, 0, ',', '.').' · Valor a precio de venta: '.money($d['stock']->value)
                    .' · Combinaciones agotadas: '.(int) $d['stock']->out.'. Abajo, las que tienen '.(int) setting('low_stock', 3).' o menos.',
                'rows' => $d['lowStock']->map(fn ($v) => [$v->product->name, $v->label(), (int) $v->stock])->all(),
            ],
            'ventas' => [
                'head' => ['Pedido', 'Fecha', 'Cliente', 'Documento', 'Celular', 'Canal', 'Medio de pago', 'Vendedor', 'Mayorista', 'Subtotal', 'Descuento', 'Envío', 'Total'],
                'types' => ['text', 'text', 'text', 'text', 'text', 'text', 'text', 'text', 'text', 'money', 'money', 'money', 'money'],
                'rows' => $d['list']->sortBy('processed_at')->map(fn ($o) => [$o->number, $date($o->processed_at), $o->customer_name, $o->customer_document, $o->customer_phone,
                    $o->channel === 'local' ? 'Local' : 'Web', $o->paymentLabel(), $o->seller?->name ?? 'Tienda online',
                    $o->is_wholesale ? 'Sí' : 'No', (int) $o->subtotal, (int) $o->discount, (int) $o->shipping, (int) $o->total])->values()->all(),
                'total' => ['Total', $d['count'].' ventas', '', '', '', '', '', '', '', (int) $d['list']->sum('subtotal'), $d['discount'], $d['shipping'], $total],
            ],
            'prendas' => [
                'head' => ['Pedido', 'Fecha', 'Cliente', 'Canal', 'Producto', 'Talle', 'Color', 'Precio', 'Cantidad', 'Mayorista', 'Total'],
                'types' => ['text', 'text', 'text', 'text', 'text', 'text', 'text', 'money', 'int', 'text', 'money'],
                'rows' => ($d['orderItems'])()->map(fn ($i) => [$i->number, $date($i->processed_at), $i->customer_name, $i->channel === 'local' ? 'Local' : 'Web',
                    $i->product_name, $i->size, $i->color, (int) $i->price, (int) $i->quantity, $i->is_wholesale ? 'Sí' : 'No', (int) $i->total])->all(),
                'total' => ['Total', '', '', '', '', '', '', '', $d['units'], '', $total],
            ],
        };

        $sheet = ['resumen' => 'Resumen', 'ventas-por-fecha' => 'Por fecha', 'mas-vendidos' => 'Más vendidos', 'lineas' => 'Líneas', 'canal' => 'Canal',
            'medio-de-pago' => 'Medio de pago', 'vendedor' => 'Vendedor', 'talles' => 'Talles', 'colores' => 'Colores', 'dias' => 'Días',
            'horarios' => 'Horarios', 'clientes' => 'Clientes', 'sin-ventas' => 'Sin ventas', 'stock' => 'Stock', 'ventas' => 'Ventas', 'prendas' => 'Prendas'][$key];

        return $s + ['title' => self::SECTIONS[$key], 'sheet' => $sheet];
    }

    private function variation(int $now, int $before): string
    {
        if (! $before) {
            return $now ? 'Sin datos antes' : '';
        }
        $v = (int) round(($now - $before) * 100 / $before);

        return ($v > 0 ? '+' : '').$v.'%';
    }

    /** @return array{0:string,1:Carbon,2:Carbon} */
    private function period(Request $request): array
    {
        $period = array_key_exists($request->input('periodo'), self::PERIODS) ? $request->input('periodo') : 'mes';
        $now = now();

        [$from, $to] = match ($period) {
            'hoy' => [today(), $now->copy()->endOfDay()],
            'ayer' => [today()->subDay(), today()->subDay()->endOfDay()],
            'semana' => [$now->copy()->startOfWeek(Carbon::MONDAY), $now->copy()->endOfDay()],
            'mes-anterior' => [$now->copy()->subMonthNoOverflow()->startOfMonth(), $now->copy()->subMonthNoOverflow()->endOfMonth()],
            'anio' => [$now->copy()->startOfYear(), $now->copy()->endOfDay()],
            'rango' => $this->range($request),
            default => [$now->copy()->startOfMonth(), $now->copy()->endOfDay()],
        };

        return [$period, $from, $to];
    }

    private function range(Request $request): array
    {
        $parse = function ($v) {
            try {
                return $v ? Carbon::createFromFormat('Y-m-d', $v)->startOfDay() : null;
            } catch (\Throwable $e) {
                return null;
            }
        };
        $from = $parse($request->input('desde')) ?? now()->startOfMonth();
        $to = ($parse($request->input('hasta')) ?? today())->endOfDay();
        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }
        if ($from->diffInDays($to) > 3 * 366) {
            $from = $to->copy()->subYears(3)->startOfDay();
        }

        return [$from, $to];
    }

    private function label(string $period, Carbon $from, Carbon $to): string
    {
        if ($from->isSameDay($to)) {
            return self::DAYS[$from->dayOfWeek].' '.$from->format('d/m/Y');
        }
        if ($period === 'mes' || $period === 'mes-anterior') {
            return ucfirst(self::MONTHS[$from->month - 1]).'. '.$from->year;
        }

        return $from->format('d/m/Y').' al '.$to->format('d/m/Y');
    }

    private function orders(Carbon $from, Carbon $to, ?string $channel)
    {
        return Order::query()->where('orders.status', '!=', 'cancelado')->where('orders.stock_applied', true)
            ->whereBetween('orders.processed_at', [$from, $to])
            ->when($channel, fn ($q) => $q->where('orders.channel', $channel));
    }

    private function items(Carbon $from, Carbon $to, ?string $channel)
    {
        return DB::table('order_items')->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', '!=', 'cancelado')->where('orders.stock_applied', true)
            ->whereBetween('orders.processed_at', [$from, $to])
            ->when($channel, fn ($q) => $q->where('orders.channel', $channel));
    }

    private function products($items)
    {
        return (clone $items)
            ->selectRaw('order_items.product_name, SUM(order_items.quantity) as qty, SUM(order_items.total) as total, COUNT(DISTINCT order_items.order_id) as orders')
            ->groupBy('order_items.product_name')->orderByDesc('qty')->orderByDesc('total');
    }

    /** Ventas por línea (o categoría si el producto no tiene línea). */
    private function byCategory($items)
    {
        $rows = (clone $items)->leftJoin('products', 'products.id', '=', 'order_items.product_id')
            ->selectRaw('products.category_id as cid, SUM(order_items.quantity) as q, SUM(order_items.total) as t')
            ->groupBy('products.category_id')->get();
        $cats = Category::with('parent')->whereIn('id', $rows->pluck('cid')->filter())->get()->keyBy('id');

        return $rows->map(fn ($r) => (object) [
            'k' => $r->cid && isset($cats[$r->cid]) ? $cats[$r->cid]->fullName() : 'Sin categoría',
            'q' => (int) $r->q,
            't' => (int) $r->t,
        ])->sortByDesc('t')->values();
    }

    private function byWeekday($orders)
    {
        $rows = (clone $orders)->selectRaw('EXTRACT(DOW FROM processed_at)::int as d, COUNT(*) as n, SUM(total) as t')
            ->groupBy('d')->get()->keyBy('d');

        // De lunes a domingo
        return collect([1, 2, 3, 4, 5, 6, 0])->map(fn ($d) => (object) [
            'k' => self::DAYS[$d],
            'n' => (int) ($rows[$d]->n ?? 0),
            't' => (int) ($rows[$d]->t ?? 0),
        ]);
    }

    /** Productos activos con stock que no se vendieron en el período. */
    private function noSales(Carbon $from, Carbon $to)
    {
        $sold = DB::table('order_items')->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', '!=', 'cancelado')->where('orders.stock_applied', true)
            ->whereBetween('orders.processed_at', [$from, $to])->whereNotNull('order_items.product_id')
            ->distinct()->pluck('order_items.product_id');

        return DB::table('products')->join('product_variants', 'product_variants.product_id', '=', 'products.id')
            ->where('products.is_active', true)->whereNotIn('products.id', $sold)
            ->selectRaw('products.id, products.name, SUM(GREATEST(product_variants.stock, 0)) as stock')
            ->groupBy('products.id', 'products.name')->havingRaw('SUM(GREATEST(product_variants.stock, 0)) > 0')
            ->orderByDesc('stock')->take(12)->get();
    }

    private function chart(Carbon $from, Carbon $to, ?string $channel, bool $byMonth): array
    {
        $fmt = $byMonth ? 'YYYY-MM' : 'YYYY-MM-DD';
        $rows = $this->orders($from, $to, $channel)
            ->selectRaw("to_char(processed_at, '$fmt') as k, channel, SUM(total) as t, COUNT(*) as n")
            ->groupBy('k', 'channel')->get();

        $out = [];
        $d = $byMonth ? $from->copy()->startOfMonth() : $from->copy()->startOfDay();
        while ($d->lte($to)) {
            $key = $d->format($byMonth ? 'Y-m' : 'Y-m-d');
            $s = $rows->where('k', $key);
            $out[] = [
                'key' => $key,
                'label' => $byMonth ? self::MONTHS[$d->month - 1] : $d->format('d/m'),
                'long' => $byMonth ? self::MONTHS[$d->month - 1].' '.$d->year : mb_substr(self::DAYS[$d->dayOfWeek], 0, 3).' '.$d->format('d/m'),
                'web' => (int) $s->where('channel', 'web')->sum('t'),
                'local' => (int) $s->where('channel', 'local')->sum('t'),
                'count' => (int) $s->sum('n'),
            ];
            $byMonth ? $d->addMonthNoOverflow() : $d->addDay();
        }

        return $out;
    }
}
