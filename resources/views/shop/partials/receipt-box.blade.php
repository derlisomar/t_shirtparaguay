{{-- Comprobante de pago del pedido: ver el que ya subió o adjuntar uno --}}
@if ($order->status !== 'cancelado' && ($order->payment_method === 'transferencia' || $order->hasReceipt()))
  <div style="display:grid;gap:10px">
    @error('receipt')<div class="alert err" style="margin:0">{{ $message }}</div>@enderror
    @if ($order->hasReceipt())
      <div class="receipt-ok"><svg class="i" style="width:18px"><use href="#i-check"/></svg> Recibimos tu comprobante el {{ $order->receipt_uploaded_at?->format('d/m/Y H:i') }}. <a href="{{ route('account.receipt', $order) }}" target="_blank" style="text-decoration:underline;margin-left:auto">Ver</a></div>
    @endif
    @if ($order->payment_status !== 'pagado')
      <form method="post" action="{{ route('account.receipt.store', $order) }}" enctype="multipart/form-data" style="display:grid;gap:10px">
        @csrf
        <label class="file-drop" data-file-drop style="position:relative">
          <input type="file" name="receipt" accept="image/jpeg,image/png,image/webp,application/pdf" required>
          <b>{{ $order->hasReceipt() ? 'Cambiar el comprobante' : 'Adjuntar comprobante de pago' }}</b>
          <span>Foto o PDF de la transferencia, hasta 5 MB.</span>
        </label>
        <button class="btn">{{ $order->hasReceipt() ? 'Subir el nuevo' : 'Enviar comprobante' }}</button>
      </form>
    @endif
  </div>
@endif
