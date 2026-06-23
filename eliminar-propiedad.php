<?php
require_once __DIR__ . '/Config/database.php';

$pdo = db();

$id = $_POST['id'] ?? null;

if (!$id) {
    die('ID no válido.');
}

try {
    $stmt = $pdo->prepare("
        DELETE FROM propiedades
        WHERE id = ?
    ");

    $stmt->execute([$id]);

    header('Location: Panel-propiedades.php?eliminado=1');
    exit;

} catch (Exception $e) {
    die('Error al eliminar propiedad: ' . $e->getMessage());
}