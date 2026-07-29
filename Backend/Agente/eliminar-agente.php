<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once ROOT_PATH . '/Config/database.php';
require_once ROOT_PATH . '/Admin/auth.php';
requiere_admin();
validar_csrf();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$pdo = db();


$id = (int)($_POST['id'] ?? 0);


if ($id <= 0) {
    die('ID no válido.');
}



try {


    $pdo->beginTransaction();



    // ===================================
    // Buscar información del agente
    // ===================================

    $stmt = $pdo->prepare("
        SELECT 
            usuario_id,
            foto_url
        FROM agentes
        WHERE id = ?
        LIMIT 1
    ");


    $stmt->execute([$id]);


    $agente = $stmt->fetch(PDO::FETCH_ASSOC);



    if (!$agente) {

        die('El agente no existe.');

    }



    $usuarioId = $agente['usuario_id'];
    $foto = $agente['foto_url'];





    // ===================================
    // Revisar propiedades asignadas
    // ===================================


    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM propiedades
        WHERE agente_id = ?
    ");


    $stmt->execute([$id]);


    $totalPropiedades = (int)$stmt->fetchColumn();





    // ===================================
    // SI TIENE PROPIEDADES
    // SOLO DESACTIVAR
    // ===================================


    if ($totalPropiedades > 0) {



        $stmt = $pdo->prepare("
            UPDATE agentes
            SET activo = 0
            WHERE id = ?
        ");


        $stmt->execute([$id]);




        // Desactivar también el acceso

        $stmt = $pdo->prepare("
            UPDATE usuarios_admin
            SET activo = 0
            WHERE id = ?
        ");


        $stmt->execute([$usuarioId]);





        $_SESSION['modal_exito'] = [

            'titulo' => 'Agente desactivado',

            'mensaje' =>
                'El agente tiene propiedades asignadas, por lo que fue desactivado.'

        ];



    } else {



        // ===================================
        // ELIMINAR FOTO FÍSICA
        // ===================================


        if (
            $foto &&
            str_starts_with($foto, 'Uploads/agentes/')
        ) {


            $rutaFoto = ROOT_PATH . '/' . $foto;


            if (is_file($rutaFoto)) {

                unlink($rutaFoto);

            }

        }





        // ===================================
        // ELIMINAR AGENTE
        // ===================================


        $stmt = $pdo->prepare("
            DELETE FROM agentes
            WHERE id = ?
        ");


        $stmt->execute([$id]);






        // ===================================
        // ELIMINAR USUARIO ADMIN
        // ===================================


        $stmt = $pdo->prepare("
            DELETE FROM usuarios_admin
            WHERE id = ?
        ");


        $stmt->execute([$usuarioId]);






        $_SESSION['modal_exito'] = [

            'titulo' => 'Agente eliminado',

            'mensaje' =>
                'El agente, usuario y fotografía fueron eliminados correctamente.'

        ];



    }




    $pdo->commit();





    header(
        'Location: ' . BASE_URL . 'Admin/Panel-agente.php'
    );


    exit;




} catch(Exception $e) {



    if($pdo->inTransaction()) {

        $pdo->rollBack();

    }



    error_log(
        "Error eliminar agente: " . $e->getMessage()
    );



    die(
        'Ocurrió un error al eliminar el agente.'
    );

}