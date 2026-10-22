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
        <p style="color:var(--ink-2)">No hace falta correo: entrás con tu usuario y contraseña.</p>
        @include('shop.partials.account-fields')
        <button class="btn accent block">Crear cuenta</button>
        <p style="text-align:center;color:var(--ink-2)">¿Ya tenés cuenta? <a href="{{ route('login') }}" style="color:var(--ink);font-weight:600;text-decoration:underline">Ingresá</a></p>
      </div>
    </form>
  </div>
</div>
@endsection
