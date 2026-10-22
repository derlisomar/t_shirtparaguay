@extends('layouts.shop')
@section('title', 'Carrito · '.setting('store_name', 't_shirtparaguay'))
@section('content')
<div class="wrap">
  <div class="page-head"><h1>Tu carrito</h1></div>
  @error('cart')<div class="alert err">{{ $message }}</div>@enderror
  @if ($lines->isEmpty())
    <div class="empty"><h3>Tu carrito está vacío</h3><p>Date una vuelta por la tienda, seguro algo te gusta.</p><a class="btn" href="{{ route('catalog') }}">Ver la tienda</a></div>
  @else
    <div class="two-col">
      <div class="panel-card" style="padding:8px 24px">
        @foreach ($lines as $l)
          <div class="line" style="grid-template-columns:100px 1fr">
            <a href="{{ route('product.show', $l->product) }}"><img src="{{ $l->image }}" alt="" style="width:100px"></a>
            <div>
              <div style="display:flex;justify-content:space-between;gap:12px">
                <div><h4>{{ $l->product->name }}</h4><div class="meta">Talle {{ $l->variant->size?->name }}@if($l->variant->color) · {{ $l->variant->color->name }}@endif · {{ money($l->price) }} c/u</div>@include('shop.partials.wholesale-note')</div>
                <b>{{ money($l->total) }}</b>
              </div>
              <div class="row">
                <form method="post" action="{{ route('cart.update', $l->variant) }}" style="display:flex;gap:8px;align-items:center">
                  @csrf @method('PATCH')
                  <select class="select" name="qty" data-autosubmit aria-label="Cantidad">
                    @for ($i = 1; $i <= max($l->qty, min(10, $l->available)); $i++)<option value="{{ $i }}" @selected($i === $l->qty)>{{ $i }}</option>@endfor
                  </select>
                </form>
                <form method="post" action="{{ route('cart.remove', $l->variant) }}">@csrf @method('DELETE')<button class="rm" style="background:none;border:0;color:var(--ink-3);text-decoration:underline">Quitar</button></form>
              </div>
              @if ($l->over_stock)<div class="warn" style="color:var(--danger);font-size:13px">Solo quedan {{ $l->available }}.</div>@endif
            </div>
          </div>
        @endforeach
      </div>
      <div class="panel-card summary">
        <h2>Resumen</h2>
        <div style="display:grid;gap:10px">
          <div class="sum-row"><span>Subtotal</span><span>{{ money($subtotal) }}</span></div>
          <div class="sum-row"><span>Envío</span><span>{{ $shipping ? money($shipping) : 'Gratis' }}</span></div>
          <p style="font-size:13.5px;color:var(--ink-2)">Si retirás en el local, el envío no se cobra.</p>
          <div class="sum-row total" style="margin-top:8px"><span>Total</span><span>{{ money($subtotal + $shipping) }}</span></div>
          <a class="btn accent block" href="{{ route('checkout') }}" style="margin-top:8px">Finalizar compra</a>
          <a class="btn ghost block" href="{{ route('catalog') }}">Seguir comprando</a>
        </div>
      </div>
    </div>
  @endif
</div>
@endsection
