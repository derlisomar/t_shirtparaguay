@extends('layouts.admin')
@section('title', 'Resumen')
@section('actions')<a class="btn accent sm" href="{{ route('admin.pos') }}"><svg class="i" style="width:16px"><use href="#i-store"/></svg> Nueva venta</a>@endsection
@section('content')
@php
  $salesSum = collect($salesChart)->sum(fn ($d) => $d['web'] + $d['local']);
  $salesCount = collect($salesChart)->sum('count');
  $ordersSum = collect($ordersChart)->sum(fn ($d) => $d['web'] + $d['local']);
  $period = $byMonth ? 'en los últimos 12 meses' : 'en los últimos 30 días';
@endphp
<div class="kpis">
  <div class="kpi hl"><span>Ventas de hoy</span><b>{{ money($todayTotal) }}</b><small>{{ $todayCount }} {{ $todayCount === 1 ? 'venta' : 'ventas' }}</small></div>
  <div class="kpi"><span>Ventas del mes</span><b>{{ money($monthTotal) }}</b><small>Web {{ money($monthWeb) }} · Local {{ money($monthLocal) }}</small></div>
  <div class="kpi"><span>Pedidos por confirmar</span><b>{{ $pending }}</b><small><a href="{{ route('admin.orders.index', ['estado' => 'pendiente']) }}" style="text-decoration:underline">{{ $pendingWithReceipt ? $pendingWithReceipt.' con comprobante' : 'Ver pendientes' }}</a> · no suman en el resumen</small></div>
  <div class="kpi"><span>Finalizados este mes</span><b>{{ $finishedMonth }}</b><small><a href="{{ route('admin.orders.index', ['vista' => 'en-curso']) }}" style="text-decoration:underline">{{ $inProgress }} en curso</a> · {{ $customers }} {{ $customers === 1 ? "cliente" : "clientes" }}</small></div>
</div>

<div class="box" style="margin-top:14px">
  <div class="chart-head">
    <div>
      <h2>Ventas realizadas</h2>
      <p class="help" style="margin-top:4px">Solo pedidos confirmados y ventas del local, {{ $byMonth ? "por el mes" : "por el día" }} en que se confirmaron.</p>
    </div>
    <div class="seg-tabs" role="tablist">
      <a href="{{ route('admin.dashboard') }}" @class(['on' => ! $byMonth])>Por día</a>
      <a href="{{ route('admin.dashboard', ['ver' => 'mes']) }}" @class(['on' => $byMonth])>Por mes</a>
    </div>
  </div>
  <div class="chart-head" style="margin-bottom:6px">
    <div class="sum">{{ money($salesSum) }}<small>{{ $salesCount }} {{ $salesCount === 1 ? 'venta' : 'ventas' }} {{ $period }}</small></div>
  </div>
  @include('admin.partials.bar-chart', ['data' => $salesChart, 'money' => true, 'series' => [
    ['key' => 'web', 'label' => 'Tienda online', 'color' => 'var(--accent)'],
    ['key' => 'local', 'label' => 'Venta en el local', 'color' => 'var(--ink)'],
  ]])
</div>

<div class="cols-even">
  <div class="box">
    <div class="chart-head">
      <div>
        <h2>Pedidos confirmados</h2>
        <p class="help" style="margin-top:4px">Cantidad de pedidos {{ $byMonth ? "por mes" : "por día" }}, contados cuando los confirmaste.</p>
      </div>
      <div class="sum r">{{ $ordersSum }}<small>{{ $period }}</small></div>
    </div>
    @include('admin.partials.bar-chart', ['data' => $ordersChart, 'money' => false, 'series' => [
      ['key' => 'web', 'label' => 'Tienda online', 'color' => 'var(--accent)'],
      ['key' => 'local', 'label' => 'Venta en el local', 'color' => 'var(--ink)'],
    ]])
  </div>
  <div class="box">
    <h2>Más vendidos (30 días)</h2>
    @forelse ($top as $t)
      <div class="sum-row" style="padding:9px 0;box-shadow:0 1px 0 var(--line)"><span>{{ $t->product_name }}</span><span><b>{{ $t->qty }}</b> <span style="color:var(--ink-3)">u. · {{ money($t->total) }}</span></span></div>
    @empty <p class="help">Todavía no hay ventas.</p> @endforelse
  </div>
</div>
<div class="cols-2">
  <div class="box">
    <h2>Últimos confirmados <a href="{{ route('admin.orders.index') }}">Ver pedidos</a></h2>
    <div style="overflow-x:auto;margin:0 -8px"><table class="t">
      @foreach ($latest as $o)
        <tr onclick="location='{{ route('admin.orders.show', $o) }}'" style="cursor:pointer">
          <td><b>{{ $o->number }}</b><br><span class="help">{{ ($o->processed_at ?? $o->created_at)->format('d/m H:i') }}</span></td>
          <td>{{ $o->customer_name }}</td>
          <td><span class="pill {{ $o->channel === 'local' ? '' : 'acc' }}">{{ $o->channel === 'local' ? 'Local' : 'Web' }}</span></td>
          <td>@include('admin.orders.status', ['o' => $o])</td>
          <td class="r"><b>{{ money($o->total) }}</b></td>
        </tr>
      @endforeach
    </table></div>
  </div>
  <div class="box">
    <h2>Poco stock <a href="{{ route('admin.stock') }}">Cargar stock</a></h2>
    @forelse ($lowStock as $v)
      <div class="sum-row" style="padding:9px 0;box-shadow:0 1px 0 var(--line)">
        <span>{{ $v->product->name }} <span class="help">{{ $v->label() }}</span></span>
        <span class="pill {{ $v->stock <= 0 ? 'err' : 'warn' }}">{{ $v->stock <= 0 ? 'Agotado' : $v->stock.' u.' }}</span>
      </div>
    @empty <p class="help">Todo con buen stock.</p> @endforelse
  </div>
</div>
@endsection
