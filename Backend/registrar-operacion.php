
<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once ROOT_PATH . '/Config/database.php';
require_once ROOT_PATH . '/Admin/auth.php';

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "Admin/Panel-propiedades.php");
    exit;
}

$propiedad_id      = (int)$_POST['propiedad_id'];
$agente_id         = (int)$_POST['agente_id'];
$tipo_operacion    = $_POST['tipo_operacion'];
$cliente_nombre    = trim($_POST['cliente_nombre']);
$cliente_telefono  = trim($_POST['cliente_telefono']);
$cliente_email     = trim($_POST['cliente_email']);
$fecha_operacion   = $_POST['fecha_operacion'];
$precio            = (float)$_POST['precio'];
$moneda            = $_POST['moneda'];
$meses_renta       = $_POST['meses_renta'] ?: null;
$observaciones     = trim($_POST['observaciones']);

$pdo->beginTransaction();

try {

    $stmt = $pdo->prepare("
        INSERT INTO operaciones_realizadas
        (
            propiedad_id,
            agente_id,
            tipo_operacion,
            cliente_nombre,
            cliente_telefono,
            cliente_email,
            fecha_operacion,
            precio,
            moneda,
            meses_renta,
            observaciones
        )
        VALUES
        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $propiedad_id,
        $agente_id,
        $tipo_operacion,
        $cliente_nombre,
        $cliente_telefono,
        $cliente_email,
        $fecha_operacion,
        $precio,
        $moneda,
        $meses_renta,
        $observaciones
    ]);

    switch ($tipo_operacion) {

        case 'venta':
            $estado = 'vendido';
            break;

        case 'traspaso':
            $estado = 'traspasado';
            break;

        case 'renta':
            $estado = 'rentado';
            break;

        default:
            $estado = 'activo';
            break;
    }

    $stmt = $pdo->prepare("
        UPDATE propiedades
        SET estado_publicacion = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $estado,
        $propiedad_id
    ]);

    $pdo->commit();

    $_SESSION['modal_exito'] = [
        'titulo' => 'Operación registrada',
        'mensaje' => 'La operación se guardó correctamente.'
    ];

} catch (Exception $e) {

    $pdo->rollBack();
    die($e->getMessage());

}

header("Location: " . BASE_URL . "Admin/Panel-propiedades.php");
exit;