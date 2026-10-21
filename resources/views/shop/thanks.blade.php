@extends('layouts.shop')
@section('title', '¡Gracias! · '.setting('store_name', 't_shirtparaguay'))
@section('content')
<div class="wrap" style="max-width:760px">
  <div class="page-head" style="text-align:center">
    <div style="width:64px;height:64px;border-radius:99px;background:var(--ok-soft);color:var(--ok);display:grid;place-items:center;margin:20px auto 18px"><svg class="i" style="width:30px;height:30px"><use href="#i-check"/></svg></div>
    <h1>¡Gracias por tu compra!</h1>
    <p>Tu pedido es el <b>{{ $order->number }}</b>. Queda <b>pendiente</b> hasta que lo revisemos y procesemos. Lo vas a poder seguir desde <a href="{{ route('account') }}" style="text-decoration:underline">Mi cuenta</a>.</p>
  </div>
  <div class="panel-card" style="display:grid;gap:16px">
    @if ($order->payment_method === 'transferencia')
      <div>
        <h2>Datos para la transferencia</h2>
        <div class="bank">{{ setting('bank_details') }}</div>
        <p style="margin-top:12px;color:var(--ink-2)">Transferí <b>{{ money($order->total) }}</b>{{ $order->hasReceipt() ? '.' : ' y adjuntá el comprobante acá abajo o mandalo por WhatsApp.' }} Cuando lo verifiquemos, procesamos tu pedido.</p>
        <div style="margin-top:14px">@include('shop.partials.receipt-box')</div>
        @if (setting('whatsapp') && ! $order->hasReceipt())
          <a class="btn ghost" style="margin-top:10px" target="_blank" rel="noopener" href="https://wa.me/{{ setting('whatsapp') }}?text={{ rawurlencode('Hola! Te paso el comprobante del pedido '.$order->number.' por '.money($order->total)) }}"><svg class="i"><use href="#i-wa"/></svg> O mandalo por WhatsApp</a>
        @endif
      </div>
    @elseif ($order->payment_method === 'efectivo')
      <p>Pagás <b>{{ money($order->total) }}</b> en efectivo {{ $order->delivery_method === 'retiro' ? 'cuando retires en el local' : 'cuando recibas el pedido' }}.</p>
    @endif
    <div>
      @foreach ($order->items as $it)
        <div class="sum-row" style="padding:8px 0"><span>{{ $it->quantity }} × {{ $it->product_name }} <span style="color:var(--ink-2)">({{ $it->size }}{{ $it->color ? ' · '.$it->color : '' }})</span></span><span>{{ money($it->total) }}</span></div>
      @endforeach
      <div class="sum-row" style="padding:8px 0"><span>Envío</span><span>{{ $order->shipping ? money($order->shipping) : 'Gratis' }}</span></div>
      <div class="sum-row total" style="padding-top:8px"><span>Total</span><span>{{ money($order->total) }}</span></div>
    </div>
    <a class="btn ghost" href="{{ route('catalog') }}">Seguir comprando</a>
  </div>
</div>
@endsection
