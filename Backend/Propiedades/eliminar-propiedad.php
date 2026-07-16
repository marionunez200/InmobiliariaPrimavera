<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

require_once ROOT_PATH . '/Config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = db();

$id = $_POST['id'] ?? null;

if (!$id || !is_numeric($id)) {
    die('ID no válido.');
}

try {
    $stmt = $pdo->prepare("
        DELETE FROM propiedades
        WHERE id = ?
    ");

    $stmt->execute([$id]);

    $_SESSION['modal_exito'] = [
        'titulo' => 'Propiedad eliminada',
        'mensaje' => 'La propiedad fue eliminada correctamente.'
    ];

    header('Location: ' . BASE_URL . 'Admin/Panel-propiedades.php');
    exit;

} catch (Exception $e) {
    die('Error al eliminar propiedad: ' . $e->getMessage());
}
