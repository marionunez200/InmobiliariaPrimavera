
<?php

require_once __DIR__ . '/Config/database.php';

session_start();

$pdo = db();

$nombre = trim($_POST['nombre'] ?? '');

if ($nombre == '') {
    header("Location: Panel-propiedades.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT id
    FROM categorias_propiedad
    WHERE nombre = ?
    LIMIT 1
");

$stmt->execute([$nombre]);

if ($stmt->fetch()) {

    $_SESSION['modal_exito'] = [
        'titulo' => 'Categoría existente',
        'mensaje' => 'Ya existe una categoría con ese nombre.'
    ];

    header("Location: Panel-propiedades.php");
    exit;
}

$stmt = $pdo->prepare("
    INSERT INTO categorias_propiedad
    (nombre, activo)
    VALUES
    (?, 1)
");

$stmt->execute([$nombre]);

$_SESSION['modal_exito'] = [
    'titulo' => 'Categoría agregada',
    'mensaje' => 'La categoría fue creada correctamente.'
];

header("Location: Panel-propiedades.php");
exit;