@extends('layouts.admin')
@section('title', $product->exists ? $product->name : 'Nuevo producto')
@section('actions')
  @if ($product->exists)
    <a class="btn sm ghost" href="{{ route('product.show', $product) }}" target="_blank">Ver en la tienda</a>
    <form method="post" action="{{ route('admin.products.duplicate', $product) }}">@csrf<button class="btn sm soft">Duplicar</button></form>
  @endif
@endsection
@section('content')
@php
  $usedSizes = $product->variants->pluck('size_id')->unique()->all();
  $usedColors = $product->variants->pluck('color_id')->filter()->unique()->all();
  if (! $product->exists) { $usedSizes = $sizes->whereIn('name', ['S', 'M', 'L', 'XL'])->pluck('id')->all(); }
  $stockMap = [];
  foreach ($product->variants as $v) { $stockMap[$v->size_id][$v->color_id ?? 0] = $v->stock; }
@endphp
<form method="post" enctype="multipart/form-data" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" id="product-form">
  @csrf @if($product->exists) @method('PUT') @endif
  <div class="form-cols">
    <div class="stack">
      <div class="box">
        <h2>Datos</h2>
        <div class="form-grid">
          <div class="field full"><label>Nombre</label><input class="input" name="name" value="{{ old('name', $product->name) }}" required placeholder="Ej.: Remera Tereré Club"></div>
          <div class="field full"><label>Descripción</label><textarea class="textarea" name="description" rows="5" placeholder="Tela, calce, detalles…">{{ old('description', $product->description) }}</textarea></div>
          @php($unitPrice = $product->isOnSale() ? $product->compare_price : $product->price)
          @php($offerPrice = $product->isOnSale() ? $product->price : null)
          <div class="field"><label>1 · Precio por unidad (Gs.)</label><input class="input" name="unit_price" inputmode="numeric" data-money value="{{ old('unit_price', $unitPrice ? number_format($unitPrice, 0, ',', '.') : '') }}" required></div>
          <div class="field"><label>2 · Precio de oferta (Gs.) <span class="help">(opcional)</span></label><input class="input" name="offer_price" inputmode="numeric" data-money value="{{ old('offer_price', $offerPrice ? number_format($offerPrice, 0, ',', '.') : '') }}" placeholder="Vacío = sin oferta"></div>
          <div class="field"><label>3 · Precio mayorista (Gs.) <span class="help">(opcional)</span></label><input class="input" name="wholesale_price" inputmode="numeric" data-money value="{{ old('wholesale_price', $product->wholesale_price ? number_format($product->wholesale_price, 0, ',', '.') : '') }}" placeholder="Ej.: 95.000"></div>
          <div class="field"><label>Mayorista desde <span class="help">(unidades)</span></label><input class="input" name="wholesale_min_qty" type="number" min="0" value="{{ old('wholesale_min_qty', $product->wholesale_min_qty) }}" placeholder="Ej.: 6"></div>
          <p class="help full" style="margin-top:-4px">Con oferta, la tienda muestra el precio por unidad tachado y el de oferta al lado. El precio mayorista se aplica solo cuando el cliente lleva esa cantidad del producto (sumando talles y colores), o siempre si el cliente está marcado como mayorista en <a href="{{ route('admin.customers.index') }}" style="text-decoration:underline">Clientes</a>. Si dejás vacía la cantidad, solo lo ven los clientes mayoristas.</p>
        </div>
      </div>

      <div class="box">
        <h2>Talles, colores y stock</h2>
        <div class="field"><label>Talles que tiene</label>
          <div class="pick" data-pick="sizes">
            @foreach ($sizes as $s)<label><input type="checkbox" value="{{ $s->id }}" data-name="{{ $s->name }}" @checked(in_array($s->id, $usedSizes))><span>{{ $s->name }}</span></label>@endforeach
          </div>
        </div>
        <div class="field" style="margin-top:14px"><label>Colores <span class="help">(si no elegís ninguno, el producto va sin color)</span></label>
          <div class="pick" data-pick="colors">
            @foreach ($colors as $c)<label><input type="checkbox" value="{{ $c->id }}" data-name="{{ $c->name }}" data-hex="{{ $c->hex }}" @checked(in_array($c->id, $usedColors))><span><i style="background:{{ $c->hex }}"></i>{{ $c->name }}</span></label>@endforeach
          </div>
          <p class="help">¿Falta un talle o color? Agregalo en <a href="{{ route('admin.attributes') }}" style="text-decoration:underline">Talles y colores</a>.</p>
        </div>
        <div class="field" style="margin-top:16px"><label>Cantidad en stock de cada combinación</label>
          <div class="stock-grid" data-stock-grid data-stock='@json($stockMap)'></div>
          <p class="help">Dejá un casillero vacío si esa combinación no existe. Poné 0 si existe pero está agotada.</p>
        </div>
      </div>

      <div class="box">
        <h2>Fotos</h2>
        @if ($product->images->count())
          <div class="imgs" style="margin-bottom:14px">
            @foreach ($product->images as $img)
              <div class="im">
                <img src="{{ $img->url }}" alt="">
                <select name="image_color[{{ $img->id }}]" aria-label="Color de la foto">
                  <option value="">Todos los colores</option>
                  @foreach ($colors as $c)<option value="{{ $c->id }}" @selected($img->color_id === $c->id)>{{ $c->name }}</option>@endforeach
                </select>
                <div style="display:flex;gap:6px">
                  <input type="number" name="image_order[{{ $img->id }}]" value="{{ $img->sort_order }}" title="Orden (la más baja es la principal)" aria-label="Orden">
                  <button type="button" class="btn sm soft" style="height:32px;padding:0 10px" data-delete="{{ route('admin.products.images.destroy', [$product, $img]) }}" data-confirm="¿Borrar esta foto?" aria-label="Borrar foto"><svg class="i" style="width:15px"><use href="#i-trash"/></svg></button>
                </div>
              </div>
            @endforeach
          </div>
        @endif
        <label class="drop" data-drop>
          <input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple data-photos>
          <svg class="i" style="width:28px;height:28px"><use href="#i-plus"/></svg>
          <b>Agregar fotos</b>
          <span>Arrastralas acá o tocá para elegir. JPG, PNG o WEBP, hasta 6 MB. Ideal: vertical 4:5 (por ejemplo 1200 × 1500).</span>
        </label>
        <div class="imgs" data-new-photos style="margin-top:12px"></div>
        <p class="help" style="margin-top:8px">Si una foto es de un color, elegilo: en la tienda se muestra cuando el cliente toca ese color. La foto con el número de orden más bajo es la principal; la segunda aparece al pasar el mouse.</p>
      </div>
    </div>

    <div class="stack" style="position:sticky;top:16px">
      <div class="box">
        <h2>Publicación</h2>
        <label class="switch">Visible en la tienda <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active))></label>
        <label class="switch">Destacado en el inicio <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured))></label>
        <label class="switch">Marcar como «Nuevo» <input type="checkbox" name="is_new" value="1" @checked(old('is_new', $product->is_new))></label>
      </div>
      <div class="box">
        <h2>Organización</h2>
        <div class="field"><label>Categoría</label>
          <select class="select input" name="category_id" style="border-radius:14px"><option value="">Sin categoría</option>@foreach($categories as $c)<option value="{{ $c->id }}" @selected(old('category_id', $product->category_id) == $c->id)>{{ $c->depth ? '— ' : '' }}{{ $c->name }}</option>@endforeach</select>
        </div>
        <div class="field" style="margin-top:12px"><label>Código (SKU) <span class="help">(opcional)</span></label>
          <div class="sku-row"><input class="input" name="sku" value="{{ old('sku', $product->sku) }}" maxlength="60" autocomplete="off" data-sku data-sku-url="{{ route('admin.products.sku') }}" @if(! $product->exists) data-sku-auto @endif><button type="button" class="btn ghost sm" data-sku-suggest>Sugerir</button></div>
          <span class="help">Código interno para identificar la prenda; el cliente no lo ve. Con él la encontrás rápido en <b>Vender</b> y en esta lista de productos. «Sugerir» arma uno libre según la categoría (ej.: REM-OVE30-001). No se puede repetir.</span>
        </div>
        <div class="field" style="margin-top:12px"><label>Dirección web <span class="help">(opcional)</span></label><input class="input" name="slug" value="{{ old('slug', $product->slug) }}" placeholder="se arma sola con el nombre"></div>
      </div>
      <button class="btn accent block" style="height:52px">Guardar producto</button>
      @if ($product->exists)
        <button type="button" class="btn ghost block" data-delete="{{ route('admin.products.destroy', $product) }}" data-confirm="¿Borrar «{{ $product->name }}»? Si ya tiene ventas, se oculta en lugar de borrarse.">Borrar producto</button>
      @endif
    </div>
  </div>
</form>
@endsection
