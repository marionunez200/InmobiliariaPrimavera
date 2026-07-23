<?php

class MonedaService
{
    private static function obtenerTipoCambio(): float
    {
        $archivo = ROOT_PATH . '/cache/tipo_cambio.json';

        // Si existe y tiene menos de 24 horas, usarlo
        if (file_exists($archivo) && (time() - filemtime($archivo) < 86400)) {

            $datos = json_decode(file_get_contents($archivo), true);

            if (isset($datos['USD'])) {
                return (float)$datos['USD'];
            }
        }

        // Consultar la API
        $respuesta = @file_get_contents(
            "https://api.frankfurter.app/latest?from=MXN&to=USD"
        );

        if ($respuesta !== false) {

            $api = json_decode($respuesta, true);

            if (isset($api['rates']['USD'])) {

                $tasa = (float)$api['rates']['USD'];

                // Crear la carpeta cache si no existe
                if (!is_dir(ROOT_PATH . '/cache')) {
                    mkdir(ROOT_PATH . '/cache', 0777, true);
                }

                file_put_contents(
                    $archivo,
                    json_encode([
                        'USD' => $tasa
                    ])
                );

                return $tasa;
            }
        }

        // Valor de respaldo si falla la API
        return 0.053;
    }

    public static function convertir(
        float $cantidad,
        string $de,
        string $a
    ): float {

        if ($de === $a) {
            return $cantidad;
        }

        $tasa = self::obtenerTipoCambio();

        if ($de === 'MXN' && $a === 'USD') {
            return round($cantidad * $tasa, 2);
        }

        if ($de === 'USD' && $a === 'MXN') {
            return round($cantidad / $tasa, 2);
        }

        return $cantidad;
    }

    public static function simbolo(string $moneda): string
{
    return '$'; // Todas las monedas usarán simplemente '$'
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