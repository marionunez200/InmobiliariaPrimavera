
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once ROOT_PATH . '/Config/database.php';
require_once ROOT_PATH . '/Backend/cambio-moneda.php';

$pdo = db();

$porPagina = 12;

$pagina = isset($_GET['pagina'])
    ? max(1, (int)$_GET['pagina'])
    : 1;

$offset = ($pagina - 1) * $porPagina;

function limpiarTexto(?string $texto): string
{
    $texto = $texto ?? '';
    $texto = str_replace(["\\n", "\\r", "\n", "\r"], ' ', $texto);
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

$sqlCount = "
    SELECT COUNT(*)
    FROM propiedades p
    INNER JOIN categorias_propiedad c
        ON c.id = p.categoria_id
    WHERE p.estado_publicacion = 'activo'
";

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
$paramsCount = [];

if ($ciudad !== '') {
    $sql .= " AND p.ciudad = ?";
    $sqlCount .= " AND p.ciudad = ?";
    $params[] = $ciudad;
    $paramsCount[] = $ciudad;
}

if ($tipoOperacion !== '') {
    $sql .= " AND p.tipo_operacion = ?";
    $sqlCount .= " AND p.tipo_operacion = ?";
    $params[] = $tipoOperacion;
    $paramsCount[] = $tipoOperacion;
}

if ($categoria > 0) {
    $sql .= " AND p.categoria_id = ?";
    $sqlCount .= " AND p.categoria_id = ?";
    $params[] = $categoria;
    $paramsCount[] = $categoria;
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
    $sqlCount .= " AND p.precio >= ?";
    $params[] = $precioMin;
    $paramsCount[] = $precioMin;
}

if ($precioMax !== '' && is_numeric($precioMax)) {
    $sql .= " AND p.precio <= ?";
    $sqlCount .= " AND p.precio <= ?";
    $params[] = $precioMax;
    $paramsCount[] = $precioMax;
}

$stmtCount = $pdo->prepare($sqlCount);
$stmtCount->execute($paramsCount);

$totalPropiedades = $stmtCount->fetchColumn();
$totalPaginas = max(1, ceil($totalPropiedades / $porPagina));

$sql .= "
    ORDER BY p.destacada DESC, p.creado_en DESC
    LIMIT $porPagina
    OFFSET $offset
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$propiedades = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="catalogo-grid">

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

        <p class="precio-mxn">
            <?= e($precio) ?>
        </p>

        <div class="info_card">
            <p>Baños: <?= e($propiedad['banos']) ?></p>
            <p>Recámaras: <?= e($propiedad['recamaras']) ?></p>
        </div>

        <a
            class="propiedad_detalles"
            href="<?= BASE_URL ?>Usuario/PropiedadInfo.php?id=<?= (int)$propiedad['id'] ?>&moneda=<?= e($monedaMostrar) ?>"
        >
            Ver detalles
        </a>

    </div>

</article>

<?php endforeach; ?>

</div>

<?php if ($totalPaginas > 1): ?>

<nav class="paginacion" aria-label="Navegación de páginas">

    <?php if ($pagina > 1): ?>

        <?php
            $query = $_GET;
            $query['pagina'] = $pagina - 1;
        ?>

        <a
            href="?<?= http_build_query($query) ?>"
            class="paginacion-btn"
        >
            &larr; Anterior
        </a>

    <?php else: ?>

        <span class="paginacion-btn deshabilitado">
            &larr; Anterior
        </span>

    <?php endif; ?>


    <?php

    $rango = 1;

    $inicio = max(1, $pagina - $rango);
    $fin = min($totalPaginas, $pagina + $rango);

    ?>


    <?php if ($inicio > 1): ?>

        <?php
            $query = $_GET;
            $query['pagina'] = 1;
        ?>

        <a
            href="?<?= http_build_query($query) ?>"
            class="paginacion-numero"
        >
            1
        </a>

        <?php if ($inicio > 2): ?>
            <span>...</span>
        <?php endif; ?>

    <?php endif; ?>


    <?php for ($i = $inicio; $i <= $fin; $i++): ?>

        <?php
            $query = $_GET;
            $query['pagina'] = $i;
        ?>

        <a
            href="?<?= http_build_query($query) ?>"
            class="paginacion-numero <?= $pagina == $i ? 'activo' : '' ?>"
        >
            <?= $i ?>
        </a>

    <?php endfor; ?>


    <?php if ($fin < $totalPaginas): ?>

        <?php if ($fin < $totalPaginas - 1): ?>
            <span>...</span>
        <?php endif; ?>

        <?php
            $query = $_GET;
            $query['pagina'] = $totalPaginas;
        ?>

        <a
            href="?<?= http_build_query($query) ?>"
            class="paginacion-numero"
        >
            <?= $totalPaginas ?>
        </a>

    <?php endif; ?>


    <?php if ($pagina < $totalPaginas): ?>

        <?php
            $query = $_GET;
            $query['pagina'] = $pagina + 1;
        ?>

        <a
            href="?<?= http_build_query($query) ?>"
            class="paginacion-btn"
        >
            Siguiente &rarr;
        </a>

    <?php else: ?>

        <span class="paginacion-btn deshabilitado">
            Siguiente &rarr;
        </span>

    <?php endif; ?>

</nav>

<?php endif; ?>