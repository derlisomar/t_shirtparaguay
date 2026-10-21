@extends('layouts.admin')
@section('title', 'Pedidos')
@section('content')
@php
  $tabs = ['en-curso' => 'En curso', 'finalizados' => 'Finalizados', 'cancelados' => 'Cancelados', 'todos' => 'Todos'];
  $statusOptions = match ($view) {
      'en-curso' => array_intersect_key(\App\Models\Order::STATUSES, array_flip(\App\Models\Order::OPEN_STATUSES)),
      'todos' => \App\Models\Order::STATUSES,
      default => [],
  };
@endphp
<div class="seg-tabs" style="margin-bottom:14px;flex-wrap:wrap">
  @foreach ($tabs as $k => $l)
    <a href="{{ route('admin.orders.index', ['vista' => $k]) }}" @class(['on' => $view === $k])>{{ $l }}@isset($counts[$k]) <span class="help">({{ $counts[$k] }})</span>@endisset</a>
  @endforeach
</div>
<form class="filters-bar">
  <input type="hidden" name="vista" value="{{ $view }}">
  <input class="input" name="q" value="{{ request('q') }}" placeholder="Número, nombre o celular">
  @if ($statusOptions)
    <select class="select" name="estado" data-autosubmit><option value="">Todos los estados</option>@foreach($statusOptions as $k => $l)<option value="{{ $k }}" @selected(request('estado') === $k)>{{ $l }}</option>@endforeach</select>
  @endif
  <select class="select" name="canal" data-autosubmit><option value="">Web y local</option><option value="web" @selected(request('canal') === 'web')>Tienda online</option><option value="local" @selected(request('canal') === 'local')>Venta en el local</option></select>
  <label class="check" style="white-space:nowrap"><input type="checkbox" name="comprobante" value="si" data-autosubmit @checked(request('comprobante') === 'si')> Con comprobante</label>
  <button class="btn sm soft">Buscar</button>
</form>
@if ($view === 'finalizados')
  <p class="help" style="margin:-4px 0 12px">Pedidos entregados y con el pago confirmado. Se ordenan por la fecha en que se finalizaron.</p>
@endif
<div class="tbl-wrap"><table class="t">
  <thead><tr><th>Pedido</th><th>Cliente</th><th>Canal</th><th>Pago</th><th>Estado</th><th class="r">Total</th><th></th></tr></thead>
  @forelse ($orders as $o)
    <tr onclick="location='{{ route('admin.orders.show', $o) }}'" style="cursor:pointer">
      <td><a href="{{ route('admin.orders.show', $o) }}"><b>{{ $o->number }}</b></a><br><span class="help">{{ $view === 'finalizados' && $o->finished_at ? 'Finalizado '.$o->finished_at->format('d/m/Y H:i') : $o->created_at->format('d/m/Y H:i') }}</span></td>
      <td>{{ $o->customer_name }}<br><span class="help">{{ $o->customer_phone }}</span></td>
      <td><span class="pill {{ $o->channel === 'local' ? '' : 'acc' }}">{{ $o->channel === 'local' ? 'Local' : 'Web' }}</span></td>
      <td>{{ $o->paymentLabel() }}<br><span class="pill {{ $o->payment_status === 'pagado' ? 'ok' : 'warn' }}">{{ $o->paymentStatusLabel() }}</span></td>
      <td>@include('admin.orders.status', ['o' => $o])@if($o->receipt_path)<br><span class="pill ok" style="margin-top:4px" title="El cliente adjuntó el comprobante">Comprobante</span>@endif</td>
      <td class="r"><b>{{ money($o->total) }}</b><br><span class="help">{{ $o->items_count }} ítems</span></td>
      <td class="r" onclick="event.stopPropagation()">
        <a class="icon-btn" href="{{ route('admin.orders.ticket', [$o, 'formato' => 'a4']) }}" target="_blank" title="Imprimir comprobante" aria-label="Imprimir comprobante de {{ $o->number }}"><svg class="i"><use href="#i-print"/></svg></a>
      </td>
    </tr>
  @empty
    <tr><td colspan="7" class="help" style="padding:40px;text-align:center">No hay pedidos.</td></tr>
  @endforelse
</table></div>
{{ $orders->links() }}
@endsection
