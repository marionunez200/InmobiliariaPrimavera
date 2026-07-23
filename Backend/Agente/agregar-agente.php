<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once ROOT_PATH . '/Admin/auth.php';
require_once ROOT_PATH . '/Config/database.php';
require_once ROOT_PATH . '/Backend/Agente/funciones-agente.php';
validar_csrf();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = db();

$id = trim($_POST['id'] ?? '');
$esEdicion = $id !== '';

$nombre = trim($_POST['nombre'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$email = trim($_POST['email'] ?? '');
$activo = isset($_POST['activo']) ? (int)$_POST['activo'] : 1;

if ($nombre === '') {
    die('El nombre del agente es obligatorio.');
}

try {
        $nuevaFoto = subirFotoAgente(null);

        $fotoFinal = $nuevaFoto ?: 'Imagenes/agente1.webp';

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
            $fotoFinal,
            $activo
        ]);

    $_SESSION['modal_exito'] = [
        'titulo' => $esEdicion ? 'Cambios guardados' : 'Agente agregado',
        'mensaje' => $esEdicion
            ? 'La información del agente se actualizó correctamente.'
            : 'El agente se agregó correctamente al panel.'
    ];
    
    header('Location: ' . BASE_URL . 'Admin/Panel-agente.php');
    exit;

} catch (Exception $e) {
    die('Error al guardar agente: ' . $e->getMessage());
}