<?php

if (!defined('BASE_URL')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
}

require_once ROOT_PATH . '/Config/database.php';
require_once ROOT_PATH . '/Admin/auth.php';


/*
================================
CREAR SLUG
================================
*/

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


/*
================================
VERIFICAR PERMISOS
================================
*/

function usuarioPuedeModificarPropiedad(
    PDO $pdo,
    int $propiedad_id
): bool {

    if (($_SESSION['rol'] ?? '') === 'admin') {
        return true;
    }

    if (($_SESSION['rol'] ?? '') === 'editor') {

        if (empty($_SESSION['id_agente'])) {
            return false;
        }

        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM propiedades
            WHERE id = ?
            AND agente_id = ?
        ");

        $stmt->execute([
            $propiedad_id,
            $_SESSION['id_agente']
        ]);

        return (int)$stmt->fetchColumn() > 0;
    }

    return false;
}


/*
================================
VERIFICAR IMAGEN PRINCIPAL
================================
*/

function propiedadTieneImagenPrincipal(
    PDO $pdo,
    int $propiedad_id
): bool {

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM multimedia_propiedades
        WHERE propiedad_id = ?
        AND es_principal = 1
        AND tipo = 'imagen'
    ");

    $stmt->execute([$propiedad_id]);

    return (int)$stmt->fetchColumn() > 0;
}


/*
================================
GUARDAR IMÁGENES Y VIDEOS
================================
*/

