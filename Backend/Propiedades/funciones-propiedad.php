<?php
if (!defined('BASE_URL')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
}
require_once ROOT_PATH . '/Config/database.php';

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

        if (!is_uploaded_file($tmpName)) {
            throw new Exception('Archivo inválido.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmpName);
        
        $mimesPermitidos = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];

        if (!in_array($mime, $mimesPermitidos, true)) {
            throw new Exception('El archivo seleccionado no es una imagen válida.');
        }

        /* Validar que GD pueda leer la imagen */
        if (getimagesize($tmpName) === false) {
            throw new Exception('La imagen está dañada o es inválida.');
        }

        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

        if (!in_array($extension, $extensionesPermitidas, true)) {
            throw new Exception('Solo se permiten imágenes JPG, JPEG, PNG o WEBP.');
        }

        $nombreNuevo = 'propiedad-' . $propiedad_id . '-' . uniqid() . '.webp';

        $rutaDestinoServidor = $carpetaUploads . $nombreNuevo;
        $rutaParaBaseDatos = 'Uploads/propiedades/' . $nombreNuevo;

        if (!convertirAWebp($tmpName, $rutaDestinoServidor, $extension)) {
            throw new Exception('No se pudo convertir la imagen a WEBP.');
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

    function convertirAWebp(string $origen, string $destino, string $extension): bool
    {
        switch ($extension) {

            case 'jpg':
            case 'jpeg':
                $imagen = imagecreatefromjpeg($origen);
                break;

            case 'png':
                $imagen = imagecreatefrompng($origen);

                imagepalettetotruecolor($imagen);
                imagealphablending($imagen, true);
                imagesavealpha($imagen, true);

                break;

            case 'webp':
                return move_uploaded_file($origen, $destino);

            default:
                return false;
        }

        if (!$imagen) {
            return false;
        }

        imagewebp($imagen, $destino, 85);

        return true;
    }