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
