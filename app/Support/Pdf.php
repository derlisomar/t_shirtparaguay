<?php

namespace App\Support;

/**
 * PDF en formato planilla (tablas con encabezado, totales y número de página) sin librerías.
 * Usa las fuentes Helvetica que trae todo lector de PDF.
 *
 * Cada sección: ['title' => '...', 'head' => [...], 'rows' => [[...]], 'types' => [...], 'total' => [...] (opcional), 'note' => '...']
 */
class Pdf
{
    private const W_R = [278,278,355,556,556,889,667,191,333,333,389,584,278,333,278,278,556,556,556,556,556,556,556,556,556,556,278,278,584,584,584,556,1015,667,667,722,722,667,611,778,722,278,500,667,556,833,722,778,667,778,722,667,611,722,667,944,667,667,611,278,278,278,469,556,333,556,556,500,556,556,278,556,556,222,222,500,222,833,556,556,556,556,333,500,278,556,500,722,500,500,500,334,260,334,584,0,556,0,222,556,333,1000,556,556,333,1000,667,333,1000,0,611,0,0,222,222,333,333,350,556,1000,333,1000,500,333,944,0,500,667,278,333,556,556,556,556,260,556,333,737,370,556,584,333,737,552,400,549,333,333,333,576,537,333,333,333,365,556,834,834,834,611,667,667,667,667,667,667,1000,722,667,667,667,667,278,278,278,278,722,722,778,778,778,778,778,584,778,722,722,722,722,667,667,611,556,556,556,556,556,556,889,500,556,556,556,556,278,278,278,278,556,556,556,556,556,556,556,549,611,556,556,556,556,500,556,500];
    private const W_B = [278,333,474,556,556,889,722,238,333,333,389,584,278,333,278,278,556,556,556,556,556,556,556,556,556,556,333,333,584,584,584,611,975,722,722,722,722,667,611,778,722,278,556,722,611,833,722,778,667,778,722,667,611,722,667,944,667,667,611,333,278,333,584,556,333,556,611,556,611,556,333,611,611,278,278,556,278,889,611,611,611,611,389,556,333,611,556,778,556,556,500,389,280,389,584,0,556,0,278,556,500,1000,556,556,333,1000,667,333,1000,0,611,0,0,278,278,500,500,350,556,1000,333,1000,556,333,944,0,500,667,278,333,556,556,556,556,280,556,333,737,370,556,584,333,737,552,400,549,333,333,333,576,556,333,333,333,365,556,834,834,834,611,722,722,722,722,722,722,1000,722,667,667,667,667,278,278,278,278,722,722,778,778,778,778,778,584,778,722,722,722,722,667,667,611,556,556,556,556,556,556,889,556,556,556,556,556,278,278,278,278,611,611,611,611,611,611,611,549,611,611,611,611,611,556,611,556];

    private float $pw;
    private float $ph;
    private float $m = 36;
    private float $y;
    private array $pages = [];
    private string $cur = '';

    public static function build(string $title, string $subtitle, array $sections, ?bool $landscape = null): string
    {
        $wide = $landscape ?? collect($sections)->contains(fn ($s) => count($s['head']) > 6);
        $pdf = new self($wide);
        $pdf->newPage();
        $pdf->text($pdf->m, $pdf->y, $title, 16, true);
        $pdf->y -= 16;
        $pdf->text($pdf->m, $pdf->y, $subtitle, 9, false, '0.4');
        $pdf->y -= 14;
        foreach ($sections as $s) {
            $pdf->section($s, count($sections) > 1);
        }

        return $pdf->output($title);
    }

    private function __construct(bool $landscape)
    {
        [$this->pw, $this->ph] = $landscape ? [842, 595] : [595, 842];
    }

    private function newPage(): void
    {
        if ($this->cur !== '') {
            $this->pages[] = $this->cur;
        }
        $this->cur = '';
        $this->y = $this->ph - $this->m - 8;
    }

