<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once ROOT_PATH . '/vendor/autoload.php';
require_once ROOT_PATH . '/Config/database.php';
require_once ROOT_PATH . '/Admin/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* Generar token CSRF si no existe */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

use RobThree\Auth\TwoFactorAuth;

$pdo = db();

/* Obtener usuario actual */
$stmt = $pdo->prepare("
    SELECT id, nombre, email, two_factor_enabled, two_factor_secret
    FROM usuarios_admin
    WHERE id = ?
");
$stmt->execute([$_SESSION['admin_id']]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

$qrCodeUri = null;

/* Si el 2FA NO está activo, generamos/leemos el secreto para mostrar el QR */
if ((int)$usuario['two_factor_enabled'] === 0) {
    $tfa = new TwoFactorAuth('Primavera');

    // Si aún no tiene un secret generado, se lo creamos
    if (empty($usuario['two_factor_secret'])) {
        $secret = $tfa->createSecret();
        
        $updateStmt = $pdo->prepare("UPDATE usuarios_admin SET two_factor_secret = ? WHERE id = ?");
        $updateStmt->execute([$secret, $usuario['id']]);
        
        $usuario['two_factor_secret'] = $secret;
    }

    // Generar la imagen DataURI del QR
    $qrCodeUri = $tfa->getQRCodeImageAsDataUri(
        $usuario['email'],
        $usuario['two_factor_secret']
    );
}

$error = $_SESSION['2fa_error'] ?? '';
$exito = $_SESSION['modal_exito']['mensaje'] ?? '';
unset($_SESSION['2fa_error'], $_SESSION['modal_exito']);
?>

<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <title>Seguridad 2FA | Primavera</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>CSS/Admin.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>CSS/Seguridad-2fa.css">
    <style>
        .box-qr { text-align: center; margin: 20px 0; background: #fff; padding: 20px; border-radius: 12px; display: inline-block; }
        .box-qr img { width: 200px; height: 200px; }
        .secret-code { font-family: monospace; font-weight: bold; background: #e0e0e0; padding: 5px 10px; border-radius: 5px; letter-spacing: 2px; }
    </style>
</head>
<body>

    <section class="configuracion">
        <h1>Autenticación en dos pasos (2FA)</h1>

        <?php if ($error): ?>
            <p style="color: red; font-weight: bold;"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <?php if ($exito): ?>
            <p style="color: green; font-weight: bold;"><?= htmlspecialchars($exito, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <?php if ((int)$usuario['two_factor_enabled'] === 0): ?>

            <p>1. Escanea este código QR con <strong>Google Authenticator</strong> o <strong>Microsoft Authenticator</strong>:</p>

            <div class="box-qr">
                <?php if ($qrCodeUri): ?>
                    <img src="<?= $qrCodeUri ?>" alt="Código QR 2FA">
                <?php endif; ?>
                <p>¿No puedes escanear? Ingresa esta clave manualmente:<br>
                    <span class="secret-code"><?= htmlspecialchars($usuario['two_factor_secret'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                </p>
            </div>

            <p>2. Introduce el código de 6 dígitos que te da la aplicación para vincular la cuenta:</p>

            <!-- Ruta relativa saliendo de 'Admin' e ingresando a 'Backend' -->
            <form action="<?= BASE_URL ?>Backend/Seguridad-2fa/confirmar-2fa.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                
                <input type="text" name="codigo" maxlength="6" placeholder="000000" pattern="[0-9]{6}" required autocomplete="off">
                <button type="submit">Activar y Vincular 2FA</button>
            </form>

        <?php else: ?>

            <div class="estado">
                <h3>✅ La autenticación en dos pasos está activada en tu cuenta.</h3>
            </div>

            <form action="desactivar-2fa.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <button type="submit" onclick="return confirm('¿Seguro que deseas desactivar el 2FA?')">
                    Desactivar 2FA
                </button>
            </form>

        <?php endif; ?>
    </section>

    <?php if($exito): ?>

<div id="modalExito" class="modal">

    <div class="modal-box">

        <i class="fa-solid fa-circle-check"></i>

        <h2>Autenticación activada</h2>

        <p><?= htmlspecialchars($exito) ?></p>

    </div>

</div>

<script>

setTimeout(function(){

    window.location.href = "<?= BASE_URL ?>Login.php";

},2500);

</script>

<?php endif; ?>
</body>
</html>