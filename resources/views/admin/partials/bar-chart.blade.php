{{--
  Gráfico de barras apiladas sin librerías.
  $data: lista de períodos (label, long y un valor por serie) · $series: [key, label, color] · $money: si los valores son guaraníes
--}}
@php
  $totals = array_map(fn ($d) => array_sum(array_map(fn ($s) => $d[$s['key']], $series)), $data);
  $rawMax = max(1, max($totals));
  // Escala "redonda" para las líneas de guía
  $mag = 10 ** floor(log10($rawMax));
  $step = collect([1, 2, 2.5, 5, 10])->map(fn ($m) => $m * $mag)->first(fn ($s) => $rawMax / $s <= 4);
  if ($step < 1) { $step = 1; }
  $top = $step * ceil($rawMax / $step);
  $short = function ($v) use ($money) {
      if (! $money) return number_format($v, 0, ',', '.');
      if ($v >= 1000000) return rtrim(rtrim(number_format($v / 1000000, 1, ',', '.'), '0'), ',').' M';
      if ($v >= 1000) return number_format($v / 1000, 0, ',', '.').' mil';
      return number_format($v, 0, ',', '.');
  };
  $every = count($data) > 14 ? 5 : 1;
@endphp
<div class="bchart" style="--n: {{ count($data) }}">
  <div class="grid">
    @for ($v = $top; $v >= 0; $v -= $step)
      <div class="gl" style="bottom: {{ $v * 100 / $top }}%"><span>{{ $short($v) }}</span></div>
    @endfor
  </div>
  <div class="bars">
    @foreach ($data as $i => $d)
      <div class="c">
        <div class="bar" style="height: {{ $totals[$i] ? max(1.5, $totals[$i] * 100 / $top) : 0 }}%">
          @foreach ($series as $s)
            @if ($d[$s['key']] > 0)<i style="flex: {{ $d[$s['key']] }}; background: {{ $s['color'] }}"></i>@endif
          @endforeach
        </div>
        <div class="tip">
          <b>{{ $d['long'] }}</b>
          @foreach ($series as $s)
            <span><i style="background: {{ $s['color'] }}"></i>{{ $s['label'] }}: {{ $money ? money($d[$s['key']]) : $d[$s['key']] }}</span>
          @endforeach
          @if (count($series) > 1)<span><b>Total: {{ $money ? money($totals[$i]) : $totals[$i] }}</b></span>@endif
        </div>
        <small @class(['hide' => $i % $every !== 0 && $i !== count($data) - 1])>{{ $d['label'] }}</small>
      </div>
    @endforeach
  </div>
</div>
<div class="legend">
  @foreach ($series as $s)<span><i style="background: {{ $s['color'] }}"></i>{{ $s['label'] }}</span>@endforeach
</div>
