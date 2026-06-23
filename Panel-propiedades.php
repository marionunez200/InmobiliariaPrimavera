<?php
require_once __DIR__ . '/Config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = db();

function panelSelected(?string $actual, string $valor): string
{
    return $actual === $valor ? 'selected' : '';
}

function panelChecked($valor): string
{
    return (int)$valor === 1 ? 'checked' : '';
}

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

function operacionPanelTexto(?string $operacion): string
{
    return match ($operacion) {
        'venta' => 'Venta',
        'renta' => 'Renta',
        default => 'Operación'
    };
}

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

$buscar = trim($_GET['buscar'] ?? '');

if ($buscar !== '') {

    $stmt = $pdo->prepare("
        SELECT
            p.*,
            a.nombre AS agente_nombre,
            (
                SELECT ip.imagen_url
                FROM imagenes_propiedades ip
                WHERE ip.propiedad_id = p.id
                ORDER BY ip.es_principal DESC, ip.orden ASC, ip.id ASC
                LIMIT 1
            ) AS imagen_principal
        FROM propiedades p
        LEFT JOIN agentes a ON p.agente_id = a.id
        WHERE p.titulo LIKE ?
            OR a.nombre LIKE ?
            OR p.ciudad LIKE ?
        ORDER BY p.creado_en DESC, p.id DESC
    ");

    $texto = "%{$buscar}%";

    $stmt->execute([
        $texto,
        $texto,
        $texto
    ]);

} else {

    $stmt = $pdo->query("
        SELECT
            p.*,
            a.nombre AS agente_nombre,
            (
                SELECT ip.imagen_url
                FROM imagenes_propiedades ip
                WHERE ip.propiedad_id = p.id
                ORDER BY ip.es_principal DESC, ip.orden ASC, ip.id ASC
                LIMIT 1
            ) AS imagen_principal
        FROM propiedades p
        LEFT JOIN agentes a ON p.agente_id = a.id
        ORDER BY p.creado_en DESC, p.id DESC
    ");
}

$propiedades = $stmt->fetchAll();

$ultimasPropiedades = array_slice($propiedades, 0, 4);
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

    <link rel="stylesheet" href="./CSS/Panel-propiedades.css">
    <link rel="stylesheet" href="./CSS/Admin.header.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
</head>

<body>

<div class="admin-panel">

    <header class="admin-header">
        <div class="contenedor-logo">
            <a href="index.html">
                <img class="logo-panel" src="Imagenes/Logosolo.png" alt="Logo de Primavera inmobiliaria">
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

        <button class="button_anadir" data-open-modal="modalAgregar">
            Agregar propiedad
        </button>
    </header>

    <aside class="admin-sidebar">
        <div class="admin-logo"></div>
    
        <nav class="admin-opciones">
            <a href="Panel-propiedades.php">Propiedades</a>
            <a href="Panel-agente.php">Agentes</a>
        </nav>
    
        <button class="cerrar-sesion" type="button" onclick="location.href='logout.php'">
            Cerrar sesión
        </button>
    </aside>

    <main class="admin-main">

        <?php if (isset($_GET['ok'])): ?>
            <p style="color: green; font-weight: bold;">Propiedad guardada correctamente.</p>
        <?php endif; ?>

        <?php if (isset($_GET['eliminado'])): ?>
            <p style="color: green; font-weight: bold;">Propiedad eliminada correctamente.</p>
        <?php endif; ?>

        <?php if (empty($agentes)): ?>
            <p style="color: red; font-weight: bold;">
                Primero necesitas agregar al menos un agente activo en la tabla agentes.
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
            <div class="contenedor_busqueda">
                <h2>Propiedades</h2>

                <form class="busqueda" method="GET">
                <input
                    type="text"
                    name="buscar"
                    placeholder="Buscar propiedad..."
                    value="<?= e($_GET['buscar'] ?? '') ?>"
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

            <?php if (empty($propiedades)): ?>
                <p>No hay propiedades registradas.</p>
            <?php endif; ?>

            <?php foreach ($propiedades as $propiedad): ?>
                <?php
                    $imagen = $propiedad['imagen_principal'] ?: 'Imagenes/casa1.jpg';

                    $propiedadJson = json_encode([
                        'id' => $propiedad['id'],
                        'agente_id' => $propiedad['agente_id'],
                        'titulo' => $propiedad['titulo'],
                        'descripcion' => $propiedad['descripcion'],
                        'precio' => $propiedad['precio'],
                        'moneda' => $propiedad['moneda'],
                        'tipo_operacion' => $propiedad['tipo_operacion'],
                        'tipo_propiedad' => $propiedad['tipo_propiedad'],
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
                        'imagen_url' => $propiedad['imagen_principal'],
                    ], JSON_UNESCAPED_UNICODE);
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
                        <?= e(tipoPanelTexto($propiedad['tipo_propiedad'])) ?>
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
                            data-propiedad='<?= e((string)$propiedadJson) ?>'
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

        </section>

        <section class="cards_propiedades">
            <div class="text_top">
                <h2>Últimas propiedades añadidas</h2>
            </div>

            <div class="contenedor_cards">

                <?php foreach ($ultimasPropiedades as $propiedad): ?>
                    <?php $imagen = $propiedad['imagen_principal'] ?: 'Imagenes/casa1.jpg'; ?>

                    <a class="card_link" href="PropiedadInfo.php?id=<?= e((string)$propiedad['id']) ?>">
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
    <form class="modal-content" action="guardar-propiedad.php" method="POST">

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
                </select>
            </label>

            <label>
                Tipo de propiedad
                <select name="tipo_propiedad" required>
                    <option value="casa">Casa</option>
                    <option value="terreno">Terreno</option>
                    <option value="departamento">Departamento</option>
                    <option value="local_comercial">Local comercial</option>
                </select>
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
                Dirección completa
                <input type="text" name="direccion_completa" placeholder="Calle, número, colonia" required>
            </label>

            <label>
                Precio
                <input type="number" name="precio" placeholder="2500000" step="0.01" required>
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
                <input type="number" name="recamaras" value="0">
            </label>

            <label>
                Baños
                <input type="number" name="banos" value="0" step="0.5">
            </label>

            <label>
                Estacionamientos
                <input type="number" name="estacionamientos" value="0">
            </label>

            <label>
                Terreno m²
                <input type="number" name="terreno_m2" step="0.01">
            </label>

            <label>
                Construcción m²
                <input type="number" name="construccion_m2" step="0.01">
            </label>

            <label>
                Imagen principal
                <input type="text" name="imagen_url" placeholder="Imagenes/casa1.jpg">
            </label>

            <label>
                Google Maps URL
                <input type="text" name="google_maps_url" placeholder="https://maps.google.com/...">
            </label>

            <label>
                Destacada
                <input type="checkbox" name="destacada" value="1">
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
    <form class="modal-content" action="guardar-propiedad.php" method="POST">

        <input type="hidden" name="id" id="edit_id">

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
                </select>
            </label>

            <label>
                Tipo de propiedad
                <select name="tipo_propiedad" id="edit_tipo_propiedad" required>
                    <option value="casa">Casa</option>
                    <option value="terreno">Terreno</option>
                    <option value="departamento">Departamento</option>
                    <option value="local_comercial">Local comercial</option>
                </select>
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
                Dirección completa
                <input type="text" name="direccion_completa" id="edit_direccion_completa" required>
            </label>

            <label>
                Precio
                <input type="number" name="precio" id="edit_precio" step="0.01" required>
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
                <input type="number" name="recamaras" id="edit_recamaras">
            </label>

            <label>
                Baños
                <input type="number" name="banos" id="edit_banos" step="0.5">
            </label>

            <label>
                Estacionamientos
                <input type="number" name="estacionamientos" id="edit_estacionamientos">
            </label>

            <label>
                Terreno m²
                <input type="number" name="terreno_m2" id="edit_terreno_m2" step="0.01">
            </label>

            <label>
                Construcción m²
                <input type="number" name="construccion_m2" id="edit_construccion_m2" step="0.01">
            </label>

            <label>
                Imagen principal
                <input type="text" name="imagen_url" id="edit_imagen_url" placeholder="Imagenes/casa1.jpg">
            </label>

            <label>
                Google Maps URL
                <input type="text" name="google_maps_url" id="edit_google_maps_url">
            </label>

            <label>
                Destacada
                <input type="checkbox" name="destacada" id="edit_destacada" value="1">
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

<!-- MODAL ELIMINAR PROPIEDAD -->
<dialog class="modal modal-small" id="modalEliminar">
    <form class="modal-content" action="eliminar-propiedad.php" method="POST">

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

<script>
const modalAgregar = document.getElementById('modalAgregar');
const modalEditar = document.getElementById('modalEditar');
const modalEliminar = document.getElementById('modalEliminar');

document.addEventListener('click', (event) => {
    const openButton = event.target.closest('[data-open-modal]');

    if (!openButton) return;

    const modal = document.getElementById(openButton.dataset.openModal);

    if (modal) {
        modal.showModal();
    }
});

document.addEventListener('click', (event) => {
    const closeButton = event.target.closest('[data-close-modal]');

    if (!closeButton) return;

    const modal = closeButton.closest('dialog');

    if (modal) {
        modal.close();
    }
});

document.querySelectorAll('dialog').forEach((modal) => {
    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            modal.close();
        }
    });
});

