@extends('layouts.admin')
@section('title', 'Talles y colores')
@section('content')
<div class="cols-2" style="margin-top:0;grid-template-columns:1fr 1fr">
  <div class="box">
    <h2>Talles</h2>
    <form method="post" action="{{ route('admin.sizes.store') }}" style="display:flex;gap:8px;margin-bottom:14px">@csrf<input class="input" name="name" placeholder="Nuevo talle (ej.: 38, XS, 3XL)" required><button class="btn">Agregar</button></form>
    @foreach ($sizes as $s)
      <form method="post" action="{{ route('admin.sizes.update', $s) }}" style="display:grid;grid-template-columns:1fr 80px auto auto;gap:8px;align-items:center;padding:6px 0;box-shadow:0 1px 0 var(--line)">
        @csrf @method('PUT')
        <input class="input" style="height:40px" name="name" value="{{ $s->name }}" aria-label="Nombre">
        <input class="input" style="height:40px" type="number" name="sort_order" value="{{ $s->sort_order }}" title="Orden" aria-label="Orden">
        <button class="btn sm soft">Guardar</button>
        <button type="button" class="btn sm ghost" data-delete="{{ route('admin.sizes.destroy', $s) }}" data-confirm="¿Borrar el talle {{ $s->name }}?" @disabled($s->variants_count) title="{{ $s->variants_count ? 'En uso en '.$s->variants_count.' variantes' : '' }}"><svg class="i" style="width:15px"><use href="#i-trash"/></svg></button>
      </form>
    @endforeach
    <p class="help" style="margin-top:10px">El orden define cómo se muestran (S antes que M, etc.).</p>
  </div>
  <div class="box">
    <h2>Colores</h2>
    <form method="post" action="{{ route('admin.colors.store') }}" style="display:flex;gap:8px;margin-bottom:14px">@csrf<input type="color" name="hex" value="#ff5a1f" style="width:50px;height:50px;border:0;border-radius:12px;background:none" aria-label="Color"><input class="input" name="name" placeholder="Nombre del color" required><button class="btn">Agregar</button></form>
    @foreach ($colors as $c)
      <form method="post" action="{{ route('admin.colors.update', $c) }}" style="display:grid;grid-template-columns:44px 1fr 70px auto auto;gap:8px;align-items:center;padding:6px 0;box-shadow:0 1px 0 var(--line)">
        @csrf @method('PUT')
        <input type="color" name="hex" value="{{ $c->hex }}" style="width:40px;height:40px;border:0;border-radius:10px;background:none" aria-label="Color">
        <input class="input" style="height:40px" name="name" value="{{ $c->name }}" aria-label="Nombre">
        <input class="input" style="height:40px" type="number" name="sort_order" value="{{ $c->sort_order }}" aria-label="Orden">
        <button class="btn sm soft">Guardar</button>
        <button type="button" class="btn sm ghost" data-delete="{{ route('admin.colors.destroy', $c) }}" data-confirm="¿Borrar el color {{ $c->name }}?" @disabled($c->variants_count)><svg class="i" style="width:15px"><use href="#i-trash"/></svg></button>
      </form>
    @endforeach
  </div>
</div>
@endsection
