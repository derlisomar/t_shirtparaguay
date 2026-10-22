@extends('layouts.admin')
@section('title', $customer->exists ? $customer->fullName() : 'Nuevo cliente')
@section('actions')
  @if ($customer->exists)
    <button type="button" class="btn sm ghost" data-delete="{{ route('admin.customers.destroy', $customer) }}" data-confirm="¿Eliminar a {{ $customer->fullName() }}? Su cuenta se borra, pero sus pedidos se conservan." data-ok="Sí, eliminar">Eliminar cliente</button>
  @endif
@endsection
@section('content')
@php($money = fn ($v) => $v ? number_format($v, 0, ',', '.') : '')
<div class="form-cols">
  <div class="stack">
    @if ($customer->exists)
      @php($spent = $customer->orders->where('status', '!=', 'cancelado')->sum('total'))
      <div class="kpis" style="grid-template-columns:repeat(3,1fr)">
        <div class="kpi"><span>Pedidos</span><b>{{ $customer->orders->count() }}</b></div>
        <div class="kpi"><span>Compró en total</span><b>{{ money($spent) }}</b></div>
        <div class="kpi"><span>Límite por compra</span><b>{{ $customer->purchase_limit ? money($customer->purchase_limit) : 'Sin límite' }}</b></div>
      </div>
      <div class="tbl-wrap"><table class="t">
        <thead><tr><th>Pedido</th><th>Estado</th><th class="r">Total</th><th></th></tr></thead>
        @forelse ($customer->orders as $o)
          <tr onclick="location='{{ route('admin.orders.show', $o) }}'" style="cursor:pointer">
            <td><b>{{ $o->number }}</b><br><span class="help">{{ $o->created_at->format('d/m/Y') }} · {{ $o->channel === 'local' ? 'Local' : 'Web' }}</span></td>
            <td>@include('admin.orders.status', ['o' => $o])</td>
            <td class="r"><b>{{ money($o->total) }}</b></td>
            <td class="r" onclick="event.stopPropagation()"><a class="icon-btn" href="{{ route('admin.orders.ticket', [$o, 'formato' => 'a4']) }}" target="_blank" title="Imprimir comprobante" aria-label="Imprimir comprobante de {{ $o->number }}"><svg class="i"><use href="#i-print"/></svg></a></td>
          </tr>
        @empty <tr><td colspan="4" class="help" style="padding:30px;text-align:center">Sin pedidos todavía.</td></tr> @endforelse
      </table></div>
    @else
      <div class="box"><p class="help">Cargá los datos del cliente. El correo y la contraseña son opcionales: si le ponés los dos, va a poder entrar a la tienda con su cuenta y ver sus pedidos.</p></div>
    @endif
  </div>

  <form method="post" action="{{ $customer->exists ? route('admin.customers.update', $customer) : route('admin.customers.store') }}" class="box" style="display:grid;gap:14px" autocomplete="off">
    @csrf @if ($customer->exists) @method('PUT') @endif
    <h2 style="margin:0">Datos del cliente</h2>
    <div class="form-grid">
      <div class="field"><label>Nombre</label><input class="input" name="name" value="{{ old('name', $customer->name) }}" required></div>
      <div class="field"><label>Apellido</label><input class="input" name="last_name" value="{{ old('last_name', $customer->last_name) }}"></div>
      <div class="field"><label>Usuario <span class="help">(para entrar a la tienda)</span></label><input class="input" name="username" value="{{ old('username', $customer->username) }}" autocapitalize="none" spellcheck="false" placeholder="Ej.: lucia.benitez"></div>
      <div class="field"><label>Correo <span class="help">(opcional)</span></label><input class="input" type="email" name="email" value="{{ old('email', $customer->email) }}"></div>
      <div class="field"><label>Celular</label><input class="input" name="phone" value="{{ old('phone', $customer->phone) }}" inputmode="tel"></div>
      <div class="field"><label>CI o RUC</label><input class="input" name="document" value="{{ old('document', $customer->document) }}"></div>
      <div class="field full"><label>Dirección</label><input class="input" name="address" value="{{ old('address', $customer->address) }}"></div>
      <div class="field"><label>Ciudad</label><input class="input" name="city" value="{{ old('city', $customer->city) }}"></div>
      <div class="field"><label>País</label><select class="select input" name="country" style="border-radius:14px">@foreach (\App\Support\Accounts::COUNTRIES as $co)<option @selected(old('country', $customer->country ?? 'Paraguay') === $co)>{{ $co }}</option>@endforeach</select></div>
      <div class="field"><label>Límite por compra (Gs.)</label><input class="input" name="purchase_limit" inputmode="numeric" data-money value="{{ old('purchase_limit', $money($customer->purchase_limit)) }}" placeholder="Sin límite"></div>
    </div>
    <p class="help" style="margin-top:-6px">El límite es el monto máximo que puede tener un pedido de este cliente, en la tienda online y en Vender. Vacío = sin límite.</p>
    <div>
      <label class="check"><input type="checkbox" name="is_wholesale" value="1" @checked(old('is_wholesale', $customer->is_wholesale))> Cliente mayorista</label>
      <p class="help" style="margin-top:4px">Paga siempre el precio mayorista de los productos que lo tengan.</p>
    </div>
    <div class="field"><label>Notas internas <span class="help">(solo las ves vos)</span></label><textarea class="textarea" name="admin_notes" rows="3">{{ old('admin_notes', $customer->admin_notes) }}</textarea></div>
    <div class="field">
      <label>{{ $customer->exists ? 'Cambiar contraseña' : 'Contraseña' }} <span class="help">(opcional)</span></label>
      <input class="input" type="text" name="password" minlength="6" autocomplete="new-password" placeholder="{{ $customer->exists ? 'Dejala vacía para no cambiarla' : 'Mínimo 6 caracteres' }}">
      <p class="help">Escribila y pasásela al cliente. Después la puede cambiar desde Mi cuenta.</p>
    </div>
    @if ($customer->exists)<p class="help">Cliente desde {{ $customer->created_at->format('d/m/Y') }}</p>@endif
    <button class="btn accent">{{ $customer->exists ? 'Guardar cambios' : 'Crear cliente' }}</button>
  </form>
</div>
@endsection
