<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once ROOT_PATH . '/Config/database.php';
require_once ROOT_PATH . '/Admin/auth.php';

$pdo = db();

$modalExitoTitulo = '';
$modalExitoMensaje = '';

if (!empty($_SESSION['modal_exito'])) {
    $modalExitoTitulo = $_SESSION['modal_exito']['titulo'] ?? '';
    $modalExitoMensaje = $_SESSION['modal_exito']['mensaje'] ?? '';

    unset($_SESSION['modal_exito']);
}
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

    $sql = "
        SELECT *
        FROM agentes
        WHERE nombre LIKE :nombre
            OR email LIKE :email
            OR telefono LIKE :telefono
    ";

    if (strtolower($buscar) === 'activo') {
        $sql .= " OR activo = 1";
    }

    if (strtolower($buscar) === 'inactivo') {
        $sql .= " OR activo = 0";
    }

    $sql .= " ORDER BY creado_en DESC, id DESC";

    $stmt = $pdo->prepare($sql);

    $texto = "%{$buscar}%";

    $stmt->execute([
        ':nombre' => $texto,
        ':email' => $texto,
        ':telefono' => $texto
    ]);

} else {

    $stmt = $pdo->query("
        SELECT *
        FROM agentes
        ORDER BY creado_en DESC, id DESC
    ");
}

$agentes = $stmt->fetchAll();

$stmtUltimos = $pdo->query("
    SELECT *
    FROM agentes
    ORDER BY creado_en DESC, id DESC
    LIMIT 4
");

$ultimosAgentes = $stmtUltimos->fetchAll();?>

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

    <link rel="stylesheet" href="<?= BASE_URL ?>CSS/Panel-propiedades.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>CSS/Admin.header.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="icon" href="<?= BASE_URL ?>favicon.ico" type="image/x-icon">
</head>

<body>

<div class="admin-panel">

    <header class="admin-header">
        <div class="contenedor-logo">
            <a href="<?= BASE_URL ?>Admin/Panel-propiedades.php">
                <img class="logo-panel" src="<?= BASE_URL?>Imagenes/Logosolo.png" alt="Logo Primavera inmobiliaria">
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
            <a href="<?= BASE_URL ?>Admin/Panel-propiedades.php">Propiedades</a>
            <a href="<?= BASE_URL ?>Admin/Panel-agente.php">Agentes</a>
            <a href="<?= BASE_URL ?>Admin/Panel-mensajes.php">Mensajes</a>
        </nav>
    
        <button
            class="cerrar-sesion"
            type="button"
            onclick="location.href='<?= BASE_URL ?>Backend/cerrar-sesion.php'">
            Cerrar sesión
        </button>
    </aside>

    <main class="admin-main">

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
            <div class="contenedor-busqueda">
                <h2>Agentes inmobiliarios</h2>

                <form method="GET" action="<?= BASE_URL ?>Admin/Panel-agente.php" class="busqueda">
                    
                    <input 
                        type="search" 
                        name="buscar"
                        placeholder="Buscar agente..."
                        value="<?= e($buscar) ?>"
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
                        $foto = $agente['foto_url'] ? BASE_URL . $agente['foto_url'] : BASE_URL . 'Imagenes/agente1.webp';

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
                    <?php $foto = $agente['foto_url'] ? BASE_URL . $agente['foto_url'] : BASE_URL . 'Imagenes/agente1.webp'; ?>

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
    <form class="modal-content" action="<?= BASE_URL ?>Backend/Agente/agregar-agente.php" method="POST" enctype="multipart/form-data">
            <input 
            type="hidden"
            name="csrf_token"
            value="<?= $_SESSION['csrf_token'] ?>"
        >
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
                            
                <div class="upload-box" id="dropAgenteAgregar">
                    <input 
                        type="file" 
                        name="foto_agente" 
                        id="inputAgenteAgregar"
                        class="upload-input"
                        accept="image/*"
                    >
                            
                    <label for="inputAgenteAgregar" class="upload-label">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <strong>Arrastra la foto aquí</strong>
                        <span>o haz clic para seleccionarla</span>
                    </label>
                </div>
                            
                <div class="lista-archivos" id="previewAgenteAgregar"></div>
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
    <form class="modal-content" action="<?= BASE_URL ?>Backend/Agente/editar-agente.php" method="POST" enctype="multipart/form-data">
        <input 
            type="hidden"
            name="csrf_token"
            value="<?= $_SESSION['csrf_token'] ?>"
        >
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

            <div class="imagenes-actuales-box">
                <p class="imagenes-actuales-title">Foto actual del agente</p>

                <div 
                    class="lista-archivos" 
                    id="fotoActualAgenteEditar"
                ></div>
            </div>

            <label>
                Cambiar foto del agente

                <div class="upload-box" id="dropAgenteEditar">
                    <input 
                        type="file" 
                        name="foto_agente" 
                        id="inputAgenteEditar"
                        class="upload-input"
                        accept="image/*"
                    >

                    <label for="inputAgenteEditar" class="upload-label">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <strong>Arrastra la nueva foto aquí</strong>
                        <span>o haz clic para seleccionarla</span>
                    </label>
                </div>

                <div class="lista-archivos" id="previewAgenteEditar"></div>
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
    <form class="modal-content" action="<?= BASE_URL ?>Backend/Agente/eliminar-agente.php" method="POST">

        <input type="hidden" name="id" id="delete_id">
        
        <input type="hidden" 
        name="csrf_token" 
        value="<?= $_SESSION['csrf_token'] ?>"
        >

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
const BASE_URL = '<?= BASE_URL ?>';

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

/* ================================
   ABRIR MODAL AGREGAR
================================ */

document.addEventListener('click', (event) => {
    const openButton = event.target.closest('[data-open-modal]');

    if (!openButton) {
        return;
    }

    const modal = document.getElementById(openButton.dataset.openModal);

    abrirModal(modal);
});

/* ================================
   CERRAR MODALES
================================ */

document.addEventListener('click', (event) => {
    const closeButton = event.target.closest('[data-close-modal]');

    if (!closeButton) {
        return;
    }

    const modal = closeButton.closest('dialog');

    cerrarModal(modal);
});

/* ================================
   FOTO ACTUAL DEL AGENTE
================================ */

function renderizarFotoActualAgente(fotoUrl, nombreAgente) {
    const contenedor = document.getElementById('fotoActualAgenteEditar');

    if (!contenedor) {
        return;
    }

    contenedor.innerHTML = '';

    const foto = fotoUrl || 'Imagenes/agente1.webp';
    const nombreArchivo = foto.split('/').pop();

    const rutaCompleta = foto.startsWith('http') ? foto : `${BASE_URL}${foto}`;

    const card = document.createElement('div');
    card.className = 'archivo-preview archivo-existente';

    card.innerHTML = `
        <img src="${rutaCompleta}" alt="${nombreAgente || 'Agente'}">

        <div class="archivo-info">
            <span class="archivo-nombre" title="${nombreArchivo}">
                ${nombreArchivo}
            </span>

            <span class="archivo-peso">
                Foto actual
            </span>
        </div>
    `;

    contenedor.appendChild(card);
}

/* ================================
   EDITAR AGENTE
================================ */

document.addEventListener('click', (event) => {
    const editButton = event.target.closest('[data-edit]');

    if (!editButton) {
        return;
    }

    let agente = {};

    try {
        agente = JSON.parse(editButton.dataset.agente);
    } catch (error) {
        console.error('Error leyendo data-agente:', error);
        return;
    }

    ponerValorSeguro('edit_id', agente.id);
    ponerValorSeguro('edit_nombre', agente.nombre);
    ponerValorSeguro('edit_telefono', agente.telefono);
    ponerValorSeguro('edit_email', agente.email);
    ponerValorSeguro('edit_activo', agente.activo ?? 1);

    renderizarFotoActualAgente(agente.foto_url, agente.nombre);

    const previewEditar = document.getElementById('previewAgenteEditar');
    const inputEditar = document.getElementById('inputAgenteEditar');

    if (previewEditar) {
        previewEditar.innerHTML = '';
    }

    if (inputEditar) {
        inputEditar.value = '';
    }

    abrirModal(modalEditar);
});

/* ================================
   ELIMINAR AGENTE
================================ */

document.addEventListener('click', (event) => {
    const deleteButton = event.target.closest('[data-delete]');

    if (!deleteButton) {
        return;
    }

    ponerValorSeguro('delete_id', deleteButton.dataset.id);

    abrirModal(modalEliminar);
});

/* ================================
   PREVIEW MINI DE FOTO
================================ */

function formatearPesoAgente(bytes) {
    if (bytes < 1024) {
        return bytes + ' B';
    }

    if (bytes < 1024 * 1024) {
        return (bytes / 1024).toFixed(1) + ' KB';
    }

    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

function configurarFotoAgente(dropId, inputId, previewId) {
    const dropzone = document.getElementById(dropId);
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);

    if (!dropzone || !input || !preview) {
        return;
    }

    function mostrarPreview(file) {
        preview.innerHTML = '';

        if (!file || !file.type.startsWith('image/')) {
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
                        ${formatearPesoAgente(file.size)}
                    </span>

                    <button type="button" class="archivo-quitar">
                        Quitar
                    </button>
                </div>
            `;

            preview.appendChild(card);
        };

        reader.readAsDataURL(file);
    }

    input.addEventListener('change', () => {
        mostrarPreview(input.files[0]);
    });

    preview.addEventListener('click', (event) => {
        const botonQuitar = event.target.closest('.archivo-quitar');

        if (!botonQuitar) {
            return;
        }

        input.value = '';
        preview.innerHTML = '';
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

        const file = event.dataTransfer.files[0];

        if (!file || !file.type.startsWith('image/')) {
            return;
        }

        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);

        input.files = dataTransfer.files;

        mostrarPreview(file);
    });
}

configurarFotoAgente('dropAgenteAgregar', 'inputAgenteAgregar', 'previewAgenteAgregar');
configurarFotoAgente('dropAgenteEditar', 'inputAgenteEditar', 'previewAgenteEditar');

/* ================================
   MODAL DE ÉXITO
================================ */

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
</script>
</body>
</html>