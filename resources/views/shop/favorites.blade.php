@extends('layouts.shop')
@section('title', 'Favoritos · '.setting('store_name', 't_shirtparaguay'))
@section('content')
<div class="wrap">
  <div class="page-head"><h1>Tus favoritos</h1></div>
  @if ($products->count())
    <div class="grid">@foreach ($products as $product)@include('shop.partials.card')@endforeach</div>
  @else
    <div class="empty"><h3>Todavía no guardaste nada</h3><p>Tocá el corazón de una prenda para guardarla acá.</p><a class="btn" href="{{ route('catalog') }}">Ver la tienda</a></div>
  @endif
</div>
@endsection
