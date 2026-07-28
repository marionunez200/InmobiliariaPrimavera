
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once ROOT_PATH . '/vendor/autoload.php';
require_once ROOT_PATH . '/Config/database.php';
require_once ROOT_PATH . '/Admin/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = db();

/* Validar que la petición sea POST */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'Admin/Seguridad-2FA.php');
    exit;
}

/* Validar Token CSRF */
if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    die('Token CSRF inválido.');
}

/* Validar que exista una sesión activa de administrador */
if (empty($_SESSION['admin_id'])) {
    header('Location: ' . BASE_URL . 'Login.php');
    exit;
}

/* Desactivar 2FA y limpiar la clave secreta para el usuario actual */
$stmt = $pdo->prepare("
    UPDATE usuarios_admin 
    SET two_factor_enabled = 0, 
        two_factor_secret = NULL 
    WHERE id = ?
");

$stmt->execute([$_SESSION['admin_id']]);

/* Mensaje de confirmación */
$_SESSION['modal_exito'] = [
    'titulo' => 'Autenticación desactivada',
    'mensaje' => 'La autenticación en dos pasos ha sido desactivada correctamente.'
];

// En confirmar-2fa.php y desactivar-2fa.php
header('Location: ' . BASE_URL . 'Admin/Seguridad-2fa.php');
exit;