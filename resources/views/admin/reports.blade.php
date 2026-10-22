@extends('layouts.admin')
@section('title', 'Reportes')
@php
  $q = array_filter(['periodo' => $period, 'desde' => $period === 'rango' ? $from->format('Y-m-d') : null, 'hasta' => $period === 'rango' ? $to->format('Y-m-d') : null, 'canal' => $channel]);
@endphp
@section('actions')
  <a class="btn soft sm" href="{{ route('admin.reports.export', ['type' => 'todo'] + $q) }}" title="Todos los reportes en un Excel, una hoja por reporte"><svg class="i" style="width:16px"><use href="#i-xls"/></svg> Excel</a>
  <a class="btn soft sm" href="{{ route('admin.reports.export', ['type' => 'todo', 'formato' => 'pdf'] + $q) }}" title="Todos los reportes en un PDF"><svg class="i" style="width:16px"><use href="#i-pdf"/></svg> PDF</a>
  <button type="button" class="btn soft sm hide-sm" onclick="window.print()"><svg class="i" style="width:16px"><use href="#i-print"/></svg> Imprimir</button>
@endsection
@section('content')
@php
  $diff = function ($now, $before) {
      if (! $before) return null;
      return (int) round(($now - $before) * 100 / $before);
  };
  $dTotal = $diff($total, $prevTotal);
  $dCount = $diff($count, $prevCount);
  $pct = fn ($v, $of) => $of ? round($v * 100 / $of) : 0;
  $payLabel = fn ($k) => config('payments.methods.'.$k.'.label', ucfirst((string) $k));
  $chLabel = fn ($k) => $k === 'local' ? 'Venta en el local' : 'Tienda online';
  $maxWeek = max(1, $byWeekday->max('t'));
  $maxHour = max(1, (int) $byHour->max('t'));
  $topProducts = $products->take(15);
@endphp

<form class="filters-bar rep-filters no-print" data-report-form>
  <div class="seg-tabs" style="flex-wrap:wrap">
    @foreach (\App\Http\Controllers\Admin\ReportController::PERIODS as $k => $l)
      @if ($k !== 'rango')
        <a href="{{ route('admin.reports', array_filter(['periodo' => $k, 'canal' => $channel])) }}" @class(['on' => $period === $k])>{{ $l }}</a>
      @endif
    @endforeach
  </div>
  <input type="hidden" name="periodo" value="rango">
  <label class="rep-date"><span>Desde</span><input class="input" type="date" name="desde" value="{{ $from->format('Y-m-d') }}" max="{{ today()->format('Y-m-d') }}"></label>
  <label class="rep-date"><span>Hasta</span><input class="input" type="date" name="hasta" value="{{ $to->format('Y-m-d') }}" max="{{ today()->format('Y-m-d') }}"></label>
  <select class="select" name="canal"><option value="">Web y local</option><option value="web" @selected($channel === 'web')>Tienda online</option><option value="local" @selected($channel === 'local')>Venta en el local</option></select>
  <button class="btn sm soft">Ver</button>
</form>

@if ($pending)
  <a class="rep-alert no-print" href="{{ route('admin.orders.index', ['estado' => 'pendiente']) }}">
    <b>{{ $pending }} {{ $pending === 1 ? 'pedido por confirmar' : 'pedidos por confirmar' }}</b>
    <span>{{ $pendingWithReceipt ? $pendingWithReceipt.' con comprobante · ' : '' }}No suman en los reportes hasta que los proceses. Ver pedidos →</span>
  </a>
@endif

<div class="rep-title">
  <h2>{{ $label }} @include('admin.partials.dl', ['k' => 'resumen'])</h2>
  <p class="help">{{ $channel ? $chLabel($channel).' · ' : '' }}Solo pedidos confirmados y ventas del local, por el día en que se confirmaron. Cada reporte se descarga en Excel o PDF; los botones de arriba bajan todos juntos.</p>
</div>

