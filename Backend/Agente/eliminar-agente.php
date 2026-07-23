<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once ROOT_PATH . '/Config/database.php';
require_once ROOT_PATH . '/Admin/auth.php';

validar_csrf();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = db();

$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    die('ID no válido.');
}

try {

    // Revisar propiedades asignadas
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM propiedades
        WHERE agente_id = ?
    ");

    $stmt->execute([$id]);

    $totalPropiedades = (int)$stmt->fetchColumn();


    if ($totalPropiedades > 0) {

        // Si tiene propiedades solo se desactiva
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


        // Obtener foto antes de eliminar
        $stmtFoto = $pdo->prepare("
            SELECT foto_url
            FROM agentes
            WHERE id = ?
        ");

        $stmtFoto->execute([$id]);

        $foto = $stmtFoto->fetchColumn();


        // Eliminar foto física
        if ($foto && str_starts_with($foto, 'Uploads/agentes/')) {

            $rutaFoto = ROOT_PATH . '/' . $foto;

            if (is_file($rutaFoto)) {
                unlink($rutaFoto);
            }
        }


        // Eliminar registro
        $stmt = $pdo->prepare("
            DELETE FROM agentes
            WHERE id = ?
        ");

        $stmt->execute([$id]);


        $_SESSION['modal_exito'] = [
            'titulo' => 'Agente eliminado',
            'mensaje' => 'El agente y su fotografía fueron eliminados correctamente.'
        ];

    }


    header('Location: ' . BASE_URL . 'Admin/Panel-agente.php');
    exit;


} catch (Exception $e) {

    die('Error al eliminar agente: ' . $e->getMessage());

}