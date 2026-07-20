<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

require_once ROOT_PATH . '/Config/database.php';

$pdo = db();

function ciudadDetalleTexto(?string $ciudad): string
{
    return match ($ciudad) {
        'navojoa' => 'Navojoa',
        'san_carlos' => 'San Carlos',
        'ciudad_obregon' => 'Ciudad Obregón',
        'guaymas' => 'Guaymas',
        default => 'Ciudad no disponible'
    };
}

function operacionDetalleTexto(?string $operacion): string
{
    return match ($operacion) {
        'venta' => 'venta',
        'renta' => 'renta',
        default => ''
    };
}

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    die('Propiedad no encontrada.');
}

$stmt = $pdo->prepare("
    SELECT
        p.*,
        c.nombre AS categoria_nombre,
        a.nombre AS agente_nombre,
        a.telefono AS agente_telefono,
        a.email AS agente_email,
        a.foto_url AS agente_foto
    FROM propiedades p
    LEFT JOIN categorias_propiedad c
        ON c.id = p.categoria_id
    LEFT JOIN agentes a
        ON p.agente_id = a.id
    WHERE p.id = ?
    LIMIT 1
");

$stmt->execute([$id]);
$propiedad = $stmt->fetch();

if (!$propiedad) {
    die('Propiedad no encontrada.');
}

$stmtImagenes = $pdo->prepare("
    SELECT imagen_url, texto_alternativo
    FROM imagenes_propiedades
    WHERE propiedad_id = ?
    ORDER BY es_principal DESC, orden ASC, id ASC
");

$stmtImagenes->execute([$id]);
$imagenes = $stmtImagenes->fetchAll();

if (empty($imagenes)) {
    $imagenes[] = [
        'imagen_url' => 'Imagenes/casa2.jpg',
        'texto_alternativo' => $propiedad['titulo']
    ];
}

$imagenPrincipal = $imagenes[0]['imagen_url'] ?? 'Imagenes/casa2.jpg';

$precioTexto = '$' . number_format((float)$propiedad['precio'], 2) . ' ' . $propiedad['moneda'];

if ($propiedad['tipo_operacion'] === 'renta') {
    $precioTexto .= '/mes';
}

$telefonoLimpio = preg_replace('/\D+/', '', (string)($propiedad['agente_telefono'] ?? ''));

$ciudadTexto = ciudadDetalleTexto($propiedad['ciudad']);
$tipoTexto = $propiedad['categoria_nombre'] ?? 'Propiedad';
$operacionTexto = operacionDetalleTexto($propiedad['tipo_operacion']);

$fotoAgente = $propiedad['agente_foto'] ?: 'Imagenes/agente1.webp';

$mapsDefault = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d56631.121546203874!2d-109.94261074999999!3d27.4865298!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x86c815e1cb75ffb9%3A0x311f38aacd3515ae!2sCdad.%20Obreg%C3%B3n%2C%20Son.!5e0!3m2!1ses!2smx!4v1780782414850!5m2!1ses!2smx';

$mapsUrl = !empty($propiedad['google_maps_url'])
    ? $propiedad['google_maps_url']
    : $mapsDefault;


$titulo = e((string)$propiedad['titulo']);
$descripcion = e((string)$propiedad['descripcion']);
$cssPaginas = [BASE_URL . 'CSS/propiedadinfo.css'];

require_once ROOT_PATH . 'Includes/header.php';
?>
<main>

    <!-- Imágenes de la propiedad -->
    <section class="propiedad_imagenes">

        <figure>
            <img 
                class="imagen_principal" 
                id="imagenPrincipal"
                src="<?= BASE_URL ?><?= e((string)$imagenPrincipal) ?>" 
                alt="<?= e((string)$propiedad['titulo']) ?>"
            >
        </figure>

        <div class="contenedor_miniaturas">

            <button class="boton boton_izquierda" type="button" id="btnMiniaturasIzquierda">
                &#10094;
            </button>
            
            <div class="miniaturas" id="miniaturas">
                <?php foreach ($imagenes as $imagen): ?>
                    <img 
                        src="<?= BASE_URL ?><?= e((string)$imagen['imagen_url']) ?>"
                        alt="<?= e((string)($imagen['texto_alternativo'] ?: $propiedad['titulo'])) ?>"
                        onclick="cambiarImagen(this.src)"
                    >
                <?php endforeach; ?>
            </div>

            <button class="boton boton_derecha" type="button" id="btnMiniaturasDerecha">
                &#10095;
            </button>

        </div>

    </section>

    <!-- Información de la propiedad -->
    <section class="propiedad">

        <article class="info_propiedad">

            <h1>
                <?= e($tipoTexto) ?> en <?= e($operacionTexto) ?> - <?= e($ciudadTexto) ?>
            </h1>

            <!-- Ubicación y precio -->
            <div class="ubicacion_precio">
                <p>
                    <?= e((string)$propiedad['direccion_completa']) ?>
                </p>

                <p class="precio">
                    <strong><?= e($precioTexto) ?></strong>
                </p>
            </div>

            <!-- Características -->
            <div class="caracteristicas">

                <ul>
                    <li>
                        <i class="fa-solid fa-house"></i>
                        <p>
                            Construcción: 
                            <?= e((string)($propiedad['construccion_m2'] ?? 0)) ?> M²
                        </p>
                    </li>

                    <li>
                        <i class="fa-solid fa-ruler-combined"></i>
                        <p>
                            Terreno: 
                            <?= e((string)($propiedad['terreno_m2'] ?? 0)) ?> M²
                        </p>
                    </li>

                    <li>
                        <i class="fa-solid fa-location-dot"></i>
                        <p><?= e($ciudadTexto) ?></p>
                    </li>

                    <li>
                        <i class="fa-solid fa-tag"></i>
                        <p><?= e($tipoTexto) ?></p>
                    </li>
                </ul>

                <ul>
                    <li>
                        <i class="fa-solid fa-building"></i>
                        <p><?= e(ucfirst($operacionTexto)) ?></p>
                    </li>

                    <li>
                        <i class="fa-solid fa-bed"></i>
                        <p><?= e((string)$propiedad['recamaras']) ?> recámaras</p>
                    </li>

                    <li>
                        <i class="fa-solid fa-toilet"></i>
                        <p><?= (int)$propiedad['banos'] ?> baños</p>
                    </li>

                    <li>
                        <i class="fa-solid fa-car"></i>
                        <p>
                            Cochera para 
                            <?= e((string)$propiedad['estacionamientos']) ?> auto(s)
                        </p>
                    </li>
                </ul>

            </div>

            <?php if (!empty($propiedad['descripcion'])): ?>
                <div class="descripcion_propiedad">
                    <h2>Descripción</h2>
                    <p><?= nl2br(e((string)$propiedad['descripcion'])) ?></p>
                </div>
            <?php endif; ?>

        </article>

        <!-- Info agente -->
        <aside class="info_agente">

            <img 
                src="<?= BASE_URL ?><?= e((string)$fotoAgente) ?>"
                alt="<?= e((string)($propiedad['agente_nombre'] ?: 'Agente inmobiliario')) ?>"
            >

            <div>
                <h2><?= e((string)($propiedad['agente_nombre'] ?: 'Agente no asignado')) ?></h2>

                <p><strong>Contacto</strong></p>

                <?php if (!empty($propiedad['agente_email'])): ?>
                    <p>
                        <a href="mailto:<?= e((string)$propiedad['agente_email']) ?>">
                            <?= e((string)$propiedad['agente_email']) ?>
                        </a>
                    </p>
                <?php endif; ?>

                <?php if (!empty($propiedad['agente_telefono'])): ?>
                    <p>
                        <a href="tel:<?= e($telefonoLimpio) ?>">
                            <?= e((string)$propiedad['agente_telefono']) ?>
                        </a>
                    </p>
                <?php endif; ?>
            </div>

            <p><strong>Contáctanos</strong></p>

            <form action="<?= BASE_URL ?>Backend/guardar-mensaje.php" method="POST" class="form-contacto">
                <input
                    type="hidden"
                    name="propiedad_id"
                    value="<?= $propiedad['id'] ?? '' ?>"
                >

                <label>
                    Nombre
                    <input
                        type="text"
                        name="nombre"
                        required
                    >
                </label>

                <label>
                    Teléfono
                    <input
                        type="tel"
                        name="telefono"
                        required
                    >
                </label>

                <label>
                    Correo electrónico
                    <input
                        type="email"
                        name="email"
                        required
                    >
                </label>

                <label>
                    Mensaje
                    <textarea
                        name="mensaje"
                        rows="5"
                        required
                    ></textarea>
                </label>

                <button type="submit">
                    Enviar mensaje
                </button>
            </form>
        </aside>

    </section>

    <button
        class="imprimir"
        type="button"
        onclick="window.open('ImprimirPropiedadInfo.php?id=<?= $id ?>', '_blank')">
        <i class="fa-solid fa-print"></i>
        Imprimir ficha
    </button>

    <section class="maps">
        <h1>Encuentra la propiedad</h1>

        <iframe 
            src="<?= e((string)$mapsUrl) ?>"
            width="945" 
            height="370" 
            style="border:0;" 
            allowfullscreen="" 
            loading="lazy" 
            referrerpolicy="no-referrer-when-downgrade">
        </iframe>
    </section>

</main>
<?php require_once ROOT_PATH . 'Includes/footer.php'; ?>

<script>
function cambiarImagen(src) {
    const imagenPrincipal = document.getElementById('imagenPrincipal');

    if (imagenPrincipal) {
        imagenPrincipal.src = src;
    }
}

const miniaturas = document.getElementById('miniaturas');
const btnIzquierda = document.getElementById('btnMiniaturasIzquierda');
const btnDerecha = document.getElementById('btnMiniaturasDerecha');

btnIzquierda.addEventListener('click', () => {
    miniaturas.scrollBy({
        left: -500,
        behavior: 'smooth'
    });
});

btnDerecha.addEventListener('click', () => {
    miniaturas.scrollBy({
        left: 500,
        behavior: 'smooth'
    });
});

document.addEventListener("DOMContentLoaded", () => {

    const modal = document.getElementById("modalMensajeEnviado");

    if (!modal) return;

    modal.showModal();

    document
        .getElementById("cerrarModalMensaje")
        .addEventListener("click", () => {

            modal.close();

            const url = new URL(window.location);
            url.searchParams.delete("mensaje");
            window.history.replaceState({}, "", url);

        });

    modal.addEventListener("click", (e) => {

        if (e.target === modal) {

            modal.close();

            const url = new URL(window.location);
            url.searchParams.delete("mensaje");
            window.history.replaceState({}, "", url);

        }

    });

});
</script>

<?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 1): ?>

<dialog id="modalMensajeEnviado" class="modal-exito">

    <div class="modal-exito-content">

        <div class="modal-exito-icon">
            <i class="fa-solid fa-circle-check"></i>
        </div>

        <h2>¡Mensaje enviado!</h2>

        <p>
            Gracias por contactarnos.<br>
            Un asesor de Inmobiliaria Primavera se comunicará contigo lo antes posible.
        </p>

        <button id="cerrarModalMensaje">
            Aceptar
        </button>

    </div>

</dialog>

<?php endif; ?>

