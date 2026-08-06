<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once ROOT_PATH . '/Admin/auth.php';
require_once ROOT_PATH . '/Config/database.php';
require_once ROOT_PATH . '/Backend/Agente/funciones-agente.php';

requiere_admin();
validar_csrf();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = db();


// ===============================
// RECIBIR DATOS
// ===============================

$nombre = trim($_POST['nombre'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');

$email = strtolower(
    trim($_POST['email'] ?? '')
);

$password = trim($_POST['password'] ?? '');

$rol = $_POST['rol'] ?? 'editor';

$activo = isset($_POST['activo'])
    ? (int)$_POST['activo']
    : 1;



// ===============================
// VALIDACIONES
// ===============================

if ($nombre === '') {
    die('El nombre es obligatorio.');
}


if ($email === '') {
    die('El correo es obligatorio.');
}


if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('Correo inválido.');
}


if ($password === '') {
    die('La contraseña es obligatoria.');
}


if (strlen($password) < 8) {
    die('La contraseña debe tener mínimo 8 caracteres.');
}



$rolesPermitidos = [
    'admin',
    'editor'
];


if (!in_array($rol, $rolesPermitidos, true)) {
    die('Rol inválido.');
}



if (!in_array($activo, [0, 1], true)) {
    $activo = 1;
}



try {


    // ===============================
    // VERIFICAR CORREO
    // ===============================


    $stmt = $pdo->prepare("
        SELECT id
        FROM usuarios_admin
        WHERE email = ?
        LIMIT 1
    ");

    $stmt->execute([
        $email
    ]);


    if ($stmt->fetch()) {
        die('Ese correo ya existe.');
    }



    $pdo->beginTransaction();



    // ===============================
    // CREAR USUARIO
    // ===============================


    $passwordHash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );


    $stmt = $pdo->prepare("
        INSERT INTO usuarios_admin
        (
            nombre,
            email,
            password_hash,
            rol,
            activo
        )
        VALUES
        (
            ?, ?, ?, ?, ?
        )
    ");



    $stmt->execute([
        $nombre,
        $email,
        $passwordHash,
        $rol,
        $activo
    ]);



    $usuarioId = $pdo->lastInsertId();



    // ===============================
    // CREAR AGENTE (PARA CUALQUIER ROL)
    // ===============================

    $foto = subirFotoAgente(null);

    if (!$foto) {
        $foto = 'Imagenes/agente1.webp';
    }



    $stmt = $pdo->prepare("
        INSERT INTO agentes
        (
            usuario_id,
            nombre,
            telefono,
            email,
            foto_url,
            activo
        )
        VALUES
        (
            ?, ?, ?, ?, ?, ?
        )
    ");



    $stmt->execute([
        $usuarioId,
        $nombre,
        $telefono,
        $email,
        $foto,
        $activo
    ]);



    $pdo->commit();



    $_SESSION['modal_exito'] = [
        'titulo' => 'Usuario creado',
        'mensaje' => ($rol === 'editor')
            ? 'El agente se agregó correctamente.'
            : 'El administrador y su agente se agregaron correctamente.'
    ];



    header(
        'Location: ' . BASE_URL . 'Admin/Panel-agente.php'
    );

    exit;
} catch (Exception $e) {


    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }


    // MODO PRUEBA
    die(
        "Error BD: " . $e->getMessage()
    );
}