    private function section(array $s, bool $withTitle): void
    {
        $types = $s['types'] ?? [];
        $fs = count($s['head']) > 8 ? 7.5 : 8.5;
        $rowH = $fs + 7;
        $avail = $this->pw - 2 * $this->m;
        $pad = 5;

        // Ancho natural de cada columna
        $cells = array_merge([$s['head']], array_map(fn ($r) => $this->formatRow($r, $types), $s['rows']));
        if (! empty($s['total'])) {
            $cells[] = $this->formatRow($s['total'], $types);
        }
        $nat = [];
        foreach ($cells as $ri => $row) {
            foreach (array_values($row) as $i => $v) {
                $nat[$i] = max($nat[$i] ?? 20, $this->width((string) $v, $fs, $ri === 0) + 2 * $pad);
            }
        }
        // Si no entra, se achican las columnas de texto más anchas
        $total = array_sum($nat);
        if ($total > $avail) {
            $textCols = array_keys(array_filter($types + array_fill(0, count($nat), 'text'), fn ($t) => $t === 'text'));
            $fixed = array_sum(array_diff_key($nat, array_flip($textCols)));
            $flex = array_sum(array_intersect_key($nat, array_flip($textCols)));
            $room = max(60, $avail - $fixed);
            foreach ($textCols as $i) {
                if (isset($nat[$i])) {
                    $nat[$i] = $nat[$i] * $room / max(1, $flex);
                }
            }
        } elseif (count($nat) > 2 || $total > $avail * 0.6) {
            // Repartir el sobrante para que la tabla ocupe todo el ancho
            $extra = ($avail - $total) / count($nat);
            $nat = array_map(fn ($w) => $w + $extra, $nat);
        }
        $widths = array_values($nat);
        $tableW = array_sum($widths);

        $need = ($withTitle ? 24 : 0) + $rowH * min(3, count($s['rows']) + 1) + 4;
        if ($this->y - $need < $this->m + 20) {
            $this->newPage();
        }
        if ($withTitle) {
            $this->y -= 8;
            $this->text($this->m, $this->y, $s['title'], 11.5, true, '0.1');
            $this->y -= 8;
        }
        if (! empty($s['note'])) {
            $this->y -= 4;
            $this->text($this->m, $this->y, $s['note'], 8, false, '0.45');
            $this->y -= 6;
        }

        $drawHead = function () use ($s, $widths, $tableW, $rowH, $fs, $pad, $types) {
            $this->rect($this->m, $this->y - $rowH, $tableW, $rowH, '0.11 0.11 0.11');
            $this->row($s['head'], $widths, $rowH, $fs, $pad, $types, true, '1');
        };
        $drawHead();

        if (! $s['rows']) {
            $this->y -= 4;
            $this->text($this->m + $pad, $this->y - $fs, 'Sin datos en este período.', $fs, false, '0.45');
            $this->y -= $rowH + 4;

            return;
        }
        foreach ($s['rows'] as $n => $r) {
            if ($this->y - $rowH < $this->m + 20) {
                $this->newPage();
                $drawHead();
            }
            if ($n % 2 === 1) {
                $this->rect($this->m, $this->y - $rowH, $tableW, $rowH, '0.965 0.965 0.965');
            }
            $this->row($this->formatRow($r, $types), $widths, $rowH, $fs, $pad, $types, false, '0.1');
        }
        if (! empty($s['total'])) {
            if ($this->y - $rowH < $this->m + 20) {
                $this->newPage();
                $drawHead();
            }
            $this->rect($this->m, $this->y - $rowH, $tableW, $rowH, '1 0.9 0.855');
            $this->row($this->formatRow($s['total'], $types), $widths, $rowH, $fs, $pad, $types, true, '0.1');
        }
        $this->y -= 14;
    }

    private function row(array $cells, array $widths, float $h, float $fs, float $pad, array $types, bool $bold, string $color): void
    {
        $x = $this->m;
        foreach (array_values($cells) as $i => $v) {
            $w = $widths[$i] ?? 40;
            $v = $this->fit((string) $v, $w - 2 * $pad, $fs, $bold);
            $right = in_array($types[$i] ?? 'text', ['int', 'money', 'pct', 'dec'], true);
            $tx = $right ? $x + $w - $pad - $this->width($v, $fs, $bold) : $x + $pad;
            $this->text($tx, $this->y - $h + ($h - $fs) / 2 + 1.5, $v, $fs, $bold, $color);
            $x += $w;
        }
        $this->y -= $h;
    }

