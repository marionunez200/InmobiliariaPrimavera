<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once ROOT_PATH . '/vendor/autoload.php';
require_once ROOT_PATH . '/Backend/Reportes/Reportes.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$mes  = (int)($_GET['mes'] ?? date('m'));
$anio = (int)($_GET['anio'] ?? date('Y'));

$operaciones = Reportes::operacionesMes($mes, $anio);
$total = Reportes::totalMes($mes, $anio);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$sheet->setTitle("Operaciones");

/* Encabezados */
$sheet->setCellValue('A1', 'Fecha');
$sheet->setCellValue('B1', 'Propiedad');
$sheet->setCellValue('C1', 'Ciudad');
$sheet->setCellValue('D1', 'Agente');
$sheet->setCellValue('E1', 'Cliente');
$sheet->setCellValue('F1', 'Tipo');
$sheet->setCellValue('G1', 'Precio');
$sheet->setCellValue('H1', 'Moneda');

$fila = 2;

foreach ($operaciones as $op) {

    $sheet->setCellValue('A'.$fila, $op['fecha_operacion']);
    $sheet->setCellValue('B'.$fila, $op['titulo']);
    $sheet->setCellValue('C'.$fila, $op['ciudad']);
    $sheet->setCellValue('D'.$fila, $op['agente']);
    $sheet->setCellValue('E'.$fila, $op['cliente_nombre']);
    $sheet->setCellValue('F'.$fila, ucfirst($op['tipo_operacion']));
    $sheet->setCellValue('G'.$fila, $op['precio']);
    $sheet->setCellValue('H'.$fila, $op['moneda']);

    $fila++;
}

/* Total */
$sheet->setCellValue('F'.($fila+1), 'TOTAL');
$sheet->setCellValue('G'.($fila+1), $total);

/* Negritas */
$sheet->getStyle('A1:H1')->getFont()->setBold(true);
$sheet->getStyle('F'.($fila+1).':G'.($fila+1))->getFont()->setBold(true);

/* Ajustar columnas */
foreach (range('A','H') as $columna) {
    $sheet->getColumnDimension($columna)->setAutoSize(true);
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Reporte_'.$mes.'_'.$anio.'.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;