<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

require_once ROOT_PATH . '/Config/database.php';
require_once ROOT_PATH . '/Admin/auth.php';
$pdo = db();

/* ================================
    MODAL DE ÉXITO
================================ */

$modalExitoTitulo = '';
$modalExitoMensaje = '';

if (!empty($_SESSION['modal_exito'])) {
    $modalExitoTitulo = $_SESSION['modal_exito']['titulo'] ?? '';
    $modalExitoMensaje = $_SESSION['modal_exito']['mensaje'] ?? '';

    unset($_SESSION['modal_exito']);
}

/* ================================
    FUNCIONES
================================ */

function ciudadPanelTexto(?string $ciudad): string
{
    return match ($ciudad) {
        'navojoa' => 'Navojoa',
        'san_carlos' => 'San Carlos',
        'ciudad_obregon' => 'Cd. Obregón',
        'guaymas' => 'Guaymas',
        default => 'Sin ciudad'
    };
}

function tipoPanelTexto(?string $tipo): string
{
    return match ($tipo) {
        'casa' => 'Casa',
        'departamento' => 'Departamento',
        'local_comercial' => 'Local comercial',
        'terreno' => 'Terreno',
        default => 'Propiedad'
    };
}

/* ================================
    DATOS GENERALES
================================ */

$buscar = trim($_GET['buscar'] ?? '');

