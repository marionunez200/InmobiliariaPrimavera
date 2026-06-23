<?php
require_once __DIR__ . '/Config/database.php';

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

function tipoDetalleTexto(?string $tipo): string
{
    return match ($tipo) {
        'casa' => 'Casa',
        'departamento' => 'Departamento',
        'local_comercial' => 'Local comercial',
        'terreno' => 'Terreno',
        default => 'Propiedad'
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
        a.nombre AS agente_nombre,
        a.telefono AS agente_telefono,
        a.email AS agente_email,
        a.foto_url AS agente_foto
    FROM propiedades p
    LEFT JOIN agentes a ON p.agente_id = a.id
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
$tipoTexto = tipoDetalleTexto($propiedad['tipo_propiedad']);
$operacionTexto = operacionDetalleTexto($propiedad['tipo_operacion']);

$fotoAgente = $propiedad['agente_foto'] ?: 'Imagenes/agente1.webp';

$mapsDefault = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d56631.121546203874!2d-109.94261074999999!3d27.4865298!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x86c815e1cb75ffb9%3A0x311f38aacd3515ae!2sCdad.%20Obreg%C3%B3n%2C%20Son.!5e0!3m2!1ses!2smx!4v1780782414850!5m2!1ses!2smx';

$mapsUrl = !empty($propiedad['google_maps_url'])
    ? $propiedad['google_maps_url']
    : $mapsDefault;
?>

<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" href="./CSS/Footer.css">
    <link rel="stylesheet" href="./CSS/header.css">

    <title><?= e((string)$propiedad['titulo']) ?> | Primavera inmobiliaria</title>

    <meta 
        name="description" 
        content="<?= e(substr((string)$propiedad['descripcion'], 0, 150)) ?>"
    >

    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#ffffff">

    <link rel="canonical" href="https://www.inmobiliariaprimavera.com/">
    <link rel="icon" href="favicon.ico" type="image/x-icon">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <meta property="og:title" content="<?= e((string)$propiedad['titulo']) ?>">
    <meta property="og:description" content="<?= e(substr((string)$propiedad['descripcion'], 0, 150)) ?>">
    <meta property="og:image" content="<?= e((string)$imagenPrincipal) ?>">
    <meta property="og:url" content="https://www.inmobiliariaprimavera.com/">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="es_MX">

    <link rel="stylesheet" href="./CSS/propiedadInfo.css">
</head>

<body>

<header class="site-header">
    <nav class="navbar">

        <div class="navbar-left">

            <a href="index.html">Inicio</a>

            <div>
                <a href="Catalogo.php?tipo_operacion=venta">Venta</a>
                <ul class="submenu">
                    <li><a href="Catalogo.php?tipo_operacion=venta&tipo_propiedad=casa">Casas en venta</a></li>
                    <li><a href="Catalogo.php?tipo_operacion=venta&tipo_propiedad=departamento">Departamentos en venta</a></li>
                    <li><a href="Catalogo.php?tipo_operacion=venta&tipo_propiedad=local_comercial">Locales comerciales en venta</a></li>
                    <li><a href="Catalogo.php?tipo_operacion=venta&tipo_propiedad=terreno">Terrenos en venta</a></li>
                </ul>
            </div>

            <div>
                <a href="Catalogo.php?tipo_operacion=renta">Renta</a>
                <ul class="submenu">
                    <li><a href="Catalogo.php?tipo_operacion=renta&tipo_propiedad=casa">Casas en renta</a></li>
                    <li><a href="Catalogo.php?tipo_operacion=renta&tipo_propiedad=departamento">Departamentos en renta</a></li>
                    <li><a href="Catalogo.php?tipo_operacion=renta&tipo_propiedad=local_comercial">Locales comerciales en renta</a></li>
                </ul>
            </div>

        </div>

        <a href="index.html" class="navbar-logo">
            <img class="logo" src="Imagenes/Logosolo.png" alt="Logo">
        </a>

        <div class="navbar-right">
            <a href="Contacto.html">Contacto</a>
        </div>

    </nav>
</header>

<main>

    <!-- Imágenes de la propiedad -->
    <section class="propiedad_imagenes">

        <figure>
            <img 
                class="imagen_principal" 
                id="imagenPrincipal"
                src="<?= e((string)$imagenPrincipal) ?>" 
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
                        src="<?= e((string)$imagen['imagen_url']) ?>" 
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
                        <p><?= e((string)$propiedad['banos']) ?> baños</p>
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
                src="<?= e((string)$fotoAgente) ?>" 
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

        </aside>

    </section>

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

<footer class="site-footer">

    <div class="footer-container">

        <div class="footer-logo">
            <a href="index.html" aria-label="Ir al inicio">
                <img src="Imagenes/Logosolo.png" alt="Logo de Primavera inmobiliaria">
            </a>
        </div>

        <nav class="footer-info" aria-label="Enlaces de información">
            <h2 class="footer-title">Información</h2>

            <ul class="footer-links">
                <li><a href="Politicas-privacidad.html" class="footer-link">Aviso de privacidad</a></li>
                <li><a href="terminos-condiciones.html" class="footer-link">Términos y condiciones</a></li>
                <li><a href="Catalogo.php" class="footer-link">Todas las propiedades</a></li>
                <li><a href="Contacto.html" class="footer-link">Contacto</a></li>
            </ul>
        </nav>

        <address class="footer-contacto">
            <h2 class="footer-title">Contacto</h2>

            <p class="footer-text">
                Ejército Nacional 1101 entre 5 de Febrero y Jalisco. Fracc. Primavera
            </p>

            <p class="footer-text">
                <a class="footer-text" href="tel:+526441435244">(644) 143 5244</a>
            </p>

            <p class="footer-text">
                <a class="footer-text" href="mailto:sucorreo@gmail.com">sucorreo@gmail.com</a>
            </p>
        </address>

        <div class="footer-redes">
            <h2 class="footer-title">Redes sociales</h2>
        
            <div class="social-list">

                <a 
                    href="https://www.facebook.com/share/14eyn1t5H3f/?mibextid=wwXIfr" 
                    target="_blank" 
                    rel="noopener noreferrer"
                    aria-label="Facebook de Primavera inmobiliaria"
                    class="social-link"
                >
                    <span class="social-icon facebook">
                        <i class="fa-brands fa-facebook-f"></i>
                    </span>

                    <span class="social-user">Primavera Inmobiliaria</span>
                </a>
            
                <a 
                    href="https://www.instagram.com/primavera.inmobiliariasc?igsh=dGZoajhrYjJpYXR6" 
                    target="_blank" 
                    rel="noopener noreferrer"
                    aria-label="Instagram de Primavera inmobiliaria"
                    class="social-link"
                >
                    <span class="social-icon instagram">
                        <i class="fa-brands fa-instagram"></i>
                    </span>

                    <span class="social-user">@primavera.inmobiliariasc</span>
                </a>

            </div>
        </div>

    </div>

    <div class="footer-bottom">
        <p>&copy; 2027 Primavera inmobiliaria. Todos los derechos reservados.</p>

        <p>
            Desarrollado por 
            <a href="contacto-desarrolladores.html">ULSA North West</a>
        </p>
    </div>

</footer>

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

if (miniaturas && btnIzquierda && btnDerecha) {
    btnIzquierda.addEventListener('click', () => {
        miniaturas.scrollBy({
            left: -180,
            behavior: 'smooth'
        });
    });

    btnDerecha.addEventListener('click', () => {
        miniaturas.scrollBy({
            left: 180,
            behavior: 'smooth'
        });
    });
}
</script>
</body>
</html>