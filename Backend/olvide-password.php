<?php

if (!defined('BASE_URL')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
}

require ROOT_PATH . 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once ROOT_PATH . '/Config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$pdo = db();

$email = trim($_POST['email'] ?? '');

if ($email === '') {

    $_SESSION['modal_exito'] = [
        'titulo' => 'Error',
        'mensaje' => 'Debes ingresar un correo.'
    ];

    header('Location: ' . BASE_URL . 'Admin/Login.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT id, nombre
    FROM usuarios_admin
    WHERE email = ?
    AND activo = 1
");

$stmt->execute([$email]);

$usuario = $stmt->fetch();

if (!$usuario) {

    $_SESSION['modal_exito'] = [
        'titulo' => 'Correo enviado',
        'mensaje' => 'Si el correo existe, recibirás un enlace para restablecer tu contraseña.'
    ];

    header('Location: ' . BASE_URL . 'Admin/Login.php');
    exit;
}

/* Crear token */

$token = bin2hex(random_bytes(32));
$expira = date('Y-m-d H:i:s', strtotime('+1 hour'));

$stmt = $pdo->prepare("
    INSERT INTO password_resets (
        usuario_id,
        token,
        expiracion
    )
    VALUES (?, ?, ?)
");

$stmt->execute([
    $usuario['id'],
    $token,
    $expira
]);

/* Link de recuperación */

$link = sprintf(
    'http://localhost%sAdmin/Restablecer-password.php?token=%s',
    BASE_URL,
    urlencode($token)
);

/* Enviar correo */

$config = require ROOT_PATH . 'Config/mail.php';

$mail = new PHPMailer(true);

try {

    $mail->isSMTP();
    $mail->Host = $config['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['username'];
    $mail->Password = $config['password'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $config['port'];

    $mail->CharSet = 'UTF-8';

    $mail->setFrom(
        $config['from_email'],
        $config['from_name']
    );

    $mail->addAddress(
        $email,
        $usuario['nombre']
    );

    $mail->isHTML(true);

    $mail->Subject = 'Restablecer contraseña';

    $mail->Body = "
        <h2>Hola {$usuario['nombre']}</h2>

        <p>Recibimos una solicitud para cambiar tu contraseña.</p>

        <p>
            <a href='{$link}'>
                Restablecer contraseña
            </a>
        </p>

        <p>Este enlace expirará en una hora.</p>
    ";

    $mail->send();

    $_SESSION['modal_exito'] = [
        'titulo' => 'Correo enviado',
        'mensaje' => 'Se ha enviado un enlace de recuperación a tu correo.'
    ];

    header('Location: ' . BASE_URL . 'Admin/Login.php');
    exit;

} catch (Exception $e) {

    die("Error al enviar el correo: " . $mail->ErrorInfo);

}