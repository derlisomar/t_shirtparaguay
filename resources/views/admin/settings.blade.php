@extends('layouts.admin')
@section('title', 'Configuración')
@section('content')
<form method="post" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
  @csrf @method('PUT')
  <div class="form-cols">
    <div class="stack">
      <div class="box">
        <h2>Marca</h2>
        <div class="form-grid">
          <div class="field"><label>Nombre de la tienda</label><input class="input" name="store_name" value="{{ $s['store_name'] ?? '' }}" required></div>
          <div class="field"><label>Frase corta</label><input class="input" name="store_tagline" value="{{ $s['store_tagline'] ?? '' }}"></div>
          <div class="field"><label>Velocidad del slider <span class="help">(segundos que se ve cada slide)</span></label><input class="input" type="number" min="2" max="20" name="slider_seconds" value="{{ $s['slider_seconds'] ?? 4 }}"></div>
          <div class="field full"><label>Barra de anuncio <span class="help">(arriba de todo; vacío = no se muestra)</span></label><input class="input" name="announcement" value="{{ $s['announcement'] ?? '' }}"></div>
          @foreach (['logo' => 'Logo (modo claro)', 'logo_dark' => 'Logo para modo oscuro (opcional)', 'favicon' => 'Ícono de la pestaña (PNG cuadrado)'] as $f => $label)
            <div class="field {{ $f === 'favicon' ? 'full' : '' }}"><label>{{ $label }}</label>
              <input type="file" name="{{ $f }}" accept="image/*">
              @if (! empty($s[$f]))<div class="file-cur"><img src="{{ upload_url($s[$f]) }}" alt="" style="{{ $f === 'logo_dark' ? 'background:#2c362f' : '' }}"><label class="check"><input type="checkbox" name="remove_{{ $f }}" value="1"> Quitar</label></div>@endif
            </div>
          @endforeach
          <p class="help full">Mientras no subas un logo se usa el de texto «t_shirt PY». Recomendado: PNG o SVG con fondo transparente, unos 400 × 120 px.</p>
        </div>
      </div>
      <div class="box">
        <h2>Bloque del inicio</h2>
        <p class="help" style="margin:-6px 0 14px">El recuadro «Diseñado en Paraguay…» que aparece en el inicio, debajo de las categorías.</p>
        <div class="form-grid">
          <label class="switch full">Mostrar este bloque en el inicio <input type="checkbox" name="story_show" value="1" @checked(($s['story_show'] ?? '1') !== '0')></label>
          <div class="field full"><label>Título <span class="help">(lo que pongas entre *asteriscos* sale en naranja y cursiva)</span></label><input class="input" name="story_title" maxlength="120" value="{{ $s['story_title'] ?? 'Diseñado en *Paraguay,* para el calor de acá.' }}"></div>
          <div class="field full"><label>Texto</label><textarea class="textarea" name="story_text" rows="3" maxlength="600">{{ $s['story_text'] ?? 'Algodón que respira, estampas con guiños nuestros (tereré, ñandutí, jopará) y talles que van del S al XXL. Si no te queda, lo cambiás sin vueltas.' }}</textarea></div>
          <div class="field"><label>Texto del botón <span class="help">(vacío = sin botón)</span></label><input class="input" name="story_button" maxlength="40" value="{{ $s['story_button'] ?? 'Conocé la colección' }}"></div>
          <div class="field"><label>Link del botón <span class="help">(vacío = la tienda)</span></label><input class="input" name="story_link" value="{{ $s['story_link'] ?? '' }}" placeholder="{{ route('catalog') }}"></div>
          <div class="field full"><label>Foto <span class="help">(opcional; vertical o cuadrada, JPG, PNG o WEBP)</span></label>
            <input type="file" name="story_image" accept="image/*">
            <div class="file-cur"><img src="{{ story()['image'] }}" alt="">@if (! empty($s['story_image']))<label class="check"><input type="checkbox" name="remove_story_image" value="1"> Volver a la foto de ejemplo</label>@endif</div>
          </div>
        </div>
      </div>
      <div class="box">
        <h2>Envíos</h2>
        <div class="form-grid">
          <div class="field"><label>Costo de envío (Gs.)</label><input class="input" name="shipping_cost" data-money value="{{ number_format((int) ($s['shipping_cost'] ?? 0), 0, ',', '.') }}"></div>
          <div class="field"><label>Envío gratis desde (Gs.) <span class="help">0 = nunca</span></label><input class="input" name="free_shipping_from" data-money value="{{ number_format((int) ($s['free_shipping_from'] ?? 0), 0, ',', '.') }}"></div>
          <label class="switch full">Mostrar avisos de envío gratis en la tienda <span class="help">(barra «te faltan Gs. X» del carrito y «envío gratis desde» en los productos)</span><input type="checkbox" name="show_free_shipping" value="1" @checked(($s['show_free_shipping'] ?? '1') !== '0')></label>
          <div class="field full"><label>Dirección para retirar</label><input class="input" name="pickup_address" value="{{ $s['pickup_address'] ?? '' }}"></div>
        </div>
      </div>
      <div class="box">
        <h2>Pagos</h2>
        <div class="field"><label>Datos para transferencia <span class="help">(se muestran al cliente al confirmar)</span></label><textarea class="textarea" name="bank_details" rows="5">{{ $s['bank_details'] ?? '' }}</textarea></div>
        <div style="margin-top:14px;padding:14px 16px;border-radius:14px;background:var(--surface-2)">
          <b>Tarjeta de crédito: {{ config('payments.card.enabled') ? 'activada' : 'preparada, todavía no activa' }}</b>
          <p class="help" style="margin-top:4px">Ya está todo listo para la segunda actualización: tabla de pagos, opción en el checkout («Próximamente») y aviso automático de la pasarela. Cuando tengas las claves de Bancard o Pagopar se completa <code>app/Payments/CardGateway.php</code> y se activa en el archivo <code>.env</code>.</p>
        </div>
      </div>
    </div>
    <div class="stack" style="position:sticky;top:16px">
      <div class="box">
        <h2>Contacto y redes</h2>
        <div style="display:grid;gap:12px">
          <div class="field"><label>WhatsApp <span class="help">(con 595, sin 0 ni espacios)</span></label><input class="input" name="whatsapp" value="{{ $s['whatsapp'] ?? '' }}" placeholder="595981123456"></div>
          <div class="field"><label>Correo</label><input class="input" name="email" value="{{ $s['email'] ?? '' }}"></div>
          <div class="field"><label>Dirección / ciudad</label><input class="input" name="address" value="{{ $s['address'] ?? '' }}"></div>
          <div class="field"><label>Instagram (link)</label><input class="input" name="instagram" value="{{ $s['instagram'] ?? '' }}"></div>
          <div class="field"><label>Facebook (link)</label><input class="input" name="facebook" value="{{ $s['facebook'] ?? '' }}"></div>
          <div class="field"><label>TikTok (link)</label><input class="input" name="tiktok" value="{{ $s['tiktok'] ?? '' }}"></div>
        </div>
      </div>
      <div class="box">
        <h2>Otros</h2>
        <div class="field"><label>Moneda</label><input class="input" name="currency" value="{{ $s['currency'] ?? 'Gs.' }}"></div>
        <div class="field" style="margin-top:12px"><label>Avisar «poco stock» desde</label><input class="input" name="low_stock" value="{{ $s['low_stock'] ?? 3 }}" inputmode="numeric"></div>
      </div>
      <button class="btn accent block" style="height:52px">Guardar configuración</button>
    </div>
  </div>
</form>
@endsection
