<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once ROOT_PATH . '/vendor/autoload.php';
require_once ROOT_PATH . '/Config/database.php';
require_once ROOT_PATH . '/Admin/auth.php';

use RobThree\Auth\TwoFactorAuth;

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

$codigo = trim($_POST['codigo'] ?? '');

if ($codigo === '') {
    $_SESSION['2fa_error'] = 'Debes ingresar el código de 6 dígitos.';
    header('Location: ' . BASE_URL . 'Admin/Seguridad-2FA.php');
    exit;
}

/* Consultar la clave secreta del usuario actual */
$stmt = $pdo->prepare("SELECT id, two_factor_secret FROM usuarios_admin WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario || empty($usuario['two_factor_secret'])) {
    $_SESSION['2fa_error'] = 'No se encontró la clave secreta de vinculación.';
    header('Location: ' . BASE_URL . 'Admin/Seguridad-2FA.php');
    exit;
}

/* Validar código con tolerancia de desfase de reloj (2 periodos = 60 segundos) */
$tfa = new TwoFactorAuth('Primavera');

if (!$tfa->verifyCode($usuario['two_factor_secret'], $codigo, 2)) {
    $_SESSION['2fa_error'] = 'El código es incorrecto o ha expirado. Verifica la hora de tu dispositivo e inténtalo de nuevo.';
    header('Location: ' . BASE_URL . 'Admin/Seguridad-2FA.php');
    exit;
}

/* Confirmación exitosa: Activar 2FA en la base de datos */
$stmt = $pdo->prepare("UPDATE usuarios_admin SET two_factor_enabled = 1 WHERE id = ?");
$stmt->execute([$usuario['id']]);

$_SESSION['modal_exito'] = [
    'titulo' => 'Autenticación activada',
    'mensaje' => 'La autenticación en dos pasos se activó correctamente.'
];

header('Location: ' . BASE_URL . 'Admin/Seguridad-2FA.php');
exit;