function guardarImagenesPropiedad(
    PDO $pdo,
    int $propiedad_id,
    string $titulo
): void {

    if (!usuarioPuedeModificarPropiedad($pdo, $propiedad_id)) {
        throw new Exception(
            'No tienes permisos para modificar esta propiedad.'
        );
    }

    if (
        empty($_FILES['imagenes']) ||
        !isset($_FILES['imagenes']['name']) ||
        !is_array($_FILES['imagenes']['name'])
    ) {
        return;
    }

    $carpetaUploads = ROOT_PATH . '/Uploads/propiedades/';

    if (!is_dir($carpetaUploads)) {

        if (!mkdir($carpetaUploads, 0755, true) && !is_dir($carpetaUploads)) {
            throw new Exception(
                'No se pudo crear la carpeta Uploads/propiedades/.'
            );
        }
    }

    if (!is_writable($carpetaUploads)) {
        throw new Exception(
            'La carpeta Uploads/propiedades/ no tiene permisos de escritura.'
        );
    }


    /*
    ==========================================
    CONFIGURACIÓN DE ARCHIVOS
    ==========================================
    */

    $extensionesImagen = [
        'jpg',
        'jpeg',
        'png',
        'webp'
    ];

    $extensionesVideo = [
        'mp4',
        'webm',
        'mov',
        'avi'
    ];

    $mimesImagen = [
        'image/jpeg',
        'image/png',
        'image/webp'
    ];

    $mimesVideo = [
        'video/mp4',
        'video/webm',
        'video/quicktime',
        'video/x-msvideo'
    ];

    $maxSizeImagen = 10 * 1024 * 1024; // 10 MB
    $maxSizeVideo  = 15 * 1024 * 1024; // 15 MB


    /*
    ==========================================
    ¿YA EXISTE UNA IMAGEN PRINCIPAL?
    ==========================================
    */

    $hayPrincipal = propiedadTieneImagenPrincipal(
        $pdo,
        $propiedad_id
    );


    /*
    ==========================================
    RECORRER ARCHIVOS
    ==========================================
    */

    $total = count($_FILES['imagenes']['name']);

    for ($i = 0; $i < $total; $i++) {

        $nombreOriginal = $_FILES['imagenes']['name'][$i] ?? '';
        $tmpName        = $_FILES['imagenes']['tmp_name'][$i] ?? '';
        $error          = $_FILES['imagenes']['error'][$i] ?? UPLOAD_ERR_NO_FILE;
        $size           = (int)($_FILES['imagenes']['size'][$i] ?? 0);


        if ($error === UPLOAD_ERR_NO_FILE) {
            continue;
        }


        /*
        ==========================================
        ERROR DE SUBIDA
        ==========================================
        */

        if ($error !== UPLOAD_ERR_OK) {

            throw new Exception(
                "Error al subir el archivo '$nombreOriginal'. Código: $error"
            );
        }


        if (!is_uploaded_file($tmpName)) {

            throw new Exception(
                "El archivo '$nombreOriginal' no es válido."
            );
        }


        /*
        ==========================================
        EXTENSIÓN
        ==========================================
        */

        $extension = strtolower(
            pathinfo(
                $nombreOriginal,
                PATHINFO_EXTENSION
            )
        );


        /*
        ==========================================
        MIME
        ==========================================
        */

        $finfo = new finfo(FILEINFO_MIME_TYPE);

        $mime = $finfo->file($tmpName);


        /*
        ==========================================
        DETERMINAR TIPO
        ==========================================
        */

        if (in_array($extension, $extensionesImagen, true)) {

            $tipo = 'imagen';

        } elseif (in_array($extension, $extensionesVideo, true)) {

            $tipo = 'video';

        } else {

            throw new Exception(
                "El archivo '$nombreOriginal' tiene un formato no permitido."
            );
        }


        /*
        ==========================================
        VALIDAR IMAGEN
        ==========================================
        */

        if ($tipo === 'imagen') {

            if ($size > $maxSizeImagen) {

                throw new Exception(
                    "La imagen '$nombreOriginal' supera el límite de 10 MB."
                );
            }

            if (!in_array($mime, $mimesImagen, true)) {

                throw new Exception(
                    "La imagen '$nombreOriginal' no es válida."
                );
            }

            if (@getimagesize($tmpName) === false) {

                throw new Exception(
                    "La imagen '$nombreOriginal' está dañada o es inválida."
                );
            }
        }


        /*
        ==========================================
        VALIDAR VIDEO
        ==========================================
        */

        if ($tipo === 'video') {

            if ($size > $maxSizeVideo) {

                throw new Exception(
                    "El video '$nombreOriginal' supera el límite de 15 MB."
                );
            }

            if (!in_array($mime, $mimesVideo, true)) {

                throw new Exception(
                    "El video '$nombreOriginal' no es válido."
                );
            }
        }


        /*
        ==========================================
        CALCULAR ORDEN
        ==========================================
        */

        $stmtOrden = $pdo->prepare("
            SELECT COALESCE(MAX(orden), 0) + 1
            FROM multimedia_propiedades
            WHERE propiedad_id = ?
        ");

        $stmtOrden->execute([
            $propiedad_id
        ]);

        $orden = (int)$stmtOrden->fetchColumn();


        /*
        ==========================================
        DETERMINAR PRINCIPAL
        ==========================================
        */

        /*
        La primera imagen que se guarda será
        principal únicamente si todavía no
        existe una imagen principal.
        */

        $esPrincipal = 0;

        if (!$hayPrincipal && $tipo === 'imagen') {
            $esPrincipal = 1;
        }


        /*
        ==========================================
        GUARDAR IMAGEN
        ==========================================
        */

        if ($tipo === 'imagen') {

            $nombreNuevo =
                'propiedad-' .
                $propiedad_id .
                '-' .
                uniqid() .
                '.webp';

            $rutaDestinoServidor =
                $carpetaUploads .
                $nombreNuevo;

            $rutaParaBaseDatos =
                'Uploads/propiedades/' .
                $nombreNuevo;


            if (!convertirAWebp(
                $tmpName,
                $rutaDestinoServidor,
                $extension
            )) {

                throw new Exception(
                    "No se pudo convertir la imagen '$nombreOriginal' a WEBP."
                );
            }


            try {

                $stmtImg = $pdo->prepare("
                    INSERT INTO multimedia_propiedades
                    (
                        propiedad_id,
                        tipo,
                        archivo_url,
                        texto_alternativo,
                        es_principal,
                        orden
                    )
                    VALUES (?, ?, ?, ?, ?, ?)
                ");

                $stmtImg->execute([
                    $propiedad_id,
                    'imagen',
                    $rutaParaBaseDatos,
                    $titulo,
                    $esPrincipal,
                    $orden
                ]);

            } catch (Exception $e) {

                if (is_file($rutaDestinoServidor)) {
                    unlink($rutaDestinoServidor);
                }

                throw $e;
            }


            if ($esPrincipal) {
                $hayPrincipal = true;
            }
        }


        /*
        ==========================================
        GUARDAR VIDEO
        ==========================================
        */

        elseif ($tipo === 'video') {

            $nombreNuevo =
                'propiedad-' .
                $propiedad_id .
                '-' .
                uniqid() .
                '.' .
                $extension;

            $rutaDestinoServidor =
                $carpetaUploads .
                $nombreNuevo;

            $rutaParaBaseDatos =
                'Uploads/propiedades/' .
                $nombreNuevo;


            if (!move_uploaded_file(
                $tmpName,
                $rutaDestinoServidor
            )) {

                throw new Exception(
                    "No se pudo guardar el video '$nombreOriginal'."
                );
            }


            try {

                $stmtVideo = $pdo->prepare("
                    INSERT INTO multimedia_propiedades
                    (
                        propiedad_id,
                        tipo,
                        archivo_url,
                        texto_alternativo,
                        es_principal,
                        orden
                    )
                    VALUES (?, ?, ?, ?, ?, ?)
                ");

                $stmtVideo->execute([
                    $propiedad_id,
                    'video',
                    $rutaParaBaseDatos,
                    $titulo,
                    0,
                    $orden
                ]);

            } catch (Exception $e) {

                if (is_file($rutaDestinoServidor)) {
                    unlink($rutaDestinoServidor);
                }

                throw $e;
            }
        }
    }
}


/*
================================
ELIMINAR IMÁGENES
================================
*/

function eliminarImagenesSeleccionadas(
    PDO $pdo,
    int $propiedad_id,
    array $idsImagenes
): void {

    if (!usuarioPuedeModificarPropiedad($pdo, $propiedad_id)) {

        throw new Exception(
            'No tienes permisos para modificar esta propiedad.'
        );
    }

    $idsImagenes = array_values(
        array_filter(
            array_map('intval', $idsImagenes)
        )
    );

    if (empty($idsImagenes)) {
        return;
    }

    $placeholders = implode(
        ',',
        array_fill(
            0,
            count($idsImagenes),
            '?'
        )
    );

    $params = array_merge(
        [$propiedad_id],
        $idsImagenes
    );


    $stmtImagenes = $pdo->prepare("
        SELECT id, archivo_url
        FROM multimedia_propiedades
        WHERE propiedad_id = ?
        AND id IN ($placeholders)
    ");

    $stmtImagenes->execute($params);

    $imagenes = $stmtImagenes->fetchAll();


    foreach ($imagenes as $imagen) {

        $ruta = (string)$imagen['archivo_url'];

        if (
            str_starts_with(
                $ruta,
                'Uploads/propiedades/'
            )
        ) {

            $rutaServidor =
                ROOT_PATH . '/' . $ruta;

            if (is_file($rutaServidor)) {
                unlink($rutaServidor);
            }
        }
    }


    $stmtDelete = $pdo->prepare("
        DELETE FROM multimedia_propiedades
        WHERE propiedad_id = ?
        AND id IN ($placeholders)
    ");

    $stmtDelete->execute($params);


    /*
    Si eliminó la principal,
    asignar otra imagen como principal.
    */

    $stmtPrincipal = $pdo->prepare("
        SELECT COUNT(*)
        FROM multimedia_propiedades
        WHERE propiedad_id = ?
        AND es_principal = 1
        AND tipo = 'imagen'
    ");

    $stmtPrincipal->execute([
        $propiedad_id
    ]);

    $tienePrincipal =
        (int)$stmtPrincipal->fetchColumn() > 0;


    if (!$tienePrincipal) {

        $stmtNuevaPrincipal = $pdo->prepare("
            SELECT id
            FROM multimedia_propiedades
            WHERE propiedad_id = ?
            AND tipo = 'imagen'
            ORDER BY orden ASC, id ASC
            LIMIT 1
        ");

        $stmtNuevaPrincipal->execute([
            $propiedad_id
        ]);

        $nuevaPrincipal =
            $stmtNuevaPrincipal->fetchColumn();


        if ($nuevaPrincipal) {

            $stmtPonerPrincipal = $pdo->prepare("
                UPDATE multimedia_propiedades
                SET es_principal = 1
                WHERE id = ?
                AND propiedad_id = ?
            ");

            $stmtPonerPrincipal->execute([
                $nuevaPrincipal,
                $propiedad_id
            ]);
        }
    }
}


/*
================================
CAMBIAR IMAGEN PRINCIPAL
================================
*/

function cambiarImagenPrincipal(
    PDO $pdo,
    int $propiedad_id,
    int $imagen_id
): void {

    if (!usuarioPuedeModificarPropiedad($pdo, $propiedad_id)) {

        throw new Exception(
            'No tienes permisos para modificar esta propiedad.'
        );
    }

    if ($imagen_id <= 0) {
        return;
    }


    $stmtVerificar = $pdo->prepare("
        SELECT COUNT(*)
        FROM multimedia_propiedades
        WHERE id = ?
        AND propiedad_id = ?
        AND tipo = 'imagen'
    ");

    $stmtVerificar->execute([
        $imagen_id,
        $propiedad_id
    ]);

    if ((int)$stmtVerificar->fetchColumn() === 0) {
        return;
    }


    $stmtQuitarPrincipal = $pdo->prepare("
        UPDATE multimedia_propiedades
        SET es_principal = 0
        WHERE propiedad_id = ?
    ");

    $stmtQuitarPrincipal->execute([
        $propiedad_id
    ]);


    $stmtPonerPrincipal = $pdo->prepare("
        UPDATE multimedia_propiedades
        SET es_principal = 1
        WHERE id = ?
        AND propiedad_id = ?
        AND tipo = 'imagen'
    ");

    $stmtPonerPrincipal->execute([
        $imagen_id,
        $propiedad_id
    ]);
}


/*
================================
CONVERTIR IMAGEN A WEBP
================================
*/

function convertirAWebp(
    string $origen,
    string $destino,
    string $extension
): bool {

    if (!function_exists('imagewebp')) {
        return false;
    }

    switch ($extension) {

        case 'jpg':
        case 'jpeg':

            if (!function_exists('imagecreatefromjpeg')) {
                return false;
            }

            $imagen = @imagecreatefromjpeg($origen);

            break;


        case 'png':

            if (!function_exists('imagecreatefrompng')) {
                return false;
            }

            $imagen = @imagecreatefrompng($origen);

            if ($imagen) {

                if (function_exists('imagepalettetotruecolor')) {
                    @imagepalettetotruecolor($imagen);
                }

                imagealphablending(
                    $imagen,
                    false
                );

                imagesavealpha(
                    $imagen,
                    true
                );
            }

            break;


        case 'webp':

            if (!function_exists('imagecreatefromwebp')) {
                return move_uploaded_file(
                    $origen,
                    $destino
                );
            }

            $imagen = @imagecreatefromwebp($origen);

            break;


        default:

            return false;
    }


    if (!$imagen) {
        return false;
    }


    $resultado = @imagewebp(
        $imagen,
        $destino,
        85
    );


    imagedestroy($imagen);

    return $resultado;
}