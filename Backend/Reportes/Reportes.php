<?php

require_once ROOT_PATH . '/Config/database.php';

class Reportes
{
public static function operacionesMes(int $mes, int $anio): array
{
    $pdo = db();

    $sql = "
        SELECT
            o.id,
            o.fecha_operacion,
            o.tipo_operacion,
            o.precio,
            o.moneda,

            o.cliente_nombre,
            o.cliente_telefono,
            o.cliente_email,

            o.meses_renta,
            o.observaciones,

            a.nombre AS agente,

            p.titulo,
            p.ciudad,
            p.direccion_completa

        FROM operaciones_realizadas o

        INNER JOIN propiedades p
            ON p.id = o.propiedad_id

        INNER JOIN agentes a
            ON a.id = o.agente_id

        WHERE
            MONTH(o.fecha_operacion) = ?
            AND YEAR(o.fecha_operacion) = ?

        ORDER BY o.fecha_operacion DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$mes, $anio]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    public static function totalMes(int $mes, int $anio): float
    {
        $pdo = db();

        $stmt = $pdo->prepare("
            SELECT SUM(precio)
            FROM operaciones_realizadas
            WHERE
                MONTH(fecha_operacion) = ?
                AND YEAR(fecha_operacion) = ?
        ");

        $stmt->execute([$mes, $anio]);

        return (float)($stmt->fetchColumn() ?? 0);
    }

    public static function operacionesPorAgente(int $mes, int $anio): array
    {
        $pdo = db();

        $stmt = $pdo->prepare("
            SELECT

                a.nombre,

                COUNT(*) AS operaciones,

                SUM(o.precio) AS total

            FROM operaciones_realizadas o

            INNER JOIN agentes a
                ON a.id = o.agente_id

            WHERE
                MONTH(o.fecha_operacion) = ?
                AND YEAR(o.fecha_operacion) = ?

            GROUP BY a.id, a.nombre

            ORDER BY total DESC
        ");

        $stmt->execute([$mes, $anio]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}