@extends('layouts.admin')
@section('title', 'Slider del inicio')
@section('actions')<a class="btn sm" href="{{ route('admin.slides.create') }}"><svg class="i" style="width:16px"><use href="#i-plus"/></svg> Nuevo slide</a>@endsection
@section('content')
<p class="help" style="margin-bottom:14px">Arrastrá desde los puntitos para cambiar el orden. Los slides apagados no se muestran.</p>
<div style="display:grid;gap:10px" data-sortable="{{ route('admin.slides.reorder') }}">
  @foreach ($slides as $s)
    <div class="slide-row" draggable="true" data-id="{{ $s->id }}">
      <span class="handle" title="Arrastrar"><svg class="i"><use href="#i-menu"/></svg></span>
      <div class="mini" style="background:{{ $s->bg_color }}">@if($s->main_image)<img src="{{ upload_url($s->main_image) }}" alt="">@endif<span style="position:absolute;left:12px;bottom:10px;width:16px;height:16px;border-radius:9px;background:{{ $s->accent_color }}"></span></div>
      <div><b style="font-family:var(--display);font-size:20px">{{ $s->title }}</b><br><span class="help">{{ $s->product?->name ?? 'Sin producto' }} · {{ $s->tagline }}</span><br>@if($s->is_active)<span class="pill ok">Visible</span>@else<span class="pill">Apagado</span>@endif</div>
      <div style="display:flex;gap:6px"><a class="btn sm soft" href="{{ route('admin.slides.edit', $s) }}">Editar</a><button type="button" class="btn sm ghost" data-delete="{{ route('admin.slides.destroy', $s) }}" data-confirm="¿Borrar este slide?">Borrar</button></div>
    </div>
  @endforeach
</div>
@if ($slides->isEmpty())<div class="box help">No hay slides. Creá el primero.</div>@endif
@endsection
