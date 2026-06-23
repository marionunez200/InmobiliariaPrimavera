<?php
require_once __DIR__ . '/Config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = db();

$id = $_POST['id'] ?? null;

if (!$id) {
    die('ID no válido.');
}

try {
    $stmt = $pdo->prepare("
        UPDATE agentes
        SET activo = 0
        WHERE id = ?
    ");

    $stmt->execute([$id]);
    
    $_SESSION['modal_exito'] = [
        'titulo' => 'Agente desactivado',
        'mensaje' => 'El agente fue marcado como inactivo correctamente.'
    ];
    
    header('Location: Panel-agente.php');
    exit;

} catch (Exception $e) {
    die('Error al desactivar agente: ' . $e->getMessage());
}