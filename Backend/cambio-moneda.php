
<?php

class MonedaService
{
    // 1 USD = 18.70 MXN (ejemplo)
    private const USD = 18.70;

    public static function convertir(
        float $cantidad,
        string $de,
        string $a
    ): float {

        if ($de === $a) {
            return $cantidad;
        }

        if ($de === 'MXN' && $a === 'USD') {
            return round($cantidad / self::USD, 2);
        }

        if ($de === 'USD' && $a === 'MXN') {
            return round($cantidad * self::USD, 2);
        }

        return $cantidad;
    }

    public static function simbolo(string $moneda): string
    {
        return match ($moneda) {
            'USD' => 'US$',
            default => '$'
        };
    }

    public static function formato(
        float $cantidad,
        string $moneda
    ): string {

        return self::simbolo($moneda)
            . number_format($cantidad, 2)
            . " $moneda";
    }
}