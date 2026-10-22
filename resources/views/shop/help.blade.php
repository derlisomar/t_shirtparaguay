@extends('layouts.shop')
@section('title', 'Ayuda · '.setting('store_name', 't_shirtparaguay'))
@section('content')
<div class="wrap" style="max-width:820px">
  <div class="page-head"><h1>¿Cómo comprar?</h1><p>Todo lo que necesitás saber antes de pedir.</p></div>
  <div class="acc">
    <details open id="envios"><summary>Envíos y retiros</summary><div class="body">Enviamos a Asunción y Central en 24 a 48 h hábiles por {{ money(setting('shipping_cost', 25000)) }}. @if (show_free_shipping())Desde {{ money(setting('free_shipping_from', 400000)) }} el envío es gratis.@endif Al interior despachamos por transportadora (el costo lo paga quien recibe).
También podés retirar sin costo en: {{ setting('pickup_address', setting('address')) }}</div></details>
    <details id="pagos"><summary>Formas de pago</summary><div class="body">Transferencia bancaria (te mostramos los datos al terminar la compra) o efectivo al recibir. Muy pronto vas a poder pagar con tarjeta de crédito o débito directamente en la página.</div></details>
    <details id="cambios"><summary>Cambios</summary><div class="body">Tenés 30 días para cambiar de talle o color. La prenda tiene que estar sin uso y con su etiqueta. Escribinos por WhatsApp y coordinamos.</div></details>
    <details><summary>¿Necesito una cuenta?</summary><div class="body">Sí, para comprar creás una cuenta con tu correo. Así podés ver el estado de tus pedidos, guardar tus datos de envío y tus favoritos.</div></details>
  </div>
</div>
@endsection
