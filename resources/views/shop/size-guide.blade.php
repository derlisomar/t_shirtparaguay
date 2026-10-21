@extends('layouts.shop')
@section('title', 'Guía de talles · '.setting('store_name', 't_shirtparaguay'))
@section('content')
<div class="wrap" style="max-width:760px">
  <div class="page-head"></div>
  <div class="panel-card">@include('shop.partials.size-table')</div>
</div>
@endsection
