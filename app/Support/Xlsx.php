<?php

namespace App\Support;

/**
 * Planilla de Excel (.xlsx) sin librerías ni la extensión zip de PHP.
 *
 * $sheets = [['name' => 'Ventas', 'title' => '...', 'subtitle' => '...', 'head' => [...], 'rows' => [[...]],
 *             'types' => ['text'|'int'|'money'|'pct'|'date', ...], 'total' => [...] (opcional)]]
 */
class Xlsx
{
    // Estilos (índices de cellXfs)
    private const S_TEXT = 0, S_TITLE = 1, S_SUB = 2, S_HEAD = 3, S_INT = 4, S_MONEY = 5, S_PCT = 6, S_TOTAL = 7, S_TOTAL_INT = 8, S_TOTAL_MONEY = 9, S_HEAD_R = 10, S_DEC = 11;

    public static function build(array $sheets): string
    {
        $files = [];
        $names = [];
        foreach (array_values($sheets) as $i => $sheet) {
            $name = self::sheetName($sheet['name'], $names);
            $names[] = $name;
            $files['xl/worksheets/sheet'.($i + 1).'.xml'] = self::sheet($sheet);
        }

        $n = count($names);
        $files['[Content_Types].xml'] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .implode('', array_map(fn ($i) => '<Override PartName="/xl/worksheets/sheet'.$i.'.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>', range(1, $n)))
            .'</Types>';
        $files['_rels/.rels'] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>';
        $files['xl/workbook.xml'] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets>'
            .implode('', array_map(fn ($i) => '<sheet name="'.self::e($names[$i]).'" sheetId="'.($i + 1).'" r:id="rId'.($i + 1).'"/>', array_keys($names)))
            .'</sheets></workbook>';
        $files['xl/_rels/workbook.xml.rels'] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .implode('', array_map(fn ($i) => '<Relationship Id="rId'.$i.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet'.$i.'.xml"/>', range(1, $n)))
            .'<Relationship Id="rId'.($n + 1).'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'</Relationships>';
        $files['xl/styles.xml'] = self::styles();

        return self::zip($files);
    }

    private static function sheet(array $s): string
    {
        $types = $s['types'] ?? [];
        $cols = count($s['head']);
        $rows = [];
        $r = 1;
        $rows[] = self::row($r++, [[$s['title'], self::S_TITLE]]);
        if (! empty($s['subtitle'])) {
            $rows[] = self::row($r++, [[$s['subtitle'], self::S_SUB]]);
        }
        $r++;
        $headRow = $r;
        $rows[] = self::row($r++, array_map(fn ($h, $i) => [$h, in_array($types[$i] ?? 'text', ['int', 'money', 'pct', 'dec'], true) ? self::S_HEAD_R : self::S_HEAD], $s['head'], array_keys($s['head'])));
        foreach ($s['rows'] as $row) {
            $cells = [];
            foreach (array_values($row) as $i => $v) {
                // Una celda puede traer su propio tipo: ['v' => 123, 't' => 'money']
                [$v, $t] = is_array($v) ? [$v['v'], $v['t']] : [$v, $types[$i] ?? 'text'];
                $cells[] = [$v, match ($t) { 'int' => self::S_INT, 'money' => self::S_MONEY, 'pct' => self::S_PCT, 'dec' => self::S_DEC, default => self::S_TEXT }];
            }
            $rows[] = self::row($r++, $cells);
        }
        if (! empty($s['total'])) {
            $cells = [];
            foreach (array_values($s['total']) as $i => $v) {
                $cells[] = [$v, match ($types[$i] ?? 'text') { 'int' => self::S_TOTAL_INT, 'money' => self::S_TOTAL_MONEY, default => self::S_TOTAL }];
            }
            $rows[] = self::row($r++, $cells);
        }

        // Ancho de columnas según el contenido
        $widths = [];
        foreach (array_merge([$s['head']], $s['rows']) as $row) {
            foreach (array_values($row) as $i => $v) {
                $v = is_array($v) ? $v['v'] : $v;
                $len = is_numeric($v) && ($types[$i] ?? '') === 'money' ? strlen(number_format((float) $v, 0, ',', '.')) + 4 : mb_strlen((string) $v);
                $widths[$i] = max($widths[$i] ?? 8, min(60, $len + 3));
            }
        }
        $colsXml = '<cols>'.implode('', array_map(fn ($w, $i) => '<col min="'.($i + 1).'" max="'.($i + 1).'" width="'.$w.'" customWidth="1"/>', $widths, array_keys($widths))).'</cols>';
        $last = self::col($cols - 1);

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheetViews><sheetView workbookViewId="0" showGridLines="0"><pane ySplit="'.$headRow.'" topLeftCell="A'.($headRow + 1).'" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            .$colsXml.'<sheetData>'.implode('', $rows).'</sheetData>'
            .(count($s['rows']) ? '<autoFilter ref="A'.$headRow.':'.$last.($headRow + count($s['rows'])).'"/>' : '')
            .'<pageMargins left="0.5" right="0.5" top="0.6" bottom="0.6" header="0.3" footer="0.3"/>'
            .'<pageSetup orientation="'.($cols > 6 ? 'landscape' : 'portrait').'" fitToWidth="1" fitToHeight="0"/>'
            .'</worksheet>';
    }

    private static function row(int $r, array $cells): string
    {
        $out = '<row r="'.$r.'">';
        foreach ($cells as $i => [$v, $style]) {
            $ref = self::col($i).$r;
            if ($v === null || $v === '') {
                $out .= '<c r="'.$ref.'" s="'.$style.'"/>';
            } elseif ((is_int($v) || is_float($v)) && ! in_array($style, [self::S_TEXT, self::S_TITLE, self::S_SUB, self::S_HEAD, self::S_HEAD_R, self::S_TOTAL], true)) {
                $out .= '<c r="'.$ref.'" s="'.$style.'"><v>'.$v.'</v></c>';
            } else {
                $out .= '<c r="'.$ref.'" s="'.$style.'" t="inlineStr"><is><t xml:space="preserve">'.self::e((string) $v).'</t></is></c>';
            }
        }

        return $out.'</row>';
    }

    private static function styles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<numFmts count="3"><numFmt numFmtId="164" formatCode="&quot;Gs.&quot; #,##0"/><numFmt numFmtId="165" formatCode="0&quot;%&quot;"/><numFmt numFmtId="166" formatCode="0.0"/></numFmts>'
            .'<fonts count="4"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="14"/><color rgb="FF111111"/><name val="Calibri"/></font>'
            .'<font><sz val="10"/><color rgb="FF666666"/><name val="Calibri"/></font><font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font></fonts>'
            .'<fills count="4"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FF1A1A1A"/><bgColor indexed="64"/></patternFill></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFFFE6DA"/><bgColor indexed="64"/></patternFill></fill></fills>'
            .'<borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border>'
            .'<border><left/><right/><top/><bottom style="thin"><color rgb="FFE2E2E2"/></bottom><diagonal/></border></borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="12">'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="1" applyBorder="1"/>'
            .'<xf numFmtId="0" fontId="1" fillId="0" borderId="0" applyFont="1"/>'
            .'<xf numFmtId="0" fontId="2" fillId="0" borderId="0" applyFont="1"/>'
            .'<xf numFmtId="0" fontId="3" fillId="2" borderId="0" applyFont="1" applyFill="1"/>'
            .'<xf numFmtId="3" fontId="0" fillId="0" borderId="1" applyNumberFormat="1" applyBorder="1"/>'
            .'<xf numFmtId="164" fontId="0" fillId="0" borderId="1" applyNumberFormat="1" applyBorder="1"/>'
            .'<xf numFmtId="165" fontId="0" fillId="0" borderId="1" applyNumberFormat="1" applyBorder="1"/>'
            .'<xf numFmtId="0" fontId="0" fillId="3" borderId="0" applyFill="1" applyFont="1"><alignment/></xf>'
            .'<xf numFmtId="3" fontId="0" fillId="3" borderId="0" applyNumberFormat="1" applyFill="1"/>'
            .'<xf numFmtId="164" fontId="0" fillId="3" borderId="0" applyNumberFormat="1" applyFill="1"/>'
            .'<xf numFmtId="0" fontId="3" fillId="2" borderId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment horizontal="right"/></xf>'
            .'<xf numFmtId="166" fontId="0" fillId="0" borderId="1" applyNumberFormat="1" applyBorder="1"/>'
            .'</cellXfs></styleSheet>';
    }

    private static function sheetName(string $name, array $taken): string
    {
        $name = mb_substr(trim(preg_replace('/[\[\]\*\?\/\\\\:]/', ' ', $name)), 0, 31) ?: 'Hoja';
        $base = $name;
        for ($i = 2; in_array($name, $taken, true); $i++) {
            $name = mb_substr($base, 0, 28).' '.$i;
        }

        return $name;
    }

    private static function col(int $i): string
    {
        $s = '';
        for ($i++; $i > 0; $i = intdiv($i - 1, 26)) {
            $s = chr(65 + ($i - 1) % 26).$s;
        }

        return $s;
    }

    private static function e(string $s): string
    {
        $s = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $s);

        return htmlspecialchars($s, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    /** Arma el .zip a mano (deflate de zlib), así no depende de la extensión zip. */
    private static function zip(array $files): string
    {
        $data = '';
        $central = '';
        $time = (int) ((date('Y') - 1980) << 25 | date('n') << 21 | date('j') << 16 | date('G') << 11 | (int) date('i') << 5 | (int) date('s') >> 1);
        foreach ($files as $name => $content) {
            $crc = crc32($content);
            $packed = gzdeflate($content, 6);
            $offset = strlen($data);
            $head = pack('vvvVVVVvv', 20, 0x0800, 8, $time, $crc, strlen($packed), strlen($content), strlen($name), 0);
            $data .= "PK\x03\x04".$head.$name.$packed;
            $central .= "PK\x01\x02".pack('vvvvVVVVvvvvvVV', 20, 20, 0x0800, 8, $time, $crc, strlen($packed), strlen($content), strlen($name), 0, 0, 0, 0, 0, $offset).$name;
        }

        return $data.$central."PK\x05\x06".pack('vvvvVVv', 0, 0, count($files), count($files), strlen($central), strlen($data), 0);
    }
}
