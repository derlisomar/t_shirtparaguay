{{-- Descargas de un reporte: Excel y PDF con el mismo período y canal --}}
<span class="dl no-print">
  <a href="{{ route('admin.reports.export', ['type' => $k] + $q) }}" title="Descargar en Excel"><svg class="i"><use href="#i-xls"/></svg>Excel</a>
  <a href="{{ route('admin.reports.export', ['type' => $k, 'formato' => 'pdf'] + $q) }}" title="Descargar en PDF"><svg class="i"><use href="#i-pdf"/></svg>PDF</a>
</span>
