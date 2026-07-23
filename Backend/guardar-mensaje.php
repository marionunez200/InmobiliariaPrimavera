<?php
if (!defined('BASE_URL')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
}
require_once ROOT_PATH . '/Config/database.php';

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "index.php");
    exit;
}

$propiedad_id = $_POST['propiedad_id'] ?: null;
$nombre = trim($_POST['nombre']);
$telefono = trim($_POST['telefono']);
$email = trim($_POST['email']);
$mensaje = trim($_POST['mensaje']);
$slug = $_POST['slug'] ?? '';

if (
    $nombre === '' ||
    $telefono === '' ||
    $email === '' ||
    $mensaje === ''
) {
    die("Todos los campos son obligatorios.");
}

$sql = "
INSERT INTO mensajes_contacto
(
    propiedad_id,
    nombre,
    telefono,
    email,
    mensaje
)
VALUES
(
    :propiedad_id,
    :nombre,
    :telefono,
    :email,
    :mensaje
)
";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':propiedad_id' => $propiedad_id,
    ':nombre' => $nombre,
    ':telefono' => $telefono,
    ':email' => $email,
    ':mensaje' => $mensaje
]);

header("Location: " . BASE_URL . "Usuario/PropiedadInfo.php?slug=" . urlencode($slug) . "&mensaje=1"); exit;