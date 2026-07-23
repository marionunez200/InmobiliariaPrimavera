
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once ROOT_PATH . '/Config/database.php';
require_once ROOT_PATH . '/Admin/auth.php';
validar_csrf();

$pdo = db();

$id = (int)($_POST['id'] ?? 0);

if ($id > 0) {
    $stmt = $pdo->prepare("
        DELETE FROM mensajes_contacto
        WHERE id = ?
    ");

    $stmt->execute([$id]);
}

header('Location: ' . BASE_URL . 'Admin/Panel-mensajes.php');
exit;