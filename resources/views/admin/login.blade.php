<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Panel · {{ setting('store_name', 't_shirtparaguay') }}</title>
  <link rel="icon" href="{{ asset('img/favicon.svg') }}">
  @include('partials.theme-head')
  <link rel="stylesheet" href="{{ asset_v('css/shop.css') }}"><link rel="stylesheet" href="{{ asset_v('css/admin.css') }}">
</head>
<body class="admin">
<div class="adm-login">
  <form method="post" action="{{ route('admin.login') }}">
    @csrf
    @include('partials.logo')
    <h1 style="font-size:30px;margin-top:8px">Panel de administración</h1>
    @if ($errors->any())<div class="alert err" style="margin:0">{{ $errors->first() }}</div>@endif
    <div class="field"><label for="e">Correo</label><input class="input" id="e" type="email" name="email" value="{{ old('email') }}" required autofocus></div>
    <div class="field"><label for="p">Contraseña</label><input class="input" id="p" type="password" name="password" required></div>
    <label class="check"><input type="checkbox" name="remember" value="1"> Recordarme</label>
    <button class="btn block">Entrar</button>
  </form>
</div>
</body>
</html>
