{{-- Datos para crear la cuenta del cliente (registro y compra sin cuenta) --}}
<div class="form-grid">
  <div class="field"><label for="af-fn">Nombre</label><input class="input" id="af-fn" name="first_name" value="{{ old('first_name') }}" required autocomplete="given-name"></div>
  <div class="field"><label for="af-ln">Apellido</label><input class="input" id="af-ln" name="last_name" value="{{ old('last_name') }}" required autocomplete="family-name"></div>
  <div class="field"><label for="af-doc">Cédula o RUC</label><input class="input" id="af-doc" name="document" value="{{ old('document') }}" required inputmode="numeric" placeholder="Ej.: 4567890"></div>
  <div class="field"><label for="af-ph">Celular</label><input class="input" id="af-ph" name="phone" value="{{ old('phone') }}" required inputmode="tel" autocomplete="tel" placeholder="0981 123 456"></div>
  <div class="field"><label for="af-co">País</label>
    <select class="select input" id="af-co" name="country" required autocomplete="country-name">
      @foreach (\App\Support\Accounts::COUNTRIES as $c)<option @selected(old('country', 'Paraguay') === $c)>{{ $c }}</option>@endforeach
    </select>
  </div>
  <div class="field"><label for="af-ci">Ciudad</label><input class="input" id="af-ci" name="city" value="{{ old('city') }}" required autocomplete="address-level2"></div>
  <div class="field full"><label for="af-ad">Dirección</label><input class="input" id="af-ad" name="address" value="{{ old('address') }}" required autocomplete="street-address" placeholder="Calle, número y referencia"></div>
  <div class="field full"><label for="af-em">Correo <span style="color:var(--ink-3);font-weight:400">(opcional)</span></label><input class="input" id="af-em" type="email" name="email" value="{{ old('email') }}" autocomplete="email" placeholder="Si no tenés, dejalo vacío"></div>
</div>
<div class="account-box">
  <b>Tu usuario para entrar</b>
  <span>Con este usuario y tu contraseña vas a poder ver tus pedidos y volver a comprar.</span>
  <div class="form-grid" style="margin-top:12px">
    <div class="field full"><label for="af-us">Nombre de usuario</label><input class="input" id="af-us" name="username" value="{{ old('username') }}" required minlength="3" maxlength="40" autocomplete="username" autocapitalize="none" spellcheck="false" placeholder="Ej.: lucia.benitez" data-username></div>
    <div class="field"><label for="af-p">Contraseña</label><input class="input" id="af-p" type="password" name="password" required minlength="6" autocomplete="new-password" placeholder="Al menos 6 caracteres"></div>
    <div class="field"><label for="af-p2">Repetí la contraseña</label><input class="input" id="af-p2" type="password" name="password_confirmation" required minlength="6" autocomplete="new-password"></div>
  </div>
</div>
