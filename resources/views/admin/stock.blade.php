@extends('layouts.admin')
@section('title', 'Stock')
@section('content')
<form class="filters-bar"><input class="input" name="q" value="{{ request('q') }}" placeholder="Buscar producto"><button class="btn sm soft">Buscar</button></form>
<form method="post" action="{{ route('admin.stock.update') }}">
  @csrf
  <div style="display:grid;gap:12px">
    @foreach ($products as $p)
      @php($sz = $p->sizeOptions())
      @php($cl = $p->colorOptions())
      <div class="box" style="display:grid;grid-template-columns:60px 1fr;gap:16px">
        <img class="thumb" src="{{ $p->mainImageUrl() }}" alt="">
        <div>
          <h2 style="margin-bottom:8px">{{ $p->name }} <a href="{{ route('admin.products.edit', $p) }}">Editar</a></h2>
          <div class="stock-grid"><table>
            <tr><th></th>@foreach($sz as $s)<th>{{ $s->name }}</th>@endforeach</tr>
            @foreach (($cl->count() ? $cl : collect([null])) as $c)
              <tr><th class="c">@if($c)<span style="display:inline-block;width:12px;height:12px;border-radius:9px;background:{{ $c->hex }};margin-right:6px"></span>{{ $c->name }}@else Único @endif</th>
              @foreach ($sz as $s)
                @php($v = $p->variants->first(fn($v) => $v->size_id === $s->id && $v->color_id === $c?->id))
                <td>@if($v)<input type="number" min="0" name="stock[{{ $v->id }}]" value="{{ $v->stock }}" aria-label="{{ $s->name }} {{ $c?->name }}" style="{{ $v->stock <= 0 ? 'color:var(--danger)' : '' }}">@else<span class="help">—</span>@endif</td>
              @endforeach</tr>
            @endforeach
          </table></div>
        </div>
      </div>
    @endforeach
  </div>
  <div style="position:sticky;bottom:16px;margin-top:16px;display:flex;justify-content:flex-end"><button class="btn accent" style="box-shadow:var(--shadow)">Guardar stock</button></div>
</form>
{{ $products->links() }}
@endsection
