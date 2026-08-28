<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/config.php";

require_once ROOT_PATH . "/Config/database.php";
require_once ROOT_PATH . "/Admin/auth.php";
requiere_editor();
$esAdmin = es_admin();
$pdo = db();

/* ================================
   MODAL DE Ã‰XITO
================================ */
$modalExitoTitulo = "";
$modalExitoMensaje = "";

if (!empty($_SESSION["modal_exito"])) {
    $modalExitoTitulo = $_SESSION["modal_exito"]["titulo"] ?? "";
    $modalExitoMensaje = $_SESSION["modal_exito"]["mensaje"] ?? "";

    unset($_SESSION["modal_exito"]);
}

/* ================================
   FUNCIONES HELPER
================================ */
function estadoPublicacionTexto($estado)
{
    switch ($estado) {
        case "activo":
            return "Activo";
        case "vendido":
            return "Vendido";
        case "rentado":
            return "Rentado";
        case "traspasado":
            return "Traspasado";
        default:
            return "Inactivo";
    }
}

/* ================================
   DATOS GENERALES Y FILTROS
================================ */
$buscar = trim($_GET["buscar"] ?? "");
$mes = $_GET["mes"] ?? date("m");
$anio = $_GET["anio"] ?? date("Y");

if ($esAdmin) {
    $agentes = $pdo
        ->query(
            "
        SELECT id, nombre
        FROM agentes
        WHERE activo = 1
        ORDER BY nombre ASC
    ",
        )
        ->fetchAll();
} else {
    $stmt = $pdo->prepare("
        SELECT id, nombre
        FROM agentes
        WHERE id = ?
        AND activo = 1
    ");
    $stmt->execute([$_SESSION["id_agente"]]);
    $agentes = $stmt->fetchAll();
}

$totalPropiedadesActivas = (int) $pdo
    ->query(
        "
    SELECT COUNT(*)
    FROM propiedades
    WHERE estado_publicacion = 'activo'
",
    )
    ->fetchColumn();

$totalAgentesActivos = (int) $pdo
    ->query(
        "
    SELECT COUNT(*)
    FROM agentes
    WHERE activo = 1
",
    )
    ->fetchColumn();

/* ================================
   CONSULTA PRINCIPAL DE PROPIEDADES
================================ */
$sql = "
    SELECT
        p.*,
        a.nombre AS agente_nombre,
        c.nombre AS categoria_nombre,
        ci.nombre AS ciudad_nombre,
        (
            SELECT ip.archivo_url
            FROM multimedia_propiedades ip
            WHERE ip.propiedad_id = p.id
            ORDER BY ip.es_principal DESC, ip.orden ASC, ip.id ASC
            LIMIT 1
        ) AS imagen_principal
    FROM propiedades p
    LEFT JOIN agentes a ON p.agente_id = a.id
    LEFT JOIN categorias_propiedad c ON p.categoria_id = c.id
    LEFT JOIN ciudades ci ON p.ciudad_id = ci.id
";

$params = [];
$where = [];

// Filtro por rol
if (!$esAdmin) {
    $where[] = "p.agente_id = ?";
    $params[] = $_SESSION["id_agente"];
}

// Buscador
if ($buscar !== "") {
    $estadoBuscado = strtolower($buscar);

    if (
        in_array($estadoBuscado, ["activo", "inactivo", "vendido", "rentado", "traspasado"], true)
    ) {
        $where[] = "p.estado_publicacion = ?";
        $params[] = $estadoBuscado;
    } else {
        $where[] = "
        (
            p.titulo LIKE ?
            OR p.direccion_completa LIKE ?
            OR ci.nombre LIKE ?
            OR c.nombre LIKE ?
            OR p.tipo_operacion LIKE ?
            OR p.estado_publicacion LIKE ?
            OR a.nombre LIKE ?
        )";

        $like = "%{$buscar}%";
        $params = array_merge($params, [$like, $like, $like, $like, $like, $like, $like]);
    }
}

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY p.creado_en DESC, p.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$propiedades = $stmt->fetchAll();

/* ================================
   IMÃGENES POR PROPIEDAD
================================ */
$imagenesPorPropiedad = [];
$idsPropiedades = array_column($propiedades, "id");

if (!empty($idsPropiedades)) {
    $placeholders = implode(",", array_fill(0, count($idsPropiedades), "?"));

    $stmtImagenesPanel = $pdo->prepare("
        SELECT 
            id,
            propiedad_id,
            archivo_url,
            texto_alternativo,
            es_principal,
            orden
        FROM multimedia_propiedades
        WHERE propiedad_id IN ($placeholders)
        ORDER BY propiedad_id ASC, es_principal DESC, orden ASC, id ASC
    ");

    $stmtImagenesPanel->execute($idsPropiedades);

    foreach ($stmtImagenesPanel->fetchAll() as $imagen) {
        $imagenesPorPropiedad[(int) $imagen["propiedad_id"]][] = $imagen;
    }
}

/* ================================
   CATEGORÃAS Y CIUDADES (PARA SELECTS)
================================ */
$categorias = $pdo
    ->query(
        "
    SELECT * FROM categorias_propiedad WHERE activo = 1 ORDER BY nombre ASC
",
    )
    ->fetchAll(PDO::FETCH_ASSOC);

$ciudades = $pdo
    ->query(
        "
    SELECT * FROM ciudades WHERE activo = 1 ORDER BY nombre ASC
",
    )
    ->fetchAll(PDO::FETCH_ASSOC);

/* ================================
   ÃšLTIMAS PROPIEDADES (RESPETANDO ROL)
================================ */
$sqlUltimas = "
    SELECT p.*, c.nombre AS categoria_nombre,
        (SELECT ip.archivo_url FROM multimedia_propiedades ip WHERE ip.propiedad_id = p.id ORDER BY ip.es_principal DESC, ip.orden ASC, ip.id ASC LIMIT 1) AS imagen_principal
    FROM propiedades p
    LEFT JOIN categorias_propiedad c ON c.id = p.categoria_id
";
$paramsUltimas = [];

if (!$esAdmin) {
    $sqlUltimas .= " WHERE p.agente_id = ?";
    $paramsUltimas[] = $_SESSION["id_agente"];
}

$sqlUltimas .= " ORDER BY p.creado_en DESC, p.id DESC LIMIT 4";

$stmtUltimas = $pdo->prepare($sqlUltimas);
$stmtUltimas->execute($paramsUltimas);

$ultimasPropiedades = $stmtUltimas->fetchAll();
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
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
</head>

<body>

<div class="admin-panel">

    <header class="admin-header">
        <div class="contenedor-logo">
            <a href="<?= BASE_URL ?>Admin/Panel-propiedades.php">
                <img class="logo-panel" src="<?= BASE_URL ?>Imagenes/Logosolo.webp" alt="Logo de Primavera inmobiliaria">
            </a>
        </div>
    
        <div class="left-adminh">
            <div class="header-top-panel">
                <h1>Panel Administrativo</h1>
                <p>GestiÃ³n de propiedades</p>

                <?php if (!empty($_SESSION["admin_nombre"])): ?>
                    <p>SesiÃ³n: <?= e((string) $_SESSION["admin_nombre"]) ?></p>
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
            <?php if ($esAdmin): ?>
            <a href="<?= BASE_URL ?>Admin/Panel-agente.php">Agentes</a>
            <a href="<?= BASE_URL ?>Admin/Panel-mensajes.php">Mensajes</a>
            <a class="btn-exportar" href="<?= BASE_URL ?>Backend/Reportes/exportar-excel.php?mes=<?= $mes ?>&anio=<?= $anio ?>">Exportar Excel</a>
            <?php endif; ?>        
        </nav>
    
        <form class="cerrar-sesion" action="<?= BASE_URL ?>Backend/cerrar-sesion.php" method="POST">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= $_SESSION["csrf_token"] ?? "" ?>"
            >

            <button
                class="cerrar-sesion"
                type="submit">
                Cerrar sesiÃ³n
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
                        <p><?= e((string) $totalPropiedadesActivas) ?></p>
                    </div>
                </li>

                <li class="card_info_agente">
                    <i class="fa-solid fa-user icon"></i>
                    <div>
                        <h2>Agentes activos</h2>
                        <p><?= e((string) $totalAgentesActivos) ?></p>
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
                <span>UbicaciÃ³n</span>
                <span>Tipo</span>
                <span>Precio</span>
                <span>Estado</span>
                <span>Fecha/CreaciÃ³n</span>
                <span>Acciones</span>
            </div>

            <div class="contenedor_agentes">
                <?php if (empty($propiedades)): ?>
                    <p>No hay propiedades registradas.</p>
                <?php endif; ?>
                
                <?php foreach ($propiedades as $propiedad): ?>
                    <?php
                    $imagen = $propiedad["imagen_principal"]
                        ? BASE_URL . $propiedad["imagen_principal"]
                        : BASE_URL . "Imagenes/casa1.webp";

                    $imagenesJson = array_map(function ($imagenItem) {
                        return [
                            "id" => (int) $imagenItem["id"],
                            "url" => $imagenItem["archivo_url"],
                            "nombre" => basename((string) $imagenItem["archivo_url"]),
                        ];
                    }, $imagenesPorPropiedad[(int) $propiedad["id"]] ?? []);

                    $propiedadJson = json_encode(
                        [
                            "id" => $propiedad["id"],
                            "agente_id" => $propiedad["agente_id"],
                            "titulo" => $propiedad["titulo"],
                            "descripcion" => $propiedad["descripcion"],
                            "precio" => $propiedad["precio"],
                            "moneda" => $propiedad["moneda"],
                            "tipo_operacion" => $propiedad["tipo_operacion"],
                            "categoria_id" => $propiedad["categoria_id"],
                            "estado_publicacion" => $propiedad["estado_publicacion"],
                            "destacada" => $propiedad["destacada"],
                            "ciudad_id" => $propiedad["ciudad_id"],
                            "direccion_completa" => $propiedad["direccion_completa"],
                            "google_maps_url" => $propiedad["google_maps_url"],
                            "recamaras" => $propiedad["recamaras"],
                            "banos" => $propiedad["banos"],
                            "estacionamientos" => $propiedad["estacionamientos"],
                            "terreno_m2" => $propiedad["terreno_m2"],
                            "construccion_m2" => $propiedad["construccion_m2"],
                            "imagenes" => $imagenesJson,
                        ],
                        JSON_UNESCAPED_UNICODE | JSON_HEX_APOS,
                    );
                    ?>

                <article class="detalles_fila" data-property-row>
                    <div class="info_propiedad">
                        <img src="<?= e((string) $imagen) ?>" alt="<?= e(
    (string) $propiedad["titulo"],
) ?>">
                
                        <div>
                            <h3><?= e((string) $propiedad["titulo"]) ?></h3>
                            <p>ID: <?= e((string) $propiedad["id"]) ?></p>
                        </div>
                    </div>
                
                    <!-- UBICACIÃ“N -->
                    <span class="text_dentro">
                        <?= e($propiedad["ciudad_nombre"] ?? "Sin ciudad") ?>
                    </span>
                
                    <span class="text_dentro">
                        <?= e($propiedad["categoria_nombre"] ?? "Sin categorÃ­a") ?>
                    </span>
                
                    <span class="text_dentro">
                        $<?= number_format((float) $propiedad["precio"], 0) ?>
                        <?= e((string) $propiedad["moneda"]) ?>
                    </span>
                
                    <?php $estado = $propiedad["estado_publicacion"]; ?>
                
                    <span class="estado estado-<?= $estado ?>">
                        <?= estadoPublicacionTexto($estado) ?>
                    </span>
                
                    <span class="text_dentro">
                        <?= date("d/m/Y", strtotime($propiedad["creado_en"])) ?>
                    </span>
                
                    <div class="acciones">
                        <button
                            class="editar"
                            type="button"
                            data-edit
                            data-propiedad="<?= e($propiedadJson ?: "{}", ENT_QUOTES, "UTF-8") ?>">
                            <i class="fa-regular fa-pen-to-square"></i>
                        </button>

                        <?php if ($esAdmin): ?>
                        <button
                            class="eliminar"
                            type="button"
                            data-delete
                            data-id="<?= e((string) $propiedad["id"]) ?>"
                        >
                            <i class="fa-solid fa-trash"></i>
                        </button>
                        
                        <button
                            class="operacion"
                            type="button"
                            data-operacion
                            data-id="<?= $propiedad["id"] ?>"
                            data-agente="<?= $propiedad["agente_id"] ?>"
                            data-tipo="<?= $propiedad["tipo_operacion"] ?>"
                            data-precio="<?= $propiedad["precio"] ?>"
                            data-moneda="<?= $propiedad["moneda"] ?>">
                            <i class="fa-solid fa-clipboard-check"></i>
                        </button>
                        <?php endif; ?>

                        <button
                            class="preview"
                            type="button"
                            onclick="window.location.href='<?= BASE_URL ?>Usuario/PropiedadInfo.php?slug=<?= urlencode(
    $propiedad["slug"],
) ?>&moneda=MXN'">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </article>
                        
                <?php endforeach; ?>
                        
            </div>
        </section>

        <section class="cards_propiedades">
            <div class="text_top">
                <h2>Ãšltimas propiedades aÃ±adidas</h2>
            </div>

            <div class="contenedor_cards">
                <?php foreach ($ultimasPropiedades as $propiedad): ?>
                    <?php $imagen = $propiedad["imagen_principal"]
                        ? BASE_URL . $propiedad["imagen_principal"]
                        : BASE_URL . "Imagenes/casa1.jpg"; ?>

                    <a class="card_link" href="<?= BASE_URL ?>Usuario/PropiedadInfo.php?slug=<?= urlencode(
    $propiedad["slug"],
) ?>&moneda=MXN">
                        <article class="propiedad-card">
                            <img 
                                class="propiedad-img" 
                                src="<?= e((string) $imagen) ?>" 
                                alt="<?= e((string) $propiedad["titulo"]) ?>"
                            >

                            <div class="text_top_info">
                                <h2><?= e((string) $propiedad["titulo"]) ?></h2>

                                <p class="precio-mxn">
                                    $<?= number_format((float) $propiedad["precio"], 0) ?>
                                    <?= e((string) $propiedad["moneda"]) ?>
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
            value="<?= $_SESSION["csrf_token"] ?? "" ?>"
        >
        <!-- INPUT OCULTO PARA ALMACENAR EL ORDEN EN FORMATO JSON -->
        <input type="hidden" name="orden_imagenes" id="orden_imagenes_agregar">

        <div class="modal-header">
            <h2>Agregar propiedad</h2>

            <button type="button" class="modal-close" data-close-modal>
                &times;
            </button>
        </div>

        <div class="modal-body">

            <label>
                Agente
                
                <?php if ($esAdmin): ?>
                
                    <select name="agente_id" required>
                        <option value="">Selecciona un agente</option>
                        <?php foreach ($agentes as $agente): ?>
                            <option value="<?= e((string) $agente["id"]) ?>">
                                <?= e((string) $agente["nombre"]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                        
                <?php else: ?>
                
                    <input 
                        type="text"
                        value="<?= e($agentes[0]["nombre"] ?? "") ?>"
                        disabled
                    >
                
                    <input 
                        type="hidden"
                        name="agente_id"
                        value="<?= e((string) $_SESSION["id_agente"]) ?>"
                    >
                
                <?php endif; ?>
            </label>

            <label>
                TÃ­tulo
                <input type="text" name="titulo" placeholder="Casa en Puente Real" required>
            </label>

            <label>
                Tipo de operaciÃ³n
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
                        <option value="">Selecciona una categorÃ­a</option>
                        <?php foreach ($categorias as $categoria): ?>
                            <option value="<?= e((string) $categoria["id"]) ?>">
                                <?= e((string) $categoria["nombre"]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <button
                        type="button"
                        class="btnNuevaCategoria plus"
                        data-open-modal="modalCategoria">
                        +
                    </button>

                    <button
                        type="button"
                        class="btnAdministrarCategorias minus"
                        data-open-modal="modalCategorias">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </label>

            <label>
                Ciudad
                <div class="tipo-propiedad-box">
                    <select name="ciudad_id" required>
                        <option value="">Selecciona una ciudad</option>
                        <?php foreach ($ciudades as $ciudad): ?>
                            <option value="<?= e((string) $ciudad["id"]) ?>">
                                <?= e((string) $ciudad["nombre"]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                        
                    <button
                        type="button"
                        class="btnNuevaCiudad plus"
                        data-open-modal="modalCiudad">
                        +
                    </button>
                        
                    <button
                        type="button"
                        class="btnAdministrarCiudades minus"
                        data-open-modal="modalCiudades">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </label>

            <label>
                Precio
                <input class="input-precio" type="text" name="precio" placeholder="2,500,000" inputmode="numeric" autocomplete="off" required>
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
                RecÃ¡maras
                <input type="number" name="recamaras" min="0" step="1">
            </label>

            <label>
                BaÃ±os
                <input type="number" name="banos" min="0" step="1">
            </label>

            <label>
                Estacionamientos
                <input type="number" name="estacionamientos" min="0" step="1">
            </label>

            <label>
                Terreno mÂ²
                <input type="number" id="add_terreno_m2" name="terreno_m2" min="0" step="any" class="incremento-personalizado">
            </label>

            <label>
                ConstrucciÃ³n mÂ²
                <input type="number" id="add_construccion_m2" name="construccion_m2" min="0" step="any" class="incremento-personalizado">
            </label>

            <!-- CAJA UNIFICADA DE IMÃGENES -->
            <div>
                <p class="imagenes-actuales-title">ImÃ¡genes de la propiedad (Arrastra para reordenar, la 1Âª es la principal)</p>

                <div class="upload-box" id="dropAgregar">
                    <input 
                        type="file" 
                        name="imagenes[]" 
                        id="inputImagenesAgregar"
                        class="upload-input"
                        accept="image/*,video/mp4,video/webm,video/quicktime"
                        multiple
                    >
                                        
                    <label for="inputImagenesAgregar" class="upload-label">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <strong>Arrastra imÃ¡genes o videos aquÃ­</strong>
                        <span>o haz clic para seleccionarlos</span>
                    </label>
                </div>

                <div class="lista-archivos" id="previewAgregar"></div>
            </div>

            <label>
                DirecciÃ³n completa
                <input
                    type="text"
                    name="direccion_completa"
                    id="direccionAgregar"
                    placeholder="Calle, nÃºmero, colonia">
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
                DescripciÃ³n
                <textarea name="descripcion" placeholder="DescripciÃ³n de la propiedad"></textarea>
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
    <form action="<?= BASE_URL ?>Backend/Propiedades/editar-propiedad.php" method="POST" enctype="multipart/form-data">
        <input 
            type="hidden"
            name="csrf_token"
            value="<?= $_SESSION["csrf_token"] ?? "" ?>"
        >
        <input type="hidden" name="id" id="edit_id">
        <input type="hidden" name="imagen_principal_id" id="imagen_principal_id">
        
        <!-- INPUT OCULTO PARA ALMACENAR EL ORDEN EN FORMATO JSON -->
        <input type="hidden" name="orden_imagenes" id="orden_imagenes_editar">

        <div class="modal-header">
            <h2>Editar propiedad</h2>

            <button type="button" class="modal-close" data-close-modal>
                &times;
            </button>
        </div>

        <div class="modal-body">

            <label>
                Agente
                
                <?php if ($esAdmin): ?>
                
                    <select name="agente_id" id="edit_agente_id" required>
                        <option value="">Selecciona un agente</option>
                        <?php foreach ($agentes as $agente): ?>
                            <option value="<?= e((string) $agente["id"]) ?>">
                                <?= e((string) $agente["nombre"]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                        
                <?php else: ?>
                
                    <input 
                        type="text"
                        value="<?= e($agentes[0]["nombre"] ?? "") ?>"
                        disabled
                    >
                
                    <input 
                        type="hidden"
                        name="agente_id"
                        value="<?= e((string) $_SESSION["id_agente"]) ?>"
                    >
                
                <?php endif; ?>
            </label>

            <label>
                TÃ­tulo
                <input type="text" name="titulo" id="edit_titulo" required>
            </label>

            <label>
                Tipo de operaciÃ³n
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
                        <option value="">Selecciona una categorÃ­a</option>
                        <?php foreach ($categorias as $categoria): ?>
                            <option value="<?= e((string) $categoria["id"]) ?>">
                                <?= e((string) $categoria["nombre"]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <button
                        type="button"
                        class="btnNuevaCategoria plus"
                        data-open-modal="modalCategoria">
                        +
                    </button>

                    <button
                        type="button"
                        class="btnAdministrarCategorias minus"
                        data-open-modal="modalCategorias">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </label>

            <label>
                Ciudad
                <div class="tipo-propiedad-box">
                    <select name="ciudad_id" id="edit_ciudad_id" required>
                        <option value="">Selecciona una ciudad</option>
                        <?php foreach ($ciudades as $ciudad): ?>
                            <option value="<?= e((string) $ciudad["id"]) ?>">
                                <?= e((string) $ciudad["nombre"]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <button
                        type="button"
                        class="btnNuevaCiudad plus"
                        data-open-modal="modalCiudad">
                        +
                    </button>
                        
                    <button
                        type="button"
                        class="btnAdministrarCiudades minus"
                        data-open-modal="modalCiudades">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </label>

            <label>
                Precio
                <input class="input-precio" type="text" name="precio" id="edit_precio" placeholder="2,500,000" inputmode="numeric" autocomplete="off" required>
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
                RecÃ¡maras
                <input type="number" name="recamaras" id="edit_recamaras" min="0" step="1">
            </label>

            <label>
                BaÃ±os
                <input type="number" name="banos" id="edit_banos" min="0" step="1">
            </label>

            <label>
                Estacionamientos
                <input type="number" name="estacionamientos" id="edit_estacionamientos" min="0" step="1">
            </label>

            <label>
                Terreno mÂ²
                <input type="number" id="edit_terreno_m2" name="terreno_m2" min="0" step="any" class="incremento-personalizado">
            </label>

            <label>
                ConstrucciÃ³n mÂ²
                <input type="number" id="edit_construccion_m2" name="construccion_m2" min="0" step="any" class="incremento-personalizado">
            </label>

            <!-- CAJA UNIFICADA DE IMÃGENES -->
            <div>
                <p class="imagenes-actuales-title">ImÃ¡genes de la propiedad (Arrastra para reordenar, la 1Âª es la principal)</p>

                <div class="upload-box" id="dropEditar">
                    <input 
                        type="file" 
                        name="imagenes[]" 
                        id="inputImagenesEditar"
                        class="upload-input"
                        accept="image/*,video/mp4,video/webm,video/quicktime"
                        multiple
                    >
                    
                    <label for="inputImagenesEditar" class="upload-label">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <strong>Arrastra imÃ¡genes o videos aquÃ­</strong>
                        <span>o haz clic para agregar mÃ¡s archivos</span>
                    </label>
                </div>

                <div class="lista-archivos" id="imagenesActualesEditar"></div>
            </div>

            <label class="checkbox-reemplazar">
                <input type="checkbox" name="reemplazar_imagenes" value="1">
                Reemplazar imÃ¡genes anteriores
            </label>

            <label>
                DirecciÃ³n completa
                <input
                    type="text"
                    name="direccion_completa"
                    id="edit_direccion_completa">
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
                DescripciÃ³n
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

<!-- MODAL NUEVA CATEGORÃA -->
<dialog class="modal modal-small" id="modalCategoria">
    <form class="modal-content" action="<?= BASE_URL ?>Backend/guardar-categoria.php" method="POST">
        <input 
            type="hidden"
            name="csrf_token"
            value="<?= $_SESSION["csrf_token"] ?? "" ?>"
        >
        <div class="modal-header">
            <h2>Nueva categorÃ­a</h2>
            <button type="button" class="modal-close" data-close-modal>&times;</button>
        </div>

        <div class="modal-body">
            <label>
                Nombre de la categorÃ­a
                <input type="text" name="nombre" placeholder="Ej. Casa Campestre" required>
            </label>
        </div>

        <div class="modal-actions">
            <button type="button" class="btn-secondary" data-close-modal>Cancelar</button>
            <button type="submit" class="btn-primary">Guardar</button>
        </div>
    </form>
</dialog>

<!-- MODAL ADMINISTRAR CATEGORIAS -->
<dialog class="modal" id="modalCategorias">
    <div class="modal-header">
        <h2>Administrar categorÃ­as</h2>
        <button type="button" class="modal-close" data-close-modal>&times;</button>
    </div>

    <div class="categorias-admin-list">
    <?php foreach ($categorias as $categoria): ?>
        <div class="categoria-item">
            <div class="categoria-info">
                <span class="categoria-nombre"><?= e($categoria["nombre"]) ?></span>
                <?php if (!empty($categoria["protegida"])): ?>
                    <span class="categoria-badge protegida">Protegida</span>
                <?php endif; ?>
            </div>

            <?php if (empty($categoria["protegida"])): ?>
                <button 
                    type="button" 
                    class="btn-danger btn-eliminar-cat-modal"
                    data-id="<?= $categoria["id"] ?>">
                    Eliminar
                </button>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    </div>
</dialog>

<!-- MODAL NUEVA CIUDAD -->
<dialog class="modal modal-small" id="modalCiudad">
    <form class="modal-content" action="<?= BASE_URL ?>Backend/guardar-ciudad.php" method="POST">
        <input 
            type="hidden"
            name="csrf_token"
            value="<?= $_SESSION["csrf_token"] ?? "" ?>"
        >
        
        <div class="modal-header">
            <h2>Nueva ciudad</h2>
            <button type="button" class="modal-close" data-close-modal>&times;</button>
        </div>

        <div class="modal-body">
            <label>
                Nombre de la ciudad
                <input type="text" name="nombre" placeholder="Ej. Guaymas" required>
            </label>
        </div>

        <div class="modal-actions">
            <button type="button" class="btn-secondary" data-close-modal>Cancelar</button>
            <button type="submit" class="btn-primary">Guardar</button>
        </div>
    </form>
</dialog>

<!-- MODAL ADMINISTRAR CIUDADES -->
<dialog class="modal" id="modalCiudades">
    <div class="modal-header">
        <h2>Administrar ciudades</h2>
        <button type="button" class="modal-close" data-close-modal>&times;</button>
    </div>

    <div class="categorias-admin-list">
    <?php foreach ($ciudades as $ciudad): ?>
        <div class="categoria-item">
            <div class="categoria-info">
                <span class="categoria-nombre"><?= e($ciudad["nombre"]) ?></span>
                <?php if (!empty($ciudad["protegida"])): ?>
                    <span class="categoria-badge protegida">Protegida</span>
                <?php endif; ?>
            </div>

            <?php if (empty($ciudad["protegida"])): ?>
                <button 
                    type="button" 
                    class="btn-danger btn-eliminar-ciu-modal" 
                    data-id="<?= $ciudad["id"] ?>">
                    Eliminar
                </button>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    </div>
</dialog>

<!-- MODAL OPERACIÃ“N REALIZADA -->
<dialog class="modal" id="modalOperacion">
    <form class="modal-content" action="<?= BASE_URL ?>Backend/registrar-operacion.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION["csrf_token"] ?? "" ?>">
        <input type="hidden" name="propiedad_id" id="operacion_propiedad_id">
        <input type="hidden" name="agente_id" id="operacion_agente_id">

        <div class="modal-header">
            <h2>Registrar operaciÃ³n</h2>
            <button type="button" class="modal-close" data-close-modal>&times;</button>
        </div>

        <div class="modal-body">
            <label>
                Tipo de operaciÃ³n
                <select name="tipo_operacion" id="operacion_tipo" required>
                    <option value="venta">Venta</option>
                    <option value="renta">Renta</option>
                    <option value="traspaso">Traspaso</option>
                </select>
            </label>

            <label>
                Cliente
                <input type="text" name="cliente_nombre" required>
            </label>

            <label>
                TelÃ©fono
                <input type="text" name="cliente_telefono">
            </label>

            <label>
                Correo electrÃ³nico
                <input type="email" name="cliente_email">
            </label>

            <label>
                Fecha
                <input type="date" name="fecha_operacion" value="<?= date("Y-m-d") ?>" required>
            </label>

            <label>
                Precio final
                <input type="number" step="0.01" name="precio" id="operacion_precio" required>
            </label>

            <label>
                Moneda
                <select name="moneda" id="operacion_moneda">
                    <option value="MXN">MXN</option>
                    <option value="USD">USD</option>
                </select>
            </label>

            <div id="grupo_renta">
                <label>
                    Tiempo de renta (meses)
                    <input type="number" min="1" name="meses_renta">
                </label>
            </div>

            <label>
                Observaciones
                <textarea name="observaciones" rows="4"></textarea>
            </label>

            <input type="hidden" name="estado_publicacion" value="inactivo">
        </div>

        <div class="modal-actions">
            <button type="button" class="btn-secondary" data-close-modal>Cancelar</button>
            <button type="submit" class="btn-primary">Guardar operaciÃ³n</button>
        </div>
    </form>
</dialog>

<!-- MODAL ELIMINAR PROPIEDAD -->
<dialog class="modal modal-small" id="modalEliminar">
    <form class="modal-content" action="<?= BASE_URL ?>Backend/Propiedades/eliminar-propiedad.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION["csrf_token"] ?? "" ?>">
        <input type="hidden" name="id" id="delete_id">

        <div class="modal-header">
            <h2>Eliminar propiedad</h2>
            <button type="button" class="modal-close" data-close-modal>&times;</button>
        </div>

        <div class="modal-body">
            <p>Â¿Seguro que quieres eliminar esta propiedad?</p>
            <p class="warning">Esta acciÃ³n no se podrÃ¡ deshacer.</p>
        </div>

        <div class="modal-actions">
            <button type="button" class="btn-secondary" data-close-modal>Cancelar</button>
            <button type="submit" class="btn-danger">SÃ­, eliminar</button>
        </div>
    </form>
</dialog>

<!-- MODAL CONFIRMAR ELIMINAR CATEGORIA -->
<dialog class="modal modal-small" id="modalEliminarCategoria">
    <form class="modal-content" action="<?= BASE_URL ?>Backend/eliminar-categoria.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION["csrf_token"] ?? "" ?>">
        <input type="hidden" name="id" id="delete_categoria_id">

        <div class="modal-header">
            <h2>Eliminar categorÃ­a</h2>
            <button type="button" class="modal-close" data-close-modal>&times;</button>
        </div>

        <div class="modal-body">
            <p>Â¿Seguro que quieres eliminar esta categorÃ­a?</p>
            <p class="warning">Esta acciÃ³n no se podrÃ¡ deshacer.</p>
        </div>

        <div class="modal-actions">
            <button type="button" class="btn-secondary" data-close-modal>Cancelar</button>
            <button type="submit" class="btn-danger">SÃ­, eliminar</button>
        </div>
    </form>
</dialog>

<!-- MODAL CONFIRMAR ELIMINAR CIUDAD -->
<dialog class="modal modal-small" id="modalEliminarCiudad">
    <form class="modal-content" action="<?= BASE_URL ?>Backend/eliminar-ciudad.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION["csrf_token"] ?? "" ?>">
        <input type="hidden" name="id" id="delete_ciudad_id">

        <div class="modal-header">
            <h2>Eliminar Ciudad</h2>
            <button type="button" class="modal-close" data-close-modal>&times;</button>
        </div>

        <div class="modal-body">
            <p>Â¿EstÃ¡s seguro de que deseas eliminar esta ciudad?</p>
            <p class="warning">Esta acciÃ³n no se podrÃ¡ deshacer.</p>
        </div>

        <div class="modal-actions">
            <button type="button" class="btn-secondary" data-close-modal>Cancelar</button>
            <button type="submit" class="btn-danger">Eliminar</button>
        </div>
    </form>
</dialog>

<!-- MODAL DE Ã‰XITO -->
<?php if ($modalExitoTitulo !== ""): ?>
    <dialog class="modal-exito" id="modalExito">
        <div class="modal-exito-content">
            <div class="modal-exito-icon">
                <i class="fa-solid fa-check"></i>
            </div>
            <h2><?= e($modalExitoTitulo) ?></h2>
            <p><?= e($modalExitoMensaje) ?></p>
            <button type="button" id="cerrarModalExito">Entendido</button>
        </div>
    </dialog>
<?php endif; ?>

<!-- MODAL CROPPER (ENCUADRE) -->
<dialog id="modalCropper" class="modal">
    <div class="modal-content" style="max-width: 700px;">
        <h3>Ajustar Encuadre</h3>
        <div class="cropper-container-wrapper" style="max-height: 450px; overflow: hidden; background: #000;">
            <img id="imagenACropper" src="" style="max-width: 100%; display: block;">
        </div>
        <div class="modal-actions" style="margin-top: 15px; display: flex; gap: 10px; justify-content: flex-end;">
            <button type="button" class="btn-secondary" id="btnCancelarCrop">Cancelar</button>
            <button type="button" class="btn-primary" id="btnGuardarCrop">Aplicar corte</button>
        </div>
    </div>
</dialog>
<!-- MODAL DE ERROR DE PESO DE ARCHIVO -->
<dialog class="modal-exito modal-error-peso" id="modalErrorPeso">
    <div class="modal-exito-content">
        <div class="modal-exito-icon" style="background-color: #f8d7da; color: #dc3545;">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h2>Archivo demasiado pesado</h2>
        <p id="textoErrorPeso">El archivo supera el lÃ­mite permitido.</p>
        <button type="button" id="cerrarModalErrorPeso">Entendido</button>
    </div>
</dialog>

<script>
const BASE_URL = "<?= e(BASE_URL) ?>";

/* =========================================================
   MODALES
========================================================= */

const modalAgregar = document.getElementById("modalAgregar");
const modalEditar = document.getElementById("modalEditar");
const modalEliminar = document.getElementById("modalEliminar");
const modalOperacion = document.getElementById("modalOperacion");
const modalCropper = document.getElementById("modalCropper");
const modalEliminarCategoria = document.getElementById("modalEliminarCategoria");
const modalEliminarCiudad = document.getElementById("modalEliminarCiudad");
const modalErrorPeso = document.getElementById("modalErrorPeso");

const textoErrorPeso = document.getElementById("textoErrorPeso");
const cerrarModalErrorPeso = document.getElementById("cerrarModalErrorPeso");

/* =========================================================
   CROPPER
========================================================= */

let cropperInstancia = null;
let itemActualCrop = null;

/* =========================================================
   LISTAS DE ARCHIVOS
========================================================= */

let listaArchivosAgregar = [];
let listaArchivosEditar = [];

/* =========================================================
   REFERENCIAS MAPAS
========================================================= */

const direccionAgregar = document.getElementById("direccionAgregar");
const iframeAgregar = document.getElementById("iframeAgregar");
const googleAgregar = document.getElementById("googleMapsAgregar");

const direccionEditar = document.getElementById("edit_direccion_completa");
const iframeEditar = document.getElementById("iframeEditar");
const googleEditar = document.getElementById("edit_google_maps_url");

/* =========================================================
   ICONOS
========================================================= */

const SVG_CROP = `
<svg xmlns="http://www.w3.org/2000/svg"
     width="16"
     height="16"
     viewBox="0 0 24 24"
     fill="none"
     stroke="currentColor"
     stroke-width="2"
     stroke-linecap="round"
     stroke-linejoin="round">
    <path d="M6 2v14a2 2 0 0 0 2 2h14"/>
    <path d="M18 22V8a2 2 0 0 0-2-2H2"/>
</svg>
`;

const SVG_TRASH = `
<svg xmlns="http://www.w3.org/2000/svg"
     width="16"
     height="16"
     viewBox="0 0 24 24"
     fill="none"
     stroke="currentColor"
     stroke-width="2"
     stroke-linecap="round"
     stroke-linejoin="round">
    <polyline points="3 6 5 6 21 6"/>
    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
</svg>
`;

/* =========================================================
   LÃMITES
========================================================= */

const MAX_IMAGE_SIZE = 10 * 1024 * 1024;
const MAX_VIDEO_SIZE = 15 * 1024 * 1024;

/* =========================================================
   FUNCIONES AUXILIARES
========================================================= */

function ponerValorSeguro(id, valor) {
  const input = document.getElementById(id);

  if (input) {
    input.value = valor ?? "";
  }
}

function ponerCheckSeguro(id, valor) {
  const input = document.getElementById(id);

  if (input) {
    input.checked = Number(valor) === 1;
  }
}

function abrirModal(modal) {
  if (!modal) return;

  if (typeof modal.showModal === "function") {
    modal.showModal();
  } else {
    modal.style.display = "flex";
  }
}

function cerrarModal(modal) {
  if (!modal) return;

  if (typeof modal.close === "function") {
    modal.close();
  } else {
    modal.style.display = "none";
  }
}

function cerrarModalCropper() {
  if (cropperInstancia) {
    cropperInstancia.destroy();
    cropperInstancia = null;
  }

  itemActualCrop = null;

  cerrarModal(modalCropper);
}

function formatearPeso(bytes) {
  if (!bytes) {
    return "0 B";
  }

  if (bytes < 1024) {
    return bytes + " B";
  }

  if (bytes < 1024 * 1024) {
    return (bytes / 1024).toFixed(1) + " KB";
  }

  return (bytes / (1024 * 1024)).toFixed(1) + " MB";
}

function escaparHTML(texto) {
  const div = document.createElement("div");

  div.textContent = texto ?? "";

  return div.innerHTML;
}

/* =========================================================
   CREAR OBJETO PARA ARCHIVO NUEVO
========================================================= */

function procesarNuevoArchivo(file) {
  return {
    tipo: "nuevo",

    fileOriginal: file,

    fileRecortado: file,

    urlPreview: URL.createObjectURL(file),
  };
}

/* =========================================================
   ELIMINAR IMAGEN EXISTENTE
========================================================= */

function registrarEliminacionExistente(id) {
  const formEditar = document.querySelector("#modalEditar form");

  if (!formEditar || !id) {
    return;
  }

  /*
     Evitar duplicar el mismo ID
    */

  const existentes = formEditar.querySelectorAll('input[name="eliminar_imagenes[]"]');

  for (const input of existentes) {
    if (Number(input.value) === Number(id)) {
      return;
    }
  }

  const hiddenDel = document.createElement("input");

  hiddenDel.type = "hidden";
  hiddenDel.name = "eliminar_imagenes[]";
  hiddenDel.value = id;

  formEditar.appendChild(hiddenDel);
}

/* =========================================================
   EVENTOS GLOBALES
========================================================= */

document.addEventListener("click", function (event) {
  /* -----------------------------------------
       ABRIR MODAL
    ----------------------------------------- */

  const openButton = event.target.closest("[data-open-modal]");

  if (openButton) {
    const modal = document.getElementById(openButton.dataset.openModal);

    abrirModal(modal);

    return;
  }

  /* -----------------------------------------
       CERRAR MODAL
    ----------------------------------------- */

  const closeButton = event.target.closest("[data-close-modal]");

  if (closeButton) {
    const modal = closeButton.closest("dialog") || closeButton.closest(".modal");

    cerrarModal(modal);

    return;
  }

  /* -----------------------------------------
       ELIMINAR PROPIEDAD
    ----------------------------------------- */

  const deleteButton = event.target.closest("[data-delete]");

  if (deleteButton) {
    const inputEliminar = document.getElementById("delete_id");

    if (inputEliminar && modalEliminar) {
      inputEliminar.value = deleteButton.dataset.id;

      abrirModal(modalEliminar);
    }

    return;
  }

  /* -----------------------------------------
       OPERACIÃ“N
    ----------------------------------------- */

  const operacionButton = event.target.closest("[data-operacion]");

  if (operacionButton) {
    ponerValorSeguro("operacion_propiedad_id", operacionButton.dataset.id);

    ponerValorSeguro("operacion_agente_id", operacionButton.dataset.agente);

    ponerValorSeguro("operacion_tipo", operacionButton.dataset.tipo);

    ponerValorSeguro("operacion_precio", operacionButton.dataset.precio);

    ponerValorSeguro("operacion_moneda", operacionButton.dataset.moneda);

    abrirModal(modalOperacion);

    return;
  }

  /* -----------------------------------------
       ELIMINAR CATEGORÃA
    ----------------------------------------- */

  const btnEliminarCat = event.target.closest(".btn-eliminar-cat-modal");

  if (btnEliminarCat) {
    ponerValorSeguro("delete_categoria_id", btnEliminarCat.dataset.id);

    abrirModal(modalEliminarCategoria);

    return;
  }

  /* -----------------------------------------
       ELIMINAR CIUDAD
    ----------------------------------------- */

  const btnEliminarCiu = event.target.closest(".btn-eliminar-ciu-modal");

  if (btnEliminarCiu) {
    ponerValorSeguro("delete_ciudad_id", btnEliminarCiu.dataset.id);

    abrirModal(modalEliminarCiudad);

    return;
  }

  /* -----------------------------------------
       QUITAR ARCHIVO - AGREGAR
    ----------------------------------------- */

  const btnQuitarAgregar = event.target.closest(".archivo-quitar");

  if (btnQuitarAgregar) {
    const index = Number(btnQuitarAgregar.dataset.index);

    const item = listaArchivosAgregar[index];

    if (item?.urlPreview) {
      URL.revokeObjectURL(item.urlPreview);
    }

    listaArchivosAgregar.splice(index, 1);

    renderizarAgregar();

    return;
  }

  /* -----------------------------------------
       QUITAR ARCHIVO - EDITAR
    ----------------------------------------- */

  const btnQuitarEditar = event.target.closest(".quitar-item-editar");

  if (btnQuitarEditar) {
    const index = Number(btnQuitarEditar.dataset.index);

    const item = listaArchivosEditar[index];

    if (!item) {
      return;
    }

    if (item.tipo === "existente") {
      registrarEliminacionExistente(item.id);
    }

    if (item.urlPreview) {
      URL.revokeObjectURL(item.urlPreview);
    }

    listaArchivosEditar.splice(index, 1);

    renderizarEditar();

    return;
  }
});

/* =========================================================
   ORDENAR IMÃGENES
========================================================= */

function actualizarAtributosImagenes(contenedor, esEdicion = false) {
  if (!contenedor) {
    return;
  }

  const cards = contenedor.querySelectorAll(".card-imagen-item");

  const inputPrincipal = document.getElementById("imagen_principal_id");

  const inputOrden = document.getElementById(
    esEdicion ? "orden_imagenes_editar" : "orden_imagenes_agregar",
  );

  const ordenData = [];

  let primeraImagenEncontrada = false;

  cards.forEach((card, index) => {
    const badge = card.querySelector(".archivo-principal");

    const esVideo = card.dataset.esVideo === "1";

    /*
         La primera IMAGEN serÃ¡ la principal.

         No importa si antes hay videos:
         buscamos la primera imagen.
        */

    const debeSerPrincipal = !esVideo && !primeraImagenEncontrada;

    if (debeSerPrincipal) {
      primeraImagenEncontrada = true;

      card.classList.add("imagen-principal-activa");

      if (badge) {
        badge.classList.remove("oculto");
      }

      if (esEdicion && inputPrincipal) {
        inputPrincipal.value = card.dataset.id || "";
      }
    } else {
      card.classList.remove("imagen-principal-activa");

      if (badge) {
        badge.classList.add("oculto");
      }
    }

    /*
         Guardar orden para ediciÃ³n
        */

    if (esEdicion) {
      ordenData.push({
        tipo: card.dataset.tipo || "nuevo",

        id: card.dataset.id || null,

        nombre: card.dataset.nombre || null,
      });
    }
  });

  if (inputPrincipal && !primeraImagenEncontrada) {
    inputPrincipal.value = "";
  }

  if (inputOrden && esEdicion) {
    inputOrden.value = JSON.stringify(ordenData);
  }
}

/* =========================================================
   SORTABLE
========================================================= */

function inicializarSortable(contenedorId, esEdicion = false) {
  const contenedor = document.getElementById(contenedorId);

  if (!contenedor || typeof Sortable === "undefined") {
    return;
  }

  if (contenedor._sortableInstance) {
    contenedor._sortableInstance.destroy();
  }

  contenedor._sortableInstance = new Sortable(contenedor, {
    animation: 150,

    ghostClass: "sortable-ghost",

    onMove: function (evt) {
      /*
                 No permitimos colocar un video
                 como primer elemento.

                 Esto solamente afecta el orden visual.
                */

      const arrastrandoVideo = evt.dragged.dataset.esVideo === "1";

      if (arrastrandoVideo) {
        const seraPrimero =
          evt.related === evt.related.parentNode.firstElementChild && !evt.willInsertAfter;

        if (seraPrimero) {
          evt.dragged.classList.add("borde-rojo-error");

          return false;
        }
      }

      evt.dragged.classList.remove("borde-rojo-error");
    },

    onEnd: function (evt) {
      evt.item.classList.remove("borde-rojo-error");

      const arregloInterno = esEdicion ? listaArchivosEditar : listaArchivosAgregar;

      if (evt.oldIndex == null || evt.newIndex == null) {
        return;
      }

      const itemMovido = arregloInterno.splice(evt.oldIndex, 1)[0];

      if (itemMovido) {
        arregloInterno.splice(evt.newIndex, 0, itemMovido);
      }

      /*
                 Actualizar Ã­ndices
                */

      const cards = contenedor.querySelectorAll(".card-imagen-item");

      cards.forEach((card, index) => {
        const btnEliminar = card.querySelector(".btn-eliminar");

        if (btnEliminar) {
          btnEliminar.dataset.index = index;
        }

        const btnCrop = card.querySelector(".btn-crop");

        if (btnCrop) {
          btnCrop.dataset.index = index;
        }
      });

      actualizarAtributosImagenes(contenedor, esEdicion);
    },
  });
}

/* =========================================================
   RENDERIZAR AGREGAR
========================================================= */

function renderizarAgregar() {
  const preview = document.getElementById("previewAgregar");

  const inputFiles = document.getElementById("inputImagenesAgregar");

  if (!preview || !inputFiles) {
    return;
  }

  preview.innerHTML = "";

  const dt = new DataTransfer();

  listaArchivosAgregar.forEach((item, index) => {
    const archivoAEnviar = item.fileRecortado || item.fileOriginal || item.file;

    if (!archivoAEnviar) {
      return;
    }

    dt.items.add(archivoAEnviar);

    const src = item.urlPreview || URL.createObjectURL(archivoAEnviar);

    const nombre = (item.fileOriginal || item.file || archivoAEnviar).name;

    const tamano = archivoAEnviar.size;

    const esVideo = archivoAEnviar.type.startsWith("video/");

    const card = document.createElement("div");

    card.className = "card-imagen-item";

    card.dataset.tipo = "nuevo";

    card.dataset.nombre = nombre;

    card.dataset.esVideo = esVideo ? "1" : "0";

    card.innerHTML = `

                <span class="badge-principal archivo-principal oculto">
                    Principal
                </span>

                <div class="card-imagen-thumb">

                    ${
                      esVideo
                        ? `

                            <video
                                src="${src}"
                                class="img-preview-src"
                                preload="metadata">
                            </video>

                        `
                        : `

                            <img
                                src="${src}"
                                alt="${escaparHTML(nombre)}"
                                class="img-preview-src">

                        `
                    }

                    <div class="thumb-overlay">

                        ${
                          !esVideo
                            ? `

                                <button
                                    type="button"
                                    class="btn-icono btn-crop"
                                    data-tipo="agregar"
                                    data-index="${index}"
                                    title="Encuadrar">

                                    ${SVG_CROP}

                                </button>

                            `
                            : ""
                        }

                        <button
                            type="button"
                            class="btn-icono btn-eliminar archivo-quitar"
                            data-index="${index}"
                            title="Quitar">

                            ${SVG_TRASH}

                        </button>

                    </div>

                </div>

                <div class="card-imagen-info">

                    <p
                        class="nombre-foto"
                        title="${escaparHTML(nombre)}">

                        ${escaparHTML(nombre)}

                    </p>

                    <div class="meta-detalles">

                        <span>
                            ${formatearPeso(tamano)}
                        </span>

                        <span class="estado-tag ${esVideo ? "estado-video" : "estado-nueva"}">

                            ${esVideo ? "Video" : "Nueva"}

                        </span>

                    </div>

                </div>
            `;

    preview.appendChild(card);
  });

  /*
     IMPORTANTE:
     aquÃ­ se sincronizan los archivos
     reales que serÃ¡n enviados por PHP.
    */

  inputFiles.files = dt.files;

  actualizarAtributosImagenes(preview, false);

  inicializarSortable("previewAgregar", false);
}

/* =========================================================
   RENDERIZAR EDITAR
========================================================= */

function renderizarEditar() {
  const contenedor = document.getElementById("imagenesActualesEditar");

  const inputFiles = document.getElementById("inputImagenesEditar");

  if (!contenedor) {
    return;
  }

  contenedor.innerHTML = "";

  const dt = new DataTransfer();

  if (listaArchivosEditar.length === 0) {
    contenedor.innerHTML = `
            <p class="sin-imagenes">
                No hay elementos en la propiedad.
            </p>
        `;

    if (inputFiles) {
      inputFiles.files = dt.files;
    }

    return;
  }

  listaArchivosEditar.forEach((item, index) => {
    const card = document.createElement("div");

    card.className = "card-imagen-item";

    /* -----------------------------------------
               ARCHIVO EXISTENTE
            ----------------------------------------- */

    if (item.tipo === "existente") {
      const src = `${BASE_URL}${item.url}`;

      const esVideoExistente = /\.(mp4|webm|mov|avi)$/i.test(item.url);

      card.dataset.tipo = "existente";

      card.dataset.id = item.id;

      card.dataset.nombre = item.nombre;

      card.dataset.esVideo = esVideoExistente ? "1" : "0";

      card.innerHTML = `

                    <span class="badge-principal archivo-principal oculto">
                        Principal
                    </span>

                    <div class="card-imagen-thumb">

                        ${
                          esVideoExistente
                            ? `

                                <video
                                    src="${src}"
                                    class="img-preview-src"
                                    preload="metadata">
                                </video>

                            `
                            : `

                                <img
                                    src="${src}"
                                    alt="${escaparHTML(item.nombre)}"
                                    class="img-preview-src">

                            `
                        }

                        <div class="thumb-overlay">

                            ${
                              !esVideoExistente
                                ? `

                                    <button
                                        type="button"
                                        class="btn-icono btn-crop"
                                        data-tipo="editar"
                                        data-index="${index}"
                                        title="Encuadrar">

                                        ${SVG_CROP}

                                    </button>

                                `
                                : ""
                            }

                            <button
                                type="button"
                                class="btn-icono btn-eliminar quitar-item-editar"
                                data-index="${index}"
                                title="Quitar">

                                ${SVG_TRASH}

                            </button>

                        </div>

                    </div>

                    <div class="card-imagen-info">

                        <p
                            class="nombre-foto"
                            title="${escaparHTML(item.nombre)}">

                            ${escaparHTML(item.nombre)}

                        </p>

                        <div class="meta-detalles">

                            <span class="estado-tag ${
                              esVideoExistente ? "estado-video" : "estado-guardada"
                            }">

                                ${esVideoExistente ? "Video" : "Guardada"}

                            </span>

                        </div>

                    </div>

                `;
    }

/* -----------------------------------------
ARCHIVO NUEVO
----------------------------------------- */
    else {
    const archivoAEnviar = item.fileRecortado || item.fileOriginal || item.file;

    if (!archivoAEnviar) {
        return;
    }

    dt.items.add(archivoAEnviar);

    const src = item.urlPreview || URL.createObjectURL(archivoAEnviar);

    const nombre = (item.fileOriginal || item.file || archivoAEnviar).name;

    const tamano = archivoAEnviar.size;

    const esVideo = archivoAEnviar.type.startsWith("video/");

    card.dataset.tipo = "nuevo";

    card.dataset.nombre = nombre;

    card.dataset.esVideo = esVideo ? "1" : "0";

    card.innerHTML = `

                    <span class="badge-principal archivo-principal oculto">
                        Principal
                    </span>

                    <div class="card-imagen-thumb">

                        ${
                        esVideo
                            ? `

                                <video
                                    src="${src}"
                                    class="img-preview-src"
                                    preload="metadata">
                                </video>

                            `
                            : `

                                <img
                                    src="${src}"
                                    alt="${escaparHTML(nombre)}"
                                    class="img-preview-src">

                            `
                        }

                        <div class="thumb-overlay">

                            ${
                            !esVideo
                                ? `

                                    <button
                                        type="button"
                                        class="btn-icono btn-crop"
                                        data-tipo="editar"
                                        data-index="${index}"
                                        title="Encuadrar">

                                        ${SVG_CROP}

                                    </button>

                                `
                                : ""
                            }

                            <button
                                type="button"
                                class="btn-icono btn-eliminar quitar-item-editar"
                                data-index="${index}"
                                title="Quitar">

                                ${SVG_TRASH}

                            </button>

                        </div>

                    </div>

                    <div class="card-imagen-info">

                        <p
                            class="nombre-foto"
                            title="${escaparHTML(nombre)}">

                            ${escaparHTML(nombre)}

                        </p>

                        <div class="meta-detalles">

                            <span>
                                ${formatearPeso(tamano)}
                            </span>

                            <span class="estado-tag ${esVideo ? "estado-video" : "estado-nueva"}">

                                ${esVideo ? "Video" : "Nueva"}

                            </span>

                        </div>

                    </div>

                `;
    }

    contenedor.appendChild(card);
});

if (inputFiles) {
    inputFiles.files = dt.files;
}

actualizarAtributosImagenes(contenedor, true);

inicializarSortable("imagenesActualesEditar", true);
}

/* =========================================================
   CROPPER
========================================================= */

document.addEventListener("click", function (e) {
  const btnCrop = e.target.closest(".btn-crop");

  if (btnCrop) {
    const tipoModal = btnCrop.dataset.tipo;

    const index = Number(btnCrop.dataset.index);

    itemActualCrop = {
      tipoModal,
      index,
    };

    let srcImagen = "";

    if (tipoModal === "agregar") {
      const item = listaArchivosAgregar[index];

      if (!item) {
        return;
      }

      srcImagen = item.urlPreview || URL.createObjectURL(item.fileOriginal || item.file);
    } else {
      const item = listaArchivosEditar[index];

      if (!item) {
        return;
      }

      if (item.tipo === "existente") {
        srcImagen = `${BASE_URL}${item.url}`;
      } else {
        srcImagen = item.urlPreview || URL.createObjectURL(item.fileOriginal || item.file);
      }
    }

    const imgCropper = document.getElementById("imagenACropper");

    if (!imgCropper || typeof Cropper === "undefined") {
      return;
    }

    imgCropper.onload = function () {
      if (cropperInstancia) {
        cropperInstancia.destroy();
      }

      cropperInstancia = new Cropper(imgCropper, {
        aspectRatio: 16 / 9,

        viewMode: 1,
      });
    };

    imgCropper.src = srcImagen;

    abrirModal(modalCropper);

    return;
  }

  /* -----------------------------------------
           CANCELAR CROP
        ----------------------------------------- */

  if (e.target.closest("#btnCancelarCrop")) {
    cerrarModalCropper();

    return;
  }

  /* -----------------------------------------
           GUARDAR CROP
        ----------------------------------------- */

  if (e.target.closest("#btnGuardarCrop")) {
    if (!cropperInstancia || !itemActualCrop) {
      return;
    }

    cropperInstancia
      .getCroppedCanvas({
        width: 1200,

        height: 675,
      })
      .toBlob(
        function (blob) {
          if (!blob) {
            return;
          }

          const { tipoModal, index } = itemActualCrop;

          if (tipoModal === "agregar") {
            const item = listaArchivosAgregar[index];

            if (!item) {
              return;
            }

            const original = item.fileOriginal || item.file;

            const nuevoRecortado = new File([blob], original.name, {
              type: "image/jpeg",
            });

            if (item.urlPreview) {
              URL.revokeObjectURL(item.urlPreview);
            }

            listaArchivosAgregar[index] = {
              tipo: "nuevo",

              fileOriginal: original,

              fileRecortado: nuevoRecortado,

              urlPreview: URL.createObjectURL(nuevoRecortado),
            };

            renderizarAgregar();
          } else {
            const item = listaArchivosEditar[index];

            if (!item) {
              return;
            }

            if (item.tipo === "existente") {
              registrarEliminacionExistente(item.id);
            }

            const nombre =
              item.tipo === "existente"
                ? item.nombre
                : (item.fileOriginal || item.file)?.name || "imagen.jpg";

            const original = item.fileOriginal || item.file || null;

            const nuevoRecortado = new File([blob], nombre, {
              type: "image/jpeg",
            });

            if (item.urlPreview) {
              URL.revokeObjectURL(item.urlPreview);
            }

            listaArchivosEditar[index] = {
              tipo: "nuevo",

              fileOriginal: original || nuevoRecortado,

              fileRecortado: nuevoRecortado,

              urlPreview: URL.createObjectURL(nuevoRecortado),
            };

            renderizarEditar();
          }

          cerrarModalCropper();
        },

        "image/jpeg",

        0.9,
      );
  }
});

/* =========================================================
   VALIDACIÃ“N DE ARCHIVOS
========================================================= */

function obtenerLimiteArchivo(file) {
  if (file.type && file.type.startsWith("video/")) {
    return MAX_VIDEO_SIZE;
  }

  return MAX_IMAGE_SIZE;
}

function obtenerTextoLimite(file) {
  if (file.type && file.type.startsWith("video/")) {
    return "15 MB";
  }

  return "10 MB";
}

function validarArchivos(files) {
  const archivosValidos = [];
  const archivosPesados = [];

  Array.from(files || []).forEach(function (file) {
    const esImagen = file.type && file.type.startsWith("image/");

    const esVideo = file.type && file.type.startsWith("video/");

    /*
             Ignorar formatos desconocidos
            */

    if (!esImagen && !esVideo) {
      return;
    }

    const limite = obtenerLimiteArchivo(file);

    if (file.size > limite) {
      archivosPesados.push({
        nombre: file.name,

        tamano: file.size,

        limite: obtenerTextoLimite(file),
      });
    } else {
      archivosValidos.push(file);
    }
  });

  return {
    archivosValidos,

    archivosPesados,
  };
}

/* =========================================================
   MODAL ARCHIVOS PESADOS
========================================================= */

function mostrarModalArchivosPesados(archivosPesados) {
  if (!modalErrorPeso || !textoErrorPeso) {
    return;
  }

  let mensaje = "";

  if (archivosPesados.length === 1) {
    const archivo = archivosPesados[0];

    mensaje = `

            <strong>
                ${escaparHTML(archivo.nombre)}
            </strong>

            <br>

            Pesa ${formatearPeso(archivo.tamano)}

            y supera el lÃ­mite permitido
            de ${archivo.limite}.

        `;
  } else {
    mensaje = `

            <strong>
                Los siguientes archivos
                superan el lÃ­mite permitido:
            </strong>

            <ul
                style="
                    text-align:left;
                    margin-top:12px;
                "
            >

                ${archivosPesados
                  .map(
                    (archivo) => `

                                <li
                                    style="
                                        margin-bottom:8px;
                                    "
                                >

                                    <strong>
                                        ${escaparHTML(archivo.nombre)}
                                    </strong>

                                    <br>

                                    ${formatearPeso(archivo.tamano)}

                                    â€”
                                    lÃ­mite:
                                    ${archivo.limite}

                                </li>

                            `,
                  )
                  .join("")}

            </ul>

        `;
  }

  textoErrorPeso.innerHTML = mensaje;

  abrirModal(modalErrorPeso);
}

if (cerrarModalErrorPeso) {
  cerrarModalErrorPeso.addEventListener("click", function () {
    cerrarModal(modalErrorPeso);
  });
}

/* =========================================================
   CONFIGURAR DROPZONE
========================================================= */

function configurarDropzone(dropEl, inputEl, alCargar) {
  if (!dropEl || !inputEl) {
    return;
  }

  function procesarArchivos(files) {
    const resultado = validarArchivos(files);

    inputEl.value = "";

    if (resultado.archivosPesados.length > 0) {
      mostrarModalArchivosPesados(resultado.archivosPesados);
    }

    resultado.archivosValidos.forEach(function (file) {
      alCargar(file);
    });
  }

  inputEl.addEventListener("change", function () {
    procesarArchivos(inputEl.files);
  });

  dropEl.addEventListener("dragover", function (e) {
    e.preventDefault();

    dropEl.classList.add("dragover");
  });

  dropEl.addEventListener("dragleave", function () {
    dropEl.classList.remove("dragover");
  });

  dropEl.addEventListener("drop", function (e) {
    e.preventDefault();

    dropEl.classList.remove("dragover");

    procesarArchivos(e.dataTransfer.files);
  });
}

/* =========================================================
   DROPZONE AGREGAR
========================================================= */

configurarDropzone(
  document.getElementById("dropAgregar"),

  document.getElementById("inputImagenesAgregar"),

  function (file) {
    listaArchivosAgregar.push(procesarNuevoArchivo(file));

    renderizarAgregar();
  },
);

/* =========================================================
   DROPZONE EDITAR
========================================================= */

configurarDropzone(
  document.getElementById("dropEditar"),

  document.getElementById("inputImagenesEditar"),

  function (file) {
    listaArchivosEditar.push(procesarNuevoArchivo(file));

    renderizarEditar();
  },
);

/* =========================================================
   PREPARAR MODAL EDITAR
========================================================= */

document.addEventListener("click", function (event) {
  const editButton = event.target.closest("[data-edit]");

  if (!editButton) {
    return;
  }

  let propiedad = {};

  try {
    propiedad = JSON.parse(editButton.dataset.propiedad);
  } catch (error) {
    console.error("Error parseando data-propiedad:", error);

    return;
  }

  /* -----------------------------------------
           DATOS
        ----------------------------------------- */

  ponerValorSeguro("edit_id", propiedad.id);

  ponerValorSeguro("edit_categoria_id", propiedad.categoria_id);

  ponerValorSeguro("edit_agente_id", propiedad.agente_id);

  ponerValorSeguro("edit_titulo", propiedad.titulo);

  ponerValorSeguro("edit_tipo_operacion", propiedad.tipo_operacion || "venta");

  ponerValorSeguro("edit_ciudad_id", propiedad.ciudad_id);

  ponerValorSeguro("edit_direccion_completa", propiedad.direccion_completa);

  ponerValorSeguro("edit_moneda", propiedad.moneda || "MXN");

  ponerValorSeguro("edit_estado_publicacion", propiedad.estado_publicacion || "activo");

  ponerValorSeguro("edit_recamaras", propiedad.recamaras ?? 0);

  ponerValorSeguro("edit_banos", propiedad.banos ?? 0);

  ponerValorSeguro("edit_estacionamientos", propiedad.estacionamientos ?? 0);

  ponerValorSeguro("edit_terreno_m2", propiedad.terreno_m2);

  ponerValorSeguro("edit_construccion_m2", propiedad.construccion_m2);

  ponerValorSeguro("edit_google_maps_url", propiedad.google_maps_url);

  ponerValorSeguro("edit_descripcion", propiedad.descripcion);

  ponerCheckSeguro("edit_destacada", propiedad.destacada);

  /* -----------------------------------------
           PRECIO
        ----------------------------------------- */

  const precioInput = document.querySelector("#modalEditar .input-precio");

  if (precioInput && propiedad.precio !== undefined) {
    precioInput.value = Math.round(Number(propiedad.precio)).toLocaleString("en-US");
  }

  /* -----------------------------------------
           LIMPIAR ELIMINACIONES ANTERIORES
        ----------------------------------------- */

  document.querySelectorAll('input[name="eliminar_imagenes[]"]').forEach((input) => input.remove());

  /* -----------------------------------------
           IMÃGENES EXISTENTES
        ----------------------------------------- */

  let imagenesArray = propiedad.imagenes || [];

  if (typeof imagenesArray === "string") {
    try {
      imagenesArray = JSON.parse(imagenesArray);
    } catch (e) {
      imagenesArray = [];
    }
  }

  if (!Array.isArray(imagenesArray)) {
    imagenesArray = [];
  }

  listaArchivosEditar = imagenesArray.map(function (img) {
    return {
      tipo: "existente",

      id: img.id,

      url: img.url,

      nombre: img.nombre || img.url,
    };
  });

  renderizarEditar();

  actualizarMapa(direccionEditar, googleEditar, iframeEditar);

  abrirModal(modalEditar);
});

/* =========================================================
   MAPAS
========================================================= */

function actualizarMapa(inputDireccion, inputHidden, iframe) {
  if (!inputDireccion || !iframe || !inputHidden) {
    return;
  }

  const direccion = inputDireccion.value.trim();

  if (direccion === "") {
    iframe.removeAttribute("src");

    inputHidden.value = "";

    return;
  }

  const url = "https://www.google.com/maps?q=" + encodeURIComponent(direccion) + "&output=embed";

  iframe.src = url;

  inputHidden.value = url;
}

direccionAgregar?.addEventListener("input", function () {
  actualizarMapa(direccionAgregar, googleAgregar, iframeAgregar);
});

direccionEditar?.addEventListener("input", function () {
  actualizarMapa(direccionEditar, googleEditar, iframeEditar);
});

/* =========================================================
   INCREMENTOS
========================================================= */

document.querySelectorAll(".incremento-personalizado").forEach(function (input) {
  input.addEventListener("keydown", function (e) {
    let valor = parseFloat(this.value) || 0;

    if (e.key === "ArrowUp") {
      e.preventDefault();

      this.value = valor + 1;
    }

    if (e.key === "ArrowDown") {
      e.preventDefault();

      this.value = Math.max(0, valor - 1);
    }
  });
});

/* =========================================================
   FORMATO PRECIO
========================================================= */

document.querySelectorAll(".input-precio").forEach(function (input) {
  input.addEventListener("input", function () {
    let valor = this.value.replace(/\D/g, "");

    this.value = valor ? Number(valor).toLocaleString("en-US") : "";
  });
});

/* =========================================================
   VALIDACIÃ“N DEL PRECIO
========================================================= */

document.querySelectorAll("form").forEach(function (form) {
  form.addEventListener("submit", function () {
    form.querySelectorAll(".input-precio").forEach(function (input) {
      input.value = input.value.replace(/,/g, "");
    });
  });
});

/* =========================================================
   MODAL Ã‰XITO
========================================================= */

document.addEventListener("DOMContentLoaded", function () {
  const modalExito = document.getElementById("modalExito");

  if (!modalExito) {
    return;
  }

  abrirModal(modalExito);

  const cerrarExito = function () {
    cerrarModal(modalExito);
  };

  document.getElementById("cerrarModalExito")?.addEventListener("click", cerrarExito);

  modalExito.addEventListener("click", function (e) {
    if (e.target === modalExito) {
      cerrarExito();
    }
  });
});

/* =========================================================
   VALIDACIÃ“N FINAL DE PROPIEDAD
========================================================= */

/*
 IMPORTANTE:

 NO buscamos ".lista-archivos".

 Usamos directamente las listas
 que controlan los archivos reales.
*/

function validarFormularioPropiedad(e) {
  const formulario = this;

  const esAgregar = formulario === document.querySelector("#modalAgregar form");

  const esEditar = formulario === document.querySelector("#modalEditar form");

  let lista = esAgregar ? listaArchivosAgregar : listaArchivosEditar;

  /*
     ---------------------------------------------
     OBTENER IMÃGENES
     ---------------------------------------------
    */

  const imagenes = lista.filter(function (item) {
    /*
                 Archivo nuevo
                */

    if (item.tipo === "nuevo") {
      const archivo = item.fileRecortado || item.fileOriginal || item.file;

      return archivo && archivo.type && archivo.type.startsWith("image/");
    }

    /*
                 Archivo existente
                */

    if (item.tipo === "existente") {
      return !/\.(mp4|webm|mov|avi)$/i.test(item.url || "");
    }

    return false;
  });

  /*
     ---------------------------------------------
     DEBE EXISTIR AL MENOS UNA IMAGEN
     ---------------------------------------------
    */

  if (imagenes.length === 0) {
    e.preventDefault();

    alert(
      "Debes agregar al menos una imagen para la propiedad. " +
        "El video no puede ser el archivo principal.",
    );

    return false;
  }

  /*
     ---------------------------------------------
     VERIFICAR QUE LA PRIMERA POSICIÃ“N
     SEA UNA IMAGEN
     ---------------------------------------------
    */

  if (lista.length > 0) {
    const primerElemento = lista[0];

    let esVideo = false;

    if (primerElemento.tipo === "nuevo") {
      const archivo =
        primerElemento.fileRecortado || primerElemento.fileOriginal || primerElemento.file;

      esVideo = archivo && archivo.type && archivo.type.startsWith("video/");
    } else if (primerElemento.tipo === "existente") {
      esVideo = /\.(mp4|webm|mov|avi)$/i.test(primerElemento.url || "");
    }

    if (esVideo) {
      e.preventDefault();

      alert(
        "La primera posiciÃ³n debe ser una imagen. " + "Un video no puede ser la imagen principal.",
      );

      return false;
    }
  }

  /*
     ---------------------------------------------
     ACTUALIZAR PRINCIPAL
     ---------------------------------------------
    */

  if (esAgregar) {
    actualizarAtributosImagenes(document.getElementById("previewAgregar"), false);
  }

  if (esEditar) {
    actualizarAtributosImagenes(document.getElementById("imagenesActualesEditar"), true);
  }

  return true;
}

/* =========================================================
   REGISTRAR VALIDACIÃ“N
========================================================= */

const formAgregar = document.querySelector("#modalAgregar form");

if (formAgregar) {
  formAgregar.addEventListener("submit", validarFormularioPropiedad);
}

const formEditar = document.querySelector("#modalEditar form");

if (formEditar) {
  formEditar.addEventListener("submit", validarFormularioPropiedad);
}
</script>

</body>
</html>
