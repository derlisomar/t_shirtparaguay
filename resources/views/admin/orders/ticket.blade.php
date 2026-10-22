<!doctype html>
<html lang="es"><head><meta charset="utf-8"><title>{{ $order->number }}</title>
<style>
  body { font: 13px/1.4 'Courier New', monospace; width: 72mm; margin: 0 auto; padding: 10px; color: #000; }
  h1 { font: 800 20px/1 Arial, sans-serif; text-align: center; margin: 6px 0; }
  .c { text-align: center; } .r { text-align: right; } hr { border: 0; border-top: 1px dashed #000; margin: 8px 0; }
  table { width: 100%; border-collapse: collapse; } td { vertical-align: top; padding: 2px 0; }
  .tot td { font-weight: bold; font-size: 15px; }
  @media print { button { display: none; } }
</style></head>
<body onload="window.print()">
  @if (setting('logo'))<img src="{{ upload_url(setting('logo')) }}" style="max-width:60%;display:block;margin:0 auto" alt="">@else<h1>{{ setting('store_name', 't_shirtparaguay') }}</h1>@endif
  <div class="c">{{ setting('address') }}@if(setting('whatsapp'))<br>WhatsApp +{{ setting('whatsapp') }}@endif</div>
  <hr>
  <div>Comprobante: <b>{{ $order->number }}</b><br>Fecha: {{ $order->created_at->format('d/m/Y H:i') }}<br>Cliente: {{ $order->customer_name }}@if($order->customer_document)<br>CI/RUC: {{ $order->customer_document }}@endif</div>
  <hr>
  <table>
    @foreach ($order->items->where('quantity', '>', 0) as $it)
      <tr><td colspan="2">{{ $it->product_name }} ({{ $it->size }}{{ $it->color ? ' '.$it->color : '' }})</td></tr>
      <tr><td>{{ $it->quantity }} x {{ number_format($it->price, 0, ',', '.') }}</td><td class="r">{{ number_format($it->total, 0, ',', '.') }}</td></tr>
    @endforeach
  </table>
  <hr>
  <table>
    <tr><td>Subtotal</td><td class="r">{{ number_format($order->subtotal, 0, ',', '.') }}</td></tr>
    @if($order->discount)<tr><td>Descuento</td><td class="r">-{{ number_format($order->discount, 0, ',', '.') }}</td></tr>@endif
    @if($order->shipping)<tr><td>Envío</td><td class="r">{{ number_format($order->shipping, 0, ',', '.') }}</td></tr>@endif
    <tr class="tot"><td>TOTAL {{ setting('currency', 'Gs.') }}</td><td class="r">{{ number_format($order->total, 0, ',', '.') }}</td></tr>
    <tr><td>Pago</td><td class="r">{{ $order->paymentLabel() }}</td></tr>
    @if($order->cash_received)<tr><td>Recibido</td><td class="r">{{ number_format($order->cash_received, 0, ',', '.') }}</td></tr><tr><td>Vuelto</td><td class="r">{{ number_format(max(0, $order->cash_received - $order->total), 0, ',', '.') }}</td></tr>@endif
  </table>
  <hr>
  <div class="c">¡Gracias por tu compra!<br>Cambios dentro de los 30 días con este comprobante.<br><small>Comprobante interno, no válido como factura.</small></div>
  <p class="c"><button onclick="window.print()">Imprimir</button></p>
</body></html>
