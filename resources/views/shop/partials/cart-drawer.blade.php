@php($free = (int) setting('free_shipping_from', 400000))
@if ($lines->isEmpty())
  <div class="lines">
    <div class="drawer-empty">
      <svg class="i" style="width:40px;height:40px"><use href="#i-bag"/></svg>
      <p>Tu carrito está vacío.</p>
      <a class="btn" href="{{ route('catalog') }}">Ver la tienda</a>
    </div>
  </div>
@else
  <div class="lines">
    @foreach ($lines as $l)
      <div class="line">
        <a href="{{ route('product.show', $l->product) }}"><img src="{{ $l->image }}" alt=""></a>
        <div>
          <h4>{{ $l->product->name }}</h4>
          <div class="meta">Talle {{ $l->variant->size?->name }}@if($l->variant->color) · {{ $l->variant->color->name }}@endif</div>
          @include('shop.partials.wholesale-note')
          <div class="row">
            <div class="qty">
              <button type="button" data-line-qty="{{ $l->qty - 1 }}" data-url="{{ route('cart.update', $l->variant) }}" aria-label="Uno menos"><svg class="i" style="width:16px"><use href="#i-minus"/></svg></button>
              <input value="{{ $l->qty }}" readonly aria-label="Cantidad">
              <button type="button" data-line-qty="{{ $l->qty + 1 }}" data-url="{{ route('cart.update', $l->variant) }}" aria-label="Uno más" @disabled($l->qty >= $l->variant->stock)><svg class="i" style="width:16px"><use href="#i-plus"/></svg></button>
            </div>
            <b>{{ money($l->total) }}</b>
          </div>
          @if ($l->over_stock)<div class="warn">Solo quedan {{ $l->variant->stock }}. Ajustá la cantidad.</div>@endif
          <button type="button" class="rm" data-line-rm data-url="{{ route('cart.remove', $l->variant) }}" style="margin-top:8px">Quitar</button>
        </div>
      </div>
    @endforeach
  </div>
  <div class="foot">
    @if ($free > 0)
      <div class="ship-bar">
        @if ($subtotal >= $free)
          <b style="color:var(--ok)">¡Tenés envío gratis!</b>
        @else
          Te faltan <b>{{ money($free - $subtotal) }}</b> para el envío gratis.
        @endif
        <div class="bar"><i style="width:{{ min(100, round($subtotal * 100 / $free)) }}%"></i></div>
      </div>
    @endif
    <div class="sum-row total"><span>Subtotal</span><span>{{ money($subtotal) }}</span></div>
    <a class="btn accent block" href="{{ route('checkout') }}">Finalizar compra</a>
    <a class="btn ghost block" href="{{ route('cart') }}">Ver carrito</a>
  </div>
@endif
