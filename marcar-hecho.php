
<?php

require_once "Config/database.php";

$pdo = db();

$id = (int)$_POST["id"];

$stmt = $pdo->prepare("
    UPDATE mensajes_contacto
SET estado_mensaje = 'cerrado',
    completado_en = NOW()
WHERE id = :id
");

$stmt->execute(['id' => $id]);

header("Location: Panel-mensajes.php");
exit;