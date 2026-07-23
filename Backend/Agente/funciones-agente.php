
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once ROOT_PATH . '/Admin/auth.php';
validar_csrf();

function convertirAWebp(string $origen, string $destino, string $extension): bool
{
    $imagen = null;

    switch (strtolower($extension)) {

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
            $imagen = imagecreatefromwebp($origen);
            break;


        default:
            return false;
    }


    if (!$imagen) {
        return false;
    }


    $resultado = imagewebp(
        $imagen,
        $destino,
        85
    );


    imagedestroy($imagen);


    return $resultado;
}

function subirFotoAgente(?string $fotoActual = null): ?string
{
    if (
        empty($_FILES['foto_agente']) ||
        $_FILES['foto_agente']['error'] === UPLOAD_ERR_NO_FILE
    ) {
        return null;
    }

    if ($_FILES['foto_agente']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Error al subir la foto del agente.');
    }

    $nombreOriginal = $_FILES['foto_agente']['name'];
    $tmpName = $_FILES['foto_agente']['tmp_name'];
    $size = $_FILES['foto_agente']['size'];

    $maxSize = 5 * 1024 * 1024; // 5 MB

    if ($size > $maxSize) {
        throw new Exception('La imagen no debe pesar más de 5 MB.');
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

    if (getimagesize($tmpName) === false) {
        throw new Exception('La imagen está dañada o es inválida.');
    }

    $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($extension, $extensionesPermitidas, true)) {
        throw new Exception('Solo se permiten imágenes JPG, JPEG, PNG o WEBP.');
    }

    $carpetaUploads = ROOT_PATH . '/Uploads/agentes/';

    if (!is_dir($carpetaUploads)) {
        mkdir($carpetaUploads, 0777, true);
    }

    $nombreNuevo = 'agente-' . uniqid() . '.webp';

    $rutaServidor = $carpetaUploads . $nombreNuevo;
    $rutaBaseDatos = 'Uploads/agentes/' . $nombreNuevo;

    if (!convertirAWebp($tmpName, $rutaServidor, $extension)) {
        throw new Exception('No se pudo convertir la imagen a WebP.');
    }

    if (
        $fotoActual &&
        str_starts_with($fotoActual, 'Uploads/agentes/')
    ) {
        $rutaFotoAnterior = ROOT_PATH . '/' . $fotoActual;

        if (is_file($rutaFotoAnterior)) {
            unlink($rutaFotoAnterior);
        }
    }

    return $rutaBaseDatos;
}
function obtenerFotoActual(PDO $pdo, int $id): ?string
{
    $stmt = $pdo->prepare("
        SELECT foto_url
        FROM agentes
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$id]);

    return $stmt->fetchColumn() ?: null;
}