@extends('layouts.shop')
@section('title', 'Mi cuenta · '.setting('store_name', 't_shirtparaguay'))
@section('content')
@php($tab = request('t', 'pedidos'))
<div class="wrap">
  <div class="page-head" style="display:flex;justify-content:space-between;align-items:end;gap:16px;flex-wrap:wrap">
    <div><h1>Hola, {{ $user->firstName() }}</h1><p>{{ $user->username ? 'Usuario: '.$user->username : $user->email }}</p></div>
    <form method="post" action="{{ route('logout') }}">@csrf<button class="btn ghost sm"><svg class="i" style="width:16px"><use href="#i-logout"/></svg> Salir</button></form>
  </div>
  <div class="tabs">
    <a class="chip {{ $tab === 'pedidos' ? 'on' : '' }}" href="?t=pedidos">Mis pedidos</a>
    <a class="chip {{ $tab === 'datos' ? 'on' : '' }}" href="?t=datos">Mis datos</a>
    <a class="chip {{ $tab === 'clave' ? 'on' : '' }}" href="?t=clave">Contraseña</a>
    <a class="chip" href="{{ route('favorites') }}">Favoritos ({{ $favCount }})</a>
  </div>
  @if ($errors->any())<div class="alert err">{{ $errors->first() }}</div>@endif

  @if ($tab === 'pedidos')
    @if ($orders->count())
      <div class="orders-list">
        @foreach ($orders as $o)
          <a class="order-row" href="{{ route('account.order', $o) }}">
            <div><b>{{ $o->number }}</b><br><span>{{ $o->created_at->format('d/m/Y') }} · {{ $o->items_count }} {{ $o->items_count === 1 ? 'prenda' : 'prendas' }}</span></div>
            <span class="pill {{ ['entregado' => 'ok', 'finalizado' => 'ok', 'cancelado' => 'err', 'pendiente' => 'warn'][$o->status] ?? 'acc' }}">{{ $o->statusLabel() }}</span>
            <span class="hide-m">{{ $o->paymentLabel() }}</span>
            <b>{{ money($o->total) }}</b>
          </a>
        @endforeach
      </div>
    @else
      <div class="empty"><h3>Todavía no hiciste pedidos</h3><p>Cuando compres algo, lo vas a ver acá.</p><a class="btn" href="{{ route('catalog') }}">Ir a la tienda</a></div>
    @endif
  @elseif ($tab === 'datos')
    <form method="post" action="{{ route('account.update') }}" class="panel-card" style="max-width:720px">
      @csrf @method('PUT')
      @if ($errors->any())<div class="alert err">{{ $errors->first() }}</div>@endif
      <div class="form-grid">
        <div class="field"><label>Nombre</label><input class="input" name="first_name" value="{{ old('first_name', $user->last_name ? $user->name : \Illuminate\Support\Str::before($user->name, ' ')) }}" required></div>
        <div class="field"><label>Apellido</label><input class="input" name="last_name" value="{{ old('last_name', $user->last_name ?? trim(\Illuminate\Support\Str::after($user->name, ' '), ' ') ) }}" required></div>
        <div class="field"><label>Cédula o RUC</label><input class="input" name="document" value="{{ old('document', $user->document) }}" required></div>
        <div class="field"><label>Celular</label><input class="input" name="phone" value="{{ old('phone', $user->phone) }}" required></div>
        <div class="field"><label>País</label><select class="select input" name="country">@foreach (\App\Support\Accounts::COUNTRIES as $c)<option @selected(old('country', $user->country ?? 'Paraguay') === $c)>{{ $c }}</option>@endforeach</select></div>
        <div class="field"><label>Ciudad</label><input class="input" name="city" value="{{ old('city', $user->city) }}" required></div>
        <div class="field full"><label>Dirección</label><input class="input" name="address" value="{{ old('address', $user->address) }}" required></div>
        <div class="field"><label>Nombre de usuario</label><input class="input" name="username" value="{{ old('username', $user->username) }}" required autocapitalize="none" spellcheck="false"></div>
        <div class="field"><label>Correo <span style="color:var(--ink-3);font-weight:400">(opcional)</span></label><input class="input" type="email" name="email" value="{{ old('email', $user->email) }}"></div>
      </div>
      <button class="btn" style="margin-top:20px">Guardar</button>
    </form>
  @else
    <form method="post" action="{{ route('account.password') }}" class="panel-card" style="max-width:480px;display:grid;gap:14px">
      @csrf @method('PUT')
      <div class="field"><label>Contraseña actual</label><input class="input" type="password" name="current_password" required autocomplete="current-password"></div>
      <div class="field"><label>Contraseña nueva</label><input class="input" type="password" name="password" required minlength="6" autocomplete="new-password"></div>
      <div class="field"><label>Repetí la nueva</label><input class="input" type="password" name="password_confirmation" required autocomplete="new-password"></div>
      <button class="btn">Cambiar contraseña</button>
    </form>
  @endif
</div>
@endsection
