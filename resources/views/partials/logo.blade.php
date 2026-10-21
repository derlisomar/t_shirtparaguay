{{-- Logo: si se subió una imagen en Configuración se usa esa; si no, el logo de texto. --}}
@php($logo = setting('logo'))
@php($logoDark = setting('logo_dark'))
<a href="{{ route('home') }}" class="logo" aria-label="{{ setting('store_name', 't_shirtparaguay') }} - inicio">
  @if ($logo)
    @if ($logoDark)<img class="img-dark" src="{{ upload_url($logoDark) }}" alt="">@endif
    <img class="img-light" src="{{ upload_url($logo) }}" alt="{{ setting('store_name') }}">
  @else
    <span>t<span class="us">_</span>shirt</span><span class="py">PY</span>
  @endif
</a>
