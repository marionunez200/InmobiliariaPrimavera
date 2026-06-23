<?php
require_once __DIR__ . '/Config/database.php';

$pdo = db();

$id = trim($_POST['id'] ?? '');

$nombre = trim($_POST['nombre'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$email = trim($_POST['email'] ?? '');
$foto_url = trim($_POST['foto_url'] ?? '');
$activo = isset($_POST['activo']) ? (int)$_POST['activo'] : 1;

if ($nombre === '') {
    die('El nombre del agente es obligatorio.');
}

try {
    if ($id !== '') {
        $stmt = $pdo->prepare("
            UPDATE agentes SET
                nombre = ?,
                telefono = ?,
                email = ?,
                foto_url = ?,
                activo = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $nombre,
            $telefono,
            $email,
            $foto_url,
            $activo,
            $id
        ]);

    } else {
        $stmt = $pdo->prepare("
            INSERT INTO agentes (
                nombre,
                telefono,
                email,
                foto_url,
                activo
            ) VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $nombre,
            $telefono,
            $email,
            $foto_url,
            $activo
        ]);
    }

    header('Location: Panel-agente.php?ok=1');
    exit;

} catch (Exception $e) {
    die('Error al guardar agente: ' . $e->getMessage());
}