<div class="kpis rep-kpis">
  <div class="kpi hl"><span>Total vendido</span><b>{{ money($total) }}</b>
    <small>@if (! is_null($dTotal))<span @class(['up' => $dTotal >= 0, 'down' => $dTotal < 0])>{{ $dTotal >= 0 ? '▲' : '▼' }} {{ abs($dTotal) }}%</span> vs. {{ $prevLabel }}@else Sin ventas en el período anterior @endif</small></div>
  <div class="kpi"><span>Ventas</span><b>{{ $count }}</b>
    <small>@if (! is_null($dCount))<span @class(['up' => $dCount >= 0, 'down' => $dCount < 0])>{{ $dCount >= 0 ? '▲' : '▼' }} {{ abs($dCount) }}%</span> · @endif{{ $prevCount }} en el período anterior</small></div>
  <div class="kpi"><span>Ticket promedio</span><b>{{ money($average) }}</b><small>Por venta · {{ $days > 1 ? money(round($total / $days)).' por día' : 'del día' }}</small></div>
  <div class="kpi"><span>Prendas vendidas</span><b>{{ number_format($units, 0, ',', '.') }}</b><small>{{ $count ? number_format($units / $count, 1, ',', '.') : 0 }} por venta</small></div>
</div>
<div class="rep-mini">
  <div><span>Ventas mayoristas</span><b>{{ money($wholesale->t ?? 0) }}</b><small>{{ (int) ($wholesale->n ?? 0) }} {{ (int) ($wholesale->n ?? 0) === 1 ? 'venta' : 'ventas' }}</small></div>
  <div><span>Descuentos dados</span><b>{{ money($discount) }}</b></div>
  <div><span>Cobrado por envíos</span><b>{{ money($shipping) }}</b></div>
  <div><span>Pedidos cancelados</span><b>{{ $cancelled }}</b></div>
</div>

@if ($days > 1)
<div class="box" style="margin-top:14px">
  <div class="chart-head">
    <div>
      <h2>Ventas {{ $byMonth ? 'por mes' : 'por día' }} @include('admin.partials.dl', ['k' => 'ventas-por-fecha'])</h2>
      <p class="help" style="margin-top:4px">Pasá el mouse (o tocá) una barra para ver el detalle.</p>
    </div>
    <div class="sum r">{{ money($total) }}<small>{{ $count }} {{ $count === 1 ? 'venta' : 'ventas' }}</small></div>
  </div>
  @include('admin.partials.bar-chart', ['data' => $chart, 'money' => true, 'series' => [
    ['key' => 'web', 'label' => 'Tienda online', 'color' => 'var(--accent)'],
    ['key' => 'local', 'label' => 'Venta en el local', 'color' => 'var(--ink)'],
  ]])
</div>
@endif

<div class="cols-even">
  <div class="box">
    <h2>Más vendidos @include('admin.partials.dl', ['k' => 'mas-vendidos'])</h2>
    @if ($products->isEmpty())
      <p class="help">No hubo ventas en este período.</p>
    @else
      <div class="tbl-wrap" style="margin:0 -8px"><table class="t rep-t rep-rank">
        <thead><tr><th>#</th><th>Producto</th><th class="r">Unid.</th><th class="r">Total</th></tr></thead>
        @foreach ($topProducts as $i => $p)
          <tr><td class="help">{{ $i + 1 }}</td><td>{{ $p->product_name }}<div class="hbar"><i style="width:{{ $pct($p->qty, $products->first()->qty) }}%"></i></div></td><td class="r"><b>{{ $p->qty }}</b></td><td class="r">{{ money($p->total) }}<br><span class="help">{{ $pct($p->total, $total) }}%</span></td></tr>
        @endforeach
      </table></div>
      @if ($products->count() > $topProducts->count())<p class="help" style="margin-top:8px">Se muestran los 15 primeros de {{ $products->count() }}. La descarga trae todos.</p>@endif
    @endif
  </div>
  <div class="box">
    <h2>Por línea y categoría @include('admin.partials.dl', ['k' => 'lineas'])</h2>
    @forelse ($byCategory as $c)
      <div class="rank"><div class="rk"><span>{{ $c->k }}</span><span><b>{{ money($c->t) }}</b> <span class="help">· {{ $c->q }} u.</span></span></div><div class="hbar"><i style="width:{{ $pct($c->t, $byCategory->first()->t) }}%"></i></div></div>
    @empty <p class="help">No hubo ventas en este período.</p> @endforelse
  </div>
