<?php
if (!defined('BASE_URL')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
}
require_once ROOT_PATH . '/Config/database.php';
require_once ROOT_PATH . '/Admin/auth.php';
require_once ROOT_PATH . '/Backend/Propiedades/funciones-propiedad.php';
validar_csrf();
requiere_editor();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = db();

$id = trim($_POST['id'] ?? '');

if ($id === '') {
    die('No se recibió el ID de la propiedad.');
}

if ($_SESSION['rol'] === 'editor') {
    $stmtPropiedad = $pdo->prepare("
        SELECT id
        FROM propiedades
        WHERE id = ?
        AND agente_id = ?
    ");

    $stmtPropiedad->execute([
        $id,
        $_SESSION['id_agente']
    ]);

    if (!$stmtPropiedad->fetch()) {
        die('No tienes permiso para editar esta propiedad.');
    }

    $agente_id = (int)$_SESSION['id_agente'];
} else {
    $agente_id = (int)($_POST['agente_id'] ?? 0);
}

$titulo = trim($_POST['titulo'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$precio = (float)($_POST['precio'] ?? 0);

if ($precio <= 0) {
    die('El precio debe ser mayor que cero.');
}

$moneda = trim($_POST['moneda'] ?? 'MXN');
$tipo_operacion = $_POST['tipo_operacion'] ?? 'venta';
$categoria_id = (int)($_POST['categoria_id'] ?? 0);
$estado_publicacion = $_POST['estado_publicacion'] ?? 'activo';
$ciudad_id = (int)($_POST['ciudad_id'] ?? 0);

$direccion_completa = trim($_POST['direccion_completa'] ?? '');
$direccion_completa = $direccion_completa === '' ? null : $direccion_completa;
$google_maps_url = trim($_POST['google_maps_url'] ?? '');
$google_maps_url = $google_maps_url === '' ? null : $google_maps_url;

$recamaras = max(0, (int)($_POST['recamaras'] ?? 0));
$banos = max(0, (float)($_POST['banos'] ?? 0));
$estacionamientos = max(0, (int)($_POST['estacionamientos'] ?? 0));

$terreno_m2 = ($_POST['terreno_m2'] ?? '') !== '' ? $_POST['terreno_m2'] : null;
$construccion_m2 = ($_POST['construccion_m2'] ?? '') !== '' ? $_POST['construccion_m2'] : null;

$destacada = isset($_POST['destacada']) ? 1 : 0;

if ($agente_id <= 0) {
    die('Debes seleccionar un agente.');
}

$stmtCategoria = $pdo->prepare("
    SELECT COUNT(*)
    FROM categorias_propiedad
    WHERE id = ?
    AND activo = 1
");
$stmtCategoria->execute([$categoria_id]);

if ((int)$stmtCategoria->fetchColumn() === 0) {
    die('La categoría seleccionada no existe.');
}

if ($ciudad_id <= 0) {
    die('Debes seleccionar una ciudad válida.');
}

if ($titulo === '') {
    die('El título es obligatorio.');
}

if (!in_array($tipo_operacion, ['venta', 'renta', 'traspaso'], true)) {
    die('Tipo de operación inválido.');
}

if (!in_array($estado_publicacion, ['activo', 'inactivo', 'promesa', 'vendido', 'rentado', 'traspasado'], true)) {
    die('Estado de publicación inválido.');
}

try {
    $pdo->beginTransaction();

    if ($_SESSION['rol'] === 'editor') {
        $sql = "
            UPDATE propiedades SET
                agente_id = ?,
                titulo = ?,
                descripcion = ?,
                precio = ?,
                moneda = ?,
                tipo_operacion = ?,
                categoria_id = ?,
                estado_publicacion = ?,
                destacada = ?,
                ciudad_id = ?,
                direccion_completa = ?,
                google_maps_url = ?,
                recamaras = ?,
                banos = ?,
                estacionamientos = ?,
                terreno_m2 = ?,
                construccion_m2 = ?
            WHERE id = ?
            AND agente_id = ?
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $agente_id, $titulo, $descripcion, $precio, $moneda,
            $tipo_operacion, $categoria_id, $estado_publicacion, $destacada,
            $ciudad_id, $direccion_completa, $google_maps_url,
            $recamaras, $banos, $estacionamientos, $terreno_m2, $construccion_m2,
            $id, $_SESSION['id_agente']
        ]);
    } else {
        $sql = "
            UPDATE propiedades SET
                agente_id = ?,
                titulo = ?,
                descripcion = ?,
                precio = ?,
                moneda = ?,
                tipo_operacion = ?,
                categoria_id = ?,
                estado_publicacion = ?,
                destacada = ?,
                ciudad_id = ?,
                direccion_completa = ?,
                google_maps_url = ?,
                recamaras = ?,
                banos = ?,
                estacionamientos = ?,
                terreno_m2 = ?,
                construccion_m2 = ?
            WHERE id = ?
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $agente_id, $titulo, $descripcion, $precio, $moneda,
            $tipo_operacion, $categoria_id, $estado_publicacion, $destacada,
            $ciudad_id, $direccion_completa, $google_maps_url,
            $recamaras, $banos, $estacionamientos, $terreno_m2, $construccion_m2,
            $id
        ]);
    }

    $propiedad_id = (int)$id;

    /* =========================================================
       1. ELIMINAR IMÁGENES SELECCIONADAS
       ========================================================= */
    if (!empty($_POST['eliminar_imagenes']) && is_array($_POST['eliminar_imagenes'])) {
        $stmtRuta = $pdo->prepare("SELECT imagen_url FROM imagenes_propiedades WHERE id = ? AND propiedad_id = ?");
        $stmtDel  = $pdo->prepare("DELETE FROM imagenes_propiedades WHERE id = ? AND propiedad_id = ?");

        foreach ($_POST['eliminar_imagenes'] as $imgId) {
            $imgId = (int)$imgId;
            $stmtRuta->execute([$imgId, $propiedad_id]);
            $img = $stmtRuta->fetch();

            if ($img) {
                $archivoFisico = ROOT_PATH . '/' . $img['imagen_url'];
                if (file_exists($archivoFisico)) {
                    @unlink($archivoFisico);
                }
                $stmtDel->execute([$imgId, $propiedad_id]);
            }
        }
    }

    /* =========================================================
       2. REORDENAR Y GUARDAR NUEVAS IMÁGENES (VÍA JSON)
       ========================================================= */
    $ordenJson = $_POST['orden_imagenes'] ?? '[]';
    $estructuraOrden = json_decode($ordenJson, true) ?: [];

    $stmtUpdateExistente = $pdo->prepare("
        UPDATE imagenes_propiedades 
        SET orden = ?, es_principal = ? 
        WHERE id = ? AND propiedad_id = ?
    ");

    $stmtInsertNueva = $pdo->prepare("
        INSERT INTO imagenes_propiedades 
        (propiedad_id, imagen_url, texto_alternativo, es_principal, orden) 
        VALUES (?, ?, ?, ?, ?)
    ");

    $archivosNuevos = $_FILES['imagenes'] ?? null;
    $indiceNuevos = 0;

    foreach ($estructuraOrden as $posicion => $item) {
        $ordenCalculado = $posicion + 1;
        $esPrincipal = ($posicion === 0) ? 1 : 0;

        if ($item['tipo'] === 'existente' && !empty($item['id'])) {
            $stmtUpdateExistente->execute([
                $ordenCalculado,
                $esPrincipal,
                (int)$item['id'],
                $propiedad_id
            ]);
        } 
        elseif ($item['tipo'] === 'nuevo' && $archivosNuevos) {
            if (isset($archivosNuevos['name'][$indiceNuevos]) && $archivosNuevos['error'][$indiceNuevos] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($archivosNuevos['name'][$indiceNuevos], PATHINFO_EXTENSION));
                $nombreArchivo = 'propiedad-' . $propiedad_id . '-' . uniqid() . '.' . $ext;
                $rutaRelativa = 'Uploads/propiedades/' . $nombreArchivo;
                $rutaDestino = ROOT_PATH . '/' . $rutaRelativa;

                if (move_uploaded_file($archivosNuevos['tmp_name'][$indiceNuevos], $rutaDestino)) {
                    $stmtInsertNueva->execute([
                        $propiedad_id,
                        $rutaRelativa,
                        $titulo,
                        $esPrincipal,
                        $ordenCalculado
                    ]);
                }
            }
            $indiceNuevos++;
        }
    }

    $pdo->commit();

    $_SESSION['modal_exito'] = [
        'titulo' => 'Cambios guardados',
        'mensaje' => 'La información de la propiedad se actualizó correctamente.'
    ];

    header('Location: ' . BASE_URL . 'Admin/Panel-propiedades.php');
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    error_log($e->getMessage());

    $_SESSION['modal_exito'] = [
        'titulo' => 'Error',
        'mensaje' => 'No fue posible guardar la propiedad.'
    ];

    header('Location: ' . BASE_URL . 'Admin/Panel-propiedades.php');
    exit;
}