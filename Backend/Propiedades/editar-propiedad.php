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
$propiedad_id = (int)$id;

if (!usuarioPuedeModificarPropiedad($pdo, $propiedad_id)) {
    die('No tienes permiso para editar esta propiedad.');
}

$agente_id = ($_SESSION['rol'] === 'editor') ? (int)$_SESSION['id_agente'] : (int)($_POST['agente_id'] ?? 0);

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
$direccion_completa = trim($_POST['direccion_completa'] ?? '') ?: null;
$google_maps_url = trim($_POST['google_maps_url'] ?? '') ?: null;
$recamaras = max(0, (int)($_POST['recamaras'] ?? 0));
$banos = max(0, (float)($_POST['banos'] ?? 0));
$estacionamientos = max(0, (int)($_POST['estacionamientos'] ?? 0));
$terreno_m2 = ($_POST['terreno_m2'] ?? '') !== '' ? $_POST['terreno_m2'] : null;
$construccion_m2 = ($_POST['construccion_m2'] ?? '') !== '' ? $_POST['construccion_m2'] : null;
$destacada = isset($_POST['destacada']) ? 1 : 0;

if ($agente_id <= 0) {
    die('Debes seleccionar un agente.');
}

$stmtCategoria = $pdo->prepare("SELECT COUNT(*) FROM categorias_propiedad WHERE id = ? AND activo = 1");
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

