@php
  $imgs = $product->images;
  $first = $imgs->first();
  $alt = $imgs->get(1);
  $colors = $product->colorOptions();
  $sizes = $product->variants->groupBy('size_id');
  $firstColor = $colors->first();
  $stock = $product->availableStock();
@endphp
<article class="card">
  <a href="{{ route('product.show', $product) }}" class="media" aria-label="{{ $product->name }}">
    <img src="{{ $first ? $first->url : asset('img/sin-foto.svg') }}" alt="{{ $product->name }}" loading="lazy" width="900" height="1125">
    @if ($alt)<img class="alt" src="{{ $alt->url }}" alt="" loading="lazy" width="900" height="1125">@endif
  </a>
  <div class="tags">
    @if ($stock <= 0)<span class="tag out">Agotado</span>
    @elseif ($product->isOnSale())<span class="tag sale">-{{ $product->discountPercent() }}%</span>@endif
    @if ($product->is_new && $stock > 0)<span class="tag">Nuevo</span>@endif
  </div>
  <button class="fav {{ isset($favIds) && in_array($product->id, $favIds) ? 'on' : '' }}" data-fav="{{ route('favorites.toggle', $product) }}" aria-label="Guardar en favoritos"><svg class="i"><use href="#i-heart"/></svg></button>
  @if ($stock > 0)
    <div class="quick" aria-label="Agregar rápido">
      <small>Agregar rápido{{ $firstColor ? ' · '.$firstColor->name : '' }}</small>
      <div class="row">
        @foreach ($product->sizeOptions() as $size)
          @php($v = $product->variants->first(fn ($v) => $v->size_id === $size->id && (! $firstColor || $v->color_id === $firstColor->id)))
          <button type="button" data-quick-add="{{ $v?->id }}" @disabled(! $v || $v->available() <= 0)>{{ $size->name }}</button>
        @endforeach
      </div>
    </div>
  @endif
  <div class="info">
    <div>
      <h3><a href="{{ route('product.show', $product) }}">{{ $product->name }}</a></h3>
      @if ($colors->count() > 1)
        <div class="dots-colors" style="margin-top:7px" aria-label="{{ $colors->count() }} colores">
          @foreach ($colors->take(5) as $c)<i style="background:{{ $c->hex }}" title="{{ $c->name }}"></i>@endforeach
        </div>
      @endif
    </div>
    <div class="prices" style="flex-direction:column;align-items:flex-end;gap:2px">
      @if (auth()->user()?->is_wholesale && $product->hasWholesale())
        <b>{{ money($product->wholesale_price) }}</b><s>{{ money($product->price) }}</s>
      @else
        <b>{{ money($product->price) }}</b>
        @if ($product->isOnSale())<s>{{ money($product->compare_price) }}</s>@endif
      @endif
    </div>
  </div>
</article>