</div>

<div class="cols-3">
  @foreach ([['Por canal', 'canal', $byChannel, $chLabel], ['Por medio de pago', 'medio-de-pago', $byPayment, $payLabel], ['Por vendedor', 'vendedor', $bySeller, fn ($k) => $k]] as [$title, $key, $rows, $name])
    <div class="box">
      <h2>{{ $title }} @include('admin.partials.dl', ['k' => $key])</h2>
      @forelse ($rows as $r)
        <div class="rank"><div class="rk"><span>{{ $name($r->k) }}</span><span><b>{{ money($r->t) }}</b> <span class="help">· {{ $pct($r->t, $total) }}%</span></span></div><div class="hbar"><i style="width:{{ $pct($r->t, $total) }}%"></i></div><small class="help">{{ $r->n }} {{ $r->n == 1 ? 'venta' : 'ventas' }}</small></div>
      @empty <p class="help">Sin datos.</p> @endforelse
    </div>
  @endforeach
</div>

<div class="cols-3">
  <div class="box">
    <h2>Talles más vendidos @include('admin.partials.dl', ['k' => 'talles'])</h2>
    @forelse ($bySize as $r)
      <div class="rank"><div class="rk"><span>{{ $r->k }}</span><span><b>{{ $r->q }} u.</b> <span class="help">· {{ money($r->t) }}</span></span></div><div class="hbar"><i style="width:{{ $pct($r->q, $bySize->first()->q) }}%"></i></div></div>
    @empty <p class="help">Sin datos.</p> @endforelse
  </div>
  <div class="box">
    <h2>Colores más vendidos @include('admin.partials.dl', ['k' => 'colores'])</h2>
    @forelse ($byColor->take(10) as $r)
      <div class="rank"><div class="rk"><span>{{ $r->k }}</span><span><b>{{ $r->q }} u.</b> <span class="help">· {{ money($r->t) }}</span></span></div><div class="hbar"><i style="width:{{ $pct($r->q, $byColor->first()->q) }}%"></i></div></div>
    @empty <p class="help">Sin datos.</p> @endforelse
  </div>
  <div class="box">
    <h2>Días que más se vende @include('admin.partials.dl', ['k' => 'dias'])</h2>
    @foreach ($byWeekday as $r)
      <div class="rank"><div class="rk"><span>{{ $r->k }}</span><span><b>{{ money($r->t) }}</b> <span class="help">· {{ $r->n }}</span></span></div><div class="hbar"><i style="width:{{ $pct($r->t, $maxWeek) }}%"></i></div></div>
    @endforeach
  </div>
</div>

<div class="cols-even">
  <div class="box">
    <h2>Mejores clientes @include('admin.partials.dl', ['k' => 'clientes'])</h2>
    @if ($customers->isEmpty())
      <p class="help">No hubo ventas en este período.</p>
    @else
      <div class="tbl-wrap" style="margin:0 -8px"><table class="t rep-t">
        <thead><tr><th>Cliente</th><th class="r">Compras</th><th class="r">Total</th></tr></thead>
        @foreach ($customers->take(10) as $c)
          <tr>
            <td>@if ($c->uid)<a href="{{ route('admin.customers.show', $c->uid) }}"><b>{{ $c->name }}</b></a>@else<b>{{ $c->name }}</b>@endif<br><span class="help">{{ $c->phone }}{{ $c->phone ? ' · ' : '' }}última {{ \Carbon\Carbon::parse($c->last)->format('d/m/Y') }}</span></td>
            <td class="r">{{ $c->n }}</td>
            <td class="r"><b>{{ money($c->t) }}</b></td>
          </tr>
        @endforeach
      </table></div>
    @endif
  </div>
  <div class="box">
    <h2>Horarios con más ventas @include('admin.partials.dl', ['k' => 'horarios'])</h2>
    @forelse ($byHour as $r)
      <div class="rank"><div class="rk"><span>{{ str_pad($r->h, 2, '0', STR_PAD_LEFT) }}:00 a {{ str_pad(($r->h + 1) % 24, 2, '0', STR_PAD_LEFT) }}:00</span><span><b>{{ money($r->t) }}</b> <span class="help">· {{ $r->n }} {{ $r->n == 1 ? 'venta' : 'ventas' }}</span></span></div><div class="hbar"><i style="width:{{ $pct($r->t, $maxHour) }}%"></i></div></div>
    @empty <p class="help">No hubo ventas en este período.</p> @endforelse
  </div>
