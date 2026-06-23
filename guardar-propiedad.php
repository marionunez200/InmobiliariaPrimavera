<?php
require_once __DIR__ . '/Config/database.php';

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

$id = trim($_POST['id'] ?? '');

$agente_id = (int)($_POST['agente_id'] ?? 0);
$titulo = trim($_POST['titulo'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$precio = $_POST['precio'] ?? 0;
$moneda = trim($_POST['moneda'] ?? 'MXN');

$tipo_operacion = $_POST['tipo_operacion'] ?? 'venta';
$tipo_propiedad = $_POST['tipo_propiedad'] ?? 'casa';
$estado_publicacion = $_POST['estado_publicacion'] ?? 'activo';
$ciudad = $_POST['ciudad'] ?? 'ciudad_obregon';

$direccion_completa = trim($_POST['direccion_completa'] ?? '');
$google_maps_url = trim($_POST['google_maps_url'] ?? '');

$recamaras = (int)($_POST['recamaras'] ?? 0);
$banos = $_POST['banos'] ?? 0;
$estacionamientos = (int)($_POST['estacionamientos'] ?? 0);

$terreno_m2 = ($_POST['terreno_m2'] ?? '') !== '' ? $_POST['terreno_m2'] : null;
$construccion_m2 = ($_POST['construccion_m2'] ?? '') !== '' ? $_POST['construccion_m2'] : null;

$imagen_url = trim($_POST['imagen_url'] ?? '');
$destacada = isset($_POST['destacada']) ? 1 : 0;

if ($agente_id <= 0) {
    die('Debes seleccionar un agente.');
}

if ($titulo === '') {
    die('El título es obligatorio.');
}

if ($direccion_completa === '') {
    die('La dirección completa es obligatoria.');
}

try {
    $pdo->beginTransaction();

    if ($id !== '') {
        $sql = "
            UPDATE propiedades SET
                agente_id = ?,
                titulo = ?,
                descripcion = ?,
                precio = ?,
                moneda = ?,
                tipo_operacion = ?,
                tipo_propiedad = ?,
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
            $tipo_propiedad,
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
                tipo_propiedad,
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
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
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
            $tipo_propiedad,
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

    if ($imagen_url !== '') {
        $stmtDeleteImg = $pdo->prepare("
            DELETE FROM imagenes_propiedades
            WHERE propiedad_id = ?
            AND es_principal = 1
        ");

        $stmtDeleteImg->execute([$propiedad_id]);

        $stmtImg = $pdo->prepare("
            INSERT INTO imagenes_propiedades (
                propiedad_id,
                imagen_url,
                texto_alternativo,
                es_principal,
                orden
            ) VALUES (?, ?, ?, 1, 0)
        ");

        $stmtImg->execute([
            $propiedad_id,
            $imagen_url,
            $titulo
        ]);
    }

    $pdo->commit();

    header('Location: Panel-propiedades.php?ok=1');
    exit;

} catch (Exception $e) {
    $pdo->rollBack();

    die('Error al guardar propiedad: ' . $e->getMessage());
}