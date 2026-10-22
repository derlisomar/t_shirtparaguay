@extends('layouts.admin')
@section('title', 'Usuarios del panel')
@section('content')
<div class="form-cols">
  <div class="tbl-wrap"><table class="t">
    <thead><tr><th>Nombre</th><th>Correo</th><th></th></tr></thead>
    @foreach ($admins as $a)
      <tr><td><b>{{ $a->name }}</b>@if($a->id === auth()->id()) <span class="pill">Vos</span>@endif</td><td>{{ $a->email }}</td>
      <td class="r">@if($a->id !== auth()->id())<button type="button" class="btn sm ghost" data-delete="{{ route('admin.admins.destroy', $a) }}" data-confirm="¿Quitarle el acceso al panel a {{ $a->name }}?" data-ok="Sí, quitar acceso">Quitar acceso</button>@endif</td></tr>
    @endforeach
  </table></div>
  <form method="post" action="{{ route('admin.admins.store') }}" class="box" style="display:grid;gap:12px">
    @csrf
    <h2 style="margin:0">Agregar o cambiar contraseña</h2>
    <p class="help">Para cambiar tu contraseña, poné tu mismo correo con la contraseña nueva.</p>
    <div class="field"><label>Nombre</label><input class="input" name="name" required></div>
    <div class="field"><label>Correo</label><input class="input" type="email" name="email" required></div>
    <div class="field"><label>Contraseña</label><input class="input" type="password" name="password" required minlength="8" autocomplete="new-password"></div>
    <button class="btn">Guardar</button>
  </form>
</div>
@endsection
