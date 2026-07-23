<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once ROOT_PATH . '/Config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = db();

$error = '';
$bloqueado = false;
$ip = $_SERVER['REMOTE_ADDR'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $usuarioLogin = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($usuarioLogin === '' || $password === '') {
        $error = 'Completa todos los campos.';
    } else {

        $stmt = $pdo->prepare("
            SELECT id, intentos, ultimo_intento
            FROM intentos_login
            WHERE usuario = ? AND ip = ?
            LIMIT 1
        ");

        $stmt->execute([$usuarioLogin, $ip]);
        $registroIntentos = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($registroIntentos) {
            $ultimoIntento = strtotime($registroIntentos['ultimo_intento']);

            // Si pasaron más de 3 minutos, eliminar el registro
            if (time() - $ultimoIntento > 120) {
                $stmt = $pdo->prepare("
                    DELETE FROM intentos_login
                    WHERE id = ?
                ");
                $stmt->execute([$registroIntentos['id']]);
                $registroIntentos = null;
            }
            elseif ($registroIntentos['intentos'] >= 5) {
                $error = 'Has excedido el número de intentos. Intenta nuevamente en 3 minutos.';
                $bloqueado = true;
            }
        }

        if (!$bloqueado && $error === '') {

            $captcha = $_POST['g-recaptcha-response'] ?? '';

            if (empty($captcha)) {
                $error = 'Debes completar el captcha.';
            } else {

                $respuesta = file_get_contents(
                    'https://www.google.com/recaptcha/api/siteverify?secret='
                    . RECAPTCHA_SECRET_KEY
                    . '&response='
                    . urlencode($captcha)
                    . '&remoteip='
                    . urlencode($ip)
                );

                $resultado = json_decode($respuesta, true);

                if (!$resultado || empty($resultado['success'])) {
                    $error = 'Captcha inválido.';
                }
            }
        }

        if (!$bloqueado && $error === '') {

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

            $stmt->execute([$usuarioLogin, $usuarioLogin]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario && password_verify($password, $usuario['password_hash'])) {

                // Login correcto: eliminar intentos fallidos
                $stmt = $pdo->prepare("
                    DELETE FROM intentos_login
                    WHERE usuario = ? AND ip = ?
                ");
                $stmt->execute([$usuarioLogin, $ip]);

                session_regenerate_id(true);

                $_SESSION['admin_id'] = $usuario['id'];
                $_SESSION['admin_nombre'] = $usuario['nombre'];
                $_SESSION['admin_email'] = $usuario['email'];
                $_SESSION['admin_rol'] = $usuario['rol'];

                header('Location: ' . BASE_URL . 'Admin/Panel-propiedades.php');
                exit;

            } else {

                // Login incorrecto: sumar intento
                if ($registroIntentos) {
                    $stmt = $pdo->prepare("
                        UPDATE intentos_login
                        SET
                            intentos = intentos + 1,
                            ultimo_intento = NOW()
                        WHERE id = ?
                    ");
                    $stmt->execute([$registroIntentos['id']]);
                } else {
                    $stmt = $pdo->prepare("
                        INSERT INTO intentos_login
                        (usuario, ip, intentos, ultimo_intento)
                        VALUES (?, ?, 1, NOW())
                    ");
                    $stmt->execute([$usuarioLogin, $ip]);
                }

                $error = 'Usuario, correo o contraseña incorrectos.';
            }
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
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
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

                <p class="login-brand-text1">
                    Primavera
                </p>

                <p class="login-brand-text2">
                    INMOBILIARIA
                </p>

            </div>

            <div class="login-container">

                <h1 id="login-title">
                    Iniciar sesión
                </h1>

                <?php if ($error !== ''): ?>

                    <div class="mensaje_error">

                        <i class="fa-solid fa-circle-exclamation"></i>

                        <span>
                            <?= e($error) ?>
                        </span>

                    </div>

                <?php endif; ?>

                <form
                    action="<?= BASE_URL ?>Login.php"
                    method="POST"
                    class="login-form"
                    autocomplete="off"
                >

                    <div class="form-group floating-group">

                        <input
                            type="text"
                            id="usuario"
                            name="usuario"
                            autocomplete="username"
                            placeholder=" "
                            required
                            value="<?= e($_POST['usuario'] ?? '') ?>"
                        >

                        <label for="usuario">
                            Usuario o correo
                        </label>

                    </div>

                    <div class="form-group floating-group">

                        <input
                            type="password"
                            id="password"
                            name="password"
                            autocomplete="current-password"
                            placeholder=" "
                            required
                        >

                        <label for="password">
                            Contraseña
                        </label>

                    </div>

                    <div class="g-recaptcha captcha"
                        data-sitekey="<?= RECAPTCHA_SITE_KEY ?>">
                    </div>

                    <div class="Login-buttons">

                        <a
                            href="<?= BASE_URL ?>index.php"
                            class="back-link"
                        >
                            Volver
                        </a>

                        <button type="submit">
                            Acceder
                        </button>

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