document.addEventListener('click', (event) => {
    const editButton = event.target.closest('[data-edit]');

    if (!editButton) return;

    const propiedad = JSON.parse(editButton.dataset.propiedad);

    document.getElementById('edit_id').value = propiedad.id ?? '';
    document.getElementById('edit_agente_id').value = propiedad.agente_id ?? '';
    document.getElementById('edit_titulo').value = propiedad.titulo ?? '';
    document.getElementById('edit_tipo_operacion').value = propiedad.tipo_operacion ?? 'venta';
    document.getElementById('edit_tipo_propiedad').value = propiedad.tipo_propiedad ?? 'casa';
    document.getElementById('edit_ciudad').value = propiedad.ciudad ?? 'ciudad_obregon';
    document.getElementById('edit_direccion_completa').value = propiedad.direccion_completa ?? '';
    document.getElementById('edit_precio').value = propiedad.precio ?? '';
    document.getElementById('edit_moneda').value = propiedad.moneda ?? 'MXN';
    document.getElementById('edit_estado_publicacion').value = propiedad.estado_publicacion ?? 'activo';
    document.getElementById('edit_recamaras').value = propiedad.recamaras ?? 0;
    document.getElementById('edit_banos').value = propiedad.banos ?? 0;
    document.getElementById('edit_estacionamientos').value = propiedad.estacionamientos ?? 0;
    document.getElementById('edit_terreno_m2').value = propiedad.terreno_m2 ?? '';
    document.getElementById('edit_construccion_m2').value = propiedad.construccion_m2 ?? '';
    document.getElementById('edit_imagen_url').value = propiedad.imagen_url ?? '';
    document.getElementById('edit_google_maps_url').value = propiedad.google_maps_url ?? '';
    document.getElementById('edit_descripcion').value = propiedad.descripcion ?? '';
    document.getElementById('edit_destacada').checked = Number(propiedad.destacada) === 1;

    modalEditar.showModal();
});

document.addEventListener('click', (event) => {
    const deleteButton = event.target.closest('[data-delete]');

    if (!deleteButton) return;

    document.getElementById('delete_id').value = deleteButton.dataset.id;

    modalEliminar.showModal();
});
</script>

</body>
</html>