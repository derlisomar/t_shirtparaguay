@extends('layouts.shop')

@section('content')
@if ($slides->count())
<div class="hero">
  <div class="slider" id="slider" data-interval="{{ max(2, min(20, (int) setting('slider_seconds', 4))) * 1000 }}" style="--bg-slide: {{ $slides[0]->bg_color }}; --acc: {{ $slides[0]->accent_color }}" aria-roledescription="carrusel" aria-label="Destacados">
    @foreach ($slides as $i => $s)
      @php($p = $s->product)
      <div class="slide {{ $i === 0 ? 'on' : '' }}" data-bg="{{ $s->bg_color }}" data-acc="{{ $s->accent_color }}" data-tagline="{{ $s->tagline }}" data-thumb="{{ upload_url($s->thumb_image ?: $s->main_image) }}" aria-roledescription="diapositiva" aria-label="{{ $i + 1 }} de {{ $slides->count() }}" @if($i) aria-hidden="true" @endif>
        <div class="art">
          @if ($s->secondary_image)<img class="second-img" src="{{ upload_url($s->secondary_image) }}" alt="" @if($i) loading="lazy" @endif>@endif
          @if ($s->main_image)<img class="main-img" src="{{ upload_url($s->main_image) }}" alt="{{ $s->title }}" @if($i) loading="lazy" @else fetchpriority="high" @endif>@endif
        </div>
        <div class="copy">
          <span style="height:40px;display:block" aria-hidden="true"></span>
          <h2>{{ $s->title }}</h2>
          @if ($s->text)<p>{{ $s->text }}</p>@endif
          @if ($s->button_text)<a class="btn cta" href="{{ $s->link() }}" @if($i) tabindex="-1" @endif>{{ $s->button_text }} <svg class="i"><use href="#i-right"/></svg></a>@endif
        </div>
        @if ($p)
          <div class="buy">
            <div class="price">{{ money($p->price) }}</div>
            @if ($p->isOnSale())<div class="was">{{ money($p->compare_price) }}</div>@endif
            @php($sz = $p->sizeOptions())
            @if ($sz->count() > 1)
              <div class="sizes-label">Elegí tu talle:</div>
              <div class="sizes">
                @foreach ($sz as $size)
                  @php($has = $p->variants->where('size_id', $size->id)->sum(fn ($v) => $v->available()) > 0)
                  <a href="{{ route('product.show', [$p, 'talle' => $size->id]) }}" class="{{ $has ? '' : 'out' }}" @if($i) tabindex="-1" @endif>{{ $size->name }}</a>
                @endforeach
              </div>
            @endif
          </div>
        @endif
      </div>
    @endforeach

    <div class="arrows">
      <button type="button" data-prev aria-label="Anterior"><svg class="i"><use href="#i-left"/></svg></button>
      <button type="button" data-next aria-label="Siguiente"><svg class="i"><use href="#i-right"/></svg></button>
    </div>
    <div class="social">
      @if (setting('instagram'))<a href="{{ setting('instagram') }}" target="_blank" rel="noopener" aria-label="Instagram"><svg class="i"><use href="#i-ig"/></svg></a>@endif
      @if (setting('facebook'))<a href="{{ setting('facebook') }}" target="_blank" rel="noopener" aria-label="Facebook"><svg class="i"><use href="#i-fb"/></svg></a>@endif
      @if (setting('tiktok'))<a href="{{ setting('tiktok') }}" target="_blank" rel="noopener" aria-label="TikTok"><svg class="i"><use href="#i-tt"/></svg></a>@endif
      @if (setting('whatsapp'))<a href="https://wa.me/{{ setting('whatsapp') }}" target="_blank" rel="noopener" aria-label="WhatsApp"><svg class="i"><use href="#i-chat"/></svg></a>@endif
    </div>
    <div class="tagline" aria-hidden="true"><span>{{ $slides[0]->tagline }}</span></div>
    @if ($slides->count() > 1)
      <button type="button" class="next-thumb" data-next aria-label="Ver la siguiente"><img src="{{ upload_url($slides[1]->thumb_image ?: $slides[1]->main_image) }}" alt=""></button>
      <div class="dots">
        @foreach ($slides as $i => $s)<button type="button" class="{{ $i === 0 ? 'on' : '' }}" data-go="{{ $i }}" aria-label="Ir a la {{ $i + 1 }}"><i></i></button>@endforeach
      </div>
    @endif
  </div>
