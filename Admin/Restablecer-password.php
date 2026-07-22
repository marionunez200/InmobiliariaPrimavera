<?php

if (!defined('BASE_URL')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$token = trim($_GET['token'] ?? '');

if ($token === '') {
    die('Token inválido.');
}

?>

<!DOCTYPE html>
<html lang="es-MX">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Restablecer contraseña | Primavera</title>

    <link rel="stylesheet" href="<?= BASE_URL ?>CSS/Recuperar-password.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="icon" href="<?= BASE_URL ?>favicon.ico" type="image/x-icon">

</head>

<body>

    <main class="login-container">

        <form
            class="login-form"
            action="<?= BASE_URL ?>Backend/restablecer-password.php"
            method="POST">

            <h1>Restablecer contraseña</h1>

            <p>
                Ingresa una nueva contraseña para tu cuenta.
            </p>

            <input
                type="hidden"
                name="token"
                value="<?= htmlspecialchars($token) ?>">

            <label for="password">
                Nueva contraseña
            </label>

            <input
                type="password"
                id="password"
                name="password"
                minlength="8"
                required>

            <label for="confirmar_password">
                Confirmar contraseña
            </label>

            <input
                type="password"
                id="confirmar_password"
                name="confirmar_password"
                minlength="8"
                required>

            <button type="submit">
                Cambiar contraseña
            </button>

            <a
                href="<?= BASE_URL ?>Admin/Login.php"
                style="margin-top:20px; text-align:center;">
                Volver al inicio de sesión
            </a>

        </form>

    </main>

</body>

</html>