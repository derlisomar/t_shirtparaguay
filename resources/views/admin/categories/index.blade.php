@extends('layouts.admin')
@section('title', 'Categorías')
@section('actions')<a class="btn sm" href="{{ route('admin.categories.create') }}"><svg class="i" style="width:16px"><use href="#i-plus"/></svg> Nueva categoría</a>@endsection
@section('content')
<div class="tbl-wrap"><table class="t">
  <thead><tr><th>Orden</th><th>Nombre</th><th>Descripción</th><th class="r">Productos</th><th>Estado</th><th></th></tr></thead>
  @foreach ($categories as $c)
    <tr>
      <td>{{ $c->sort_order }}</td><td><b>{{ $c->name }}</b></td><td class="help">{{ $c->description }}</td><td class="r">{{ $c->products_count }}</td>
      <td>@if($c->is_active)<span class="pill ok">Visible</span>@else<span class="pill">Oculta</span>@endif</td>
      <td class="r" style="white-space:nowrap"><a class="btn sm soft" href="{{ route('admin.categories.edit', $c) }}">Editar</a>
        <button type="button" class="btn sm ghost" data-delete="{{ route('admin.categories.destroy', $c) }}" data-confirm="¿Borrar la categoría {{ $c->name }}?">Borrar</button></td>
    </tr>
  @endforeach
</table></div>
@endsection
