<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

require_once ROOT_PATH . '/Config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_id'])) {
    header("Location: Login.php");
    exit;
}
$pdo = db();

$pdo->exec("
    DELETE
    FROM mensajes_contacto
    WHERE estado_mensaje='hecho'
    AND completado_en <= DATE_SUB(NOW(), INTERVAL 10 DAY)
");

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

$stmt = $pdo->query("
    SELECT
        m.*,
        p.titulo,
        i.imagen_url
    FROM mensajes_contacto m

    LEFT JOIN propiedades p
        ON p.id = m.propiedad_id

    LEFT JOIN imagenes_propiedades i
        ON i.propiedad_id = p.id
        AND i.es_principal = 1

    ORDER BY m.creado_en DESC
");

$mensajes = $stmt->fetchAll();

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

    <link rel="stylesheet" href="<?= BASE_URL ?>CSS/Panel-mensajes.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>CSS/Admin.header.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="icon" href="<?= BASE_URL ?>favicon.ico" type="image/x-icon">
</head>

<body>

<div class="admin-panel">

    <header class="admin-header">
        <div class="contenedor-logo">
            <a href="<?= BASE_URL ?>Admin/Panel-propiedades.php">
                <img class="logo-panel" src="<?= BASE_URL ?>Imagenes/Logosolo.png" alt="Logo Primavera inmobiliaria">
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

    </header>

    <aside class="admin-sidebar">
        <div class="admin-logo"></div>
    
        <nav class="admin-opciones">
            <a href="<?= BASE_URL ?>Admin/Panel-propiedades.php">Propiedades</a>
            <a href="<?= BASE_URL ?>Admin/Panel-agente.php">Agentes</a>
            <a href="<?= BASE_URL ?>Admin/Panel-mensajes.php">Mensajes</a>
        </nav>
    
        <button class="cerrar-sesion" type="button" onclick="location.href='login.php'">
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
                <h2>Mensajes disponibles</h2>
            </div>

            <div class="subtitulos_agente">
                <span>Nombre</span>
                <span>Teléfono</span>
                <span>Email</span>
                <span>Mensaje</span>
                <span>Estado</span>
                <span>Fecha de creación</span>
                <span>Acciones</span>
            </div>

            <div class="contenedor_mensajes">

                <?php if (empty($mensajes)): ?>

                    <p class="sin-mensajes">
                        No hay mensajes recibidos.
                    </p>

                <?php else: ?>

                    <?php foreach ($mensajes as $mensaje): ?>

                        <article class="detalles_agente_fila">

                            <!-- Nombre -->
                            <div class="info_mensaje">

                                <img
                                    class="mini_propiedad"
                                    src="<?= e($mensaje['imagen_url'] ? BASE_URL . $mensaje['imagen_url'] : BASE_URL . 'Imagenes/sin-imagen.webp') ?>"
                                    alt="<?= e($mensaje['titulo']) ?>">

                                <div>
                                    <h3><?= e($mensaje['nombre']) ?></h3>
                                    <p><?= e($mensaje['titulo']) ?></p>
                                    <small>ID mensaje: <?= e($mensaje['id']) ?></small>
                                </div>

                            </div>
                            <!-- Teléfono -->
                            <span class="text_dentro">
                                <?= e($mensaje['telefono']) ?>
                            </span>

                            <!-- Email -->
                            <span class="text_dentro">
                                <?= e($mensaje['email']) ?>
                            </span>

                            <!-- Mensaje -->
                            <span class="text_dentro mensaje">
                                <?= e(mb_strimwidth($mensaje['mensaje'], 0, 60, "...")) ?>
                            </span>

                            <!-- Estado -->
                            <span>
                                <?= e($mensaje['estado_mensaje']) ?>
                            </span>
                            <!-- Fecha -->
                            <span class="text_dentro">
                                <?= date('d/m/Y', strtotime($mensaje['creado_en'])) ?>
                            </span>

                            <!-- Acciones -->
                            <div class="acciones">

                                <button
                                    type="button"
                                    class="editar"
                                    data-ver
                                    data-nombre="<?= e($mensaje['nombre']) ?>"
                                    data-telefono="<?= e($mensaje['telefono']) ?>"
                                    data-email="<?= e($mensaje['email']) ?>"
                                    data-mensaje="<?= e($mensaje['mensaje']) ?>">
                                    Ver
                                </button>

                                <?php if (strtolower(trim($mensaje['estado_mensaje'])) == 'cerrado'): ?>

                                    <form action="<?= BASE_URL ?>Backend/eliminar-mensaje.php" method="POST">
                                        <input type="hidden" name="id" value="<?= e($mensaje['id']) ?>">

                                        <button
                                            type="button"
                                            class="eliminar"
                                            data-confirmar-eliminar>
                                            Eliminar
                                        </button>
                                    </form>

                                <?php else: ?>

                                    <form action="<?= BASE_URL ?>Backend/marcar-hecho.php" method="POST" class="form-hecho">
                                        <input type="hidden" name="id" value="<?= e($mensaje['id']) ?>">

                                        <button type="button" class="hecho" data-confirmar-hecho>
                                            Hecho
                                        </button>
                                    </form>

                                <?php endif; ?>

                            </div>

                        </article>

                    <?php endforeach; ?>

                <?php endif; ?>

            </div>

        </section>

    </main>
</div>

<dialog id="modalMensaje" class="modal">

    <div class="modal-content">

        <div class="modal-header">
            <h2>Mensaje recibido</h2>

            <button
                type="button"
                class="modal-close"
                data-close-modal>
                &times;
            </button>
        </div>

        <div class="modal-body">

            <p><strong>Nombre:</strong> <span id="verNombre"></span></p>

            <p><strong>Teléfono:</strong> <span id="verTelefono"></span></p>

            <p><strong>Email:</strong> <span id="verEmail"></span></p>

            <p><strong>Mensaje:</strong></p>

            <textarea id="verMensaje" readonly></textarea>

        </div>

    </div>

</dialog>

        <dialog id="modalConfirmarHecho" class="modal-exito">

            <div class="modal-exito-content">

                <div class="modal-exito-icon">
                    <i class="fa-solid fa-circle-check"></i>
                </div>

                <h2>¿Marcar como hecho?</h2>

                <p>
                    El mensaje cambiará a estado <strong>"Hecho"</strong>.
                </p>

                <div class="acciones-confirmacion">

                    <button
                        type="button"
                        class="btn-cancelar"
                        id="cancelarHecho">
                        Cancelar
                    </button>

                    <button
                        type="button"
                        class="btn-confirmar"
                        id="confirmarHecho">
                        Sí, marcar
                    </button>

                </div>

            </div>

        </dialog>

        <dialog id="modalConfirmarEliminar" class="modal-confirmar">

        <div class="modal-confirmar-content">

            <div class="modal-confirmar-icono">
                <i class="fa-solid fa-trash"></i>
            </div>

            <h2>Eliminar mensaje</h2>

            <p>
                ¿Estás seguro de que deseas eliminar este mensaje?<br>
                Esta acción no se puede deshacer.
            </p>

            <div class="modal-confirmar-botones">

                <button type="button" class="cancelar" id="cancelarEliminar">
                    Cancelar
                </button>

                <button type="button" class="eliminar" id="confirmarEliminar">
                    Eliminar
                </button>

            </div>

        </div>

    </dialog>
<script>

const modalMensaje = document.getElementById("modalMensaje");

document.addEventListener("click", (e)=>{

    const boton = e.target.closest("[data-ver]");

    if(!boton) return;

    document.getElementById("verNombre").textContent = boton.dataset.nombre;
    document.getElementById("verTelefono").textContent = boton.dataset.telefono;
    document.getElementById("verEmail").textContent = boton.dataset.email;
    document.getElementById("verMensaje").value = boton.dataset.mensaje;

    modalMensaje.showModal();

});

/* ================================
    CERRAR MODALES
================================ */
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

    const card = document.createElement('div');
    card.className = 'archivo-preview archivo-existente';

    card.innerHTML = `
        <img src="${foto}" alt="${nombreAgente || 'Agente'}">

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

const modalConfirmarHecho = document.getElementById("modalConfirmarHecho");

let formularioHecho = null;

document.querySelectorAll("[data-confirmar-hecho]").forEach((boton) => {

    boton.addEventListener("click", () => {

        formularioHecho = boton.closest("form");

        modalConfirmarHecho.showModal();

    });

});

document.getElementById("cancelarHecho").addEventListener("click", () => {

    modalConfirmarHecho.close();

});

document.getElementById("confirmarHecho").addEventListener("click", () => {

    if(formularioHecho){
        formularioHecho.submit();
    }

});

const modalEliminar = document.getElementById("modalConfirmarEliminar");
const btnCancelarEliminar = document.getElementById("cancelarEliminar");
const btnConfirmarEliminar = document.getElementById("confirmarEliminar");

let formularioEliminar = null;

document.querySelectorAll("[data-confirmar-eliminar]").forEach(boton => {

    boton.addEventListener("click", () => {

        formularioEliminar = boton.closest("form");

        modalEliminar.showModal();

    });

});

btnCancelarEliminar.addEventListener("click", () => {

    formularioEliminar = null;
    modalEliminar.close();

});

btnConfirmarEliminar.addEventListener("click", () => {

    if(formularioEliminar){
        formularioEliminar.submit();
    }

});
</script>
</body>
</html>