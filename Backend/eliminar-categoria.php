
<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once ROOT_PATH . '/Config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = db();

$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    header('Location: ' . BASE_URL . 'Admin/Panel-propiedades.php');
    exit;
}

/* Verificar si la categoría existe */
$stmt = $pdo->prepare("
    SELECT protegida
    FROM categorias_propiedad
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$categoria = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$categoria) {
    $_SESSION['modal_exito'] = [
        'titulo' => 'Error',
        'mensaje' => 'La categoría no existe.'
    ];

    header('Location: ' . BASE_URL . 'Admin/Panel-propiedades.php');
    exit;
}

/* No permitir eliminar categorías protegidas */
if ((int)$categoria['protegida'] === 1) {

    $_SESSION['modal_exito'] = [
        'titulo' => 'Acción no permitida',
        'mensaje' => 'Esta categoría está protegida y no puede eliminarse.'
    ];

    header('Location: ' . BASE_URL . 'Admin/Panel-propiedades.php');
    exit;
}

/* Verificar si alguna propiedad usa la categoría */
$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM propiedades
    WHERE categoria_id = ?
");

$stmt->execute([$id]);

$total = (int)$stmt->fetchColumn();

if ($total > 0) {

    $_SESSION['modal_exito'] = [
        'titulo' => 'No se puede eliminar',
        'mensaje' => 'Hay propiedades utilizando esta categoría.'
    ];

    header('Location: ' . BASE_URL . 'Admin/Panel-propiedades.php');
    exit;
}

/* Eliminar */
$stmt = $pdo->prepare("
    DELETE FROM categorias_propiedad
    WHERE id = ?
");

$stmt->execute([$id]);

$_SESSION['modal_exito'] = [
    'titulo' => 'Categoría eliminada',
    'mensaje' => 'La categoría fue eliminada correctamente.'
];

header('Location: ' . BASE_URL . 'Admin/Panel-propiedades.php');
exit;