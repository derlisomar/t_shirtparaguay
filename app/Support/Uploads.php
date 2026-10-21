<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * Guarda las fotos directamente en public/uploads (no hace falta "storage:link",
 * que en Windows suele pedir permisos de administrador).
 */
class Uploads
{
    public static function store(UploadedFile $file, string $folder): string
    {
        $dir = public_path('uploads/'.$folder);
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $name = Str::random(20).'.'.$ext;
        $file->move($dir, $name);

        return $folder.'/'.$name;
    }

    public static function delete(?string $path): void
    {
        if ($path && ! str_starts_with($path, 'seed/')) {
            $full = public_path('uploads/'.$path);
            if (is_file($full)) {
                @unlink($full);
            }
        }
    }
}
