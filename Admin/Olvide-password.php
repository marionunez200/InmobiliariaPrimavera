
<?php

if (!defined('BASE_URL')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>

<!DOCTYPE html>
<html lang="es-MX">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Recuperar contraseña | Primavera</title>

    <link rel="stylesheet" href="<?= BASE_URL ?>CSS/Recuperar-password.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="icon" href="<?= BASE_URL ?>favicon.ico" type="image/x-icon">

</head>

<body>

    <main class="login-container">

        <form
            class="login-form"
            action="<?= BASE_URL ?>Backend/olvide-password.php"
            method="POST">

            <h1>¿Olvidaste tu contraseña?</h1>

            <p>
                Escribe el correo asociado a tu cuenta de administrador.
                Si existe, recibirás un enlace para restablecer tu contraseña.
            </p>

            <label for="email">
                Correo electrónico
            </label>

            <input
                type="email"
                id="email"
                name="email"
                placeholder="correo@ejemplo.com"
                required
            >

            <button type="submit">
                Enviar enlace
            </button>

            <a
                href="<?= BASE_URL ?>Admin/Login.php"
                style="display:block; margin-top:20px; text-align:center;">
                Volver al inicio de sesión
            </a>

        </form>

    </main>

</body>

</html>
