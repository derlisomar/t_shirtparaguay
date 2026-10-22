@extends('layouts.admin')
@section('title', 'Categorías')
@section('actions')<a class="btn sm" href="{{ route('admin.categories.create') }}"><svg class="i" style="width:16px"><use href="#i-plus"/></svg> Nueva categoría</a>@endsection
@section('content')
<p class="help" style="margin:-6px 0 14px">Las <b>líneas</b> agrupan los productos dentro de una categoría (por ejemplo, Remeras › Oversize 30.1). En la tienda, al entrar a la categoría se ven separadas por línea.</p>
<div class="tbl-wrap"><table class="t">
  <thead><tr><th>Orden</th><th>Nombre</th><th>Descripción</th><th class="r">Productos</th><th>Estado</th><th></th></tr></thead>
  @foreach ($categories as $c)
    <tr>
      <td>{{ $c->sort_order }}</td><td>@if ($c->depth)<span class="help" style="margin-right:6px">└</span>{{ $c->name }}@else<b>{{ $c->name }}</b>@endif</td><td class="help">{{ $c->description }}</td><td class="r">{{ $c->products_count }}</td>
      <td>@if($c->is_active)<span class="pill ok">Visible</span>@else<span class="pill">Oculta</span>@endif</td>
      <td class="r" style="white-space:nowrap">@unless ($c->depth)<a class="btn sm ghost" href="{{ route('admin.categories.create', ['dentro_de' => $c->id]) }}">+ Línea</a> @endunless<a class="btn sm soft" href="{{ route('admin.categories.edit', $c) }}">Editar</a>
        <button type="button" class="btn sm ghost" data-delete="{{ route('admin.categories.destroy', $c) }}" data-confirm="¿Borrar la categoría {{ $c->name }}?">Borrar</button></td>
    </tr>
  @endforeach
</table></div>
@endsection
