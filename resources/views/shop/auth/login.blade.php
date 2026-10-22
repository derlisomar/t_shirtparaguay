@extends('layouts.shop')
@section('title', 'Ingresar · '.setting('store_name', 't_shirtparaguay'))
@section('content')
<div class="auth">
  <div class="pic" style="background:#1f3326"><img src="{{ asset('uploads/seed/cut/remera-terere-club--verde-salvia.webp') }}" alt=""><p>Tu ronda de tereré, tu remera.</p></div>
  <div class="form">
    <form method="post" action="{{ route('login') }}">
      @csrf
      <div>
        <h1>Hola de nuevo</h1>
        <p style="color:var(--ink-2)">Ingresá para ver tus pedidos y terminar tu compra.</p>
        @if ($errors->any())<div class="alert err" style="margin:0">{{ $errors->first() }}</div>@endif
        <div class="field"><label for="e">Usuario</label><input class="input" id="e" name="login" value="{{ old('login') }}" required autofocus autocomplete="username" autocapitalize="none" spellcheck="false"><span style="font-size:13px;color:var(--ink-3)">También podés poner tu número de cédula o tu correo.</span></div>
        <div class="field"><label for="p">Contraseña</label><input class="input" id="p" type="password" name="password" required autocomplete="current-password"></div>
        <label class="check"><input type="checkbox" name="remember" value="1" checked> Recordarme en este dispositivo</label>
        <button class="btn block">Ingresar</button>
        <p style="text-align:center;color:var(--ink-2)">¿No tenés cuenta? <a href="{{ route('register') }}" style="color:var(--ink);font-weight:600;text-decoration:underline">Creá una en un minuto</a></p>
      </div>
    </form>
  </div>
</div>
@endsection
