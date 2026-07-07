<?php
if (!defined('BASE_URL')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
}
require_once ROOT_PATH . '/Config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = db();

function crearSlugPanel(string $texto): string
{
    $texto = strtolower(trim($texto));

    $texto = str_replace(
        ['á', 'é', 'í', 'ó', 'ú', 'ñ'],
        ['a', 'e', 'i', 'o', 'u', 'n'],
        $texto
    );

    $texto = preg_replace('/[^a-z0-9]+/', '-', $texto);
    $texto = trim($texto, '-');

    if ($texto === '') {
        $texto = 'propiedad';
    }

    return $texto . '-' . substr(uniqid(), -6);
}

function propiedadTieneImagenPrincipal(PDO $pdo, int $propiedad_id): bool
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM imagenes_propiedades
        WHERE propiedad_id = ?
        AND es_principal = 1
    ");

    $stmt->execute([$propiedad_id]);

    return (int)$stmt->fetchColumn() > 0;
}

function guardarImagenesPropiedad(PDO $pdo, int $propiedad_id, string $titulo, bool $reemplazar = false): void
{
    if (
        empty($_FILES['imagenes']) ||
        empty($_FILES['imagenes']['name']) ||
        !is_array($_FILES['imagenes']['name'])
    ) {
        return;
    }

    $carpetaUploads = ROOT_PATH . '/Uploads/propiedades/';

    if (!is_dir($carpetaUploads)) {
        mkdir($carpetaUploads, 0777, true);
    }

    if ($reemplazar) {
        $stmtImagenes = $pdo->prepare("
            SELECT imagen_url
            FROM imagenes_propiedades
            WHERE propiedad_id = ?
        ");

        $stmtImagenes->execute([$propiedad_id]);
        $imagenesViejas = $stmtImagenes->fetchAll();

        foreach ($imagenesViejas as $imagen) {
            $ruta = (string)$imagen['imagen_url'];

            if (str_starts_with($ruta, 'Uploads/propiedades/')) {
                $rutaServidor = ROOT_PATH . '/' . $ruta;

                if (is_file($rutaServidor)) {
                    unlink($rutaServidor);
                }
            }
        }

        $stmtDelete = $pdo->prepare("
            DELETE FROM imagenes_propiedades
            WHERE propiedad_id = ?
        ");

        $stmtDelete->execute([$propiedad_id]);
    }

    $hayPrincipal = propiedadTieneImagenPrincipal($pdo, $propiedad_id);

    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];
    $maxSize = 5 * 1024 * 1024;

    $total = count($_FILES['imagenes']['name']);

    for ($i = 0; $i < $total; $i++) {
        $nombreOriginal = $_FILES['imagenes']['name'][$i];
        $tmpName = $_FILES['imagenes']['tmp_name'][$i];
        $error = $_FILES['imagenes']['error'][$i];
        $size = $_FILES['imagenes']['size'][$i];

        if ($error === UPLOAD_ERR_NO_FILE) {
            continue;
        }

        if ($error !== UPLOAD_ERR_OK) {
            throw new Exception('Error al subir una imagen.');
        }

        if ($size > $maxSize) {
            throw new Exception('Una imagen supera los 5 MB.');
        }

        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

        if (!in_array($extension, $extensionesPermitidas, true)) {
            throw new Exception('Solo se permiten imágenes JPG, JPEG, PNG o WEBP.');
        }

        $nombreNuevo = 'propiedad-' . $propiedad_id . '-' . uniqid() . '.' . $extension;

        $rutaDestinoServidor = $carpetaUploads . $nombreNuevo;
        $rutaParaBaseDatos = 'Uploads/propiedades/' . $nombreNuevo;

        if (!move_uploaded_file($tmpName, $rutaDestinoServidor)) {
            throw new Exception('No se pudo guardar la imagen en la carpeta Uploads.');
        }

        $esPrincipal = $hayPrincipal ? 0 : 1;

        $stmtOrden = $pdo->prepare("
            SELECT COALESCE(MAX(orden), 0) + 1
            FROM imagenes_propiedades
            WHERE propiedad_id = ?
        ");

        $stmtOrden->execute([$propiedad_id]);
        $orden = (int)$stmtOrden->fetchColumn();

        $stmtImg = $pdo->prepare("
            INSERT INTO imagenes_propiedades (
                propiedad_id,
                imagen_url,
                texto_alternativo,
                es_principal,
                orden
            ) VALUES (?, ?, ?, ?, ?)
        ");

        $stmtImg->execute([
            $propiedad_id,
            $rutaParaBaseDatos,
            $titulo,
            $esPrincipal,
            $orden
        ]);

        $hayPrincipal = true;
    }
}

function eliminarImagenesSeleccionadas(PDO $pdo, int $propiedad_id, array $idsImagenes): void
{
    $idsImagenes = array_values(array_filter(array_map('intval', $idsImagenes)));

    if (empty($idsImagenes)) {
        return;
    }

    $placeholders = implode(',', array_fill(0, count($idsImagenes), '?'));

    $params = array_merge([$propiedad_id], $idsImagenes);

    $stmtImagenes = $pdo->prepare("
        SELECT id, imagen_url
        FROM imagenes_propiedades
        WHERE propiedad_id = ?
        AND id IN ($placeholders)
    ");

    $stmtImagenes->execute($params);
    $imagenes = $stmtImagenes->fetchAll();

    foreach ($imagenes as $imagen) {
        $ruta = (string)$imagen['imagen_url'];

        if (str_starts_with($ruta, 'Uploads/propiedades/')) {
            $rutaServidor = __DIR__ . '/' . $ruta;

            if (is_file($rutaServidor)) {
                unlink($rutaServidor);
            }
        }
    }

    $stmtDelete = $pdo->prepare("
        DELETE FROM imagenes_propiedades
        WHERE propiedad_id = ?
        AND id IN ($placeholders)
    ");

    $stmtDelete->execute($params);

    $stmtPrincipal = $pdo->prepare("
        SELECT COUNT(*)
        FROM imagenes_propiedades
        WHERE propiedad_id = ?
        AND es_principal = 1
    ");

    $stmtPrincipal->execute([$propiedad_id]);

    $tienePrincipal = (int)$stmtPrincipal->fetchColumn() > 0;

    if (!$tienePrincipal) {
        $stmtNuevaPrincipal = $pdo->prepare("
            UPDATE imagenes_propiedades
            SET es_principal = 1
            WHERE propiedad_id = ?
            ORDER BY orden ASC, id ASC
            LIMIT 1
        ");

        $stmtNuevaPrincipal->execute([$propiedad_id]);
    }
}

function cambiarImagenPrincipal(PDO $pdo, int $propiedad_id, int $imagen_id): void
{
    if ($imagen_id <= 0) {
        return;
    }

    $stmtVerificar = $pdo->prepare("
        SELECT COUNT(*)
        FROM imagenes_propiedades
        WHERE id = ?
        AND propiedad_id = ?
    ");

    $stmtVerificar->execute([
        $imagen_id,
        $propiedad_id
    ]);

    $existe = (int)$stmtVerificar->fetchColumn() > 0;

    if (!$existe) {
        return;
    }

    $stmtQuitarPrincipal = $pdo->prepare("
        UPDATE imagenes_propiedades
        SET es_principal = 0
        WHERE propiedad_id = ?
    ");

    $stmtQuitarPrincipal->execute([$propiedad_id]);

    $stmtPonerPrincipal = $pdo->prepare("
        UPDATE imagenes_propiedades
        SET es_principal = 1
        WHERE id = ?
        AND propiedad_id = ?
    ");

    $stmtPonerPrincipal->execute([
        $imagen_id,
        $propiedad_id
    ]);
}

$id = trim($_POST['id'] ?? '');
$esEdicion = $id !== '';

$agente_id = (int)($_POST['agente_id'] ?? 0);
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
$ciudad = $_POST['ciudad'] ?? 'ciudad_obregon';

$direccion_completa = trim($_POST['direccion_completa'] ?? '');
$google_maps_url = trim($_POST['google_maps_url'] ?? '');

$recamaras = (int)($_POST['recamaras'] ?? 0);
$banos = $_POST['banos'] ?? 0;
$estacionamientos = (int)($_POST['estacionamientos'] ?? 0);

$terreno_m2 = ($_POST['terreno_m2'] ?? '') !== '' ? $_POST['terreno_m2'] : null;
$construccion_m2 = ($_POST['construccion_m2'] ?? '') !== '' ? $_POST['construccion_m2'] : null;

$destacada = isset($_POST['destacada']) ? 1 : 0;
$reemplazarImagenes = isset($_POST['reemplazar_imagenes']);
$imagenPrincipalId = (int)($_POST['imagen_principal_id'] ?? 0);

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

if ($titulo === '') {
    die('El título es obligatorio.');
}

if ($direccion_completa === '') {
    die('La dirección completa es obligatoria.');
}

if (!in_array($tipo_operacion, ['venta', 'renta', 'traspaso'], true)) {
    die('Tipo de operación inválido.');
}

if (!in_array($estado_publicacion, ['activo', 'inactivo'], true)) {
    die('Estado de publicación inválido.');
}

$precio = (float)$precio;
$banos = (float)$banos;

try {
    $pdo->beginTransaction();

    if ($esEdicion) {
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
                ciudad = ?,
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
            $agente_id,
            $titulo,
            $descripcion,
            $precio,
            $moneda,
            $tipo_operacion,
            $categoria_id,
            $estado_publicacion,
            $destacada,
            $ciudad,
            $direccion_completa,
            $google_maps_url,
            $recamaras,
            $banos,
            $estacionamientos,
            $terreno_m2,
            $construccion_m2,
            $id
        ]);

        $propiedad_id = (int)$id;

    } else {
        $slug = crearSlugPanel($titulo);

        $sql = "
            INSERT INTO propiedades (
                agente_id,
                titulo,
                slug,
                descripcion,
                precio,
                moneda,
                tipo_operacion,
                categoria_id,
                estado_publicacion,
                destacada,
                ciudad,
                direccion_completa,
                google_maps_url,
                recamaras,
                banos,
                estacionamientos,
                terreno_m2,
                construccion_m2
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            $agente_id,
            $titulo,
            $slug,
            $descripcion,
            $precio,
            $moneda,
            $tipo_operacion,
            $categoria_id,
            $estado_publicacion,
            $destacada,
            $ciudad,
            $direccion_completa,
            $google_maps_url,
            $recamaras,
            $banos,
            $estacionamientos,
            $terreno_m2,
            $construccion_m2
        ]);

        $propiedad_id = (int)$pdo->lastInsertId();
    }

    if (!empty($_POST['eliminar_imagenes']) && is_array($_POST['eliminar_imagenes'])) {
        eliminarImagenesSeleccionadas(
            $pdo,
            $propiedad_id,
            $_POST['eliminar_imagenes']
        );
    }

    guardarImagenesPropiedad(
        $pdo,
        $propiedad_id,
        $titulo,
        $reemplazarImagenes
    );

    if ($imagenPrincipalId > 0) {
        cambiarImagenPrincipal(
            $pdo,
            $propiedad_id,
            $imagenPrincipalId
        );
    }

    $pdo->commit();

    $_SESSION['modal_exito'] = [
        'titulo' => $esEdicion ? 'Cambios guardados' : 'Propiedad agregada',
        'mensaje' => $esEdicion
            ? 'La información de la propiedad se actualizó correctamente.'
            : 'La propiedad se agregó correctamente al catálogo.'
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

    header('Location: Panel-propiedades.php');
    exit;
}