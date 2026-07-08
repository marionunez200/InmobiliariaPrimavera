<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

require_once ROOT_PATH . '/Config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = db();

$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    die('ID no válido.');
}

try {

    // Verificar cuántas propiedades tiene asignadas
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM propiedades
        WHERE agente_id = ?
    ");

    $stmt->execute([$id]);

    $totalPropiedades = (int)$stmt->fetchColumn();

    if ($totalPropiedades > 0) {

        // Tiene propiedades: solo desactivar
        $stmt = $pdo->prepare("
            UPDATE agentes
            SET activo = 0
            WHERE id = ?
        ");

        $stmt->execute([$id]);

        $_SESSION['modal_exito'] = [
            'titulo' => 'Agente desactivado',
            'mensaje' => 'El agente tiene propiedades asignadas, por lo que fue desactivado.'
        ];

    } else {

        // No tiene propiedades: eliminar
        $stmt = $pdo->prepare("
            DELETE FROM agentes
            WHERE id = ?
        ");

        $stmt->execute([$id]);

        $_SESSION['modal_exito'] = [
            'titulo' => 'Agente eliminado',
            'mensaje' => 'El agente fue eliminado correctamente.'
        ];

    }

    header('Location: ' . BASE_URL . 'Admin/Panel-agente.php');
    exit;

} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}