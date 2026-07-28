<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once ROOT_PATH . '/Admin/auth.php';
require_once ROOT_PATH . '/Config/database.php';
require_once ROOT_PATH . '/Backend/Agente/funciones-agente.php';

validar_csrf();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$pdo = db();


$nombre = trim($_POST['nombre'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$rol = $_POST['rol'] ?? 'editor';
$activo = isset($_POST['activo']) ? (int)$_POST['activo'] : 1;



if ($nombre === '') {
    die('El nombre es obligatorio.');
}


if ($email === '') {
    die('El correo es obligatorio.');
}


if ($password === '') {
    die('La contraseña es obligatoria.');
}



$rolesPermitidos = [
    'admin',
    'editor'
];


if (!in_array($rol, $rolesPermitidos)) {
    die('Rol no válido.');
}



try {

    $pdo->beginTransaction();

    /*
        Crear usuario
    */

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
        VALUES (?, ?, ?, ?, ?)
    ");


    $stmt->execute([
        $nombre,
        $email,
        $passwordHash,
        $rol,
        $activo
    ]);



    $usuarioId = $pdo->lastInsertId();





    /*
        Si es editor creamos perfil de agente
    */


    if ($rol === 'editor') {


        $nuevaFoto = subirFotoAgente(null);


        $fotoFinal = $nuevaFoto 
            ?: 'Imagenes/agente1.webp';



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
            VALUES (?, ?, ?, ?, ?, ?)
        ");


        $stmt->execute([

            $usuarioId,
            $nombre,
            $telefono,
            $email,
            $fotoFinal,
            $activo

        ]);

    }



    $pdo->commit();



    $_SESSION['modal_exito'] = [

        'titulo' => 'Usuario creado',

        'mensaje' => $rol === 'editor'
            ? 'El agente se agregó correctamente.'
            : 'El administrador se agregó correctamente.'

    ];



    header(
        'Location: ' . BASE_URL . 'Admin/Panel-agente.php'
    );

    exit;



} catch(Exception $e) {


    if($pdo->inTransaction()){
        $pdo->rollBack();
    }


    die(
        'Error al guardar usuario: ' 
        . $e->getMessage()
    );

}