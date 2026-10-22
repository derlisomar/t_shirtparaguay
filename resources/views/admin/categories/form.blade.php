@extends('layouts.admin')
@section('title', $category->exists ? 'Editar categoría' : 'Nueva categoría')
@section('content')
<form method="post" enctype="multipart/form-data" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" class="box" style="max-width:640px;display:grid;gap:14px">
  @csrf @if($category->exists) @method('PUT') @endif
  @if ($parents->count())
    <div class="field"><label>Va dentro de <span class="help">(opcional)</span></label>
      <select class="select input" name="parent_id" style="border-radius:14px">
        <option value="">Ninguna: es una categoría principal del menú</option>
        @foreach ($parents as $p)<option value="{{ $p->id }}" @selected(old('parent_id', $category->parent_id) == $p->id)>{{ $p->name }}</option>@endforeach
      </select>
      <span class="help">Elegí una categoría si esta es una <b>línea</b> adentro de ella. Ej.: «Clásicas hilo 40.1 peinado» va dentro de «Remeras».</span>
    </div>
  @endif
  <div class="field"><label>Nombre</label><input class="input" name="name" value="{{ old('name', $category->name) }}" required></div>
  <div class="field"><label>Descripción corta</label><input class="input" name="description" value="{{ old('description', $category->description) }}"></div>
  <div class="field"><label>Orden en el menú</label><input class="input" type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}"></div>
  <div class="field"><label>Foto de portada <span class="help">(opcional; si no hay, se usa la foto de un producto)</span></label><input type="file" name="image" accept="image/*">
    @if($category->image)<div class="file-cur"><img src="{{ upload_url($category->image) }}" alt=""></div>@endif</div>
  <label class="switch">Visible en la tienda <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active))></label>
  <div style="display:flex;gap:8px"><button class="btn accent">Guardar</button><a class="btn ghost" href="{{ route('admin.categories.index') }}">Volver</a></div>
</form>
@endsection
