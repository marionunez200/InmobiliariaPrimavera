<?php
require_once __DIR__ . '/Config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = db();

function agenteActivoTexto($activo): string
{
    return (int)$activo === 1 ? 'Activo' : 'Inactivo';
}

function agenteSelected($actual, $valor): string
{
    return (string)$actual === (string)$valor ? 'selected' : '';
}

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
        SELECT *
        FROM agentes
        WHERE nombre LIKE ?
            OR email LIKE ?
            OR telefono LIKE ?
        ORDER BY creado_en DESC, id DESC
    ");

    $texto = "%{$buscar}%";

    $stmt->execute([
        $texto,
        $texto,
        $texto
    ]);

} else {

    $stmt = $pdo->query("
        SELECT *
        FROM agentes
        ORDER BY creado_en DESC, id DESC
    ");
}

$agentes = $stmt->fetchAll();

$ultimosAgentes = array_slice($agentes, 0, 4);
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
                <img class="logo-panel" src="Imagenes/Logosolo.png" alt="Logo Primavera inmobiliaria">
            </a>
        </div>

        <div class="left-adminh">
            <div class="header-top-panel">
                <h1>Panel Administrativo</h1>
                <p>Gestión de agentes inmobiliarios</p>

                <?php if (!empty($_SESSION['admin_nombre'])): ?>
                    <p>Sesión: <?= e((string)$_SESSION['admin_nombre']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <button class="button_anadir" type="button" data-open-modal="modalAgregar">
            Agregar agente
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
            <p style="color: green; font-weight: bold;">Agente guardado correctamente.</p>
        <?php endif; ?>

        <?php if (isset($_GET['eliminado'])): ?>
            <p style="color: green; font-weight: bold;">Agente desactivado correctamente.</p>
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

        <section class="detalles_agente">        
            <div class="contenedor_busqueda">
                <h2>Agentes inmobiliarios</h2>

                <form class="busqueda" method="GET">
                <input
                    type="text"
                    name="buscar"
                    placeholder="Buscar agente..."
                    value="<?= e($_GET['buscar'] ?? '') ?>"
                >
                    <button type="submit">
                        Buscar
                    </button>
                </form>
            </div>

            <div class="subtitulos_agente">
                <span>Nombre</span>
                <span>Correo electrónico</span>
                <span>Teléfono</span>
                <span>Estado</span>
                <span>Acciones</span>
            </div>

            <div class="contenedor_agentes">
                <?php if (empty($agentes)): ?>
                    <p>No hay agentes registrados.</p>
                <?php endif; ?>

                <?php foreach ($agentes as $agente): ?>
                    <?php
                        $foto = $agente['foto_url'] ?: 'Imagenes/agente1.webp';

                        $agenteJson = json_encode([
                            'id' => $agente['id'],
                            'nombre' => $agente['nombre'],
                            'telefono' => $agente['telefono'],
                            'email' => $agente['email'],
                            'foto_url' => $agente['foto_url'],
                            'activo' => $agente['activo'],
                        ], JSON_UNESCAPED_UNICODE);
                    ?>

                    <article class="detalles_agente_fila" data-agent-row>
                        <div class="info_agente">
                            <img src="<?= e((string)$foto) ?>" alt="<?= e((string)$agente['nombre']) ?>">

                            <div>
                                <h3><?= e((string)$agente['nombre']) ?></h3>
                                <p>ID: <?= e((string)$agente['id']) ?></p>
                            </div>
                        </div>

                        <span class="text_dentro">
                            <?= e((string)($agente['email'] ?: 'Sin correo')) ?>
                        </span>

                        <span class="text_dentro">
                            <?= e((string)($agente['telefono'] ?: 'Sin teléfono')) ?>
                        </span>

                        <span class="text_dentro">
                            <?= e(agenteActivoTexto($agente['activo'])) ?>
                        </span>

                        <div class="acciones">
                            <button 
                                class="editar" 
                                type="button"
                                data-edit
                                data-agente='<?= e((string)$agenteJson) ?>'
                            >
                                Editar
                            </button>

                            <button 
                                class="eliminar" 
                                type="button"
                                data-delete
                                data-id="<?= e((string)$agente['id']) ?>"
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
                <h2>Últimos agentes añadidos</h2>
            </div>

            <div class="contenedor_cards">
                <?php foreach ($ultimosAgentes as $agente): ?>
                    <?php $foto = $agente['foto_url'] ?: 'Imagenes/agente1.webp'; ?>

                    <a class="card_link" href="#">
                        <article class="propiedad-card">
                            <img 
                                class="propiedad-img" 
                                src="<?= e((string)$foto) ?>" 
                                alt="<?= e((string)$agente['nombre']) ?>"
                            >

                            <div class="text_top_info">
                                <h2><?= e((string)$agente['nombre']) ?></h2>

                                <p class="precio-mxn">
                                    Estado: 
                                    <strong><?= e(agenteActivoTexto($agente['activo'])) ?></strong>
                                </p>
                            </div>
                        </article>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

    </main>
</div>

<!-- MODAL AGREGAR AGENTE -->
<dialog class="modal" id="modalAgregar">
    <form class="modal-content" action="guardar-agente.php" method="POST">

        <div class="modal-header">
            <h2>Agregar agente</h2>

            <button type="button" class="modal-close" data-close-modal>
                &times;
            </button>
        </div>

        <div class="modal-body">

            <label>
                Nombre
                <input 
                    type="text" 
                    name="nombre" 
                    placeholder="Marisol Pérez"
                    required
                >
            </label>

            <label>
                Teléfono
                <input 
                    type="tel" 
                    name="telefono" 
                    placeholder="6444567890"
                >
            </label>

            <label>
                Correo electrónico
                <input 
                    type="email" 
                    name="email" 
                    placeholder="sucorreo@gmail.com"
                >
            </label>

            <label>
                Estado
                <select name="activo">
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </label>

            <label>
                Foto del agente
                <input 
                    type="text" 
                    name="foto_url" 
                    placeholder="Imagenes/agente1.webp"
                >
            </label>

        </div>

        <div class="modal-actions">
            <button type="button" class="btn-secondary" data-close-modal>
                Cancelar
            </button>

            <button type="submit" class="btn-primary">
                Guardar agente
            </button>
        </div>

    </form>
</dialog>

<!-- MODAL EDITAR AGENTE -->
<dialog class="modal" id="modalEditar">
    <form class="modal-content" action="guardar-agente.php" method="POST">

        <input type="hidden" name="id" id="edit_id">

        <div class="modal-header">
            <h2>Editar información del agente</h2>

            <button type="button" class="modal-close" data-close-modal>
                &times;
            </button>
        </div>

        <div class="modal-body">

            <label>
                Nombre
                <input 
                    type="text" 
                    name="nombre" 
                    id="edit_nombre"
                    required
                >
            </label>

            <label>
                Teléfono
                <input 
                    type="tel" 
                    name="telefono"
                    id="edit_telefono"
                >
            </label>

            <label>
                Correo electrónico
                <input 
                    type="email" 
                    name="email"
                    id="edit_email"
                >
            </label>

            <label>
                Estado
                <select name="activo" id="edit_activo">
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </label>

            <label>
                Foto del agente
                <input 
                    type="text" 
                    name="foto_url"
                    id="edit_foto_url"
                    placeholder="Imagenes/agente1.webp"
                >
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

<!-- MODAL ELIMINAR AGENTE -->
<dialog class="modal modal-small" id="modalEliminar">
    <form class="modal-content" action="eliminar-agente.php" method="POST">

        <input type="hidden" name="id" id="delete_id">

        <div class="modal-header">
            <h2>Eliminar agente</h2>

            <button type="button" class="modal-close" data-close-modal>
                &times;
            </button>
        </div>

        <div class="modal-body">
            <p>¿Seguro que quieres eliminar este agente?</p>
            <p class="warning">
                Para evitar problemas con propiedades asignadas, el agente se marcará como inactivo.
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

    const agente = JSON.parse(editButton.dataset.agente);

    document.getElementById('edit_id').value = agente.id ?? '';
    document.getElementById('edit_nombre').value = agente.nombre ?? '';
    document.getElementById('edit_telefono').value = agente.telefono ?? '';
    document.getElementById('edit_email').value = agente.email ?? '';
    document.getElementById('edit_foto_url').value = agente.foto_url ?? '';
    document.getElementById('edit_activo').value = agente.activo ?? 1;

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