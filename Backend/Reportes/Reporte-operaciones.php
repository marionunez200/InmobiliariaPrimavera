<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once ROOT_PATH . '/Backend/Reportes/Reportes.php';

$mes = (int)($_GET['mes'] ?? date('m'));
$anio = (int)($_GET['anio'] ?? date('Y'));

$operaciones = Reportes::operacionesMes($mes, $anio);
$total = Reportes::totalMes($mes, $anio);
$agentes = Reportes::operacionesPorAgente($mes, $anio);

$meses = [
    1 => 'Enero',
    2 => 'Febrero',
    3 => 'Marzo',
    4 => 'Abril',
    5 => 'Mayo',
    6 => 'Junio',
    7 => 'Julio',
    8 => 'Agosto',
    9 => 'Septiembre',
    10 => 'Octubre',
    11 => 'Noviembre',
    12 => 'Diciembre'
];

$nombreMes = $meses[$mes] ?? 'Mes';

$totalVentas = 0;
$totalRentas = 0;
$totalTraspasos = 0;

foreach ($operaciones as $op) {
    switch ($op['tipo_operacion']) {
        case 'venta':
            $totalVentas++;
            break;

        case 'renta':
            $totalRentas++;
            break;

        case 'traspaso':
            $totalTraspasos++;
            break;
    }
}

function h($valor): string
{
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="es-MX">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Reporte de operaciones |
        <?= h($nombreMes) ?>
        <?= h($anio) ?>
    </title>

    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>CSS/reporte-operaciones.css"
    >
</head>

<body>

    <div class="acciones">
        <a
            href="<?= BASE_URL ?>Admin/Panel-propiedades.php"
            class="btn btn-volver"
        >
            Volver
        </a>

        <form method="GET" class="selector-periodo">

            <select name="mes">
                <?php foreach ($meses as $numero => $nombre): ?>
                    <option
                        value="<?= $numero ?>"
                        <?= $mes == $numero ? 'selected' : '' ?>
                    >
                        <?= $nombre ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="anio">
                <?php
                $anioActual = (int)date('Y');

                for ($i = $anioActual; $i >= $anioActual - 5; $i--):
                ?>
                    <option
                        value="<?= $i ?>"
                        <?= $anio == $i ? 'selected' : '' ?>
                    >
                        <?= $i ?>
                    </option>
                <?php endfor; ?>
            </select>

            <button
                type="submit"
                class="btn btn-periodo"
            >
                Ver mes
            </button>

        </form>

        <button
            type="button"
            class="btn btn-pdf"
            onclick="window.print()"
        >
            Guardar como PDF
        </button>
    </div>

    <main class="reporte">

        <header class="encabezado">

            <img
                class="logo"
                src="<?= BASE_URL ?>Imagenes/Logosolo.webp"
                alt="Logo Primavera"
            >

            <div class="encabezado-texto">

                <h1>Inmobiliaria Primavera</h1>

                <h2>Reporte de operaciones</h2>

            </div>

            <div class="periodo">

                <strong>
                    <?= h($nombreMes) ?>
                    <?= h($anio) ?>
                </strong>

                <span>
                    Generado:
                    <?= date('d/m/Y') ?>
                </span>

            </div>

        </header>

        <section class="resumen">

            <div class="resumen-card">
                <span>Operaciones</span>
                <strong><?= count($operaciones) ?></strong>
            </div>

            <div class="resumen-card">
                <span>Ventas</span>
                <strong><?= $totalVentas ?></strong>
            </div>

            <div class="resumen-card">
                <span>Rentas</span>
                <strong><?= $totalRentas ?></strong>
            </div>

            <div class="resumen-card">
                <span>Traspasos</span>
                <strong><?= $totalTraspasos ?></strong>
            </div>

            <div class="resumen-card">
                <span>Monto total</span>

                <strong>
                    $<?= number_format($total, 0) ?>
                </strong>
            </div>

        </section>

        <section class="seccion">

            <h2>Operaciones realizadas</h2>

            <?php if (empty($operaciones)): ?>

                <div class="sin-registros">
                    No hay operaciones registradas para
                    <?= h($nombreMes) ?>
                    de
                    <?= h($anio) ?>.
                </div>

            <?php else: ?>

                <table>

                    <thead>

                        <tr>
                            <th>Fecha</th>
                            <th>Propiedad</th>
                            <th>Ciudad</th>
                            <th>Agente</th>
                            <th>Cliente</th>
                            <th>Tipo</th>
                            <th>Precio</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($operaciones as $op): ?>

                            <tr>

                                <td>
                                    <?= date(
                                        'd/m/Y',
                                        strtotime($op['fecha_operacion'])
                                    ) ?>
                                </td>

                                <td>
                                    <?= h($op['titulo']) ?>
                                </td>

                                <td>
                                    <?= h($op['ciudad'] ?? 'N/A') ?>
                                </td>

                                <td>
                                    <?= h($op['agente']) ?>
                                </td>

                                <td>
                                    <?= h($op['cliente_nombre']) ?>
                                </td>

                                <td class="tipo">
                                    <?= h($op['tipo_operacion']) ?>
                                </td>

                                <td class="precio">
                                    $<?= number_format(
                                        (float)$op['precio'],
                                        0
                                    ) ?>

                                    <?= h($op['moneda']) ?>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

                <div class="total-reporte">

                    <div class="total-box">

                        <span>Total del periodo</span>

                        <strong>
                            $<?= number_format($total, 0) ?>
                        </strong>

                    </div>

                </div>

            <?php endif; ?>

        </section>

        <?php if (!empty($agentes)): ?>

            <section class="seccion">

                <h2>Operaciones por agente</h2>

                <table class="tabla-agentes">

                    <thead>

                        <tr>
                            <th>Agente</th>
                            <th>Operaciones</th>
                            <th>Total</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($agentes as $agente): ?>

                            <tr>

                                <td>
                                    <?= h($agente['nombre']) ?>
                                </td>

                                <td>
                                    <?= (int)$agente['operaciones'] ?>
                                </td>

                                <td class="precio">
                                    $<?= number_format(
                                        (float)$agente['total'],
                                        0
                                    ) ?>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </section>

        <?php endif; ?>

        <footer class="pie">

            <span>Inmobiliaria Primavera</span>

        </footer>

    </main>

</body>

</html>