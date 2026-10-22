<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Panel') · {{ setting('store_name', 't_shirtparaguay') }}</title>
  <link rel="icon" href="{{ setting('favicon') ? upload_url(setting('favicon')) : asset('img/favicon.svg') }}">
  @include('partials.theme-head')
  <link rel="stylesheet" href="{{ asset_v('css/shop.css') }}">
  <link rel="stylesheet" href="{{ asset_v('css/admin.css') }}">
</head>
<body class="admin">
@include('partials.icons')
@php($pendingCount = \App\Models\Order::where('status', 'pendiente')->count())
<div class="adm">
  <aside class="side" id="side">
    @include('partials.logo')
    <a class="it sell" href="{{ route('admin.pos') }}"><svg class="i"><use href="#i-store"/></svg> Vender</a>
    <a class="it {{ request()->routeIs('admin.reports*') ? 'on' : '' }}" href="{{ route('admin.reports') }}"><svg class="i"><use href="#i-chart"/></svg> Reportes</a>
    <a class="it {{ request()->routeIs('admin.orders.*') ? 'on' : '' }}" href="{{ route('admin.orders.index') }}"><svg class="i"><use href="#i-bag"/></svg> Pedidos @if($pendingCount)<span class="n">{{ $pendingCount }}</span>@endif</a>
    <a class="it {{ request()->routeIs('admin.customers.*') ? 'on' : '' }}" href="{{ route('admin.customers.index') }}"><svg class="i"><use href="#i-user"/></svg> Clientes</a>
    <div class="grp">Catálogo</div>
    <a class="it {{ request()->routeIs('admin.products.*') ? 'on' : '' }}" href="{{ route('admin.products.index') }}"><svg class="i"><use href="#i-box"/></svg> Productos</a>
    <a class="it {{ request()->routeIs('admin.stock') ? 'on' : '' }}" href="{{ route('admin.stock') }}"><svg class="i"><use href="#i-swap"/></svg> Stock</a>
    <a class="it {{ request()->routeIs('admin.categories.*') ? 'on' : '' }}" href="{{ route('admin.categories.index') }}"><svg class="i"><use href="#i-filter"/></svg> Categorías</a>
    <a class="it {{ request()->routeIs('admin.attributes') ? 'on' : '' }}" href="{{ route('admin.attributes') }}"><svg class="i"><use href="#i-check"/></svg> Talles y colores</a>
    <div class="grp">Tienda</div>
    <a class="it {{ request()->routeIs('admin.slides.*') ? 'on' : '' }}" href="{{ route('admin.slides.index') }}"><svg class="i"><use href="#i-right"/></svg> Slider del inicio</a>
    <a class="it {{ request()->routeIs('admin.settings') ? 'on' : '' }}" href="{{ route('admin.settings') }}"><svg class="i"><use href="#i-card"/></svg> Configuración</a>
    <a class="it {{ request()->routeIs('admin.admins') ? 'on' : '' }}" href="{{ route('admin.admins') }}"><svg class="i"><use href="#i-user"/></svg> Usuarios del panel</a>
    <div class="bottom">
      <a class="it" href="{{ route('home') }}" target="_blank"><svg class="i"><use href="#i-arrow"/></svg> Ver la tienda</a>
      <form method="post" action="{{ route('logout') }}">@csrf<button class="it" style="border:0;background:none;width:100%;display:flex;gap:12px;align-items:center;padding:10px 12px;color:var(--ink-2);font-weight:500"><svg class="i"><use href="#i-logout"/></svg> Salir</button></form>
    </div>
  </aside>
  <div class="main">
    <div class="topbar">
      <div style="display:flex;gap:8px;align-items:center">
        <button class="icon-btn burger-a" onclick="document.getElementById('side').classList.toggle('open')" aria-label="Menú"><svg class="i"><use href="#i-menu"/></svg></button>
        <h1>@yield('title', 'Panel')</h1>
      </div>
      <div class="acts">
        @yield('actions')
        <button class="icon-btn theme-toggle" aria-label="Cambiar modo claro u oscuro"><svg class="i moon"><use href="#i-moon"/></svg><svg class="i sun"><use href="#i-sun"/></svg></button>
      </div>
    </div>
    @if (session('ok'))<div class="alert">{{ session('ok') }}</div>@endif
    @if ($errors->any())<div class="alert err">{{ $errors->first() }}</div>@endif
    @yield('content')
  </div>
</div>
<div class="toast" id="toast" role="status" aria-live="polite"></div>
<script>window.SHOP = { csrf: '{{ csrf_token() }}' };</script>
<script src="{{ asset_v('vendor/sweetalert2/sweetalert2.all.min.js') }}" defer></script>
<script src="{{ asset_v('js/shop.js') }}" defer></script>
<script src="{{ asset_v('js/admin.js') }}" defer></script>
@stack('scripts')
</body>
</html>