try {
    $pdo->beginTransaction();

    $sql = "
        UPDATE propiedades SET
            agente_id = ?, titulo = ?, descripcion = ?, precio = ?, moneda = ?, 
            tipo_operacion = ?, categoria_id = ?, estado_publicacion = ?, destacada = ?, 
            ciudad_id = ?, direccion_completa = ?, google_maps_url = ?, recamaras = ?, 
            banos = ?, estacionamientos = ?, terreno_m2 = ?, construccion_m2 = ?
        WHERE id = ?
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $agente_id, $titulo, $descripcion, $precio, $moneda,
        $tipo_operacion, $categoria_id, $estado_publicacion, $destacada,
        $ciudad_id, $direccion_completa, $google_maps_url, $recamaras,
        $banos, $estacionamientos, $terreno_m2, $construccion_m2,
        $propiedad_id
    ]);

    if (!empty($_POST['eliminar_imagenes']) && is_array($_POST['eliminar_imagenes'])) {
        eliminarImagenesSeleccionadas($pdo, $propiedad_id, $_POST['eliminar_imagenes']);
    }

    $ordenJson = $_POST['orden_imagenes'] ?? '[]';
    $estructuraOrden = json_decode($ordenJson, true) ?: [];

    $stmtUpdateExistente = $pdo->prepare("
        UPDATE multimedia_propiedades 
        SET orden = ?, es_principal = ? 
        WHERE id = ? AND propiedad_id = ?
    ");

    $stmtInsertNueva = $pdo->prepare("
        INSERT INTO multimedia_propiedades 
        (propiedad_id, tipo, archivo_url, texto_alternativo, es_principal, orden) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $archivosNuevos   = $_FILES['imagenes'] ?? null;
    $indiceNuevos     = 0; 
    $extensionesVideo = ['mp4', 'webm', 'mov', 'avi'];
    $maxSizeImagen    = 10 * 1024 * 1024; 
    $maxSizeVideo     = 15 * 1024 * 1024; 

    if (!is_dir(ROOT_PATH . '/Uploads/propiedades/')) {
        mkdir(ROOT_PATH . '/Uploads/propiedades/', 0755, true);
    }

    foreach ($estructuraOrden as $posicion => $item) {
        $ordenCalculado = $posicion + 1;
        $esPrincipal = ($posicion === 0) ? 1 : 0;
        $tipoItem = $item['tipo'] ?? '';

        if ($tipoItem === 'existente' && !empty($item['id'])) {
            $stmtUpdateExistente->execute([
                $ordenCalculado,
                $esPrincipal,
                (int)$item['id'],
                $propiedad_id
            ]);
        } 
        elseif ($tipoItem === 'nuevo') {
            if ($archivosNuevos && isset($archivosNuevos['name'][$indiceNuevos]) && $archivosNuevos['error'][$indiceNuevos] === UPLOAD_ERR_OK) {
                
                $tmpName      = $archivosNuevos['tmp_name'][$indiceNuevos];
                $nameOriginal = $archivosNuevos['name'][$indiceNuevos];
                $ext          = strtolower(pathinfo($nameOriginal, PATHINFO_EXTENSION));
                $tamano       = $archivosNuevos['size'][$indiceNuevos];
                
                $tipo = in_array($ext, $extensionesVideo, true) ? 'video' : 'imagen';

                if ($tipo === 'video' && $tamano > $maxSizeVideo) {
                    throw new Exception("El video '$nameOriginal' excede el tamaño máximo permitido (15 MB).");
                }
                if ($tipo === 'imagen' && $tamano > $maxSizeImagen) {
                    throw new Exception("La imagen '$nameOriginal' excede el tamaño máximo permitido (10 MB).");
                }

                if ($tipo === 'video') {
                    $nombreArchivo = 'propiedad-' . $propiedad_id . '-' . uniqid() . '.' . $ext;
                    $rutaRelativa  = 'Uploads/propiedades/' . $nombreArchivo;
                    $rutaDestino   = ROOT_PATH . '/' . $rutaRelativa;

                    if (move_uploaded_file($tmpName, $rutaDestino)) {
                        $stmtInsertNueva->execute([
                            $propiedad_id, $tipo, $rutaRelativa, $titulo, $esPrincipal, $ordenCalculado
                        ]);
                    }
                } else {
                    $nombreArchivo = 'propiedad-' . $propiedad_id . '-' . uniqid() . '.webp';
                    $rutaRelativa  = 'Uploads/propiedades/' . $nombreArchivo;
                    $rutaDestino   = ROOT_PATH . '/' . $rutaRelativa;

                    if (function_exists('convertirAWebp') && convertirAWebp($tmpName, $rutaDestino, $ext)) {
                        $stmtInsertNueva->execute([
                            $propiedad_id, $tipo, $rutaRelativa, $titulo, $esPrincipal, $ordenCalculado
                        ]);
                    } else {
                        $nombreArchivoFallback = 'propiedad-' . $propiedad_id . '-' . uniqid() . '.' . $ext;
                        $rutaRelativaFallback  = 'Uploads/propiedades/' . $nombreArchivoFallback;
                        if (move_uploaded_file($tmpName, ROOT_PATH . '/' . $rutaRelativaFallback)) {
                            $stmtInsertNueva->execute([
                                $propiedad_id, $tipo, $rutaRelativaFallback, $titulo, $esPrincipal, $ordenCalculado
                            ]);
                        }
                    }
                }
            }
            $indiceNuevos++;
        }
    }

    $stmtValidarTotal = $pdo->prepare("SELECT COUNT(*) FROM multimedia_propiedades WHERE propiedad_id = ?");
    $stmtValidarTotal->execute([$propiedad_id]);

    if ((int)$stmtValidarTotal->fetchColumn() === 0) {
        throw new Exception('La propiedad debe tener al menos una imagen.');
    }

    $pdo->commit();

    $_SESSION['modal_exito'] = [
        'titulo' => 'Cambios guardados',
        'mensaje' => 'La información de la propiedad se actualizó correctamente.'
    ];

    header('Location: ' . BASE_URL . 'Admin/Panel-propiedades.php');
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Error editar propiedad: ' . $e->getMessage());

    $_SESSION['modal_exito'] = [
        'titulo' => 'Error',
        'mensaje' => 'No fue posible guardar la propiedad: ' . $e->getMessage()
    ];

    header('Location: ' . BASE_URL . 'Admin/Panel-propiedades.php');
    exit;
}