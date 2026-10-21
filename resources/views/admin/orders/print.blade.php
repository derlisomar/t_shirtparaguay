@php
  $n = fn ($v) => number_format($v, 0, ',', '.');
  $cur = setting('currency', 'Gs.');
  $logo = setting('logo');
@endphp
<!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Comprobante {{ $order->number }}</title>
<style>
  @page { size: A4; margin: 14mm; }
  * { box-sizing: border-box; }
  body { margin: 0; font: 13px/1.45 Arial, Helvetica, sans-serif; color: #151413; background: #f4f3f0; }
  .sheet { width: 210mm; min-height: 297mm; margin: 16px auto; padding: 14mm; background: #fff; box-shadow: 0 4px 24px rgba(0,0,0,.08); }
  .head { display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; padding-bottom: 14px; border-bottom: 3px solid #ff5a1f; }
  .brand { font: 800 26px/1 Arial, sans-serif; letter-spacing: -.02em; }
  .brand .py { display: inline-block; font-size: 11px; padding: 3px 6px; border-radius: 99px; background: #ff5a1f; color: #fff; vertical-align: top; margin-left: 2px; }
  .brand img { max-height: 54px; max-width: 220px; }
  .store { color: #5b5853; font-size: 12px; margin-top: 8px; }
  .doc { text-align: right; }
  .doc h1 { margin: 0; font-size: 20px; text-transform: uppercase; letter-spacing: .04em; }
  .doc .num { font-size: 18px; font-weight: 700; margin-top: 4px; }
  .doc .meta { color: #5b5853; font-size: 12px; margin-top: 6px; }
  .tag { display: inline-block; margin-top: 8px; padding: 4px 10px; border-radius: 99px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; background: #ececea; }
  .tag.ok { background: #dcf1e4; color: #17683a; } .tag.warn { background: #fbecc8; color: #8a5a00; } .tag.err { background: #fbdcd6; color: #a3261a; }
  .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin: 18px 0; }
  .card { border: 1px solid #e3e1dc; border-radius: 10px; padding: 12px 14px; }
  .card h3 { margin: 0 0 6px; font-size: 11px; text-transform: uppercase; letter-spacing: .06em; color: #8a867f; }
  table { width: 100%; border-collapse: collapse; }
  th { text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: .05em; color: #8a867f; padding: 8px; border-bottom: 2px solid #151413; }
  td { padding: 9px 8px; border-bottom: 1px solid #ececea; vertical-align: top; }
  .r { text-align: right; } .c { text-align: center; }
  .small { color: #8a867f; font-size: 11.5px; }
  .totals { width: 300px; margin: 14px 0 0 auto; }
  .totals td { border: 0; padding: 4px 8px; }
  .totals .grand td { font-size: 17px; font-weight: 800; border-top: 2px solid #151413; padding-top: 8px; }
  .notes { margin-top: 16px; }
  .sign { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 60px; }
  .sign div { border-top: 1px solid #151413; padding-top: 6px; text-align: center; font-size: 12px; color: #5b5853; }
  .foot { margin-top: 30px; padding-top: 10px; border-top: 1px solid #ececea; color: #8a867f; font-size: 11px; text-align: center; }
  .bar { position: sticky; top: 0; display: flex; justify-content: center; gap: 8px; padding: 10px; background: #151413; }
  .bar button, .bar a { font: 600 14px Arial, sans-serif; padding: 9px 18px; border-radius: 99px; border: 0; cursor: pointer; text-decoration: none; background: #fff; color: #151413; }
  .bar button { background: #ff5a1f; color: #fff; }
  @media print { body { background: #fff; } .bar { display: none; } .sheet { width: auto; min-height: 0; margin: 0; padding: 0; box-shadow: none; } }
  @media (max-width: 800px) { .sheet { width: auto; margin: 0; padding: 18px; } .grid { grid-template-columns: 1fr; } .totals { width: 100%; } }
</style></head>
<body>
<div class="bar"><button onclick="window.print()">Imprimir</button><a href="{{ route('admin.orders.ticket', $order) }}">Ver como ticket</a></div>
<div class="sheet">
  <div class="head">
    <div>
      <div class="brand">@if ($logo)<img src="{{ upload_url($logo) }}" alt="{{ setting('store_name') }}">@else t_shirt<span class="py">PY</span>@endif</div>
      <div class="store">
        {{ setting('store_name', 't_shirtparaguay') }}<br>
        {{ setting('address') }}
        @if (setting('whatsapp'))<br>WhatsApp +{{ setting('whatsapp') }}@endif
        @if (setting('email')) · {{ setting('email') }}@endif
      </div>
    </div>
    <div class="doc">
      <h1>Comprobante de pedido</h1>
      <div class="num">{{ $order->number }}</div>
      <div class="meta">
        Fecha: {{ $order->created_at->format('d/m/Y H:i') }}
        @if ($order->finished_at)<br>Finalizado: {{ $order->finished_at->format('d/m/Y H:i') }}@endif
      </div>
      <span class="tag {{ ['finalizado' => 'ok', 'entregado' => 'ok', 'cancelado' => 'err', 'pendiente' => 'warn'][$order->status] ?? '' }}">{{ $order->statusLabel() }}</span>
      <span class="tag {{ $order->payment_status === 'pagado' ? 'ok' : 'warn' }}">{{ $order->payment_status === 'pagado' ? 'Pagado' : 'Pago '.strtolower($order->paymentStatusLabel()) }}</span>
    </div>
  </div>

  <div class="grid">
    <div class="card">
      <h3>Cliente</h3>
      <b>{{ $order->customer_name }}</b>
      @if ($order->customer_document)<br>CI/RUC: {{ $order->customer_document }}@endif
      @if ($order->customer_phone)<br>Cel.: {{ $order->customer_phone }}@endif
      @if ($order->customer_email)<br>{{ $order->customer_email }}@endif
    </div>
    <div class="card">
      <h3>Entrega y pago</h3>
      {{ $order->delivery_method === 'retiro' ? ($order->channel === 'local' ? 'Venta en el local' : 'Retira en el local') : 'Envío a domicilio' }}
      @if ($order->address)<br>{{ $order->address }}{{ $order->city ? ', '.$order->city : '' }}@endif
      <br>Forma de pago: {{ $order->paymentLabel() }}
      @if ($order->seller)<br><span class="small">Atendió: {{ $order->seller->name }}</span>@endif
    </div>
  </div>

  <table>
    <thead><tr><th>Producto</th><th>Talle / color</th><th class="c">Cant.</th><th class="r">Precio unit.</th><th class="r">Subtotal</th></tr></thead>
    <tbody>
      @foreach ($order->items->where('quantity', '>', 0) as $it)
        <tr>
          <td>{{ $it->product_name }}@if ($it->is_wholesale)<br><span class="small">Precio mayorista</span>@endif @if ($it->wasAdjusted())<br><span class="small">Pidió {{ $it->original_quantity }}</span>@endif</td>
          <td>{{ $it->size }}{{ $it->color ? ' · '.$it->color : '' }}</td>
          <td class="c">{{ $it->quantity }}</td>
          <td class="r">{{ $n($it->price) }}</td>
          <td class="r">{{ $n($it->total) }}</td>
        </tr>
      @endforeach
      @foreach ($order->items->where('quantity', 0) as $it)
        <tr class="small"><td colspan="5">No incluido (sin stock): {{ $it->product_name }} · {{ $it->size }}{{ $it->color ? ' · '.$it->color : '' }} · pidió {{ $it->original_quantity }}</td></tr>
      @endforeach
    </tbody>
  </table>

  <table class="totals">
    <tr><td>Subtotal</td><td class="r">{{ $cur }} {{ $n($order->subtotal) }}</td></tr>
    @if ($order->discount)<tr><td>Descuento</td><td class="r">-{{ $cur }} {{ $n($order->discount) }}</td></tr>@endif
    <tr><td>Envío</td><td class="r">{{ $order->shipping ? $cur.' '.$n($order->shipping) : 'Sin costo' }}</td></tr>
    <tr class="grand"><td>Total</td><td class="r">{{ $cur }} {{ $n($order->total) }}</td></tr>
    @if ($order->cash_received)<tr><td>Recibido</td><td class="r">{{ $cur }} {{ $n($order->cash_received) }}</td></tr><tr><td>Vuelto</td><td class="r">{{ $cur }} {{ $n(max(0, $order->cash_received - $order->total)) }}</td></tr>@endif
  </table>

  @if ($order->notes)<div class="notes card"><h3>Notas</h3>{{ $order->notes }}</div>@endif

  <div class="sign"><div>Entregado por</div><div>Recibí conforme (firma y aclaración)</div></div>

  <div class="foot">¡Gracias por tu compra! Cambios dentro de los 30 días presentando este comprobante.<br>Comprobante interno, no válido como factura.</div>
</div>
</body></html>
