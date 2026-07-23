<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Verificar sesión

if (!isset($_SESSION['admin_id'])) {
    header("Location: " . BASE_URL . "Admin/Login.php");
    exit;
}

// Crear token CSRF

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Función para validar formularios

function validar_csrf()
{

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        die("Método no permitido");
    }

    if (
        !isset($_POST['csrf_token']) ||
        $_POST['csrf_token'] !== $_SESSION['csrf_token']
    ) {
        die("Solicitud inválida");
    }

}
