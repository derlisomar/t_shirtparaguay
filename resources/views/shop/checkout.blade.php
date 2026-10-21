@extends('layouts.shop')
@section('title', 'Finalizar compra · '.setting('store_name', 't_shirtparaguay'))
@section('content')
<div class="wrap">
  <div class="page-head"><h1>Finalizar compra</h1></div>
  @if ($errors->any())<div class="alert err">{{ $errors->first() }}</div>@endif
  @if ($limit && $subtotal > $limit)
    <div class="alert err">Tu límite por compra es {{ money($limit) }} y tu carrito suma {{ money($subtotal) }}. Sacá algún producto para poder confirmar, o escribinos por WhatsApp.</div>
  @endif
  <form method="post" action="{{ route('checkout.store') }}" class="two-col" enctype="multipart/form-data">
    @csrf
    <div style="display:grid;gap:20px">
      <div class="panel-card">
        <h2>Tus datos</h2>
        <div class="form-grid">
          <div class="field full"><label for="n">Nombre y apellido</label><input class="input" id="n" name="customer_name" value="{{ old('customer_name', $user->name) }}" required autocomplete="name"></div>
          <div class="field"><label for="ph">Celular</label><input class="input" id="ph" name="customer_phone" value="{{ old('customer_phone', $user->phone) }}" required inputmode="tel" autocomplete="tel" placeholder="0981 123 456"></div>
          <div class="field"><label for="doc">CI o RUC <span style="color:var(--ink-3);font-weight:400">(para la factura)</span></label><input class="input" id="doc" name="customer_document" value="{{ old('customer_document', $user->document) }}"></div>
        </div>
      </div>

      <div class="panel-card">
        <h2>Entrega</h2>
        <div class="opts">
          <label class="opt-card"><input type="radio" name="delivery_method" value="envio" @checked(old('delivery_method', 'envio') === 'envio')><span class="dot"></span><div><b>Envío a domicilio</b><span>24 a 48 h hábiles en Asunción y Central. {{ $shippingCost ? money($shippingCost) : 'Gratis' }}.</span></div></label>
          <label class="opt-card"><input type="radio" name="delivery_method" value="retiro" @checked(old('delivery_method') === 'retiro')><span class="dot"></span><div><b>Retiro en el local</b><span>Sin costo. {{ setting('pickup_address', setting('address')) }}</span></div></label>
        </div>
        <div class="form-grid" data-address-box style="margin-top:18px">
          <div class="field full"><label for="ad">Dirección</label><input class="input" id="ad" name="address" value="{{ old('address', $user->address) }}" autocomplete="street-address" placeholder="Calle, número y referencia"></div>
          <div class="field"><label for="ci">Ciudad</label><input class="input" id="ci" name="city" value="{{ old('city', $user->city) }}" autocomplete="address-level2"></div>
          <label class="check" style="align-self:end;padding-bottom:14px"><input type="checkbox" name="save_data" value="1" checked> Guardar para la próxima</label>
        </div>
        <div class="field" style="margin-top:16px"><label for="no">Notas <span style="color:var(--ink-3);font-weight:400">(opcional)</span></label><textarea class="textarea" id="no" name="notes" placeholder="Horario de entrega, timbre, etc.">{{ old('notes') }}</textarea></div>
      </div>

      <div class="panel-card">
        <h2>Pago</h2>
        <div class="opts">
          @foreach ($methods as $key => $m)
            <label class="opt-card">
              <input type="radio" name="payment_method" value="{{ $key }}" @checked(old('payment_method', 'transferencia') === $key) @disabled(! $m['available'])>
              <span class="dot"></span>
              <div>
                <b>{{ $m['label'] }}</b>
                <span>
                  @switch($key)
                    @case('transferencia') Te mostramos los datos de la cuenta al confirmar. @break
                    @case('efectivo') Pagás cuando recibís o retirás tu pedido. @break
                    @case('tarjeta') Visa, Mastercard y tarjetas locales. @break
                  @endswitch
                </span>
              </div>
              @unless ($m['available'])<span class="pill soon">Próximamente</span>@endunless
            </label>
          @endforeach
        </div>
        <div data-receipt-box style="margin-top:18px;display:grid;gap:8px">
          <label class="file-drop" data-file-drop style="position:relative">
            <input type="file" name="receipt" accept="image/jpeg,image/png,image/webp,application/pdf">
            <b>Adjuntar comprobante de pago</b>
            <span>Opcional. Si ya transferiste, subí la foto o el PDF (hasta 5 MB). También lo podés subir después desde Mi cuenta.</span>
          </label>
        </div>
      </div>
    </div>

    <div class="panel-card summary">
      <h2>Tu pedido</h2>
      @foreach ($lines as $l)
        <div class="line">
          <img src="{{ $l->image }}" alt="">
          <div><h4>{{ $l->product->name }}</h4><div class="meta">{{ $l->variant->size?->name }}@if($l->variant->color) · {{ $l->variant->color->name }}@endif · x{{ $l->qty }}</div>@if($l->wholesale)<div class="ws-note on">Precio mayorista</div>@endif</div>
          <b>{{ money($l->total) }}</b>
        </div>
      @endforeach
      <div style="display:grid;gap:10px;margin-top:16px">
        <div class="sum-row"><span>Subtotal</span><span>{{ money($subtotal) }}</span></div>
        <div class="sum-row"><span>Envío</span><span data-ship data-cost="{{ $shippingCost }}" data-cost-text="{{ money($shippingCost) }}">{{ $shippingCost ? money($shippingCost) : 'Gratis' }}</span></div>
        <div class="sum-row total"><span>Total</span><span data-total data-with-ship="{{ money($subtotal + $shippingCost) }}" data-no-ship="{{ money($subtotal) }}">{{ money($subtotal + $shippingCost) }}</span></div>
        <button class="btn accent block" type="submit" style="margin-top:8px">Confirmar pedido</button>
        <p style="font-size:13px;color:var(--ink-3);text-align:center">Vas a recibir el número de pedido al confirmar.</p>
      </div>
    </div>
  </form>
</div>
@endsection
