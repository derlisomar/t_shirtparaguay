@extends('layouts.shop')
@section('title', $product->name.' · '.setting('store_name', 't_shirtparaguay'))
@section('description', \Illuminate\Support\Str::limit($product->description, 150))

@section('content')
@php
  $colors = $product->colorOptions();
  $sizes = $product->sizeOptions();
  $first = $product->images->first();
  $wa = setting('whatsapp');
@endphp
<div class="wrap" data-pdp data-variants='@json($variants)' data-pre-size="{{ (int) request('talle') }}">
  <div class="crumbs" style="margin-top:24px">
    <a href="{{ route('home') }}">Inicio</a><span>/</span>
    @if ($product->category)<a href="{{ route('category', $product->category) }}">{{ $product->category->name }}</a><span>/</span>@endif
    <span>{{ $product->name }}</span>
  </div>

  <div class="pdp">
    <div class="gallery">
      <div class="thumbs">
        @foreach ($product->images as $i => $img)
          <button type="button" class="{{ $i === 0 ? 'on' : '' }}" data-src="{{ $img->url }}" data-color-id="{{ $img->color_id }}" aria-label="Foto {{ $i + 1 }}"><img src="{{ $img->url }}" alt="" loading="lazy"></button>
        @endforeach
      </div>
      <div class="stage">
        <img src="{{ $first ? $first->url : asset('img/sin-foto.svg') }}" alt="{{ $product->name }}">
      </div>
    </div>

    <div class="buybox">
      @if ($product->is_new)<span class="pill acc" style="margin-bottom:12px">Nuevo</span>@endif
      <h1>{{ $product->name }}</h1>
      <div class="price-row">
        <b>{{ money($product->price) }}</b>
        @if ($product->isOnSale())<s>{{ money($product->compare_price) }}</s><span class="tag sale">-{{ $product->discountPercent() }}%</span>@endif
      </div>
      @if ($product->hasWholesale())
        @if (auth()->user()?->is_wholesale)
          <div class="ws-box"><span class="tag">Mayorista</span><span>Tu precio de cliente mayorista: <b>{{ money($product->wholesale_price) }}</b> c/u</span></div>
        @elseif ($product->wholesale_min_qty > 0)
          <div class="ws-box"><span class="tag">Mayorista</span><span>Llevando <b>{{ $product->wholesale_min_qty }} o más</b> (talles y colores combinados) pagás <b>{{ money($product->wholesale_price) }}</b> c/u</span></div>
        @endif
      @endif
      <p style="color:var(--ink-2);font-size:14.5px">Envío gratis desde {{ money(setting('free_shipping_from', 400000)) }}</p>

      @if ($colors->count())
        <div class="opt">
          <div class="opt-head"><span>Color: <b data-color-name>{{ $colors->first()->name }}</b></span></div>
          <div class="swatch-row">
            @foreach ($colors as $c)
              <button type="button" data-color="{{ $c->id }}" data-name="{{ $c->name }}" style="background:{{ $c->hex }}" aria-label="{{ $c->name }}" title="{{ $c->name }}"></button>
            @endforeach
          </div>
        </div>
      @endif

      <div class="opt">
        <div class="opt-head"><span>Talle: <b data-size-name></b></span><button type="button" data-modal="size-guide">Guía de talles</button></div>
        <div class="size-row">
          @foreach ($sizes as $s)
            <button type="button" data-size="{{ $s->id }}" aria-pressed="false">{{ $s->name }}</button>
          @endforeach
        </div>
        <div class="stock-hint" aria-live="polite"></div>
      </div>

      <div class="buy-actions">
        <div class="qty">
          <button type="button" data-step="-1" aria-label="Uno menos"><svg class="i" style="width:16px"><use href="#i-minus"/></svg></button>
          <input type="number" value="1" min="1" max="50" data-qty aria-label="Cantidad">
          <button type="button" data-step="1" aria-label="Uno más"><svg class="i" style="width:16px"><use href="#i-plus"/></svg></button>
        </div>
        <button type="button" class="btn accent" data-add><svg class="i" style="width:18px"><use href="#i-bag"/></svg> Agregar al carrito</button>
        <button type="button" class="fav-big {{ $isFavorite ? 'on' : '' }}" data-fav="{{ route('favorites.toggle', $product) }}" aria-label="Guardar en favoritos"><svg class="i"><use href="#i-heart"/></svg></button>
      </div>
      @if ($wa)
        <a class="wa-ask" href="https://wa.me/{{ $wa }}?text={{ rawurlencode('Hola! Quería consultar por '.$product->name.' ('.route('product.show', $product).')') }}" target="_blank" rel="noopener"><svg class="i"><use href="#i-chat"/></svg> ¿Dudas con el talle? Consultanos por WhatsApp</a>
      @endif

      <div class="acc">
        <details open><summary>Descripción</summary><div class="body">{{ $product->description }}</div></details>
        <details><summary>Envíos y retiro</summary><div class="body">Envío a domicilio en Asunción y Central en 24 a 48 h hábiles ({{ money(setting('shipping_cost', 25000)) }}, gratis desde {{ money(setting('free_shipping_from', 400000)) }}). Al interior por transportadora.
Retiro sin costo: {{ setting('pickup_address', setting('address')) }}</div></details>
        <details><summary>Cambios</summary><div class="body">Tenés 30 días para cambiar de talle o color. La prenda tiene que estar sin uso y con su etiqueta.</div></details>
      </div>
    </div>
  </div>

  @if ($related->count())
    <section class="block">
      <div class="block-head"><h2>También te puede gustar</h2></div>
      <div class="grid">
        @foreach ($related as $product)@include('shop.partials.card')@endforeach
      </div>
    </section>
  @endif

  <div class="mobile-buy">
    <div><b>{{ money($product->price) }}</b></div>
    <button type="button" class="btn accent" data-add>Agregar al carrito</button>
  </div>
</div>

<div class="modal" id="size-guide" aria-hidden="true" role="dialog" aria-label="Guía de talles">
  <div class="scrim" data-close></div>
  <div class="box">
    <button class="icon-btn close" data-close aria-label="Cerrar"><svg class="i"><use href="#i-close"/></svg></button>
    @include('shop.partials.size-table')
  </div>
</div>
@endsection
