<?php

use App\Models\Setting;

if (! function_exists('money')) {
    /** Formatea un monto en guaraníes: Gs. 139.000 */
    function money($amount): string
    {
        return setting('currency', 'Gs.').' '.number_format((float) $amount, 0, ',', '.');
    }
}

if (! function_exists('setting')) {
    function setting(string $key, $default = null)
    {
        try {
            $all = Setting::allCached();
        } catch (\Throwable $e) {
            return $default;
        }

        return isset($all[$key]) && $all[$key] !== '' ? $all[$key] : $default;
    }
}

if (! function_exists('show_free_shipping')) {
    /** ¿Se muestran los avisos de envío gratis? (Configuración › Envíos) */
    function show_free_shipping(): bool
    {
        return setting('show_free_shipping', '1') !== '0' && (int) setting('free_shipping_from', 400000) > 0;
    }
}

if (! function_exists('upload_url')) {
    /** URL pública de un archivo subido (carpeta public/uploads). */
    function upload_url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }
        if (str_starts_with($path, 'http')) {
            return $path;
        }

        return asset('uploads/'.ltrim($path, '/'));
    }
}

if (! function_exists('asset_v')) {
    /** asset() con versión según la fecha del archivo, para que el navegador no guarde CSS viejo. */
    function asset_v(string $path): string
    {
        $file = public_path($path);

        return asset($path).(is_file($file) ? '?v='.filemtime($file) : '');
    }
}

if (! function_exists('story')) {
    /**
     * Textos del bloque «Diseñado en Paraguay…» del inicio (Configuración › Inicio).
     * En el título, lo que va entre asteriscos sale en naranja y cursiva.
     */
    function story(): array
    {
        // Texto y botón pueden quedar vacíos a propósito (sin texto / sin botón)
        try {
            $all = Setting::allCached();
        } catch (\Throwable $e) {
            $all = [];
        }
        $raw = fn ($k, $d) => array_key_exists($k, $all) && $all[$k] !== null ? trim((string) $all[$k]) : $d;
        $title = setting('story_title', 'Diseñado en *Paraguay,* para el calor de acá.');

        return [
            'show' => setting('story_show', '1') !== '0',
            'title' => preg_replace('/\*(.+?)\*/u', '<em>$1</em>', e($title)),
            'text' => $raw('story_text', 'Algodón que respira, estampas con guiños nuestros (tereré, ñandutí, jopará) y talles que van del S al XXL. Si no te queda, lo cambiás sin vueltas.'),
            'button' => $raw('story_button', 'Conocé la colección'),
            'link' => setting('story_link') ?: route('catalog'),
            'image' => setting('story_image') ? upload_url(setting('story_image')) : asset('uploads/seed/products/remera-nanduti--rosa--detalle.webp'),
        ];
    }
}