    private function formatRow(array $r, array $types): array
    {
        return array_map(function ($v, $i) use ($types) {
            [$v, $t] = is_array($v) ? [$v['v'], $v['t']] : [$v, $types[$i] ?? 'text'];
            if ($v === null || $v === '' || ! is_numeric($v)) {
                return (string) $v;
            }

            return match ($t) {
                'money' => 'Gs. '.number_format((float) $v, 0, ',', '.'),
                'int' => number_format((float) $v, 0, ',', '.'),
                'pct' => number_format((float) $v, 0, ',', '.').'%',
                'dec' => number_format((float) $v, 1, ',', '.'),
                default => (string) $v,
            };
        }, array_values($r), array_keys(array_values($r)));
    }

    private function fit(string $s, float $max, float $fs, bool $bold): string
    {
        if ($this->width($s, $fs, $bold) <= $max) {
            return $s;
        }
        while (mb_strlen($s) > 1 && $this->width($s.'…', $fs, $bold) > $max) {
            $s = mb_substr($s, 0, -1);
        }

        return rtrim($s).'…';
    }

    private function width(string $s, float $fs, bool $bold): float
    {
        $w = 0;
        $table = $bold ? self::W_B : self::W_R;
        foreach (str_split($this->enc($s)) as $ch) {
            $c = ord($ch);
            $w += $c >= 32 ? ($table[$c - 32] ?: 556) : 0;
        }

        return $w * $fs / 1000;
    }

    private function text(float $x, float $y, string $s, float $fs, bool $bold, string $gray = '0'): void
    {
        $color = str_contains($gray, ' ') ? $gray.' rg' : $gray.' g';
        $esc = strtr($this->enc($s), ['\\' => '\\\\', '(' => '\\(', ')' => '\\)', "\r" => '', "\n" => ' ']);
        $this->cur .= sprintf("BT %s /%s %.2F Tf %.2F %.2F Td (%s) Tj ET\n", $color, $bold ? 'F2' : 'F1', $fs, $x, $y, $esc);
    }

    private function rect(float $x, float $y, float $w, float $h, string $rgb): void
    {
        $this->cur .= sprintf("%s rg %.2F %.2F %.2F %.2F re f\n", $rgb, $x, $y, $w, $h);
    }

    private function enc(string $s): string
    {
        return mb_convert_encoding($s, 'Windows-1252', 'UTF-8');
    }

    private function output(string $title): string
    {
        $this->pages[] = $this->cur;
        $n = count($this->pages);
        $objs = [];
        $objs[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objs[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
        $objs[4] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';
        $kids = [];
        $stamp = 'Generado el '.now()->format('d/m/Y H:i');
        foreach ($this->pages as $i => $content) {
            $this->cur = '';
            $this->text($this->m, $this->m - 14, setting('store_name', 't_shirtparaguay').' · '.$stamp, 7.5, false, '0.5');
            $label = 'Página '.($i + 1).' de '.$n;
            $this->text($this->pw - $this->m - $this->width($label, 7.5, false), $this->m - 14, $label, 7.5, false, '0.5');
            $stream = $content.$this->cur;
            $pageId = 5 + $i * 2;
            $objs[$pageId] = sprintf('<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %d %d] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents %d 0 R >>', $this->pw, $this->ph, $pageId + 1);
            $z = gzcompress($stream);
            $objs[$pageId + 1] = '<< /Length '.strlen($z).' /Filter /FlateDecode >>'."\nstream\n".$z."\nendstream";
            $kids[] = $pageId.' 0 R';
        }
        $objs[2] = '<< /Type /Pages /Kids ['.implode(' ', $kids).'] /Count '.$n.' >>';
        $infoId = 5 + $n * 2;
        $objs[$infoId] = '<< /Title ('.strtr($this->enc($title), ['\\' => '\\\\', '(' => '\\(', ')' => '\\)']).') /Producer (t_shirtparaguay) >>';
        ksort($objs);

        $out = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [];
        foreach ($objs as $id => $body) {
            $offsets[$id] = strlen($out);
            $out .= $id." 0 obj\n".$body."\nendobj\n";
        }
        $xref = strlen($out);
        $max = max(array_keys($objs));
        $out .= "xref\n0 ".($max + 1)."\n0000000000 65535 f \n";
        for ($i = 1; $i <= $max; $i++) {
            $out .= isset($offsets[$i]) ? sprintf("%010d 00000 n \n", $offsets[$i]) : "0000000000 65535 f \n";
        }

        return $out.'trailer << /Size '.($max + 1).' /Root 1 0 R /Info '.$infoId." 0 R >>\nstartxref\n".$xref."\n%%EOF";
    }
}