$agentes = $pdo->query("
    SELECT id, nombre
    FROM agentes
    WHERE activo = 1
    ORDER BY nombre ASC
")->fetchAll();

$totalPropiedadesActivas = (int)$pdo->query("
    SELECT COUNT(*)
    FROM propiedades
    WHERE estado_publicacion = 'activo'
")->fetchColumn();

$totalAgentesActivos = (int)$pdo->query("
    SELECT COUNT(*)
    FROM agentes
    WHERE activo = 1
")->fetchColumn();

/* ================================
    CONSULTA PROPIEDADES
================================ */

$sql = "
    SELECT
        p.*,
        a.nombre AS agente_nombre,
        c.nombre AS categoria_nombre,
        (
            SELECT ip.imagen_url
            FROM imagenes_propiedades ip
            WHERE ip.propiedad_id = p.id
            ORDER BY ip.es_principal DESC, ip.orden ASC, ip.id ASC
            LIMIT 1
        ) AS imagen_principal
    FROM propiedades p
    LEFT JOIN agentes a
        ON p.agente_id = a.id
    LEFT JOIN categorias_propiedad c
        ON p.categoria_id = c.id
";

$params = [];

if ($buscar !== '') {

    if (strtolower($buscar) === 'activo') {

        $sql .= "
            WHERE p.estado_publicacion = 'activo'
        ";

    } elseif (strtolower($buscar) === 'inactivo') {

        $sql .= "
            WHERE p.estado_publicacion = 'inactivo'
        ";

    } else {

        $sql .= "
            WHERE
                p.titulo LIKE ?
                OR p.direccion_completa LIKE ?
                OR p.ciudad LIKE ?
                OR c.nombre LIKE ?
                OR p.tipo_operacion LIKE ?
                OR p.estado_publicacion LIKE ?
                OR a.nombre LIKE ?
        ";

        $like = "%{$buscar}%";

        $params = [
            $like,
            $like,
            $like,
            $like,
            $like,
            $like,
            $like
        ];
    }
}

$sql .= "
    ORDER BY p.creado_en DESC, p.id DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$propiedades = $stmt->fetchAll();

/* ================================
    IMÁGENES POR PROPIEDAD
================================ */

$imagenesPorPropiedad = [];

$idsPropiedades = array_column($propiedades, 'id');

if (!empty($idsPropiedades)) {
    $placeholders = implode(',', array_fill(0, count($idsPropiedades), '?'));

    $stmtImagenesPanel = $pdo->prepare("
        SELECT 
            id,
            propiedad_id,
            imagen_url,
            texto_alternativo,
            es_principal,
            orden
        FROM imagenes_propiedades
        WHERE propiedad_id IN ($placeholders)
        ORDER BY propiedad_id ASC, es_principal DESC, orden ASC, id ASC
    ");

    $stmtImagenesPanel->execute($idsPropiedades);

    foreach ($stmtImagenesPanel->fetchAll() as $imagen) {
        $imagenesPorPropiedad[(int)$imagen['propiedad_id']][] = $imagen;
    }
}

$stmtUltimas = $pdo->query("
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

    LEFT JOIN categorias_propiedad c
    ON c.id = p.categoria_id

    ORDER BY p.creado_en DESC, p.id DESC
    LIMIT 4
");

$ultimasPropiedades = $stmtUltimas->fetchAll();

$stmtCategorias = $pdo->query("
    SELECT *
    FROM categorias_propiedad
    WHERE activo = 1
    ORDER BY nombre ASC
");

$categorias = $stmtCategorias->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inmobiliaria Primavera | Panel Administrativo</title>

    <meta 
        name="description" 
        content="Panel administrativo, agrega agentes y propiedades"
    >

    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#ffffff">

    <link rel="stylesheet" href="<?= BASE_URL ?>CSS/panel-propiedades.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>CSS/Admin.header.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="icon" href="<?= BASE_URL ?>favicon.ico" type="image/x-icon">
</head>

<body>

<div class="admin-panel">

    <header class="admin-header">
        <div class="contenedor-logo">
            <a href="<?= BASE_URL ?>Admin/Panel-agente.php">
                <img class="logo-panel" src="<?= BASE_URL ?>Imagenes/Logosolo.png" alt="Logo de Primavera inmobiliaria">
            </a>
        </div>

        <div class="left-adminh">
            <div class="header-top-panel">
                <h1>Panel Administrativo</h1>
                <p>Gestión de propiedades</p>

                <?php if (!empty($_SESSION['admin_nombre'])): ?>
                    <p>Sesión: <?= e((string)$_SESSION['admin_nombre']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <button class="button_anadir" type="button" data-open-modal="modalAgregar">
            Agregar propiedad
        </button>
    </header>

    <aside class="admin-sidebar">
        <div class="admin-logo"></div>
    
        <nav class="admin-opciones">
            <a href="<?= BASE_URL ?>Admin/Panel-propiedades.php">Propiedades</a>
            <a href="<?= BASE_URL ?>Admin/Panel-agente.php">Agentes</a>
            <a href="<?= BASE_URL ?>Admin/Panel-mensajes.php">Mensajes</a>
        </nav>
    
        <form class="cerrar-sesion" action="<?= BASE_URL ?>Backend/cerrar-sesion.php" method="POST">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= $_SESSION['csrf_token'] ?>"
            >

            <button
                class="cerrar-sesion"
                type="submit">
                Cerrar sesión
            </button>
        </form>
        
    </aside>

    <main class="admin-main">

        <?php if (empty($agentes)): ?>
            <p class="panel-alerta">
                Primero necesitas agregar al menos un agente activo.
            </p>
        <?php endif; ?>

        <section class="cards_top">
            <ul>
                <li class="card_info_propiedades">
                    <i class="fa-solid fa-house icon"></i>
                    <div>
                        <h2>Propiedades activas</h2>
                        <p><?= e((string)$totalPropiedadesActivas) ?></p>
                    </div>
                </li>

                <li class="card_info_agente">
                    <i class="fa-solid fa-user icon"></i>
                    <div>
                        <h2>Agentes activos</h2>
                        <p><?= e((string)$totalAgentesActivos) ?></p>
                    </div>
                </li>
            </ul>
        </section>

        <section class="detalles_propiedad">

            <div class="contenedor-busqueda">
                <h2>Propiedades</h2>

                <form method="GET" action="<?= BASE_URL ?>Admin/Panel-propiedades.php" class="busqueda">

                    <input 
                        type="search" 
                        name="buscar"
                        placeholder="Buscar propiedad..."
                        value="<?= e($buscar) ?>"
                    >

                    <button type="submit">
                        Buscar
                    </button>
                </form>
            </div>

            <div class="subtitulos">
                <span>Propiedad</span>
                <span>Ubicación</span>
                <span>Tipo</span>
                <span>Precio</span>
                <span>Estado</span>
                <span>Acciones</span>
            </div>

            <div class="contenedor_agentes">
                <?php if (empty($propiedades)): ?>
                    <p>No hay propiedades registradas.</p>
                <?php endif; ?>

                <?php foreach ($propiedades as $propiedad): ?>
                    <?php
                        $imagen = $propiedad['imagen_principal'] ? BASE_URL . $propiedad['imagen_principal' ] : BASE_URL . 'Imagenes/casa1.jpg';

                    $imagenesJson = array_map(function ($imagenItem) {
                        return [
                            'id' => (int)$imagenItem['id'],
                            'url' => $imagenItem['imagen_url'],
                            'nombre' => basename((string)$imagenItem['imagen_url']),
                            'es_principal' => (int)$imagenItem['es_principal'],
                        ];
                    }, $imagenesPorPropiedad[(int)$propiedad['id']] ?? []);

                    $propiedadJson = json_encode([
                        'id' => $propiedad['id'],
                        'agente_id' => $propiedad['agente_id'],
                        'titulo' => $propiedad['titulo'],
                        'descripcion' => $propiedad['descripcion'],
                        'precio' => $propiedad['precio'],
                        'moneda' => $propiedad['moneda'],
                        'tipo_operacion' => $propiedad['tipo_operacion'],
                        'categoria_id' => $propiedad['categoria_id'],
                        'estado_publicacion' => $propiedad['estado_publicacion'],
                        'destacada' => $propiedad['destacada'],
                        'ciudad' => $propiedad['ciudad'],
                        'direccion_completa' => $propiedad['direccion_completa'],
                        'google_maps_url' => $propiedad['google_maps_url'],
                        'recamaras' => $propiedad['recamaras'],
                        'banos' => $propiedad['banos'],
                        'estacionamientos' => $propiedad['estacionamientos'],
                        'terreno_m2' => $propiedad['terreno_m2'],
                        'construccion_m2' => $propiedad['construccion_m2'],
                        'imagenes' => $imagenesJson,
                    ], JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT);
                ?>

                <article class="detalles_fila" data-property-row>
                    <div class="info_propiedad">
                        <img src="<?= e((string)$imagen) ?>" alt="<?= e((string)$propiedad['titulo']) ?>">

                        <div>
                            <h3><?= e((string)$propiedad['titulo']) ?></h3>
                            <p>ID: <?= e((string)$propiedad['id']) ?></p>
                        </div>
                    </div>

                    <span class="text_dentro">
                        <?= e(ciudadPanelTexto($propiedad['ciudad'])) ?>
                    </span>

                    <span class="text_dentro">
                        <?= e($propiedad['categoria_nombre'] ?? 'Sin categoría') ?>
                    </span>

                    <span class="text_dentro">
                        $<?= number_format((float)$propiedad['precio'], 0) ?>
                        <?= e((string)$propiedad['moneda']) ?>
                    </span>

                    <span class="text_dentro">
                        <?= e((string)$propiedad['estado_publicacion']) ?>
                    </span>

                    <div class="acciones">
                        <button
                            class="editar"
                            type="button"
                            data-edit
                            data-propiedad='<?= e((string)($propiedadJson ?: "{}")) ?>'
                        >
                            Editar
                        </button>

                        <button
                            class="eliminar"
                            type="button"
                            data-delete
                            data-id="<?= e((string)$propiedad['id']) ?>"
                        >
                            Eliminar
                        </button>
                    </div>
                </article>

                <?php endforeach; ?>

            </div>
        </section>

        <section class="cards_propiedades">
            <div class="text_top">
                <h2>Últimas propiedades añadidas</h2>
            </div>

            <div class="contenedor_cards">
                <?php foreach ($ultimasPropiedades as $propiedad): ?>
                    <?php $imagen = $propiedad['imagen_principal'] ? BASE_URL . $propiedad['imagen_principal' ] : BASE_URL . 'Imagenes/casa1.jpg'; ?>

                    <a class="card_link" href="<?= BASE_URL ?>Usuario/PropiedadInfo.php?id=<?= e((string)$propiedad['id']) ?>">
                        <article class="propiedad-card">
                            <img 
                                class="propiedad-img" 
                                src="<?= e((string)$imagen) ?>" 
                                alt="<?= e((string)$propiedad['titulo']) ?>"
                            >

                            <div class="text_top_info">
                                <h2><?= e((string)$propiedad['titulo']) ?></h2>

                                <p class="precio-mxn">
                                    $<?= number_format((float)$propiedad['precio'], 0) ?>
                                    <?= e((string)$propiedad['moneda']) ?>
                                </p>
                            </div>
                        </article>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

    </main>
</div>

<!-- MODAL AGREGAR PROPIEDAD -->
<dialog class="modal" id="modalAgregar">
    <form class="modal-content" action="<?= BASE_URL ?>Backend/Propiedades/agregar-propiedad.php" method="POST" enctype="multipart/form-data">
        <input 
            type="hidden"
            name="csrf_token"
            value="<?= $_SESSION['csrf_token'] ?>"
        >
        <div class="modal-header">
            <h2>Agregar propiedad</h2>

            <button type="button" class="modal-close" data-close-modal>
                &times;
            </button>
        </div>

        <div class="modal-body">

            <label>
                Agente
                <select name="agente_id" required>
                    <option value="">Selecciona un agente</option>

                    <?php foreach ($agentes as $agente): ?>
                        <option value="<?= e((string)$agente['id']) ?>">
                            <?= e((string)$agente['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Título
                <input type="text" name="titulo" placeholder="Casa en Puente Real" required>
            </label>

            <label>
                Tipo de operación
                <select name="tipo_operacion" required>
                    <option value="venta">Venta</option>
                    <option value="renta">Renta</option>
                    <option value="traspaso">Traspaso</option>
                </select>
            </label>

            <label>
                Tipo de propiedad
                <div class="tipo-propiedad-box">
                    <select name="categoria_id" required>

                        <option value="">Selecciona una categoría</option>

                        <?php foreach($categorias as $categoria): ?>

                            <option value="<?= e((string)$categoria['id']) ?>">
                                <?= e((string)$categoria['nombre']) ?>
                            </option>

                        <?php endforeach; ?>
                    </select>

                    <button
                        type="button"
                        class="btnNuevaCategoria plus">
                        +
                    </button>

                    <button
                        type="button"
                        class="btnAdministrarCategorias minus">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </label>

            <label>
                Ciudad
                <select name="ciudad" required>
                    <option value="ciudad_obregon">Ciudad Obregón</option>
                    <option value="navojoa">Navojoa</option>
                    <option value="san_carlos">San Carlos</option>
                    <option value="guaymas">Guaymas</option>
                </select>
            </label>

            <label>
                Precio
                <input type="number" name="precio" placeholder="2500000" min="0" step="1" required>
            </label>

            <label>
                Moneda
                <input type="text" name="moneda" value="MXN">
            </label>

            <label>
                Estado
                <select name="estado_publicacion">
                    <option value="activo">Activo</option>
                    <option value="inactivo">Inactivo</option>
                </select>
            </label>

            <label>
                Recámaras
                <input type="number" name="recamaras" value="0" min="0" step="1">
            </label>

            <label>
                Baños
                <input type="number" name="banos" value="0" min="0" step="1">
            </label>

            <label>
                Estacionamientos
                <input type="number" name="estacionamientos" value="0" min="0" step="1">
            </label>

            <label>
                Terreno m²
                <input type="number" name="terreno_m2" min="0" step="1">
            </label>

            <label>
                Construcción m²
                <input type="number" name="construccion_m2" min="0" step="1">
            </label>

            <div>
                <p class="imagenes-actuales-title">Imágenes de la propiedad</p>

                <div class="upload-box" id="dropAgregar">
                    <input 
                        type="file" 
                        name="imagenes[]" 
                        id="inputImagenesAgregar"
                        class="upload-input"
                        accept="image/*"
                        multiple
                    >

                    <label for="inputImagenesAgregar" class="upload-label">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <strong>Arrastra imágenes aquí</strong>
                        <span>o haz clic para seleccionarlas</span>
                    </label>
                </div>

                <div class="lista-archivos" id="previewAgregar"></div>
            </div>

            <label>
                Dirección completa
                <input
                    type="text"
                    name="direccion_completa"
                    id="direccionAgregar"
                    placeholder="Calle, número, colonia"
                    required>
            </label>

            <input
                type="hidden"
                name="google_maps_url"
                id="googleMapsAgregar">

            <div class="mapa-preview">
                <iframe
                    id="iframeAgregar"
                    loading="lazy"
                    allowfullscreen>
                </iframe>
            </div>

            <label class="checkbox-reemplazar">
                <input type="checkbox" name="destacada" value="1">
                Destacada
            </label>

            <label>
                Descripción
                <textarea name="descripcion" placeholder="Descripción de la propiedad"></textarea>
            </label>

        </div>

        <div class="modal-actions">
            <button type="button" class="btn-secondary" data-close-modal>
                Cancelar
            </button>

            <button type="submit" class="btn-primary">
                Guardar propiedad
            </button>
        </div>

    </form>
</dialog>

<!-- MODAL EDITAR PROPIEDAD -->
<dialog class="modal" id="modalEditar">
    <form class="modal-content" action="<?= BASE_URL ?>Backend/Propiedades/editar-propiedad.php" method="POST" enctype="multipart/form-data">
        <input 
            type="hidden"
            name="csrf_token"
            value="<?= $_SESSION['csrf_token'] ?>"
        >
        <input type="hidden" name="id" id="edit_id">
        <input type="hidden" name="imagen_principal_id" id="imagen_principal_id">

        <div class="modal-header">
            <h2>Editar propiedad</h2>

            <button type="button" class="modal-close" data-close-modal>
                &times;
            </button>
        </div>

        <div class="modal-body">

            <label>
                Agente
                <select name="agente_id" id="edit_agente_id" required>
                    <option value="">Selecciona un agente</option>

                    <?php foreach ($agentes as $agente): ?>
                        <option value="<?= e((string)$agente['id']) ?>">
                            <?= e((string)$agente['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Título
                <input type="text" name="titulo" id="edit_titulo" required>
            </label>

            <label>
                Tipo de operación
                <select name="tipo_operacion" id="edit_tipo_operacion" required>
                    <option value="venta">Venta</option>
                    <option value="renta">Renta</option>
                    <option value="traspaso">Traspaso</option>
                </select>
            </label>

            <label>
                Tipo de propiedad

                <div class="tipo-propiedad-box">

                    <select name="categoria_id" id="edit_categoria_id" required>

                        <option value="">Selecciona una categoría</option>

                        <?php foreach ($categorias as $categoria): ?>
                            <option value="<?= e($categoria['id']) ?>">
                                <?= e($categoria['nombre']) ?>
                            </option>
                        <?php endforeach; ?>

                    </select>

                    <button
                        type="button"
                        class="btnNuevaCategoria plus">
                        +
                    </button>

                    <button
                        type="button"
                        class="btnAdministrarCategorias minus">
                        <i class="fa-solid fa-trash"></i>
                    </button>

                </div>

            </label>

            <label>
                Ciudad
                <select name="ciudad" id="edit_ciudad" required>
                    <option value="ciudad_obregon">Ciudad Obregón</option>
                    <option value="navojoa">Navojoa</option>
                    <option value="san_carlos">San Carlos</option>
                    <option value="guaymas">Guaymas</option>
                </select>
            </label>

            <label>
                Precio
                <input type="number" name="precio" id="edit_precio" min="0" step="1" required>
            </label>

            <label>
                Moneda
                <input type="text" name="moneda" id="edit_moneda">
            </label>

            <label>
                Estado
                <select name="estado_publicacion" id="edit_estado_publicacion">
                    <option value="activo">Activo</option>
                    <option value="inactivo">Inactivo</option>
                </select>
            </label>

            <label>
                Recámaras
                <input type="number" name="recamaras" id="edit_recamaras" min="0" step="1">
            </label>

            <label>
                Baños
                <input type="number" name="banos" id="edit_banos" min="0" step="1">
            </label>

            <label>
                Estacionamientos
                <input type="number" name="estacionamientos" id="edit_estacionamientos" min="0" step="1">
            </label>

            <label>
                Terreno m²
                <input type="number" name="terreno_m2" id="edit_terreno_m2" min="0" step="1">
            </label>

            <label>
                Construcción m²
                <input type="number" name="construccion_m2" id="edit_construccion_m2" min="0" step="1">
            </label>

            <div class="imagenes-actuales-box">
                <p class="imagenes-actuales-title">Imágenes ya subidas</p>

                <div 
                    class="lista-archivos" 
                    id="imagenesActualesEditar"
                ></div>
            </div>

            <div>
                <p class="imagenes-actuales-title">Agregar más imágenes</p>

                <div class="upload-box" id="dropEditar">
                    <input 
                        type="file" 
                        name="imagenes[]" 
                        id="inputImagenesEditar"
                        class="upload-input"
                        accept="image/*"
                        multiple
                    >

                    <label for="inputImagenesEditar" class="upload-label">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <strong>Arrastra imágenes aquí</strong>
                        <span>o haz clic para seleccionarlas</span>
                    </label>
                </div>

                <div class="lista-archivos" id="previewEditar"></div>
            </div>

            <label class="checkbox-reemplazar">
                <input type="checkbox" name="reemplazar_imagenes" value="1">
                Reemplazar imágenes anteriores
            </label>

            <label>
                Dirección completa
                <input
                    type="text"
                    name="direccion_completa"
                    id="edit_direccion_completa"
                    required>
            </label>

            <input
                type="hidden"
                name="google_maps_url"
                id="edit_google_maps_url">

            <div class="mapa-preview">
                <iframe
                    id="iframeEditar"
                    loading="lazy"
                    allowfullscreen>
                </iframe>
            </div>

            <label class="checkbox-reemplazar">
                <input type="checkbox" name="destacada" id="edit_destacada" value="1">
                Destacada
            </label>

            <label>
                Descripción
                <textarea name="descripcion" id="edit_descripcion"></textarea>
            </label>

        </div>

        <div class="modal-actions">
            <button type="button" class="btn-secondary" data-close-modal>
                Cancelar
            </button>

            <button type="submit" class="btn-primary">
                Guardar cambios
            </button>
        </div>

    </form>
</dialog>

<!-- MODAL NUEVA CATEGORÍA -->
<dialog class="modal modal-small" id="modalCategoria">

<form class="modal-content"
    action="<?= BASE_URL ?>Backend/guardar-categoria.php"
    method="POST">
        <input 
            type="hidden"
            name="csrf_token"
            value="<?= $_SESSION['csrf_token'] ?>"
        >
        <div class="modal-header">
            <h2>Nueva categoría</h2>

            <button
                type="button"
                class="modal-close"
                data-close-modal>
                &times;
            </button>
        </div>

        <div class="modal-body">

            <label>

                Nombre de la categoría

                <input
                    type="text"
                    name="nombre"
                    placeholder="Ej. Casa Campestre"
                    required>

            </label>

        </div>

        <div class="modal-actions">

            <button
                type="button"
                class="btn-secondary"
                data-close-modal>

                Cancelar

            </button>

            <button
                type="submit"
                class="btn-primary">

                Guardar

            </button>

        </div>

    </form>

</dialog>

    <!-- MODAL ELIMINAR CATEGORIA -->
<dialog class="modal" id="modalCategorias">

    <div class="modal-header">
        <h2>Administrar categorias</h2>

        <button type="button" class="modal-close" data-close-modal>
            &times;
        </button>
    </div>

    <div class="categorias-admin-list">

    <?php foreach ($categorias as $categoria): ?>

        <div class="categoria-item">

            <div class="categoria-info">
                <span class="categoria-nombre">
                    <?= e($categoria['nombre']) ?>
                </span>

                <?php if ($categoria['protegida']): ?>
                    <span class="categoria-badge protegida">
                        Protegida
                    </span>
                <?php endif; ?>
            </div>

            <?php if (!$categoria['protegida']): ?>

                <form       
                    action="<?= BASE_URL ?>Backend/eliminar-categoria.php"
                    method="POST">

                    <input 
                        type="hidden"
                        name="csrf_token"
                        value="<?= $_SESSION['csrf_token'] ?>"
                    >

                    <input
                        type="hidden"
                        name="id"
                        value="<?= $categoria['id'] ?>">

                    <button
                        type="button"
                        class="btn-danger btn-eliminar-categoria"
                        data-id="<?= $categoria['id'] ?>">
                        Eliminar
                    </button>

                </form>

            <?php endif; ?>

        </div>

    <?php endforeach; ?>

</div>
</dialog>
    
<!-- MODAL ELIMINAR PROPIEDAD -->
<dialog class="modal modal-small" id="modalEliminar">
    <form class="modal-content" action="<?= BASE_URL ?>Backend/Propiedades/eliminar-propiedad.php" method="POST">
        <input 
            type="hidden"
            name="csrf_token"
            value="<?= $_SESSION['csrf_token'] ?>"
        >
        <input type="hidden" name="id" id="delete_id">

        <div class="modal-header">
            <h2>Eliminar propiedad</h2>

            <button type="button" class="modal-close" data-close-modal>
                &times;
            </button>
        </div>

        <div class="modal-body">
            <p>¿Seguro que quieres eliminar esta propiedad?</p>

            <p class="warning">
                Esta acción no se podrá deshacer.
            </p>
        </div>

        <div class="modal-actions">
            <button type="button" class="btn-secondary" data-close-modal>
                Cancelar
            </button>

            <button type="submit" class="btn-danger">
                Sí, eliminar
            </button>
        </div>

    </form>
</dialog>

<!-- MODAL CONFIRMAR ELIMINAR CATEGORIA -->
<dialog class="modal modal-small" id="modalEliminarCategoria">
    <form
        class="modal-content"
        action="<?= BASE_URL ?>Backend/eliminar-categoria.php"
        method="POST">

        <input
            type="hidden"
            name="csrf_token"
            value="<?= $_SESSION['csrf_token'] ?>"
        >

            type="hidden"
            name="id"
            id="delete_categoria_id">

        <div class="modal-header">
            <h2>Eliminar categoría</h2>

            <button
                type="button"
                class="modal-close"
                data-close-modal>
                &times;
            </button>
        </div>

        <div class="modal-body">
            <p>
                ¿Seguro que quieres eliminar esta categoría?
            </p>

            <p class="warning">
                Esta acción no se podrá deshacer.
            </p>
        </div>

        <div class="modal-actions">
            <button
                type="button"
                class="btn-secondary"
                data-close-modal>
                Cancelar
            </button>

            <button
                type="submit"
                class="btn-danger">
                Sí, eliminar
            </button>
        </div>

    </form>
</dialog>

<!-- MODAL DE ÉXITO -->
<?php if ($modalExitoTitulo !== ''): ?>
    <dialog class="modal-exito" id="modalExito">
        <div class="modal-exito-content">

            <div class="modal-exito-icon">
                <i class="fa-solid fa-check"></i>
            </div>

            <h2><?= e($modalExitoTitulo) ?></h2>

            <p><?= e($modalExitoMensaje) ?></p>

            <button type="button" id="cerrarModalExito">
                Entendido
            </button>

        </div>
    </dialog>
<?php endif; ?>

<script>
const BASE_URL = '<?= e(BASE_URL) ?>';

const modalAgregar = document.getElementById('modalAgregar');
const modalEditar = document.getElementById('modalEditar');
const modalEliminar = document.getElementById('modalEliminar');

function ponerValorSeguro(id, valor) {
    const input = document.getElementById(id);

    if (!input) {
        return;
    }

    input.value = valor ?? '';
}

function ponerCheckSeguro(id, valor) {
    const input = document.getElementById(id);

    if (!input) {
        return;
    }

    input.checked = Number(valor) === 1;
}

function abrirModal(modal) {
    if (modal) {
        modal.showModal();
    }
}

function cerrarModal(modal) {
    if (modal) {
        modal.close();
    }
}

/* Abrir modal agregar */
document.addEventListener('click', (event) => {
    const openButton = event.target.closest('[data-open-modal]');

    if (!openButton) {
        return;
    }

    const modal = document.getElementById(openButton.dataset.openModal);

    abrirModal(modal);
});

/* Cerrar modales */
document.addEventListener('click', (event) => {
    const closeButton = event.target.closest('[data-close-modal]');

    if (!closeButton) {
        return;
    }

    const modal = closeButton.closest('dialog');

    cerrarModal(modal);
});

/* Borrar categoría */
const modalCategorias = document.getElementById("modalCategorias");

document.querySelectorAll(".btnAdministrarCategorias").forEach(btn => {
    btn.addEventListener("click", () => {
        modalCategorias.showModal();
    });
});

/* Confirmar eliminar categoría */
const modalEliminarCategoria = document.getElementById("modalEliminarCategoria");
const deleteCategoriaId = document.getElementById("delete_categoria_id");

document.querySelectorAll(".btn-eliminar-categoria").forEach(btn => {

    btn.addEventListener("click", () => {

        deleteCategoriaId.value = btn.dataset.id;

        modalEliminarCategoria.showModal();

    });

});

/* Imágenes actuales en editar */
function renderizarImagenesActuales(imagenes) {
    const contenedor = document.getElementById('imagenesActualesEditar');
    const inputPrincipal = document.getElementById('imagen_principal_id');

    if (!contenedor) {
        return;
    }

    contenedor.innerHTML = '';

    if (inputPrincipal) {
        inputPrincipal.value = '';
    }

    if (!imagenes || imagenes.length === 0) {
        contenedor.innerHTML = '<p class="sin-imagenes">Esta propiedad no tiene imágenes guardadas.</p>';
        return;
    }

    imagenes.forEach((imagen) => {
        const card = document.createElement('div');
        card.className = 'archivo-preview archivo-existente';

        if (Number(imagen.es_principal) === 1) {
            card.classList.add('imagen-principal-activa');

            if (inputPrincipal) {
                inputPrincipal.value = imagen.id;
            }
        }

        const badgePrincipal = Number(imagen.es_principal) === 1
            ? '<span class="archivo-principal">Principal</span>'
            : '<span class="archivo-principal oculto">Principal</span>';

        card.innerHTML = `
            <img src="${BASE_URL}${imagen.url}" alt="${imagen.nombre}">

            <div class="archivo-info">
                <span class="archivo-nombre" title="${imagen.nombre}">
                    ${imagen.nombre}
                </span>

                <span class="archivo-peso">
                    Imagen guardada
                </span>

                ${badgePrincipal}

                <button 
                    type="button" 
                    class="archivo-principal-btn" 
                    data-id="${imagen.id}"
                >
                    Hacer principal
                </button>

                <button 
                    type="button" 
                    class="archivo-quitar quitar-imagen-existente" 
                    data-id="${imagen.id}"
                >
                    Quitar
                </button>
            </div>
        `;

        contenedor.appendChild(card);
    });
}

/* Elegir imagen principal */
document.addEventListener('click', (event) => {
    const botonPrincipal = event.target.closest('.archivo-principal-btn');

    if (!botonPrincipal) {
        return;
    }

    const idImagen = botonPrincipal.dataset.id;
    const inputPrincipal = document.getElementById('imagen_principal_id');
    const contenedor = document.getElementById('imagenesActualesEditar');

    if (!inputPrincipal || !contenedor) {
        return;
    }

    inputPrincipal.value = idImagen;

    contenedor.querySelectorAll('.archivo-preview').forEach((card) => {
        card.classList.remove('imagen-principal-activa');

        const badge = card.querySelector('.archivo-principal');

        if (badge) {
            badge.classList.add('oculto');
        }
    });

    const cardSeleccionada = botonPrincipal.closest('.archivo-preview');

    if (cardSeleccionada) {
        cardSeleccionada.classList.add('imagen-principal-activa');

        const badge = cardSeleccionada.querySelector('.archivo-principal');

        if (badge) {
            badge.classList.remove('oculto');
        }
    }
});

/* Quitar imágenes existentes */
document.addEventListener('click', (event) => {
    const botonQuitar = event.target.closest('.quitar-imagen-existente');

    if (!botonQuitar) {
        return;
    }

    const idImagen = botonQuitar.dataset.id;
    const formEditar = document.querySelector('#modalEditar form');

    if (!formEditar) {
        return;
    }

    const inputHidden = document.createElement('input');
    inputHidden.type = 'hidden';
    inputHidden.name = 'eliminar_imagenes[]';
    inputHidden.value = idImagen;

    formEditar.appendChild(inputHidden);

    const card = botonQuitar.closest('.archivo-preview');

    if (card) {
        card.remove();
    }
});

/* Botón editar */
document.addEventListener('click', (event) => {
    const editButton = event.target.closest('[data-edit]');

    if (!editButton) {
        return;
    }

    let propiedad = {};

    try {
        propiedad = JSON.parse(editButton.dataset.propiedad);
    } catch (error) {
        console.error('Error leyendo data-propiedad:', error);
        return;
    }

    ponerValorSeguro('edit_id', propiedad.id);
    ponerValorSeguro('edit_categoria_id', propiedad.categoria_id);
    ponerValorSeguro('edit_agente_id', propiedad.agente_id);
    ponerValorSeguro('edit_titulo', propiedad.titulo);
    ponerValorSeguro('edit_tipo_operacion', propiedad.tipo_operacion || 'venta');
    ponerValorSeguro('edit_ciudad', propiedad.ciudad || 'ciudad_obregon');
    ponerValorSeguro('edit_direccion_completa', propiedad.direccion_completa);
    ponerValorSeguro('edit_precio', propiedad.precio);
    ponerValorSeguro('edit_moneda', propiedad.moneda || 'MXN');
    ponerValorSeguro('edit_estado_publicacion', propiedad.estado_publicacion || 'activo');
    ponerValorSeguro('edit_recamaras', propiedad.recamaras || 0);
    ponerValorSeguro('edit_banos', propiedad.banos || 0);
    ponerValorSeguro('edit_estacionamientos', propiedad.estacionamientos || 0);
    ponerValorSeguro('edit_terreno_m2', propiedad.terreno_m2);
    ponerValorSeguro('edit_construccion_m2', propiedad.construccion_m2);
    ponerValorSeguro('edit_google_maps_url', propiedad.google_maps_url);
    ponerValorSeguro('edit_descripcion', propiedad.descripcion);

    ponerCheckSeguro('edit_destacada', propiedad.destacada);

    renderizarImagenesActuales(propiedad.imagenes ?? []);

    ponerValorSeguro('edit_direccion_completa',propiedad.direccion_completa);

    actualizarMapa(
        direccionEditar,
        googleEditar,
        iframeEditar
    );

    const previewEditar = document.getElementById('previewEditar');
    const inputImagenesEditar = document.getElementById('inputImagenesEditar');

    if (previewEditar) {
        previewEditar.innerHTML = '';
    }

    if (inputImagenesEditar) {
        inputImagenesEditar.value = '';
    }

    document.querySelectorAll('input[name="eliminar_imagenes[]"]').forEach((input) => {
        input.remove();
    });

    abrirModal(modalEditar);
});

/* Botón eliminar */
function abrirModalEliminar(idPropiedad) {
    const inputEliminar = document.getElementById('delete_id');
    const modalEliminarPropiedad = document.getElementById('modalEliminar');

    if (!inputEliminar || !modalEliminarPropiedad) {
        console.error('No se encontró el modal de eliminar o el input delete_id');
        return;
    }

    inputEliminar.value = idPropiedad;
    modalEliminarPropiedad.showModal();
}

document.addEventListener('click', (event) => {
    const deleteButton = event.target.closest('[data-delete]');

    if (!deleteButton) {
        return;
    }

    abrirModalEliminar(deleteButton.dataset.id);
});

/* Upload mini archivos */
function configurarUploadMini(dropId, inputId, previewId) {
    const dropzone = document.getElementById(dropId);
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);

    if (!dropzone || !input || !preview) {
        return;
    }

    let archivosSeleccionados = [];

    function formatearPeso(bytes) {
        if (bytes < 1024) {
            return bytes + ' B';
        }

        if (bytes < 1024 * 1024) {
            return (bytes / 1024).toFixed(1) + ' KB';
        }

        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }

    function actualizarInputFiles() {
        const dataTransfer = new DataTransfer();

        archivosSeleccionados.forEach((file) => {
            dataTransfer.items.add(file);
        });

        input.files = dataTransfer.files;
    }

    function renderizarArchivos() {
        preview.innerHTML = '';

        archivosSeleccionados.forEach((file, index) => {
            if (!file.type.startsWith('image/')) {
                return;
            }

            const reader = new FileReader();

            reader.onload = (event) => {
                const card = document.createElement('div');
                card.className = 'archivo-preview';

                card.innerHTML = `
                    <img src="${event.target.result}" alt="${file.name}">

                    <div class="archivo-info">
                        <span class="archivo-nombre" title="${file.name}">
                            ${file.name}
                        </span>

                        <span class="archivo-peso">
                            ${formatearPeso(file.size)}
                        </span>

                        <button type="button" class="archivo-quitar" data-index="${index}">
                            Quitar
                        </button>
                    </div>
                `;

                preview.appendChild(card);
            };

            reader.readAsDataURL(file);
        });

        actualizarInputFiles();
    }

    function agregarArchivos(files) {
        const nuevosArchivos = Array.from(files).filter((file) => {
            return file.type.startsWith('image/');
        });

        archivosSeleccionados = archivosSeleccionados.concat(nuevosArchivos);

        renderizarArchivos();
    }

    input.addEventListener('change', () => {
        agregarArchivos(input.files);
    });

    preview.addEventListener('click', (event) => {
        const botonQuitar = event.target.closest('.archivo-quitar');

        if (!botonQuitar) {
            return;
        }

        const index = Number(botonQuitar.dataset.index);

        archivosSeleccionados.splice(index, 1);

        renderizarArchivos();
    });

    dropzone.addEventListener('dragover', (event) => {
        event.preventDefault();
        dropzone.classList.add('dragover');
    });

    dropzone.addEventListener('dragleave', () => {
        dropzone.classList.remove('dragover');
    });

    dropzone.addEventListener('drop', (event) => {
        event.preventDefault();
        dropzone.classList.remove('dragover');

        agregarArchivos(event.dataTransfer.files);
    });
}

configurarUploadMini('dropAgregar', 'inputImagenesAgregar', 'previewAgregar');
configurarUploadMini('dropEditar', 'inputImagenesEditar', 'previewEditar');

/* Modal de éxito */
document.addEventListener('DOMContentLoaded', () => {
    const modalExito = document.getElementById('modalExito');
    const cerrarModalExito = document.getElementById('cerrarModalExito');

    if (!modalExito) {
        return;
    }

    modalExito.showModal();

    function cerrarExito() {
        modalExito.close();
    }

    if (cerrarModalExito) {
        cerrarModalExito.addEventListener('click', cerrarExito);
    }

    modalExito.addEventListener('click', (event) => {
        if (event.target === modalExito) {
            cerrarExito();
        }
    });
});

function actualizarMapa(inputDireccion, inputHidden, iframe){

    const direccion = inputDireccion.value.trim();

    if(direccion === ""){
        iframe.removeAttribute("src");
        inputHidden.value = "";
        return;
    }

    const url = "https://www.google.com/maps?q=" +
                encodeURIComponent(direccion) +
                "&output=embed";

    iframe.src = url;
    inputHidden.value = url;
}

/* ================================
   MAPA - AGREGAR PROPIEDAD
================================ */

const direccionAgregar = document.getElementById("direccionAgregar");
const iframeAgregar = document.getElementById("iframeAgregar");
const googleAgregar = document.getElementById("googleMapsAgregar");

direccionAgregar.addEventListener("input", () => {

    actualizarMapa(
        direccionAgregar,
        googleAgregar,
        iframeAgregar
    );

});

/* ================================
   MAPA - EDITAR PROPIEDAD
================================ */

const direccionEditar = document.getElementById("edit_direccion_completa");
const iframeEditar = document.getElementById("iframeEditar");
const googleEditar = document.getElementById("edit_google_maps_url");

direccionEditar.addEventListener("input", () => {

    actualizarMapa(
        direccionEditar,
        googleEditar,
        iframeEditar
    );

});

const modalCategoria = document.getElementById("modalCategoria");

document.querySelectorAll(".btnNuevaCategoria").forEach((boton)=>{

    boton.addEventListener("click", ()=>{

        modalCategoria.showModal();

    });

});
</script>
</body>
</html>
