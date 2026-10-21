@extends('layouts.admin')
@section('title', 'Clientes')
@section('actions')<a class="btn accent sm" href="{{ route('admin.customers.create') }}"><svg class="i" style="width:16px"><use href="#i-plus"/></svg> Nuevo cliente</a>@endsection
@section('content')
<form class="filters-bar"><input class="input" name="q" value="{{ request('q') }}" placeholder="Nombre, correo, celular o CI"><select class="select" name="tipo" data-autosubmit><option value="">Todos</option><option value="mayorista" @selected(request('tipo') === 'mayorista')>Solo mayoristas</option></select><button class="btn sm soft">Buscar</button></form>
<div class="tbl-wrap"><table class="t">
  <thead><tr><th>Cliente</th><th>Contacto</th><th>Ciudad</th><th class="r">Pedidos</th><th class="r">Compró</th><th>Límite</th><th>Desde</th><th></th></tr></thead>
  @forelse ($customers as $c)
    <tr onclick="location='{{ route('admin.customers.show', $c) }}'" style="cursor:pointer">
      <td><b>{{ $c->name }}</b>@if($c->is_wholesale) <span class="pill acc">Mayorista</span>@endif</td><td>{{ $c->email ?: 'Sin correo' }}<br><span class="help">{{ $c->phone }}</span></td><td>{{ $c->city }}</td>
      <td class="r">{{ $c->orders_count }}</td><td class="r"><b>{{ money($c->spent ?? 0) }}</b></td><td class="help">{{ $c->purchase_limit ? money($c->purchase_limit) : '—' }}</td><td class="help">{{ $c->created_at->format('d/m/Y') }}</td>
      <td class="r" onclick="event.stopPropagation()" style="white-space:nowrap">
        <a class="icon-btn" href="{{ route('admin.customers.show', $c) }}" title="Editar" aria-label="Editar a {{ $c->name }}"><svg class="i"><use href="#i-edit"/></svg></a>
        <button type="button" class="icon-btn" title="Eliminar" aria-label="Eliminar a {{ $c->name }}" data-delete="{{ route('admin.customers.destroy', $c) }}" data-confirm="¿Eliminar a {{ $c->name }}? Su cuenta se borra, pero sus pedidos se conservan."><svg class="i"><use href="#i-trash"/></svg></button>
      </td>
    </tr>
  @empty <tr><td colspan="8" class="help" style="padding:40px;text-align:center">Todavía no hay clientes registrados.</td></tr> @endforelse
</table></div>
{{ $customers->links() }}
@endsection
