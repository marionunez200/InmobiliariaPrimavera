console.log("Modal.js cargado correctamente");

// ================================
// MODALES
// ================================

const modalAgregar = document.getElementById("modalAgregar");
const modalEditar = document.getElementById("modalEditar");
const modalEliminar = document.getElementById("modalEliminar");

let filaEditando = null;
let filaAEliminar = null;

// ================================
// FUNCIONES DE AYUDA
// ================================

function abrirModal(modal) {
    if (!modal) {
        console.error("El modal no existe");
        return;
    }

    modal.showModal();
}

function cerrarModal(modal) {
    if (modal) {
        modal.close();
    }
}

function limpiarPrecio(precio) {
    return precio.replace("$", "").replaceAll(",", "").trim();
}

function formatearPrecio(precio) {
    const numero = Number(precio);

    if (isNaN(numero)) {
        return precio;
    }

    return "$" + numero.toLocaleString("es-MX");
}

function obtenerValor(modal, nombreInput) {
    const input = modal.querySelector(`[name="${nombreInput}"]`);

    if (!input) {
        console.warn(`No encontré el input con name="${nombreInput}"`);
        return "";
    }

    return input.value.trim();
}

function ponerValor(modal, nombreInput, valor) {
    const input = modal.querySelector(`[name="${nombreInput}"]`);

    if (!input) {
        console.warn(`No encontré el input con name="${nombreInput}"`);
        return;
    }

    input.value = valor;
}

// ================================
// ABRIR MODAL AGREGAR
// ================================

document.addEventListener("click", (event) => {
    const botonAbrir = event.target.closest("[data-open-modal]");

    if (!botonAbrir) return;

    const modalId = botonAbrir.dataset.openModal;
    const modal = document.getElementById(modalId);

    abrirModal(modal);
});

// ================================
// CERRAR MODALES
// ================================

document.addEventListener("click", (event) => {
    const botonCerrar = event.target.closest("[data-close-modal]");

    if (!botonCerrar) return;

    const modal = botonCerrar.closest("dialog");

    cerrarModal(modal);
});

// Cerrar modal al hacer click fuera del contenido
document.querySelectorAll("dialog").forEach((modal) => {
    modal.addEventListener("click", (event) => {
        if (event.target === modal) {
            modal.close();
        }
    });
});

// ================================
// MODAL EDITAR
// ================================

document.addEventListener("click", (event) => {
    const botonEditar = event.target.closest("[data-edit]");

    if (!botonEditar) return;

    filaEditando = botonEditar.closest("[data-property-row]");

    if (!filaEditando) {
        console.error("No encontré la fila. Agrega data-property-row al article.");
        return;
    }

    const titulo = filaEditando.querySelector("h3")?.textContent.trim() || "";

    const idTexto = filaEditando.querySelector(".info_propiedad p")?.textContent.trim() || "";
    const id = idTexto.replace("ID:", "").trim();

    const datos = filaEditando.querySelectorAll(".text_dentro");

    const ubicacion = datos[0]?.textContent.trim() || "";
    const tipo = datos[1]?.textContent.trim() || "";
    const precio = limpiarPrecio(datos[2]?.textContent.trim() || "");
    const estado = datos[3]?.textContent.trim() || "";

    ponerValor(modalEditar, "id", id);
    ponerValor(modalEditar, "titulo", titulo);
    ponerValor(modalEditar, "ubicacion", ubicacion);
    ponerValor(modalEditar, "tipo", tipo);
    ponerValor(modalEditar, "precio", precio);
    ponerValor(modalEditar, "estado", estado);

    abrirModal(modalEditar);
});

// Guardar cambios de editar
const formularioEditar = modalEditar?.querySelector("form");

if (formularioEditar) {
    formularioEditar.addEventListener("submit", (event) => {
        event.preventDefault();

        if (!filaEditando) {
            console.error("No hay ninguna fila seleccionada para editar.");
            return;
        }

        const id = obtenerValor(modalEditar, "id");
        const titulo = obtenerValor(modalEditar, "titulo");
        const ubicacion = obtenerValor(modalEditar, "ubicacion");
        const tipo = obtenerValor(modalEditar, "tipo");
        const precio = obtenerValor(modalEditar, "precio");
        const estado = obtenerValor(modalEditar, "estado");

        const tituloHTML = filaEditando.querySelector("h3");
        const idHTML = filaEditando.querySelector(".info_propiedad p");
        const datos = filaEditando.querySelectorAll(".text_dentro");

        if (tituloHTML) tituloHTML.textContent = titulo;
        if (idHTML) idHTML.textContent = `ID: ${id}`;

        if (datos[0]) datos[0].textContent = ubicacion;
        if (datos[1]) datos[1].textContent = tipo;
        if (datos[2]) datos[2].textContent = formatearPrecio(precio);
        if (datos[3]) datos[3].textContent = estado;

        cerrarModal(modalEditar);

        filaEditando = null;
    });
}

// ================================
// MODAL ELIMINAR
// ================================

document.addEventListener("click", (event) => {
    const botonEliminar = event.target.closest("[data-delete]");

    if (!botonEliminar) return;

    filaAEliminar = botonEliminar.closest("[data-property-row]");

    if (!filaAEliminar) {
        console.error("No encontré la fila. Agrega data-property-row al article.");
        return;
    }

    abrirModal(modalEliminar);
});

const botonConfirmarEliminar = document.getElementById("confirmarEliminar");

if (botonConfirmarEliminar) {
    botonConfirmarEliminar.addEventListener("click", () => {
        if (filaAEliminar) {
            filaAEliminar.remove();
            filaAEliminar = null;
        }

        cerrarModal(modalEliminar);
    });
}