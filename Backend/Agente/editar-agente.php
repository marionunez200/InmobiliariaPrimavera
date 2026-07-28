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


$id = (int)($_POST['id'] ?? 0);

$nombre = trim($_POST['nombre'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$rol = $_POST['rol'] ?? 'editor';
$activo = isset($_POST['activo']) ? (int)$_POST['activo'] : 1;



if ($id <= 0) {
    die('ID inválido.');
}


if ($nombre === '') {
    die('El nombre es obligatorio.');
}



try {


    $pdo->beginTransaction();



    /*
        Obtener usuario relacionado
    */

    $stmt = $pdo->prepare("
        SELECT usuario_id, foto_url
        FROM agentes
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$id]);

    $agente = $stmt->fetch(PDO::FETCH_ASSOC);


    if (!$agente) {
        throw new Exception('El agente no existe.');
    }



    $usuarioId = $agente['usuario_id'];



    /*
        Actualizar usuario
    */


    if ($password !== '') {


        $passwordHash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );


        $stmt = $pdo->prepare("
            UPDATE usuarios_admin SET
                nombre = ?,
                email = ?,
                password_hash = ?,
                rol = ?,
                activo = ?
            WHERE id = ?
        ");


        $stmt->execute([
            $nombre,
            $email,
            $passwordHash,
            $rol,
            $activo,
            $usuarioId
        ]);



    } else {


        $stmt = $pdo->prepare("
            UPDATE usuarios_admin SET
                nombre = ?,
                email = ?,
                rol = ?,
                activo = ?
            WHERE id = ?
        ");


        $stmt->execute([
            $nombre,
            $email,
            $rol,
            $activo,
            $usuarioId
        ]);

    }




    /*
        Actualizar foto
    */


    $nuevaFoto = subirFotoAgente($agente['foto_url']);


    $fotoFinal = $nuevaFoto ?: $agente['foto_url'];




    /*
        Actualizar perfil agente
    */


    $stmt = $pdo->prepare("
        UPDATE agentes SET
            nombre = ?,
            telefono = ?,
            email = ?,
            foto_url = ?,
            activo = ?
        WHERE id = ?
    ");



    $stmt->execute([

        $nombre,
        $telefono,
        $email,
        $fotoFinal,
        $activo,
        $id

    ]);



    $pdo->commit();



    $_SESSION['modal_exito'] = [
        'titulo' => 'Cambios guardados',
        'mensaje' => 'La información del agente se actualizó correctamente.'
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
        'Error al editar agente: ' 
        . $e->getMessage()
    );

}