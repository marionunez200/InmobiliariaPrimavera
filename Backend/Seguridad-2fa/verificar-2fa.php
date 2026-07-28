
<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once ROOT_PATH . '/vendor/autoload.php';
require_once ROOT_PATH . '/Config/database.php';

use RobThree\Auth\TwoFactorAuth;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'Login.php');
    exit;
}

$codigo = trim($_POST['codigo'] ?? '');

if ($codigo === '') {
    die('Debes ingresar el código.');
}

if (empty($_SESSION['2fa_user'])) {
    header('Location: ' . BASE_URL . 'Login.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT
        id,
        two_factor_secret
    FROM usuarios_admin
    WHERE id = ?
");

$stmt->execute([
    $_SESSION['2fa_user']
]);

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    session_destroy();
    die('Usuario no encontrado.');
}

$tfa = new TwoFactorAuth('Primavera');

if (!$tfa->verifyCode($usuario['two_factor_secret'], $codigo)) {
    $_SESSION['2fa_error'] = 'El código de 6 dígitos es incorrecto o ha expirado.';
    header('Location: ' . BASE_URL . 'Admin/Verificar-2FA.php');
    exit;
}

/* Login definitivo */
$_SESSION['admin_id'] = $usuario['id'];
$_SESSION['admin_nombre'] = $_SESSION['2fa_nombre'];
$_SESSION['admin_email'] = $_SESSION['2fa_email'];
$_SESSION['admin_rol'] = $_SESSION['2fa_rol'];

// Limpiar variables temporales de 2FA
unset($_SESSION['2fa_user'], $_SESSION['2fa_nombre'], $_SESSION['2fa_email'], $_SESSION['2fa_rol']);

header('Location: ' . BASE_URL . 'Admin/Panel-propiedades.php');
exit;