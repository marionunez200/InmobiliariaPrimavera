<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// =================================
// Verificar sesión
// =================================

if (!isset($_SESSION['admin_id'])) {

    header(
        "Location: " . BASE_URL . "Login.php"
    );

    exit;

}



// =================================
// Crear token CSRF
// =================================

if (!isset($_SESSION['csrf_token'])) {

    $_SESSION['csrf_token'] = bin2hex(
        random_bytes(32)
    );

}



// =================================
// Validar CSRF
// =================================

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



// =================================
// Solo administrador
// =================================

function requiere_admin()
{

    if (
        !isset($_SESSION['rol']) ||
        $_SESSION['rol'] !== 'admin'
    ) {

        die("Acceso denegado.");

    }

}



// =================================
// Solo editor o administrador
// =================================

function requiere_editor()
{

    if (
        !isset($_SESSION['rol']) ||
        !in_array(
            $_SESSION['rol'],
            ['admin','editor'],
            true
        )
    ) {

        die("Acceso denegado.");

    }

}