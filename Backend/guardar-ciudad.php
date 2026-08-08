<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once ROOT_PATH . '/Config/database.php';
require_once ROOT_PATH . '/Admin/auth.php';
validar_csrf();
requiere_editor();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = db();

$nombre = trim($_POST['nombre'] ?? '');

if ($nombre == '') {
    header("Location: " . BASE_URL . "Admin/Panel-propiedades.php");
    exit;
}

/* Verificar si la ciudad ya existe */
$stmt = $pdo->prepare("
    SELECT id
    FROM ciudades
    WHERE nombre = ?
    LIMIT 1
");

$stmt->execute([$nombre]);

if ($stmt->fetch()) {
    $_SESSION['modal_exito'] = [
        'titulo' => 'Ciudad existente',
        'mensaje' => 'Ya existe una ciudad con ese nombre.'
    ];

    header("Location: " . BASE_URL . "Admin/Panel-propiedades.php");
    exit;
}

/* Insertar nueva ciudad */
$stmt = $pdo->prepare("
    INSERT INTO ciudades
    (nombre, activo)
    VALUES
    (?, 1)
");

$stmt->execute([$nombre]);

$_SESSION['modal_exito'] = [
    'titulo' => 'Ciudad agregada',
    'mensaje' => 'La ciudad fue creada correctamente.'
];

header("Location: " . BASE_URL . "Admin/Panel-propiedades.php");
exit;