@extends('layouts.admin')
@section('title', $slide->exists ? 'Editar slide' : 'Nuevo slide')
@section('content')
<form method="post" enctype="multipart/form-data" action="{{ $slide->exists ? route('admin.slides.update', $slide) : route('admin.slides.store') }}" data-slide-form>
  @csrf @if($slide->exists) @method('PUT') @endif
  <div class="form-cols">
    <div class="stack">
      <div class="box">
        <h2>Vista previa</h2>
        <div class="slide-prev" data-prev style="background:{{ $slide->bg_color }}">
          <div style="position:absolute;inset:0;background:radial-gradient(45% 60% at 70% 55%, {{ $slide->accent_color }}66, transparent 70%)" data-prev-glow></div>
          <h3 data-prev-title>{{ $slide->title ?: 'Título del slide' }}</h3>
          <p data-prev-text>{{ $slide->text }}</p>
          <img data-prev-img src="{{ upload_url($slide->main_image) }}" alt="" @if(!$slide->main_image) hidden @endif>
        </div>
      </div>
      <div class="box">
        <h2>Textos</h2>
        <div class="form-grid">
          <div class="field full"><label>Título <span class="help">(corto, 2 a 5 palabras)</span></label><input class="input" name="title" value="{{ old('title', $slide->title) }}" required maxlength="80" data-bind="title"></div>
          <div class="field full"><label>Texto</label><textarea class="textarea" name="text" maxlength="400" data-bind="text">{{ old('text', $slide->text) }}</textarea></div>
          <div class="field full"><label>Frase de abajo <span class="help">(en letra cursiva, al centro)</span></label><input class="input" name="tagline" value="{{ old('tagline', $slide->tagline) }}" maxlength="80"></div>
          <div class="field"><label>Texto del botón</label><input class="input" name="button_text" value="{{ old('button_text', $slide->button_text) }}" maxlength="40"></div>
          <div class="field"><label>Link del botón <span class="help">(vacío = va al producto)</span></label><input class="input" name="button_link" value="{{ old('button_link', $slide->button_link) }}" placeholder="/categoria/remeras"></div>
        </div>
      </div>
      <div class="box">
        <h2>Imágenes</h2>
        <p class="help" style="margin-bottom:14px">Para que quede como en el ejemplo, usá fotos de la prenda <b>sin fondo</b> (PNG o WEBP transparente). Podés quitar el fondo gratis en remove.bg o con Canva.</p>
        @foreach (['main_image' => 'Prenda principal (grande, al centro)', 'secondary_image' => 'Prenda flotante (chica, arriba, desenfocada)', 'thumb_image' => 'Miniatura (abajo a la derecha, anuncia el siguiente)'] as $f => $label)
          <div class="field" style="margin-bottom:14px"><label>{{ $label }}</label>
            <input type="file" name="{{ $f }}" accept="image/png,image/webp,image/jpeg" @if($f === 'main_image') data-prev-file @endif>
            @if ($slide->$f)<div class="file-cur"><img src="{{ upload_url($slide->$f) }}" alt=""><label class="check"><input type="checkbox" name="remove_{{ $f }}" value="1"> Quitar</label></div>@endif
          </div>
        @endforeach
      </div>
    </div>
    <div class="stack" style="position:sticky;top:16px">
      <div class="box">
        <h2>Colores</h2>
        <div class="field"><label>Fondo</label><div class="color-in"><input type="color" name="bg_color" value="{{ old('bg_color', $slide->bg_color) }}" data-bind="bg"><span class="help">Mejor oscuro, para que el texto blanco se lea.</span></div></div>
        <div class="field" style="margin-top:12px"><label>Resplandor</label><div class="color-in"><input type="color" name="accent_color" value="{{ old('accent_color', $slide->accent_color) }}" data-bind="acc"><span class="help">El brillo detrás de la prenda.</span></div></div>
      </div>
      <div class="box">
        <h2>Producto</h2>
        <div class="field"><label>Producto que muestra <span class="help">(precio y talles)</span></label>
          <select class="select input" name="product_id" style="border-radius:14px"><option value="">Ninguno</option>@foreach($products as $p)<option value="{{ $p->id }}" @selected(old('product_id', $slide->product_id) == $p->id)>{{ $p->name }} · {{ money($p->price) }}</option>@endforeach</select>
        </div>
        <label class="switch" style="margin-top:8px">Visible <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $slide->is_active))></label>
      </div>
      <button class="btn accent block" style="height:52px">Guardar slide</button>
      <a class="btn ghost block" href="{{ route('admin.slides.index') }}">Volver</a>
    </div>
  </div>
</form>
@endsection
