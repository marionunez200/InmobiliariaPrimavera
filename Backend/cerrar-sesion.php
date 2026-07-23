
<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once ROOT_PATH . '/Admin/auth.php';
validar_csrf();


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* Vaciar la sesión */
$_SESSION = [];

/* Eliminar la cookie de sesión */
if (ini_get("session.use_cookies")) {

    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

/* Destruir la sesión */
session_destroy();

/* Regresar al login */
header('Location: ' . BASE_URL . 'Login.php');
exit;