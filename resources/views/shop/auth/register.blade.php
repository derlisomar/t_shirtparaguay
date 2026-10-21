@extends('layouts.shop')
@section('title', 'Crear cuenta · '.setting('store_name', 't_shirtparaguay'))
@section('content')
<div class="auth">
  <div class="pic"><img src="{{ asset('uploads/seed/cut/buzo-canguro-jopara--lila.webp') }}" alt=""><p>Guardá tus favoritos y seguí tus pedidos.</p></div>
  <div class="form">
    <form method="post" action="{{ route('register') }}">
      @csrf
      <div>
        <h1>Creá tu cuenta</h1>
        @if ($errors->any())<div class="alert err" style="margin:0">{{ $errors->first() }}</div>@endif
        <div class="field"><label for="n">Nombre y apellido</label><input class="input" id="n" name="name" value="{{ old('name') }}" required autocomplete="name"></div>
        <div class="field"><label for="e">Correo</label><input class="input" id="e" type="email" name="email" value="{{ old('email') }}" required autocomplete="email"></div>
        <div class="field"><label for="ph">Celular <span style="color:var(--ink-3);font-weight:400">(opcional)</span></label><input class="input" id="ph" name="phone" value="{{ old('phone') }}" inputmode="tel" autocomplete="tel"></div>
        <div class="field"><label for="p">Contraseña</label><input class="input" id="p" type="password" name="password" required minlength="8" autocomplete="new-password" placeholder="Al menos 8 caracteres"></div>
        <div class="field"><label for="p2">Repetí la contraseña</label><input class="input" id="p2" type="password" name="password_confirmation" required autocomplete="new-password"></div>
        <button class="btn accent block">Crear cuenta</button>
        <p style="text-align:center;color:var(--ink-2)">¿Ya tenés cuenta? <a href="{{ route('login') }}" style="color:var(--ink);font-weight:600;text-decoration:underline">Ingresá</a></p>
      </div>
    </form>
  </div>
</div>
@endsection