</div>

<div class="cols-even">
  <div class="box">
    <h2>Sin ventas en el período @include('admin.partials.dl', ['k' => 'sin-ventas'])</h2>
    <p class="help" style="margin:-8px 0 10px">Productos activos con stock que no se vendieron. Sirve para decidir ofertas.</p>
    @forelse ($noSales->take(12) as $p)
      <div class="sum-row" style="padding:8px 0;box-shadow:0 1px 0 var(--line)"><a href="{{ route('admin.products.edit', $p->id) }}">{{ $p->name }}</a><span class="pill">{{ $p->stock }} u. en stock</span></div>
    @empty <p class="help">Todos los productos con stock tuvieron ventas.</p> @endforelse
  </div>
  <div class="box">
    <h2>Stock actual @include('admin.partials.dl', ['k' => 'stock'])</h2>
    <div class="rep-mini in">
      <div><span>Prendas en stock</span><b>{{ number_format((int) $stock->units, 0, ',', '.') }}</b></div>
      <div><span>Valor a precio de venta</span><b>{{ money($stock->value) }}</b></div>
      <div><span>Combinaciones agotadas</span><b>{{ (int) $stock->out }}</b><small>talle y color sin stock</small></div>
    </div>
    <h3 class="rep-sub">Poco stock <a href="{{ route('admin.stock') }}" class="no-print">Cargar stock</a></h3>
    @forelse ($lowStock->take(8) as $v)
      <div class="sum-row" style="padding:8px 0;box-shadow:0 1px 0 var(--line)">
        <span>{{ $v->product->name }} <span class="help">{{ $v->label() }}</span></span>
        <span class="pill {{ $v->stock <= 0 ? 'err' : 'warn' }}">{{ $v->stock <= 0 ? 'Agotado' : $v->stock.' u.' }}</span>
      </div>
    @empty <p class="help">Todo con buen stock.</p> @endforelse
    @if ($lowStock->count() > 8)<p class="help" style="margin-top:8px">Y {{ $lowStock->count() - 8 }} más en la descarga.</p>@endif
  </div>
</div>

<div class="box" style="margin-top:14px">
  <h2>Detalle de ventas
    <span class="rep-dls no-print">
      @include('admin.partials.dl', ['k' => 'ventas'])
      <span class="help">Cada prenda:</span> @include('admin.partials.dl', ['k' => 'prendas'])
    </span>
  </h2>
  @if ($list->isEmpty())
    <p class="help">No hubo ventas en este período.</p>
  @else
    <div class="tbl-wrap" style="margin:0 -8px"><table class="t">
      <thead><tr><th>Pedido</th><th>Cliente</th><th>Canal</th><th>Pago</th><th>Vendedor</th><th class="r">Total</th></tr></thead>
      @foreach ($list->take(100) as $o)
        <tr onclick="location='{{ route('admin.orders.show', $o) }}'" style="cursor:pointer">
          <td><b>{{ $o->number }}</b><br><span class="help">{{ $o->processed_at->format('d/m/Y H:i') }}</span></td>
          <td>{{ $o->customer_name }}@if ($o->is_wholesale) <span class="pill acc">Mayorista</span>@endif<br><span class="help">{{ $o->items_count }} {{ $o->items_count === 1 ? 'ítem' : 'ítems' }}</span></td>
          <td><span class="pill {{ $o->channel === 'local' ? '' : 'acc' }}">{{ $o->channel === 'local' ? 'Local' : 'Web' }}</span></td>
          <td>{{ $o->paymentLabel() }}</td>
          <td>{{ $o->seller?->name ?? 'Tienda online' }}</td>
          <td class="r"><b>{{ money($o->total) }}</b></td>
        </tr>
      @endforeach
    </table></div>
    @if ($count > 100)<p class="help" style="margin-top:10px">Se muestran las últimas 100 de {{ $count }} ventas. La descarga trae todas.</p>@endif
  @endif
</div>
@endsection
