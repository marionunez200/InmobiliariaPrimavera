
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'Admin/Seguridad-2FA.php');
    exit;
}

/* Obtener usuario */
$stmt = $pdo->prepare("
    SELECT
        id,
        nombre,
        email
    FROM usuarios_admin
    WHERE id = ?
");

$stmt->execute([$_SESSION['admin_id']]);

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    die('Usuario no encontrado.');
}

/* Crear instancia */
$tfa = new TwoFactorAuth('Primavera');

/* Generar clave secreta */
$secret = $tfa->createSecret();

/* Guardarla temporalmente */
$stmt = $pdo->prepare("
    UPDATE usuarios_admin
    SET two_factor_secret = ?
    WHERE id = ?
");

$stmt->execute([
    $secret,
    $usuario['id']
]);

/* Generar QR */
$qr = $tfa->getQRCodeImageAsDataUri(
    $usuario['email'],
    $secret
);
?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <title>Activar autenticación</title>

</head>

<body>

    <h2>Escanea este código QR</h2>

    <p>
        Usa Microsoft Authenticator o Google Authenticator.
    </p>

    <img
        src="<?= $qr ?>"
        alt="Código QR">

    <form
        action="<?= BASE_URL ?>Backend/confirmar-2fa.php"
        method="POST">

        <input
            type="hidden"
            name="csrf_token"
            value="<?= $_SESSION['csrf_token'] ?>">

        <label>
            Código de 6 dígitos
        </label>

        <input
            type="text"
            name="codigo"
            maxlength="6"
            required>

        <button type="submit">
            Confirmar
        </button>

    </form>

</body>

</html>