<?php 
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

require_once ROOT_PATH . '/Config/database.php';
require_once ROOT_PATH . '/Admin/auth.php';
validar_csrf();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = db();

$id = $_POST['id'] ?? null;

if (!$id || !is_numeric($id)) {
    die('ID no válido.');
}


try {

    $pdo->beginTransaction();

    $stmtImagenes = $pdo->prepare("
        SELECT imagen_url
        FROM imagenes_propiedades
        WHERE propiedad_id = ?
    ");

    $stmtImagenes->execute([$id]);

    $imagenes = $stmtImagenes->fetchAll(PDO::FETCH_COLUMN);

    foreach ($imagenes as $imagen) {

        $rutaArchivo = ROOT_PATH . '/' . $imagen;

        if (is_file($rutaArchivo)) {
            unlink($rutaArchivo);
        }

    }

    $stmt = $pdo->prepare("
        DELETE FROM imagenes_propiedades
        WHERE propiedad_id = ?
    ");

    $stmt->execute([$id]);

    $stmt = $pdo->prepare("
        DELETE FROM propiedades
        WHERE id = ?
    ");

    $stmt->execute([$id]);



    $pdo->commit();


    $_SESSION['modal_exito'] = [
        'titulo' => 'Propiedad eliminada',
        'mensaje' => 'La propiedad y sus imágenes fueron eliminadas correctamente.'
    ];


    header('Location: ' . BASE_URL . 'Admin/Panel-propiedades.php');
    exit;



} catch (Exception $e) {


    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    die('Error al eliminar propiedad: ' . $e->getMessage());

}