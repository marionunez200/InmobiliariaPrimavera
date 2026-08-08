<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once ROOT_PATH . '/Config/database.php';
require_once ROOT_PATH . '/Admin/auth.php';
validar_csrf();
requiere_admin();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = db();

$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    header('Location: ' . BASE_URL . 'Admin/Panel-propiedades.php');
    exit;
}

/* Verificar si la ciudad existe */
$stmt = $pdo->prepare("
    SELECT protegida
    FROM ciudades
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$ciudad = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ciudad) {
    $_SESSION['modal_exito'] = [
        'titulo' => 'Error',
        'mensaje' => 'La ciudad no existe.'
    ];

    header('Location: ' . BASE_URL . 'Admin/Panel-propiedades.php');
    exit;
}

/* No permitir eliminar ciudades protegidas */
if ((int)$ciudad['protegida'] === 1) {
    $_SESSION['modal_exito'] = [
        'titulo' => 'Acción no permitida',
        'mensaje' => 'Esta ciudad está protegida y no puede eliminarse.'
    ];

    header('Location: ' . BASE_URL . 'Admin/Panel-propiedades.php');
    exit;
}

/* Verificar si alguna propiedad utiliza la ciudad */
$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM propiedades
    WHERE ciudad_id = ?
");

$stmt->execute([$id]);

$total = (int)$stmt->fetchColumn();

if ($total > 0) {
    $_SESSION['modal_exito'] = [
        'titulo' => 'No se puede eliminar',
        'mensaje' => 'Hay propiedades utilizando esta ciudad.'
    ];

    header('Location: ' . BASE_URL . 'Admin/Panel-propiedades.php');
    exit;
}

/* Eliminar */
$stmt = $pdo->prepare("
    DELETE FROM ciudades
    WHERE id = ?
");

$stmt->execute([$id]);

$_SESSION['modal_exito'] = [
    'titulo' => 'Ciudad eliminada',
    'mensaje' => 'La ciudad fue eliminada correctamente.'
];

header('Location: ' . BASE_URL . 'Admin/Panel-propiedades.php');
exit;