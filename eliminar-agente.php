<?php
require_once __DIR__ . '/Config/database.php';

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

    header('Location: Panel-agente.php?eliminado=1');
    exit;

} catch (Exception $e) {
    die('Error al desactivar agente: ' . $e->getMessage());
}