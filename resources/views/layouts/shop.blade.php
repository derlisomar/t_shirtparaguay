<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', setting('store_name', 't_shirtparaguay').' · '.setting('store_tagline', 'Ropa urbana'))</title>
  <meta name="description" content="@yield('description', setting('store_tagline', 'Ropa urbana hecha en Paraguay'))">
  <meta name="theme-color" content="#151413">
  <link rel="icon" href="{{ setting('favicon') ? upload_url(setting('favicon')) : asset('img/favicon.svg') }}">
  <link rel="preload" href="{{ asset('fonts/bricolage-grotesque-latin-wght-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="{{ asset('fonts/dm-sans-latin-wght-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
  @include('partials.theme-head')
  <link rel="stylesheet" href="{{ asset_v('css/shop.css') }}">
  @stack('head')
</head>
<body>
@include('partials.icons')

@if (setting('announcement'))
  <div class="announce">{{ setting('announcement') }}</div>
@endif

<header class="header" id="header">
  <div class="wrap">
    <button class="icon-btn burger" data-open="mnav" aria-label="Abrir menú"><svg class="i"><use href="#i-menu"/></svg></button>
    @include('partials.logo')
    <nav class="nav" aria-label="Principal">
      <a href="{{ route('catalog') }}" class="{{ request()->routeIs('catalog') ? 'on' : '' }}">Tienda</a>
      @foreach ($navCategories as $c)
        @php($navOn = request()->is('categoria/'.$c->slug) || $c->children->contains(fn ($l) => request()->is('categoria/'.$l->slug)))
        @if ($c->children->isEmpty())
          <a href="{{ route('category', $c) }}" class="{{ $navOn ? 'on' : '' }}">{{ $c->name }}</a>
        @else
          <div class="nav-drop">
            <a href="{{ route('category', $c) }}" class="{{ $navOn ? 'on' : '' }}">{{ $c->name }}</a>
            <div class="nav-menu">
              <a href="{{ route('category', $c) }}"><b>Todas las {{ mb_strtolower($c->name) }}</b></a>
              @foreach ($c->children as $l)<a href="{{ route('category', $l) }}">{{ $l->name }}</a>@endforeach
            </div>
          </div>
        @endif
      @endforeach
      <a href="{{ route('catalog', ['oferta' => 1]) }}">Ofertas</a>
    </nav>
    <div class="tools">
      <button class="icon-btn" data-open="search" aria-label="Buscar"><svg class="i"><use href="#i-search"/></svg></button>
      <button class="icon-btn theme-toggle" aria-label="Cambiar modo claro u oscuro"><svg class="i moon"><use href="#i-moon"/></svg><svg class="i sun"><use href="#i-sun"/></svg></button>
      <a class="icon-btn hide-m" href="{{ route('favorites') }}" aria-label="Favoritos"><svg class="i"><use href="#i-heart"/></svg></a>
      <a class="icon-btn hide-m" href="{{ auth()->check() ? route('account') : route('login') }}" aria-label="Mi cuenta"><svg class="i"><use href="#i-user"/></svg></a>
      <a class="icon-btn" href="{{ route('cart') }}" data-open="cart" aria-label="Carrito"><svg class="i"><use href="#i-bag"/></svg><span class="badge-count" data-cart-count data-n="{{ $cartCount }}">{{ $cartCount ?: '' }}</span></a>
    </div>
  </div>
</header>

<main>
  @if (session('ok'))<div class="wrap" style="margin-top:16px"><div class="alert">{{ session('ok') }}</div></div>@endif
  @yield('content')
</main>

<footer class="footer">
  <div class="wrap">
    <div class="cols">
      <div>
        @include('partials.logo')
        <p style="margin-top:14px;max-width:320px">{{ setting('store_tagline') }}. {{ setting('address') }}</p>
        <div class="soc">
          @if (setting('instagram'))<a class="icon-btn" href="{{ setting('instagram') }}" target="_blank" rel="noopener" aria-label="Instagram"><svg class="i"><use href="#i-ig"/></svg></a>@endif
          @if (setting('facebook'))<a class="icon-btn" href="{{ setting('facebook') }}" target="_blank" rel="noopener" aria-label="Facebook"><svg class="i"><use href="#i-fb"/></svg></a>@endif
          @if (setting('tiktok'))<a class="icon-btn" href="{{ setting('tiktok') }}" target="_blank" rel="noopener" aria-label="TikTok"><svg class="i"><use href="#i-tt"/></svg></a>@endif
        </div>
      </div>
      <div>
        <h4>Comprar</h4>
        <ul>
          <li><a href="{{ route('catalog') }}">Toda la tienda</a></li>
          @foreach ($navCategories as $c)<li><a href="{{ route('category', $c) }}">{{ $c->name }}</a></li>@endforeach
        </ul>
      </div>
      <div>
        <h4>Ayuda</h4>
        <ul>
          <li><a href="{{ route('help') }}#envios">Envíos y retiros</a></li>
          <li><a href="{{ route('help') }}#cambios">Cambios</a></li>
          <li><a href="{{ route('help') }}#pagos">Formas de pago</a></li>
          <li><a href="{{ route('size-guide') }}">Guía de talles</a></li>
        </ul>
      </div>
      <div>
        <h4>Mi cuenta</h4>
        <ul>
          @auth
            <li><a href="{{ route('account') }}">Mis pedidos</a></li>
            <li><a href="{{ route('favorites') }}">Favoritos</a></li>
          @else
            <li><a href="{{ route('login') }}">Ingresar</a></li>
            <li><a href="{{ route('register') }}">Crear cuenta</a></li>
          @endauth
          @if (setting('whatsapp'))<li><a href="https://wa.me/{{ setting('whatsapp') }}" target="_blank" rel="noopener">WhatsApp</a></li>@endif
          @if (setting('email'))<li><a href="mailto:{{ setting('email') }}">{{ setting('email') }}</a></li>@endif
        </ul>
      </div>
    </div>
    <div class="big-word" aria-hidden="true">t<span>_</span>shirt</div>
    <div class="bottom">
      <span>© {{ date('Y') }} {{ setting('store_name', 't_shirtparaguay') }}</span>
      <span>Transferencia · Efectivo · Tarjeta próximamente</span>
    </div>
  </div>
</footer>

@if (setting('whatsapp'))
  <a class="wa-float" href="https://wa.me/{{ setting('whatsapp') }}?text={{ rawurlencode('Hola! Quería consultar por ') }}" target="_blank" rel="noopener" aria-label="Escribinos por WhatsApp"><svg class="i"><use href="#i-wa"/></svg></a>
@endif

{{-- Menú móvil --}}
<div class="mnav" id="mnav" aria-hidden="true">
  <div class="scrim" data-close></div>
  <div class="panel">
    <div class="head">@include('partials.logo')<button class="icon-btn" data-close aria-label="Cerrar"><svg class="i"><use href="#i-close"/></svg></button></div>
    <a class="big" href="{{ route('catalog') }}">Tienda</a>
    @foreach ($navCategories as $c)
      <a class="big" href="{{ route('category', $c) }}">{{ $c->name }}</a>
      @foreach ($c->children as $l)<a class="sub" href="{{ route('category', $l) }}">{{ $l->name }}</a>@endforeach
    @endforeach
    <a class="big" href="{{ route('catalog', ['oferta' => 1]) }}">Ofertas</a>
    <div style="margin-top:auto;display:grid;gap:10px;padding-top:24px">
      <a class="btn soft block" href="{{ route('favorites') }}"><svg class="i"><use href="#i-heart"/></svg> Favoritos</a>
      <a class="btn block" href="{{ auth()->check() ? route('account') : route('login') }}"><svg class="i"><use href="#i-user"/></svg> {{ auth()->check() ? 'Mi cuenta' : 'Ingresar o crear cuenta' }}</a>
    </div>
  </div>
</div>

{{-- Buscador --}}
<div class="search-pop" id="search" aria-hidden="true">
  <div class="scrim" data-close></div>
  <div class="box">
    <form action="{{ route('catalog') }}" role="search">
      <input name="q" type="search" placeholder="Buscá remeras, buzos, gorras…" autocomplete="off" data-search-input aria-label="Buscar productos">
      <button class="icon-btn" type="button" data-close aria-label="Cerrar"><svg class="i"><use href="#i-close"/></svg></button>
    </form>
    <div class="results" data-search-results></div>
  </div>
</div>

{{-- Carrito lateral --}}
<div class="drawer" id="cart" aria-hidden="true">
  <div class="scrim" data-close></div>
  <aside class="panel" aria-label="Carrito">
    <div class="top"><h3>Tu carrito</h3><button class="icon-btn" data-close aria-label="Cerrar"><svg class="i"><use href="#i-close"/></svg></button></div>
    <div data-drawer-body style="display:contents"></div>
  </aside>
</div>

<div class="toast" id="toast" role="status" aria-live="polite"></div>

<script>
  window.SHOP = {
    csrf: '{{ csrf_token() }}',
    drawerUrl: '{{ route('cart.drawer') }}',
    addUrl: '{{ route('cart.add') }}',
    searchUrl: '{{ route('search') }}',
    loginUrl: '{{ route('login') }}',
  };
</script>
<script src="{{ asset_v('vendor/sweetalert2/sweetalert2.all.min.js') }}" defer></script>
<script src="{{ asset_v('js/shop.js') }}" defer></script>
@stack('scripts')
</body>
</html>
