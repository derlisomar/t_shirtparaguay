@if ($l->wholesale)
  <div class="ws-note on">Precio mayorista aplicado (normal {{ money($l->regular_price) }})</div>
@elseif ($l->product->hasWholesale() && $l->product->wholesale_min_qty > 0)
  <div class="ws-note">Llevando {{ $l->product->wholesale_min_qty }} o más (talles y colores combinados) pagás {{ money($l->product->wholesale_price) }} c/u</div>
@endif
