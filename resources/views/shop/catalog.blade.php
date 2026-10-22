@extends('layouts.shop')
@section('title', ($category?->name ?? (request('q') ? 'Resultados para «'.request('q').'»' : 'Tienda')).' · '.setting('store_name', 't_shirtparaguay'))

@section('content')
<div class="wrap">
  <div class="page-head">
    <div class="crumbs"><a href="{{ route('home') }}">Inicio</a><span>/</span><a href="{{ route('catalog') }}">Tienda</a>@if($category?->parent)<span>/</span><a href="{{ route('category', $category->parent) }}">{{ $category->parent->name }}</a>@endif @if($category)<span>/</span><span>{{ $category->name }}</span>@endif</div>
    <h1>{{ $category?->name ?? (request('q') ? '«'.request('q').'»' : (request('oferta') ? 'Ofertas' : 'Toda la tienda')) }}</h1>
    @if ($category?->description)<p>{{ $category->description }}</p>@endif
  </div>

  <div class="chips">
    <a class="chip {{ ! $category ? 'on' : '' }}" href="{{ route('catalog') }}">Todo</a>
    @foreach ($categories as $c)
      <a class="chip {{ $category && ($category->id === $c->id || $category->parent_id === $c->id) ? 'on' : '' }}" href="{{ route('category', $c) }}">{{ $c->name }}</a>
    @endforeach
  </div>
  @if ($lines->isNotEmpty())
    @php($root = $category->parent ?? $category)
    <div class="chips lines" aria-label="Líneas de {{ $root->name }}">
      <a class="chip {{ $category->id === $root->id ? 'on' : '' }}" href="{{ route('category', $root) }}">Todas las líneas</a>
      @foreach ($lines as $l)
        <a class="chip {{ $category->id === $l->id ? 'on' : '' }}" href="{{ route('category', $l) }}">{{ $l->name }}</a>
      @endforeach
    </div>
  @endif

  <div class="catalog">
    <form class="filters" id="filters" method="get" action="{{ url()->current() }}">
      @if (request('q'))<input type="hidden" name="q" value="{{ request('q') }}">@endif
      @if (request('orden'))<input type="hidden" name="orden" value="{{ request('orden') }}">@endif
      <div style="display:flex;justify-content:space-between;align-items:center">
        <h3 style="font-size:22px">Filtrar</h3>
        <button type="button" class="icon-btn filters-toggle" data-filters-toggle aria-label="Cerrar filtros"><svg class="i"><use href="#i-close"/></svg></button>
      </div>
      <div>
        <h4>Talle</h4>
        <div class="sizes">
          @foreach ($allSizes as $s)
            <label class="sz"><input type="checkbox" name="talle[]" value="{{ $s->id }}" @checked(in_array($s->id, $selSizes))><span>{{ $s->name }}</span></label>
          @endforeach
        </div>
      </div>
      <div>
        <h4>Color</h4>
        <div class="swatches">
          @foreach ($allColors as $c)
            <label class="sw" title="{{ $c->name }}"><input type="checkbox" name="color[]" value="{{ $c->id }}" @checked(in_array($c->id, $selColors))><span style="background:{{ $c->hex }}"></span><span class="sr">{{ $c->name }}</span></label>
          @endforeach
        </div>
      </div>
      <div>
        <h4>Precio (Gs.)</h4>
        <div class="range">
          <input class="input" style="height:42px" name="min" inputmode="numeric" placeholder="Desde" value="{{ request('min') }}" aria-label="Precio desde">
          <input class="input" style="height:42px" name="max" inputmode="numeric" placeholder="Hasta" value="{{ request('max') }}" aria-label="Precio hasta">
        </div>
      </div>
      <label class="check"><input type="checkbox" name="oferta" value="1" @checked(request('oferta'))> Solo ofertas</label>
      <div style="display:flex;gap:8px">
        <button class="btn sm" type="submit">Aplicar</button>
        <a class="btn sm ghost" href="{{ url()->current() }}">Limpiar</a>
      </div>
    </form>

    <div>
      <div class="toolbar">
        <span class="count">{{ $products->total() }} {{ $products->total() === 1 ? 'producto' : 'productos' }}</span>
        <div style="display:flex;gap:8px">
          <button type="button" class="chip filters-toggle" data-filters-toggle><svg class="i" style="width:16px"><use href="#i-filter"/></svg> Filtrar</button>
          <form method="get">
            @foreach (request()->except('orden', 'page') as $k => $v)
              @if (is_array($v)) @foreach ($v as $vv)<input type="hidden" name="{{ $k }}[]" value="{{ $vv }}">@endforeach
              @else <input type="hidden" name="{{ $k }}" value="{{ $v }}"> @endif
            @endforeach
            <select class="select" name="orden" data-autosubmit aria-label="Ordenar">
              <option value="">Más nuevos</option>
              <option value="mas-vendidos" @selected(request('orden') === 'mas-vendidos')>Más vendidos</option>
              <option value="precio-asc" @selected(request('orden') === 'precio-asc')>Menor precio</option>
              <option value="precio-desc" @selected(request('orden') === 'precio-desc')>Mayor precio</option>
            </select>
          </form>
        </div>
      </div>

      @if ($groups)
        @foreach ($groups as $g)
          <section class="line-group">
            <div class="line-head">
              <div><h2>{{ $g->category?->name ?? 'Otros modelos' }}</h2>@if ($g->category?->description)<p>{{ $g->category->description }}</p>@endif</div>
              @if ($g->category)<a href="{{ route('category', $g->category) }}">Ver solo esta línea ({{ $g->products->count() }})</a>@endif
            </div>
            <div class="grid">
              @foreach ($g->products as $product)@include('shop.partials.card')@endforeach
            </div>
          </section>
        @endforeach
      @elseif ($products->count())
        <div class="grid">
          @foreach ($products as $product)@include('shop.partials.card')@endforeach
        </div>
        {{ $products->links() }}
      @else
        <div class="empty">
          <h3>No encontramos prendas con esos filtros</h3>
          <p>Probá sacar algún filtro o buscá con otra palabra.</p>
          <a class="btn" href="{{ route('catalog') }}">Ver toda la tienda</a>
        </div>
      @endif
    </div>
  </div>
</div>
@endsection
