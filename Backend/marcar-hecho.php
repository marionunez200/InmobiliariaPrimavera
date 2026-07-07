
<?php

if (!defined('BASE_URL')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
}
require_once ROOT_PATH . '/Config/database.php';

$pdo = db();

$id = (int)$_POST["id"];

$stmt = $pdo->prepare("
    UPDATE mensajes_contacto
SET estado_mensaje = 'cerrado',
    completado_en = NOW()
WHERE id = :id
");

$stmt->execute(['id' => $id]);

header("Location: " . BASE_URL . "Admin/Panel-mensajes.php");
exit;