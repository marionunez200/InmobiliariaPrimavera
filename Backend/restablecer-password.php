<?php

if (!defined('BASE_URL')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
}

require_once ROOT_PATH . '/Config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = db();

$token = trim($_POST['token'] ?? '');
$password = $_POST['password'] ?? '';
$confirmar = $_POST['confirmar_password'] ?? '';

if ($token === '') {
    die('Token inválido.');
}

if ($password === '' || $confirmar === '') {
    die('Debes completar todos los campos.');
}

if ($password !== $confirmar) {
    die('Las contraseñas no coinciden.');
}

if (strlen($password) < 8) {
    die('La contraseña debe tener al menos 8 caracteres.');
}

try {

    $stmt = $pdo->prepare("
        SELECT *
        FROM password_resets
        WHERE token = ?
        LIMIT 1
    ");

    $stmt->execute([$token]);

    $reset = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reset) {
        die('El enlace no es válido.');
    }

    if ((int)$reset['usado'] === 1) {
        die('Este enlace ya fue utilizado.');
    }

    if (strtotime($reset['expiracion']) < time()) {
        die('El enlace ha expirado.');
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        UPDATE usuarios_admin
        SET password_hash = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $passwordHash,
        $reset['usuario_id']
    ]);

    $stmt = $pdo->prepare("
        UPDATE password_resets
        SET usado = 1
        WHERE id = ?
    ");

    $stmt->execute([
        $reset['id']
    ]);

    $_SESSION['modal_exito'] = [
        'titulo' => 'Contraseña actualizada',
        'mensaje' => 'Ahora puedes iniciar sesión con tu nueva contraseña.'
    ];

    header('Location: ' . BASE_URL . 'Admin/Login.php');
    exit;

} catch (Exception $e) {

    error_log($e->getMessage());

    die('Ocurrió un error al actualizar la contraseña.');
}