<?php

namespace App\Support;

use App\Models\Order;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Comprobantes de pago que adjuntan los clientes.
 * Se guardan fuera de la carpeta pública (storage/app/private/comprobantes),
 * así solo los ven el cliente dueño del pedido y el panel.
 */
class Receipts
{
    public const RULE = 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120';

    public const MESSAGES = [
        'receipt.mimes' => 'El comprobante tiene que ser una foto (JPG, PNG o WEBP) o un PDF.',
        'receipt.max' => 'El comprobante puede pesar hasta 5 MB.',
        'receipt.uploaded' => 'No se pudo subir el comprobante. Probá con un archivo más liviano.',
    ];

    public static function attach(Order $order, UploadedFile $file): void
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $path = $file->storeAs('comprobantes', $order->number.'-'.Str::random(12).'.'.$ext, 'local');

        if ($order->receipt_path) {
            Storage::disk('local')->delete($order->receipt_path);
        }
        $order->update(['receipt_path' => $path, 'receipt_uploaded_at' => now()]);
    }

    public static function response(Order $order)
    {
        abort_unless($order->receipt_path && Storage::disk('local')->exists($order->receipt_path), 404);

        return Storage::disk('local')->response($order->receipt_path, 'comprobante-'.$order->number.'.'.pathinfo($order->receipt_path, PATHINFO_EXTENSION), [
            'Cache-Control' => 'private, max-age=0',
        ], 'inline');
    }
}
