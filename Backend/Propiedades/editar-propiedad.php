<?php
if (!defined('BASE_URL')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
}
require_once ROOT_PATH . '/Config/database.php';
require_once ROOT_PATH . '/Admin/auth.php';
require_once ROOT_PATH . '/Backend/Propiedades/funciones-propiedad.php';
validar_csrf();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = db();

$id = trim($_POST['id'] ?? '');
if ($id === '') {
    die('No se recibió el ID de la propiedad.');
}

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

$banos = max(0, (int)($_POST['banos'] ?? 0));
$recamaras = max(0, (int)($_POST['recamaras'] ?? 0));
$estacionamientos = max(0, (int)($_POST['estacionamientos'] ?? 0));

$precio = (float)$precio;
$banos = (float)$banos;

try {
    $pdo->beginTransaction();
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
        'titulo' => 'Cambios guardados',
        'mensaje' => 'La información de la propiedad se actualizó correctamente.'
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