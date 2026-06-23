console.log("Modal.js cargado correctamente");

const modalEditar = document.getElementById("modalEditar");
const modalEliminar = document.getElementById("modalEliminar");

let propiedadAEliminar = null;

// Abrir modal de agregar propiedad
document.addEventListener("click", (event) => {
    const openButton = event.target.closest("[data-open-modal]");

    if (!openButton) return;

    const modalId = openButton.dataset.openModal;
    const modal = document.getElementById(modalId);

    if (!modal) {
        console.error("No existe un modal con el id:", modalId);
        return;
    }

    modal.showModal();
});

// Abrir modal de editar
document.addEventListener("click", (event) => {
    const editButton = event.target.closest("[data-edit]");

    if (!editButton) return;

    const fila = editButton.closest("[data-property-row]");

    if (!fila) {
        console.error("No encontré la fila. Agrega data-property-row al article.");
        return;
    }

    modalEditar.querySelector("[name='id']").value = fila.dataset.id;
    modalEditar.querySelector("[name='titulo']").value = fila.dataset.titulo;
    modalEditar.querySelector("[name='precio']").value = fila.dataset.precio;
    modalEditar.querySelector("[name='ubicacion']").value = fila.dataset.ubicacion;

    modalEditar.showModal();
});

// Abrir modal de eliminar
document.addEventListener("click", (event) => {
    const deleteButton = event.target.closest("[data-delete]");

    if (!deleteButton) return;

    const fila = deleteButton.closest("[data-property-row]");

    if (!fila) {
        console.error("No encontré la fila. Agrega data-property-row al article.");
        return;
    }

    propiedadAEliminar = fila.dataset.id;

    modalEliminar.showModal();
});

// Cerrar cualquier modal
document.addEventListener("click", (event) => {
    const closeButton = event.target.closest("[data-close-modal]");

    if (!closeButton) return;

    const modal = closeButton.closest("dialog");

    if (modal) {
        modal.close();
    }
});

// Confirmar eliminación
const confirmarEliminar = document.getElementById("confirmarEliminar");

confirmarEliminar.addEventListener("click", () => {
    console.log("Eliminar propiedad:", propiedadAEliminar);

    modalEliminar.close();
});



// Abrir modal de agregar agente
document.addEventListener("click", (event) => {
    const openButton = event.target.closest("[data-open-modal]");

    if (!openButton) return;

    const modalId = openButton.dataset.openModal;
    const modal = document.getElementById(modalId);

    if (!modal) {
        console.error("No existe un modal con el id:", modalId);
        return;
    }

    modal.showModal();
});

// Abrir modal de editar agente
document.addEventListener("click", (event) => {
    const editButton = event.target.closest("[data-edit]");

    if (!editButton) return;

    const fila = editButton.closest("[data-property-row]");

    if (!fila) {
        console.error("No encontré la fila. Agrega data-property-row al article.");
        return;
    }

    modalEditar.querySelector("[name='id_agente']").value = fila.dataset.id;

    modalEditar.querySelector("[name='nombre_agente']").value = fila.dataset.nombre;

    modalEditar.querySelector("[name='phone']").value = fila.dataset.telefono;

    modalEditar.querySelector("[name='correo']").value = fila.dataset.correo; 

    modalEditar.showModal();
});
