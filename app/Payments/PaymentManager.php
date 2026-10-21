<?php

namespace App\Payments;

class PaymentManager
{
    public function gateway(string $key): PaymentGateway
    {
        return match ($key) {
            'card' => new CardGateway,
            default => new ManualGateway,
        };
    }

    public function forMethod(string $method): PaymentGateway
    {
        return $this->gateway(config("payments.methods.$method.gateway", 'manual'));
    }

    /** Medios que se muestran en el checkout web (incluye los que todavía no están activos). */
    public function webMethods(): array
    {
        $out = [];
        foreach (config('payments.methods') as $key => $m) {
            if (! $m['web']) {
                continue;
            }
            $out[$key] = $m + ['available' => $this->forMethod($key)->isAvailable()];
        }

        return $out;
    }

    public function localMethods(): array
    {
        return array_filter(config('payments.methods'), fn ($m) => $m['local']);
    }
}
