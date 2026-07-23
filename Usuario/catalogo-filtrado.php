
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

require_once ROOT_PATH . '/Config/database.php';

require_once ROOT_PATH . '/Backend/cambio-moneda.php';

$pdo = db();

function ciudadTexto(?string $ciudad): string
{
    return match ($ciudad) {
        'navojoa' => 'Navojoa',
        'san_carlos' => 'San Carlos',
        'ciudad_obregon' => 'Ciudad Obregón',
        'guaymas' => 'Guaymas',
        default => 'Ciudad Obregón'
    };
}

function operacionTexto(?string $operacion): string
{
    return match ($operacion) {
        'venta' => 'venta',
        'renta' => 'renta',
        default => ''
    };
}

function limpiarTexto(?string $texto): string
{
    $texto = $texto ?? '';
    $texto = str_replace(["\\n","\\r","\n","\r"],' ',$texto);
    return trim($texto);
}

$ciudad = $_GET['ciudad'] ?? '';
$tipoOperacion = $_GET['tipo_operacion'] ?? '';
$categoria = (int)($_GET['categoria'] ?? 0);
$precioMin = $_GET['precio_min'] ?? '';
$precioMax = $_GET['precio_max'] ?? '';
$monedaMostrar = strtoupper($_GET['moneda'] ?? 'MXN');

if (!in_array($monedaMostrar, ['MXN', 'USD'], true)) {
    $monedaMostrar = 'MXN';
}

if ($precioMax === '' && isset($_GET['precio_rango'])) {
    $precioMax = $_GET['precio_rango'];
}

$sql = "
    SELECT
        p.*,
        c.nombre AS categoria_nombre,
        (
            SELECT ip.imagen_url
            FROM imagenes_propiedades ip
            WHERE ip.propiedad_id = p.id
            ORDER BY ip.es_principal DESC, ip.orden ASC, ip.id ASC
            LIMIT 1
        ) AS imagen_principal
    FROM propiedades p
    INNER JOIN categorias_propiedad c
        ON c.id = p.categoria_id
    WHERE p.estado_publicacion = 'activo'
";

$params = [];

if ($ciudad !== '') {
    $sql .= " AND p.ciudad = ?";
    $params[] = $ciudad;
}

if ($tipoOperacion !== '') {
    $sql .= " AND p.tipo_operacion = ?";
    $params[] = $tipoOperacion;
}

if ($categoria > 0) {
    $sql .= " AND p.categoria_id = ?";
    $params[] = $categoria;
}

if ($monedaMostrar === 'USD') {

    if ($precioMin !== '' && is_numeric($precioMin)) {
        $precioMin = MonedaService::convertir(
            (float)$precioMin,
            'USD',
            'MXN'
        );
    }

    if ($precioMax !== '' && is_numeric($precioMax)) {
        $precioMax = MonedaService::convertir(
            (float)$precioMax,
            'USD',
            'MXN'
        );
    }

}

if ($precioMin !== '' && is_numeric($precioMin)) {
    $sql .= " AND p.precio >= ?";
    $params[] = $precioMin;
}

if ($precioMax !== '' && is_numeric($precioMax)) {
    $sql .= " AND p.precio <= ?";
    $params[] = $precioMax;
}

$sql .= " ORDER BY p.destacada DESC, p.creado_en DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$propiedades = $stmt->fetchAll();
?>

<?php if (empty($propiedades)): ?>
    <p>No hay propiedades disponibles con esos filtros.</p>
<?php endif; ?>

<?php foreach ($propiedades as $propiedad): ?>

<?php

$imagen = limpiarTexto($propiedad['imagen_principal']);

if ($imagen === '') {
    $imagen = 'Imagenes/casa1.jpg';
}

$titulo = limpiarTexto($propiedad['titulo']);

$precioConvertido = MonedaService::convertir(
    (float)$propiedad['precio'],
    $propiedad['moneda'],
    $monedaMostrar
);

$precio = MonedaService::formato(
    $precioConvertido,
    $monedaMostrar
);

if ($propiedad['tipo_operacion'] === 'renta') {
    $precio .= ' /mes';
}

?>

<article class="propiedad-card">

    <img
        class="propiedad-img"
        src="<?= BASE_URL ?><?= e($imagen) ?>"
        alt="<?= e($titulo) ?>"
    >

    <div class="propiedad-info">

        <h2><?= e($titulo) ?></h2>

        <p class="precio-mxn"><?= e($precio) ?></p>

        <div class="info_card">
            <p>Baños: <?= e($propiedad['banos']) ?></p>
            <p>Recámaras: <?= e($propiedad['recamaras']) ?></p>
        </div>

        <a
            class="propiedad_detalles"
            href="<?= BASE_URL ?>Usuario/PropiedadInfo.php?id=<?= e($propiedad['id']) ?>&moneda=<?= e($monedaMostrar) ?>"
        >
            Ver detalles
        </a>

    </div>

</article>

<?php endforeach; ?>