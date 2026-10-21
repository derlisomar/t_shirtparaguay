@extends('layouts.admin')
@section('title', 'Vender en el local')
@section('content')
<div class="pos" data-pos data-search="{{ route('admin.pos.search') }}" data-customers="{{ route('admin.pos.customers') }}" data-store="{{ route('admin.pos.store') }}" data-currency="{{ setting('currency', 'Gs.') }}">
  <div>
    <div class="filters-bar">
      <input class="input" type="search" placeholder="Buscar prenda o código…" data-pos-q autofocus>
      <div class="chips">
        <button type="button" class="chip on" data-pos-cat="">Todo</button>
        @foreach ($categories as $c)<button type="button" class="chip" data-pos-cat="{{ $c->id }}">{{ $c->name }}</button>@endforeach
      </div>
    </div>
    <div class="pos-grid" data-pos-grid><p class="help">Cargando…</p></div>
  </div>

  <aside class="ticket-box">
    <h2 style="font-size:22px">Venta actual</h2>
    <div data-pos-lines><p class="help">Tocá una prenda para agregarla.</p></div>

    <div class="field"><label>Cliente <span class="help">(opcional)</span></label>
      <input class="input" style="height:44px" placeholder="Buscar cliente registrado o escribir un nombre" data-pos-customer autocomplete="off">
      <div class="cust-res" data-pos-cust-res></div>
      <div class="form-grid" style="gap:8px;grid-template-columns:1fr 1fr">
        <input class="input" style="height:44px" placeholder="Celular" data-pos-phone>
        <input class="input" style="height:44px" placeholder="CI o RUC" data-pos-doc>
      </div>
    </div>

    <label class="check"><input type="checkbox" data-pos-wholesale> Precio mayorista para toda la venta</label>

    <div class="field"><label>Forma de pago</label>
      <div class="seg">
        @foreach ($methods as $k => $m)<label><input type="radio" name="pos_pay" value="{{ $k }}" @checked($loop->first)><span>{{ str_replace(' al recibir', '', $m['label']) }}</span></label>@endforeach
      </div>
    </div>

    <div class="form-grid" style="gap:8px;grid-template-columns:1fr 1fr">
      <div class="field"><label>Descuento (Gs.)</label><input class="input" style="height:44px" inputmode="numeric" data-money data-pos-discount placeholder="0"></div>
      <div class="field" data-cash-box><label>Paga con</label><input class="input" style="height:44px" inputmode="numeric" data-money data-pos-cash placeholder="0"></div>
    </div>

    <div style="display:grid;gap:6px">
      <div class="sum-row"><span>Subtotal</span><span data-pos-sub>0</span></div>
      <div class="sum-row"><span>Descuento</span><span data-pos-disc>0</span></div>
      <div class="sum-row total"><span>Total</span><span data-pos-total>0</span></div>
      <div class="sum-row" data-change-row hidden><span>Vuelto</span><b data-pos-change>0</b></div>
    </div>
    <div class="alert err" style="margin:0" data-pos-limit hidden></div>
    <button class="btn accent block" style="height:54px" data-pos-confirm disabled>Cobrar</button>
    <button class="btn ghost block sm" data-pos-clear>Vaciar</button>
  </aside>
</div>

<div class="modal" id="pos-variant" aria-hidden="true" role="dialog">
  <div class="scrim" data-close></div>
  <div class="box">
    <button class="icon-btn close" data-close aria-label="Cerrar"><svg class="i"><use href="#i-close"/></svg></button>
    <div data-variant-body></div>
  </div>
</div>
<div class="modal" id="pos-done" aria-hidden="true" role="dialog">
  <div class="scrim" data-close></div>
  <div class="box" style="text-align:center;display:grid;gap:14px;justify-items:center">
    <div style="width:64px;height:64px;border-radius:99px;background:var(--ok-soft);color:var(--ok);display:grid;place-items:center"><svg class="i" style="width:30px;height:30px"><use href="#i-check"/></svg></div>
    <h2 style="font-size:28px">Venta registrada</h2>
    <p data-done-text></p>
    <div style="display:flex;gap:8px;flex-wrap:wrap;justify-content:center"><a class="btn" data-done-ticket target="_blank">Imprimir comprobante</a><button class="btn soft" data-close>Nueva venta</button></div>
  </div>
</div>
@endsection