</div>
@endif

<div class="wrap">
  <section class="block reveal">
    <div class="block-head">
      <div><h2>Elegí por categoría</h2></div>
      <a class="link-arrow" href="{{ route('catalog') }}">Ver toda la tienda <svg class="i" style="width:16px;height:16px"><use href="#i-arrow"/></svg></a>
    </div>
    <div class="cats">
      @foreach ($categories->take(5) as $c)
        @php($cover = $c->image ? upload_url($c->image) : optional(\App\Models\ProductImage::whereHas('product', fn($q) => $q->whereIn('category_id', $c->familyIds())->where('is_active', true))->orderBy('product_id')->orderBy('sort_order')->first())->url)
        <a class="cat" href="{{ route('category', $c) }}">
          @if ($cover)<img src="{{ $cover }}" alt="" loading="lazy">@endif
          <div><h3>{{ $c->name }}</h3>@php($n = \App\Models\Product::active()->whereIn('category_id', $c->familyIds())->count())<span>{{ $n }} {{ $n === 1 ? 'producto' : 'productos' }}</span></div>
          <span class="go" aria-hidden="true"><svg class="i"><use href="#i-arrow"/></svg></span>
        </a>
      @endforeach
    </div>
  </section>

  @if ($new->count())
  <section class="block reveal">
    <div class="block-head">
      <div><h2>Recién llegados</h2><p>Lo último que entró al local.</p></div>
      <a class="link-arrow" href="{{ route('catalog') }}">Ver todo <svg class="i" style="width:16px;height:16px"><use href="#i-arrow"/></svg></a>
    </div>
    <div class="rail">
      @foreach ($new as $product)@include('shop.partials.card')@endforeach
    </div>
  </section>
  @endif

  <section class="block reveal">
    <div class="story">
      <div class="txt">
        <h2>Diseñado en <em>Paraguay,</em> para el calor de acá.</h2>
        <p>Algodón que respira, estampas con guiños nuestros (tereré, ñandutí, jopará) y talles que van del S al XXL. Si no te queda, lo cambiás sin vueltas.</p>
        <a class="btn" href="{{ route('catalog') }}">Conocé la colección <svg class="i" style="width:16px;height:16px"><use href="#i-arrow"/></svg></a>
      </div>
      <div class="pic"><img src="{{ asset('uploads/seed/products/remera-nanduti--rosa--detalle.webp') }}" alt="Estampa ñandutí de cerca" loading="lazy"></div>
    </div>
  </section>

  @if ($featured->count())
  <section class="block reveal">
    <div class="block-head">
      <div><h2>Los que más salen</h2><p>Los favoritos de la temporada.</p></div>
      <a class="link-arrow" href="{{ route('catalog', ['orden' => 'mas-vendidos']) }}">Ver más vendidos <svg class="i" style="width:16px;height:16px"><use href="#i-arrow"/></svg></a>
    </div>
    <div class="grid">
      @foreach ($featured as $product)@include('shop.partials.card')@endforeach
    </div>
  </section>
  @endif

  <section class="block">
    <div class="perks">
      <div class="perk"><svg class="i"><use href="#i-truck"/></svg><div><b>Envíos a todo el país</b><span>{{ show_free_shipping() ? 'Gratis desde '.money(setting('free_shipping_from', 400000)).' en Asunción y Central.' : 'En 24 a 48 h hábiles en Asunción y Central.' }}</span></div></div>
      <div class="perk"><svg class="i"><use href="#i-swap"/></svg><div><b>Cambios sin vueltas</b><span>Tenés 30 días para cambiar de talle o color.</span></div></div>
      <div class="perk"><svg class="i"><use href="#i-card"/></svg><div><b>Pagá como quieras</b><span>Transferencia o efectivo. Muy pronto, con tarjeta.</span></div></div>
      <div class="perk"><svg class="i"><use href="#i-chat"/></svg><div><b>Te ayudamos por WhatsApp</b><span>Consultá talles y stock antes de comprar.</span></div></div>
    </div>
  </section>
</div>
@endsection
