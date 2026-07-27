
<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once ROOT_PATH . '/Backend/Reportes/Reportes.php';

$mes = (int)($_GET['mes'] ?? date('m'));
$anio = (int)($_GET['anio'] ?? date('Y'));

$operaciones = Reportes::operacionesMes($mes, $anio);

$total = Reportes::totalMes($mes, $anio);

$agentes = Reportes::operacionesPorAgente($mes, $anio);
?>