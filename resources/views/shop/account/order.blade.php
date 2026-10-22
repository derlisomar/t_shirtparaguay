@extends('layouts.shop')
@section('title', 'Pedido '.$order->number.' · '.setting('store_name', 't_shirtparaguay'))
@section('content')
@php($steps = ['pendiente' => 'Pendiente', 'procesado' => 'Procesado', 'enviado' => $order->delivery_method === 'retiro' ? 'Listo para retirar' : 'En camino', 'entregado' => 'Entregado'])
@php($idx = array_search($order->status === 'finalizado' ? 'entregado' : $order->status, array_keys($steps)))
<div class="wrap" style="max-width:900px">
  <div class="page-head">
    <div class="crumbs"><a href="{{ route('account') }}">Mi cuenta</a><span>/</span><span>{{ $order->number }}</span></div>
    <h1>Pedido {{ $order->number }}</h1>
    <p>{{ $order->created_at->format('d/m/Y H:i') }} · {{ $order->paymentLabel() }} · Pago {{ strtolower($order->paymentStatusLabel()) }}</p>
    <a class="btn sm ghost" style="margin-top:12px" href="{{ route('checkout.invoice', $order) }}" target="_blank"><svg class="i" style="width:16px"><use href="#i-print"/></svg> Ver boleta</a>
  </div>
  @if ($order->status === 'cancelado')
    <div class="alert err">Este pedido fue cancelado.</div>
  @else
    <div class="panel-card" style="margin-bottom:16px">
      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px">
        @foreach ($steps as $k => $label)
          @php($done = $loop->index <= $idx)
          <div><div style="height:6px;border-radius:9px;background:{{ $done ? 'var(--accent)' : 'var(--surface-3)' }}"></div><div style="font-size:13px;margin-top:8px;color:{{ $done ? 'var(--ink)' : 'var(--ink-3)' }};font-weight:{{ $loop->index === $idx ? 700 : 500 }}">{{ $label }}</div></div>
        @endforeach
      </div>
    </div>
  @endif
  <div class="panel-card">
    @foreach ($order->items as $it)
      <div class="line" style="grid-template-columns:64px 1fr auto;align-items:center">
        <img src="{{ $it->product ? $it->product->mainImageUrl() : asset('img/sin-foto.svg') }}" alt="" style="width:64px">
        <div><h4>{{ $it->product_name }}</h4><div class="meta">{{ $it->size }}{{ $it->color ? ' · '.$it->color : '' }} · x{{ $it->quantity }}</div>@if ($it->wasAdjusted())<div class="ws-note" style="color:var(--warn)">{{ $it->quantity ? 'Pediste '.$it->original_quantity.'; te enviamos '.$it->quantity.' porque no había más stock.' : 'No había stock: la sacamos del pedido y no se cobra.' }}</div>@endif</div>
        <b>{{ money($it->total) }}</b>
      </div>
    @endforeach
    <div style="display:grid;gap:8px;margin-top:16px">
      <div class="sum-row"><span>Subtotal</span><span>{{ money($order->subtotal) }}</span></div>
      @if ($order->discount)<div class="sum-row"><span>Descuento</span><span>-{{ money($order->discount) }}</span></div>@endif
      <div class="sum-row"><span>Envío</span><span>{{ $order->shipping ? money($order->shipping) : 'Gratis' }}</span></div>
      <div class="sum-row total"><span>Total</span><span>{{ money($order->total) }}</span></div>
    </div>
    @if ($order->payment_method === 'transferencia' && $order->payment_status === 'pendiente')
      <h3 style="margin:24px 0 10px;font-size:18px">Datos para transferir</h3>
      <div class="bank">{{ setting('bank_details') }}</div>
    @endif
    @if ($order->payment_method === 'transferencia' || $order->hasReceipt())
      <h3 style="margin:24px 0 10px;font-size:18px">Comprobante de pago</h3>
      @include('shop.partials.receipt-box')
    @endif
  </div>
</div>
@endsection
