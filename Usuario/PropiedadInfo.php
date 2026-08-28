<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once ROOT_PATH . '/Config/database.php';
require_once ROOT_PATH . '/Backend/cambio-moneda.php';

$pdo = db();

$monedaMostrar = strtoupper($_GET['moneda'] ?? 'MXN');

if (!in_array($monedaMostrar, ['MXN', 'USD'], true)) {
    $monedaMostrar = 'MXN';
}

function operacionDetalleTexto(?string $operacion): string
{
    return match ($operacion) {
        'venta' => 'venta',
        'renta' => 'renta',
        default => ''
    };
}

$slug = $_GET['slug'] ?? null;

if (!$slug) {
    die('Propiedad no encontrada.');
}

$stmt = $pdo->prepare("
    SELECT
        p.*,
        c.nombre AS categoria_nombre,
        ci.nombre AS ciudad_nombre,
        a.nombre AS agente_nombre,
        a.telefono AS agente_telefono,
        a.email AS agente_email,
        a.foto_url AS agente_foto
    FROM propiedades p
    LEFT JOIN categorias_propiedad c
        ON c.id = p.categoria_id
    LEFT JOIN ciudades ci
        ON ci.id = p.ciudad_id
    LEFT JOIN agentes a
        ON p.agente_id = a.id
    WHERE p.slug = ?
    LIMIT 1
");

$stmt->execute([$slug]);
$propiedad = $stmt->fetch();

if (!$propiedad) {
    die('Propiedad no encontrada.');
}

// Obtener elementos multimedia (fotos y videos)
$stmtImagenes = $pdo->prepare("
    SELECT archivo_url, texto_alternativo, tipo
    FROM multimedia_propiedades
    WHERE propiedad_id = ?
    ORDER BY es_principal DESC, orden ASC, id ASC
");

$stmtImagenes->execute([$propiedad['id']]);
$imagenes = $stmtImagenes->fetchAll();

if (empty($imagenes)) {
    $imagenes[] = [
        'archivo_url' => 'Imagenes/casa2.webp',
        'texto_alternativo' => $propiedad['titulo'],
        'tipo' => 'imagen'
    ];
}

$primerElemento = $imagenes[0];

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

$telefonoLimpio = preg_replace('/\D+/', '', (string)($propiedad['agente_telefono'] ?? ''));

$ciudadTexto = $propiedad['ciudad_nombre'] ?? 'Ciudad no disponible';
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

require_once ROOT_PATH . '/Includes/header.php';
?>

<main>
    <!-- Sección Multimedia -->
    <section class="propiedad_imagenes">
        <figure id="contenedorVisor">
            <?php if (($primerElemento['tipo'] ?? 'imagen') === 'video'): ?>
                <div class="contenedor-video-principal" id="wrapperVideo">
                    <video 
                        class="imagen_principal" 
                        id="visorPrincipal"
                        src="<?= BASE_URL ?><?= e((string)$primerElemento['archivo_url']) ?>" 
                        muted
                        playsinline>
                    </video>
                    <div class="overlay-pausa">▶</div>
                </div>
            <?php else: ?>
                <img 
                    class="imagen_principal" 
                    id="visorPrincipal"
                    src="<?= BASE_URL ?><?= e((string)$primerElemento['archivo_url']) ?>" 
                    alt="<?= e((string)$propiedad['titulo']) ?>"
                >
            <?php endif; ?>
        </figure>

        <div class="contenedor_miniaturas">
            <button class="boton boton_izquierda" type="button" id="btnMiniaturasIzquierda">
                &#10094;
            </button>

            <div class="miniaturas" id="miniaturas">
                <div class="miniaturas-contenedor">
                    <?php foreach ($imagenes as $index => $item): ?>
                        <div 
                            class="miniatura-item <?= $index === 0 ? 'activa' : '' ?>"
                            data-type="<?= e($item['tipo'] ?? 'imagen') ?>"
                            data-src="<?= BASE_URL ?><?= e((string)$item['archivo_url']) ?>"
                            onclick="cambiarMedia(this)"
                        >
                            <?php if (($item['tipo'] ?? 'imagen') === 'video'): ?>
                                <video src="<?= BASE_URL ?><?= e((string)$item['archivo_url']) ?>#t=0.5" preload="metadata" muted></video>
                            <?php else: ?>
                                <img
                                    src="<?= BASE_URL ?><?= e((string)$item['archivo_url']) ?>"
                                    alt="<?= e((string)($item['texto_alternativo'] ?: $propiedad['titulo'])) ?>"
                                >
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <button class="boton boton_derecha" type="button" id="btnMiniaturasDerecha">
                &#10095;
            </button>
        </div>
    </section>

    <!-- Información detallada de la propiedad -->
    <section class="propiedad">
        <article class="info_propiedad">
            <h1><?= e($titulo) ?></h1>

            <div class="ubicacion_precio">
                <p><?= e((string)$propiedad['direccion_completa']) ?></p>
                <p class="precio"><?= e($precio) ?></p>
            </div>

            <div class="caracteristicas">
                <ul>
                    <li>
                        <i class="fa-solid fa-house"></i>
                        <p>Construcción: <?= e((string)($propiedad['construccion_m2'] ?? 0)) ?> M²</p>
                    </li>
                    <li>
                        <i class="fa-solid fa-ruler-combined"></i>
                        <p>Terreno: <?= e((string)($propiedad['terreno_m2'] ?? 0)) ?> M²</p>
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
                        <p><?= e((string)$propiedad['estacionamientos']) ?> Estacionamientos</p>
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

        <!-- Información y contacto del agente -->
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
                <input type="hidden" name="propiedad_id" value="<?= $propiedad['id'] ?? '' ?>">
                <label>Nombre <input type="text" name="nombre" required></label>
                <label>Teléfono <input type="tel" name="telefono" required></label>
                <label>Correo electrónico <input type="email" name="email" required></label>
                <label>Mensaje <textarea name="mensaje" rows="5" required></textarea></label>
                <button type="submit">Enviar mensaje</button>
                <input type="hidden" name="slug" value="<?= e($propiedad['slug']) ?>">
            </form>
        </aside>
    </section>

    <!-- Botón que lanza la impresión directa -->
    <button
        class="imprimir"
        type="button"
        onclick="imprimirDirecto('<?= BASE_URL ?>Usuario/ImprimirPropiedadInfo.php?id=<?= $propiedad['id'] ?>&moneda=<?= urlencode($monedaMostrar) ?>')">
        <i class="fa-solid fa-print"></i>
        Imprimir ficha
    </button>

    <!-- Ubicación Google Maps -->
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

    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 1): ?>
        <dialog id="modalMensajeEnviado" class="modal-exito">
            <div class="modal-exito-content">
                <div class="modal-exito-icon">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <h2>¡Mensaje enviado!</h2>
                <p>
                    Gracias por contactarnos.<br>
                    Un asesor de Primavera Inmobiliaria se comunicará contigo lo antes posible.
                </p>
                <button id="cerrarModalMensaje">Aceptar</button>
            </div>
        </dialog>
    <?php endif; ?>

</main>

<?php require_once ROOT_PATH . '/Includes/footer.php'; ?>

<script>
let mediaActual = 0;
let listaMultimedia = [];
let inicioX = 0;
let finalX = 0;

document.addEventListener("DOMContentLoaded", () => {
    // 1. Cargar items multimedia
    const items = document.querySelectorAll(".miniatura-item");
    listaMultimedia = Array.from(items).map(item => ({
        src: item.dataset.src,
        type: item.dataset.type
    }));

    // Vincular evento de clic / pausa al video principal inicial si existe
    const videoInicial = document.getElementById("visorPrincipal");
    const wrapperInicial = document.getElementById("wrapperVideo");
    if (videoInicial && wrapperInicial) {
        configurarEventosVideo(videoInicial, wrapperInicial);
    }

    // 2. Swipe táctil en visor
    const visor = document.getElementById('contenedorVisor');
    if (visor) {
        visor.addEventListener("touchstart", (e) => { inicioX = e.touches[0].clientX; });
        visor.addEventListener("touchend", (e) => {
            finalX = e.changedTouches[0].clientX;
            if (inicioX - finalX > 50) siguienteMedia();
            if (finalX - inicioX > 50) anteriorMedia();
        });
    }

    // 3. Scroll de miniaturas
    const miniaturas = document.getElementById('miniaturas');
    const btnIzquierda = document.getElementById('btnMiniaturasIzquierda');
    const btnDerecha = document.getElementById('btnMiniaturasDerecha');

    if (btnIzquierda && miniaturas) {
        btnIzquierda.addEventListener('click', () => miniaturas.scrollBy({ left: -500, behavior: 'smooth' }));
    }
    if (btnDerecha && miniaturas) {
        btnDerecha.addEventListener('click', () => miniaturas.scrollBy({ left: 500, behavior: 'smooth' }));
    }

    // 4. Modal de mensaje enviado
    const modalMensaje = document.getElementById("modalMensajeEnviado");
    if (modalMensaje) {
        modalMensaje.showModal();
        const cerrarBtn = document.getElementById("cerrarModalMensaje");
        if (cerrarBtn) {
            cerrarBtn.addEventListener("click", () => {
                modalMensaje.close();
                limpiarUrlMensaje();
            });
        }
        modalMensaje.addEventListener("click", (e) => {
            if (e.target === modalMensaje) {
                modalMensaje.close();
                limpiarUrlMensaje();
            }
        });
    }
});

// Función para imprimir directamente sin modal visible
function imprimirDirecto(url) {
    let iframe = document.getElementById("iframeImpresionOculto");
    
    // Si no existe el iframe invisible, lo creamos
    if (!iframe) {
        iframe = document.createElement("iframe");
        iframe.id = "iframeImpresionOculto";
        iframe.style.position = "fixed";
        iframe.style.right = "0";
        iframe.style.bottom = "0";
        iframe.style.width = "0px";
        iframe.style.height = "0px";
        iframe.style.border = "none";
        document.body.appendChild(iframe);
    }

    // Evento al cargar el iframe
    iframe.onload = function() {
        const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;

        // Esperar a que las imágenes internas del iframe terminen de cargar para rapidez y precisión
        const imagenes = iframeDoc.querySelectorAll("img");
        const promesasImagenes = Array.from(imagenes).map(img => {
            if (img.complete) return Promise.resolve();
            return new Promise(resolve => {
                img.onload = resolve;
                img.onerror = resolve;
            });
        });

        Promise.all(promesasImagenes).then(() => {
            setTimeout(() => {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
            }, 100);
        });
    };

    iframe.src = url;
}

function limpiarUrlMensaje() {
    const url = new URL(window.location);
    url.searchParams.delete("mensaje");
    window.history.replaceState({}, "", url);
}

function configurarEventosVideo(video, wrapper) {
    video.addEventListener('click', (e) => {
        if (e.target === video) {
            if (video.paused) {
                video.play();
                wrapper.classList.remove('pausado');
            } else {
                video.pause();
                wrapper.classList.add('pausado');
            }
        }
    });

    video.addEventListener('pause', () => wrapper.classList.add('pausado'));
    video.addEventListener('play', () => wrapper.classList.remove('pausado'));
}

function cambiarMedia(element) {
    const src = element.dataset.src;
    const type = element.dataset.type;
    const contenedor = document.getElementById("contenedorVisor");
    if (!contenedor) return;

    const items = Array.from(document.querySelectorAll(".miniatura-item"));
    mediaActual = items.indexOf(element);

    items.forEach(el => el.classList.remove("activa"));
    element.classList.add("activa");

    contenedor.innerHTML = "";

    if (type === 'video') {
        const wrapper = document.createElement("div");
        wrapper.className = "contenedor-video-principal animacion-derecha";
        wrapper.id = "wrapperVideo";

        const video = document.createElement("video");
        video.className = "imagen_principal";
        video.id = "visorPrincipal";
        video.src = src;
        video.autoplay = true;
        video.muted = true; // Forzado a estar muteado al cambiar de video
        video.playsInline = true;

        const overlay = document.createElement("div");
        overlay.className = "overlay-pausa";
        overlay.innerHTML = "▶";

        wrapper.appendChild(video);
        wrapper.appendChild(overlay);
        contenedor.appendChild(wrapper);

        configurarEventosVideo(video, wrapper);
    } else {
        const img = document.createElement("img");
        img.className = "imagen_principal animacion-derecha";
        img.id = "visorPrincipal";
        img.src = src;
        img.alt = "Imagen de propiedad";
        contenedor.appendChild(img);
    }
}

function siguienteMedia() {
    if (mediaActual < listaMultimedia.length - 1) {
        mediaActual++;
        const items = document.querySelectorAll(".miniatura-item");
        cambiarMedia(items[mediaActual]);
    }
}

function anteriorMedia() {
    if (mediaActual > 0) {
        mediaActual--;
        const items = document.querySelectorAll(".miniatura-item");
        cambiarMedia(items[mediaActual]);
    }
}
</script>