<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once ROOT_PATH . '/Config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = db();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $usuarioLogin = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($usuarioLogin === '' || $password === '') {

        $error = 'Completa todos los campos.';

    } else {

        /*
        Buscamos solamente el usuario.
        La contraseña NO se compara en SQL.
        */

        $stmt = $pdo->prepare("
            SELECT
                id,
                nombre,
                email,
                rol,
                activo,
                password_hash
            FROM usuarios_admin
            WHERE (email = ? OR nombre = ?)
            AND activo = 1
            LIMIT 1
        ");

        $stmt->execute([
            $usuarioLogin,
            $usuarioLogin
        ]);

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        /*
        Verificamos la contraseña usando password_verify()
        */

        if (
            $usuario &&
            password_verify($password, $usuario['password_hash'])
        ) {

            /*
            Regeneramos la sesión para evitar
            ataques de fijación de sesión
            */

            session_regenerate_id(true);

            $_SESSION['admin_id'] = $usuario['id'];
            $_SESSION['admin_nombre'] = $usuario['nombre'];
            $_SESSION['admin_email'] = $usuario['email'];
            $_SESSION['admin_rol'] = $usuario['rol'];

            header('Location: ' . BASE_URL . 'Admin/Panel-propiedades.php');
            exit;

        } else {

            $error = 'Usuario, correo o contraseña incorrectos.';

        }
    }
}
?>

<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Acceso administrativo | Primavera inmobiliaria</title>

    <meta 
        name="description" 
        content="Página de acceso administrativo para Primavera inmobiliaria."
    >

    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#ffffff">

    <link rel="stylesheet" href="<?= BASE_URL ?>CSS/Login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="icon" href="<?= BASE_URL ?>favicon.ico" type="image/x-icon">
</head>

<body class="bodymagin">

    <main class="login-main">
        <section class="login-section" aria-labelledby="login-title">

            <div class="login-brand">
                <img 
                    src="<?= BASE_URL ?>Imagenes/Logosolo.png" 
                    alt="Logo de Primavera inmobiliaria" 
                    class="login-logo"
                >

                <p class="login-brand-text1">Primavera</p>
                <p class="login-brand-text2">INMOBILIARIA</p>
            </div>

            <div class="login-container">
                    <h1 id="login-title">Iniciar sesión</h1>

                    <?php if ($error !== ''): ?>
                        <div class="mensaje_error">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            <span><?= e($error) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <form action="<?= BASE_URL ?>Admin/Login.php" method="POST" class="login-form">

                    <div class="form-group floating-group">
                        <input 
                            type="text" 
                            id="usuario" 
                            name="usuario" 
                            autocomplete="username"
                            required
                            placeholder=" "
                            value="<?= e($_POST['usuario'] ?? '') ?>"
                        >
                        <label for="usuario">Usuario o correo</label>
                    </div>

                    <div class="form-group floating-group">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            autocomplete="current-password"
                            required
                            placeholder=" "
                        >
                        <label for="password">Contraseña</label>
                    </div>

                    <div class="Login-buttons">
                        <a href="<?= BASE_URL ?>index.php" class="back-link">Volver</a>
                        <button type="submit">Acceder</button>
                    </div>

                </form>
            </div>

            <p class="olvide-password">
                <a href="<?= BASE_URL ?>Admin/Olvide-password.php">
                    ¿Olvidaste tu contraseña?
                </a>
            </p>

        </section>
    </main>

    <div class="login-footer">
        <p class="login-footer-text">
            &copy; 2026 Primavera inmobiliaria. Todos los derechos reservados.
        </p>
    </div>

</body>
</html>
