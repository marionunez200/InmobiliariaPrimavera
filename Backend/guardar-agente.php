<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

require_once ROOT_PATH . '/Config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = db();

$pdo = db();

function convertirAWebp(string $origen, string $destino, string $extension): bool
{
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
            return move_uploaded_file($origen, $destino);

        default:
            return false;
    }

    $resultado = imagewebp($imagen, $destino, 85);

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

$id = trim($_POST['id'] ?? '');
$esEdicion = $id !== '';

$nombre = trim($_POST['nombre'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$email = trim($_POST['email'] ?? '');
$activo = isset($_POST['activo']) ? (int)$_POST['activo'] : 1;

if ($nombre === '') {
    die('El nombre del agente es obligatorio.');
}

try {
    if ($id !== '') {
        $stmtActual = $pdo->prepare("
            SELECT foto_url
            FROM agentes
            WHERE id = ?
            LIMIT 1
        ");

        $stmtActual->execute([$id]);
        $agenteActual = $stmtActual->fetch();

        $fotoActual = $agenteActual['foto_url'] ?? null;

        $nuevaFoto = subirFotoAgente($fotoActual);

        $fotoFinal = $nuevaFoto ?: $fotoActual;

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

    } else {
        $nuevaFoto = subirFotoAgente(null);

        $fotoFinal = $nuevaFoto ?: 'Imagenes/agente1.webp';

        $stmt = $pdo->prepare("
            INSERT INTO agentes (
                nombre,
                telefono,
                email,
                foto_url,
                activo
            ) VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $nombre,
            $telefono,
            $email,
            $fotoFinal,
            $activo
        ]);
    }

    $_SESSION['modal_exito'] = [
        'titulo' => $esEdicion ? 'Cambios guardados' : 'Agente agregado',
        'mensaje' => $esEdicion
            ? 'La información del agente se actualizó correctamente.'
            : 'El agente se agregó correctamente al panel.'
    ];
    
    header('Location: ' . BASE_URL . 'Admin/Panel-agente.php');
    exit;

} catch (Exception $e) {
    die('Error al guardar agente: ' . $e->getMessage());
}