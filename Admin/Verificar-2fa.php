
<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* Si no viene del login, regresar */
if (empty($_SESSION['2fa_user'])) {
    header('Location: ' . BASE_URL . 'Login.php');
    exit;
}

$error = $_SESSION['2fa_error'] ?? '';
unset($_SESSION['2fa_error']);

?>

<!DOCTYPE html>
<html lang="es-MX">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Verificación en dos pasos</title>

    <link rel="stylesheet" href="<?= BASE_URL ?>CSS/Login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</head>

<body class="bodymagin">

<main class="login-main">

    <section class="login-section">

        <div class="login-container">

            <h1>Verificación en dos pasos</h1>

            <p>
                Abre Microsoft Authenticator o Google Authenticator e
                introduce el código de 6 dígitos.
            </p>

            <?php if ($error): ?>

                <div class="mensaje_error">

                    <i class="fa-solid fa-circle-exclamation"></i>

                    <span>
                        <?= e($error) ?>
                    </span>

                </div>

            <?php endif; ?>

            <form
                action="<?= BASE_URL ?>Backend/Seguridad-2fa/verificar-2fa.php"
                method="POST"
                class="login-form">

                <div class="form-group floating-group">

                    <input
                        type="text"
                        name="codigo"
                        id="codigo"
                        maxlength="6"
                        pattern="[0-9]{6}"
                        placeholder=" "
                        required>

                    <label for="codigo">
                        Código de autenticación
                    </label>

                </div>

                <button type="submit">
                    Verificar
                </button>

            </form>

        </div>

    </section>

</main>

</body>

</html>