@extends('layouts.admin')
@section('title', 'Pedido '.$order->number)
@section('actions')
  <a class="btn sm soft" href="{{ route('admin.orders.ticket', [$order, 'formato' => 'a4']) }}" target="_blank"><svg class="i" style="width:16px"><use href="#i-print"/></svg> Imprimir comprobante</a>
  <a class="btn sm ghost" href="{{ route('admin.orders.ticket', $order) }}" target="_blank">Ticket</a>
@endsection
@section('content')
<div class="form-cols">
  <div class="stack">
    <div class="box">
      @php($editable = $order->canEditItems())
      <h2>Prendas @if ($editable)<span class="help" style="font:500 13px var(--text)">Podés cambiar cantidades o quitar prendas</span>@endif</h2>
      @if ($editable)
        <form method="post" action="{{ route('admin.orders.items', $order) }}" data-items-form>
        @csrf
        @method('PUT')
      @endif
      @foreach ($order->items as $it)
        <div class="line item-line {{ $it->isRemoved() ? 'removed' : '' }}" style="grid-template-columns:56px 1fr auto;align-items:center" data-item>
          <img src="{{ $it->product ? $it->product->mainImageUrl() : asset('img/sin-foto.svg') }}" alt="" style="width:56px">
          <div>
            <h4>{{ $it->product_name }}</h4>
            <div class="meta">Talle {{ $it->size }}{{ $it->color ? ' · '.$it->color : '' }} · {{ money($it->price) }} c/u @if($it->is_wholesale) · <b style="color:var(--ok)">mayorista</b>@endif @if(! $order->stock_applied && $order->status !== 'cancelado' && $it->variant) · <span style="color:{{ $it->variant->stock < $it->quantity ? 'var(--danger)' : 'var(--ink-3)' }}">hay {{ $it->variant->stock }} en stock</span>@endif</div>
            @if ($it->wasAdjusted())<div class="adj-note">{{ $it->isRemoved() ? 'Quitada del pedido' : 'Cambiada' }} · el cliente pidió {{ $it->original_quantity }}</div>@endif
          </div>
          <div style="display:grid;justify-items:end;gap:6px">
            <b data-line-total>{{ money($it->total) }}</b>
            @if ($editable)
              <div class="qty-edit">
                <button type="button" data-q="-1" aria-label="Uno menos">−</button>
                <input type="number" name="qty[{{ $it->id }}]" value="{{ $it->quantity }}" min="0" max="999" data-price="{{ $it->price }}" data-orig="{{ $it->quantity }}" aria-label="Cantidad de {{ $it->product_name }}">
                <button type="button" data-q="1" aria-label="Uno más">+</button>
              </div>
              <button type="button" class="link-btn" data-remove>{{ $it->quantity ? 'Quitar' : 'Volver a agregar' }}</button>
            @else
              <span class="help">× {{ $it->quantity }}</span>
            @endif
          </div>
        </div>
      @endforeach
      @if ($editable)
        <div class="items-save" data-items-save hidden>
          <span>Nuevo subtotal: <b data-new-sub></b></span>
          <div style="display:flex;gap:8px"><button type="button" class="btn sm ghost" data-items-reset>Deshacer</button><button class="btn sm accent">Guardar cambios</button></div>
        </div>
        <p class="help" style="margin-top:10px">{{ $order->stock_applied ? 'El stock de este pedido ya se descontó: si bajás una cantidad, vuelve al stock; si la subís, se descuenta.' : 'El pedido todavía no descontó stock; se descuenta al procesarlo con las cantidades que dejes acá.' }}</p>
        </form>
      @endif
      <div style="display:grid;gap:8px;margin-top:14px">
        <div class="sum-row"><span>Subtotal</span><span>{{ money($order->subtotal) }}</span></div>
        @if ($order->discount)<div class="sum-row"><span>Descuento</span><span>-{{ money($order->discount) }}</span></div>@endif
        <div class="sum-row"><span>Envío</span><span>{{ $order->shipping ? money($order->shipping) : 'Sin costo' }}</span></div>
        <div class="sum-row total"><span>Total</span><span>{{ money($order->total) }}</span></div>
        @if ($order->cash_received)<div class="sum-row help"><span>Pagó con {{ money($order->cash_received) }}</span><span>Vuelto {{ money(max(0, $order->cash_received - $order->total)) }}</span></div>@endif
      </div>
    </div>
    <div class="box">
      <h2>Pagos registrados</h2>
      @forelse ($order->payments as $p)
        <div class="sum-row" style="padding:8px 0;box-shadow:0 1px 0 var(--line)"><span>{{ $p->created_at->format('d/m H:i') }} · {{ ucfirst($p->gateway) }} · {{ $p->method }}{{ $p->transaction_id ? ' · '.$p->transaction_id : '' }}</span><span>{{ money($p->amount) }} <span class="pill {{ $p->status === 'pagado' ? 'ok' : 'warn' }}">{{ ucfirst($p->status) }}</span></span></div>
      @empty <p class="help">Sin pagos registrados.</p> @endforelse
    </div>
  </div>
  <div class="stack">
    @if ($order->status === 'pendiente')
      @php($short = $order->items->filter(fn ($it) => $it->variant && $it->variant->stock < $it->quantity))
      <form method="post" action="{{ route('admin.orders.process', $order) }}" class="box process-box" style="display:grid;gap:12px">
        @csrf
        <h2 style="margin:0">Procesar pedido</h2>
        <p class="help">Al procesarlo se descuenta el stock de cada prenda y el pedido pasa a «Procesado». Mientras siga pendiente, el stock no se toca.</p>
        @if ($short->isNotEmpty())
          <div class="alert err" style="margin:0">No alcanza el stock de: {{ $short->map(fn ($it) => $it->product_name.' ('.$it->size.($it->color ? ' · '.$it->color : '').'): hay '.$it->variant->stock)->join(', ') }}. Cargá stock antes de procesarlo.</div>
        @endif
        @if ($order->payment_status !== 'pagado')
          <label class="check"><input type="checkbox" name="paid" value="1" @checked($order->hasReceipt())> Marcar el pago como recibido</label>
        @endif
        <button class="btn accent" @disabled($short->isNotEmpty())>Procesar pedido</button>
      </form>
    @endif
    @if (in_array($order->status, ['procesado', 'enviado', 'entregado']))
      <form method="post" action="{{ route('admin.orders.finish', $order) }}" class="box process-box" style="display:grid;gap:12px" data-confirm="¿Confirmás que el pedido {{ $order->number }} se entregó y el pago está conforme?" data-ok="Sí, finalizar">
        @csrf
        <h2 style="margin:0">Finalizar pedido</h2>
        <p class="help">Cuando el cliente ya recibió el pedido y el pago está conforme, finalizalo. Pasa a la lista de <b>Finalizados</b>{{ $order->payment_status !== 'pagado' ? ' y el pago se marca como recibido' : '' }}.</p>
        <button class="btn accent">Confirmar entrega y pago</button>
      </form>
    @elseif ($order->status === 'finalizado')
      <div class="box"><div class="receipt-ok"><svg class="i" style="width:18px"><use href="#i-check"/></svg> Pedido finalizado{{ $order->finished_at ? ' el '.$order->finished_at->format('d/m/Y H:i') : '' }}</div></div>
    @endif
    @if ($order->hasReceipt())
      <div class="box">
        <h2>Comprobante de pago <a href="{{ route('admin.orders.receipt', $order) }}" target="_blank">Abrir</a></h2>
        @if ($order->receiptIsPdf())
          <a class="btn soft block" href="{{ route('admin.orders.receipt', $order) }}" target="_blank">Ver PDF del comprobante</a>
        @else
          <a href="{{ route('admin.orders.receipt', $order) }}" target="_blank"><img src="{{ route('admin.orders.receipt', $order) }}" alt="Comprobante del pedido {{ $order->number }}" style="width:100%;max-height:420px;object-fit:contain;border-radius:12px;background:var(--surface-2)"></a>
        @endif
        <p class="help" style="margin-top:8px">Subido el {{ $order->receipt_uploaded_at?->format('d/m/Y H:i') }}</p>
      </div>
    @elseif ($order->channel === 'web' && $order->payment_method === 'transferencia' && $order->payment_status !== 'pagado')
      <div class="box"><h2>Comprobante de pago</h2><p class="help">El cliente todavía no adjuntó el comprobante.</p></div>
    @endif
    <form method="post" action="{{ route('admin.orders.update', $order) }}" class="box" style="display:grid;gap:12px">
      @csrf @method('PUT')
      <h2 style="margin:0">Estado</h2>
      <div class="field"><label>Estado del pedido</label><select class="select input" name="status" style="border-radius:14px">@foreach(\App\Models\Order::STATUSES as $k => $l)<option value="{{ $k }}" @selected($order->status === $k)>{{ $l }}</option>@endforeach</select></div>
      <div class="field"><label>Pago</label><select class="select input" name="payment_status" style="border-radius:14px">@foreach(\App\Models\Order::PAYMENT_STATUSES as $k => $l)<option value="{{ $k }}" @selected($order->payment_status === $k)>{{ $l }}</option>@endforeach</select></div>
      <p class="help">
        Stock: <b>{{ $order->stock_applied ? 'ya descontado' : 'sin descontar' }}</b>@if($order->processed_at) (procesado el {{ $order->processed_at->format('d/m/Y H:i') }})@endif.
        Al pasarlo a Procesado, Enviado o Entregado se descuenta; si lo cancelás o lo volvés a Pendiente, el stock vuelve.
      </p>
      <button class="btn">Guardar</button>
    </form>
    <div class="box">
      <h2>Cliente</h2>
      <p><b>{{ $order->customer_name }}</b></p>
      <p class="help">{{ $order->customer_email }}<br>{{ $order->customer_phone }}@if($order->customer_document)<br>CI/RUC {{ $order->customer_document }}@endif</p>
      @if ($order->customer_phone)
        <a class="btn sm" style="margin-top:12px;background:#25d366;color:#fff" target="_blank" rel="noopener" href="https://wa.me/{{ preg_replace('/^0/', '595', preg_replace('/\D/', '', $order->customer_phone)) }}?text={{ rawurlencode('Hola '.$order->customer_name.'! Te escribimos de '.setting('store_name').' por tu pedido '.$order->number.'.') }}"><svg class="i" style="width:16px"><use href="#i-wa"/></svg> Escribirle</a>
      @endif
      <h2 style="margin-top:20px">Entrega</h2>
      <p>{{ $order->delivery_method === 'retiro' ? 'Retira en el local' : 'Envío a domicilio' }}</p>
      @if ($order->address)<p class="help">{{ $order->address }}, {{ $order->city }}</p>@endif
      @if ($order->notes)<p class="help" style="margin-top:8px">Nota: {{ $order->notes }}</p>@endif
      <p class="help" style="margin-top:12px">{{ $order->channel === 'local' ? 'Venta en el local'.($order->seller ? ' por '.$order->seller->name : '') : 'Pedido de la tienda online' }} · {{ $order->paymentLabel() }}</p>
    </div>
  </div>
</div>
@endsection
