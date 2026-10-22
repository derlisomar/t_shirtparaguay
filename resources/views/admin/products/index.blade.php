@extends('layouts.admin')
@section('title', 'Productos')
@section('actions')<a class="btn sm" href="{{ route('admin.products.create') }}"><svg class="i" style="width:16px"><use href="#i-plus"/></svg> Nuevo producto</a>@endsection
@section('content')
<form class="filters-bar">
  <input class="input" name="q" value="{{ request('q') }}" placeholder="Buscar por nombre o código">
  <select class="select" name="categoria" data-autosubmit><option value="">Todas las categorías</option>@foreach($categories as $c)<option value="{{ $c->id }}" @selected(request('categoria') == $c->id)>{{ $c->depth ? '— ' : '' }}{{ $c->name }}</option>@endforeach</select>
  <select class="select" name="estado" data-autosubmit><option value="">Todos</option><option value="sin-stock" @selected(request('estado') === 'sin-stock')>Sin stock</option><option value="ocultos" @selected(request('estado') === 'ocultos')>Ocultos</option></select>
  <button class="btn sm soft">Buscar</button>
</form>
<div class="tbl-wrap">
  <table class="t">
    <thead><tr><th></th><th>Producto</th><th>Categoría</th><th class="r">Precio</th><th class="r">Stock</th><th>Estado</th><th></th></tr></thead>
    <tbody>
    @forelse ($products as $p)
      @php($st = $p->totalStock())
      <tr>
        <td style="width:60px"><img class="thumb" src="{{ $p->mainImageUrl() }}" alt=""></td>
        <td><a href="{{ route('admin.products.edit', $p) }}"><b>{{ $p->name }}</b></a><br><span class="help">{{ $p->sku }} · {{ $p->variants->count() }} variantes</span></td>
        <td>{{ $p->category?->name ?? '—' }}</td>
        <td class="r"><b>{{ money($p->price) }}</b>@if($p->isOnSale())<br><s class="help">{{ money($p->compare_price) }}</s>@endif</td>
        <td class="r"><span class="pill {{ $st <= 0 ? 'err' : ($st <= 5 ? 'warn' : 'ok') }}">{{ $st }}</span></td>
        <td>@if($p->is_active)<span class="pill ok">Visible</span>@else<span class="pill">Oculto</span>@endif @if($p->is_featured)<span class="pill acc">Destacado</span>@endif</td>
        <td class="r" style="white-space:nowrap">
          <a class="btn sm soft" href="{{ route('admin.products.edit', $p) }}">Editar</a>
          <a class="btn sm ghost" href="{{ route('product.show', $p) }}" target="_blank">Ver</a>
        </td>
      </tr>
    @empty
      <tr><td colspan="7" style="padding:40px;text-align:center" class="help">No hay productos con esos filtros.</td></tr>
    @endforelse
    </tbody>
  </table>
</div>
@if ($products->total())<p class="help list-count">{{ $products->hasPages() ? 'Mostrando '.$products->firstItem().' a '.$products->lastItem().' de '.$products->total().' productos · 20 por página' : $products->total().' '.($products->total() === 1 ? 'producto' : 'productos') }}</p>@endif
{{ $products->links() }}
@endsection
