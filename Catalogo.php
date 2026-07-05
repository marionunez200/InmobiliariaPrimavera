<?php
require_once __DIR__ . '/Config/database.php';

$pdo = db();

function limpiarTexto(?string $texto): string
{
    $texto = $texto ?? '';
    $texto = str_replace(["\\n", "\\r", "\n", "\r"], ' ', $texto);
    return trim($texto);
}

function selectedOption(string $actual, string $valor): string
{
    return $actual === $valor ? 'selected' : '';
}

function ciudadTexto(?string $ciudad): string
{
    return match ($ciudad) {
        'navojoa' => 'Navojoa',
        'san_carlos' => 'San Carlos',
        'ciudad_obregon' => 'Ciudad Obregón',
        'guaymas' => 'Guaymas',
        default => 'Ciudad Obregón'
    };
}

function operacionTexto(?string $operacion): string
{
    return match ($operacion) {
        'venta' => 'venta',
        'renta' => 'renta',
        default => ''
    };
}

$ciudad = $_GET['ciudad'] ?? '';
$tipoOperacion = $_GET['tipo_operacion'] ?? '';
$tipoPropiedad = $_GET['tipo_propiedad'] ?? '';
$precioMin = $_GET['precio_min'] ?? '';
$precioMax = $_GET['precio_max'] ?? '';

$sql = "
    SELECT
        p.*,
        (
            SELECT ip.imagen_url
            FROM imagenes_propiedades ip
            WHERE ip.propiedad_id = p.id
            ORDER BY ip.es_principal DESC, ip.orden ASC, ip.id ASC
            LIMIT 1
        ) AS imagen_principal
    FROM propiedades p
    WHERE p.estado_publicacion = 'activo'
";

$params = [];

if ($ciudad !== '') {
    $sql .= " AND p.ciudad = ?";
    $params[] = $ciudad;
}

if ($tipoOperacion !== '') {
    $sql .= " AND p.tipo_operacion = ?";
    $params[] = $tipoOperacion;
}

if ($tipoPropiedad !== '') {
    $sql .= " AND p.tipo_propiedad = ?";
    $params[] = $tipoPropiedad;
}

if ($precioMin !== '' && is_numeric($precioMin)) {
    $sql .= " AND p.precio >= ?";
    $params[] = $precioMin;
}

if ($precioMax !== '' && is_numeric($precioMax)) {
    $sql .= " AND p.precio <= ?";
    $params[] = $precioMax;
}

$sql .= " ORDER BY p.destacada DESC, p.creado_en DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$propiedades = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Primavera inmobiliaria | Catálogo de propiedades</title>

    <meta 
        name="description" 
        content="Encuentra casas, terrenos, departamentos y locales comerciales en venta y renta en Sonora."
    >

    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#ffffff">

    <link rel="canonical" href="https://www.inmobiliariaprimavera.com/catalogo">

    <link rel="stylesheet" href="./CSS/Footer.css">
    <link rel="stylesheet" href="./CSS/header.css">
    <link rel="stylesheet" href="./CSS/catalogo.css">
    <link rel="stylesheet" href="./CSS/burbuja.css">

    <link 
        rel="stylesheet" 
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    >
</head>

<body style="margin:0">

    <header class="site-header">

        <a href="index.html" class="navbar-logo movil">
            <img class="logo" src="Imagenes/Logosolo.png" alt="Logo de Primavera inmobiliaria">
        </a>

        <button class="menu-toggle" id="menu-toggle">
            <i class="fa-solid fa-bars"></i>
        </button>

        <nav class="navbar" id="navbar">

            <div class="navbar-left">

                <a href="index.html">Inicio</a>

                <div>
                    <a href="catalogo.php">Venta</a>
                    <ul class="submenu">
                        <li><a href="catalogo.php">Casas en venta</a></li>
                        <li><a href="catalogo.php">Departamentos en venta</a></li>
                        <li><a href="catalogo.php">Locales comerciales en venta</a></li>
                        <li><a href="catalogo.php">Terrenos en venta</a></li>
                    </ul>
                </div>

                <div>
                    <a href="catalogo.php">Renta</a>
                    <ul class="submenu">
                        <li><a href="catalogo.php">Casas en renta</a></li>
                        <li><a href="catalogo.php">Departamentos en renta</a></li>
                        <li><a href="catalogo.php">Locales comerciales en renta</a></li>
                    </ul>
                </div>

            </div>

            <a href="index.html" class="navbar-logo pc">
                <img class="logo" src="Imagenes/Logosolo.png" alt="Logo de Primavera inmobiliaria">
            </a>

            <div class="navbar-right">
                <a href="Contacto.html">Contacto</a>
            </div>

        </nav>
    </header>

<main class="site-main">

    <section class="filtro">
        <form class="filtro-form" action="Catalogo.php" method="GET" aria-label="Formulario de filtrado de propiedades">

            <div class="filtro-group-top">

                <label for="ciudad">Ubicación:</label>
                <select id="ciudad" name="ciudad">
                    <option value="">Cualquiera</option>
                    <option value="ciudad_obregon" <?= selectedOption($ciudad, 'ciudad_obregon') ?>>Ciudad Obregón</option>
                    <option value="navojoa" <?= selectedOption($ciudad, 'navojoa') ?>>Navojoa</option>
                    <option value="san_carlos" <?= selectedOption($ciudad, 'san_carlos') ?>>San Carlos</option>
                    <option value="guaymas" <?= selectedOption($ciudad, 'guaymas') ?>>Guaymas</option>
                </select>

                <label for="tipo_operacion">Venta/Renta:</label>
                <select id="tipo_operacion" name="tipo_operacion">
                    <option value="">Cualquiera</option>
                    <option value="venta" <?= selectedOption($tipoOperacion, 'venta') ?>>Venta</option>
                    <option value="renta" <?= selectedOption($tipoOperacion, 'renta') ?>>Renta</option>
                </select>

                <label for="tipo_propiedad">Tipo de propiedad:</label>
                <select id="tipo_propiedad" name="tipo_propiedad">
                    <option value="">Cualquiera</option>
                    <option value="casa" <?= selectedOption($tipoPropiedad, 'casa') ?>>Casa</option>
                    <option value="departamento" <?= selectedOption($tipoPropiedad, 'departamento') ?>>Departamento</option>
                    <option value="local_comercial" <?= selectedOption($tipoPropiedad, 'local_comercial') ?>>Local comercial</option>
                    <option value="terreno" <?= selectedOption($tipoPropiedad, 'terreno') ?>>Terreno</option>
                </select>

            </div>

            <div class="filtro-group-bottom">

                <div class="floating-group">
                    <input 
                        type="number" 
                        id="precio_min" 
                        name="precio_min" 
                        placeholder="$0"
                        value="<?= e($precioMin) ?>"
                    >
                    <label for="precio_min">Precio mínimo:</label>
                </div>

                <div>
                    <input 
                        type="range" 
                        id="precio" 
                        name="precio_rango" 
                        min="0" 
                        max="10000000" 
                        step="100000" 
                        value="<?= e($precioMax !== '' ? $precioMax : '5000000') ?>"
                        aria-label="Rango de precios"
                    >
                </div>

                <div class="filtro-group floating-group">
                    <input 
                        type="number" 
                        id="precio_max" 
                        name="precio_max" 
                        placeholder="$10,000,000"
                        value="<?= e($precioMax) ?>"
                    >
                    <label for="precio_max">Precio máximo:</label>
                </div>

                <button type="submit" id="aplicar_filtro" class="btn">
                    Aplicar filtros
                </button>

            </div>

        </form>
    </section>

    <section class="catalogo">
        <div class="catalogo-grid">

            <?php if (empty($propiedades)): ?>
                <p>No hay propiedades disponibles con esos filtros.</p>
            <?php endif; ?>
            
            <?php foreach ($propiedades as $propiedad): ?>
                <?php
                    $imagen = limpiarTexto($propiedad['imagen_principal'] ?? '');
            
                    if ($imagen === '') {
                        $imagen = 'Imagenes/casa1.jpg';
                    }
            
                    $titulo = limpiarTexto($propiedad['titulo']);
                    $ciudadLegible = ciudadTexto($propiedad['ciudad']);
                    $operacionLegible = operacionTexto($propiedad['tipo_operacion']);
            
                    $precio = '$' . number_format((float)$propiedad['precio'], 0) . ' ' . $propiedad['moneda'];
            
                    if ($propiedad['tipo_operacion'] === 'renta') {
                        $precio .= '/mes';
                    }
                ?>

                <article class="propiedad-card">
            
                    <img 
                        class="propiedad-img" 
                        src="<?= e($imagen) ?>" 
                        alt="<?= e($titulo) ?>"
                    >
            
                    <div class="propiedad-info">
            
                        <h2>
                            <?= e($titulo) ?>
                        </h2>
            
                        <p class="precio-mxn">
                            <?= e($precio) ?>
                        </p>
            
                        <div class="info_card">
                            <p>Baños: <?= e((string)$propiedad['banos']) ?></p>
                            <p>Recámaras: <?= e((string)$propiedad['recamaras']) ?></p>
                        </div>
            
                        <a 
                            class="propiedad_detalles" 
                            href="PropiedadInfo.php?id=<?= e((string)$propiedad['id']) ?>"
                        >
                            Ver detalles
                        </a>
            
                    </div>
            
                </article>
            
            <?php endforeach; ?>
            
        </div>
    </section>

    <div class="chat-widget" id="chatWidget">

        <div class="chat-card" id="chatCard">

            <div class="chat-texto">
                <h3>¿No encontraste lo que buscabas?</h3>

                <p>
                    Contáctame y con gusto te ayudaré a encontrar
                    la propiedad ideal para ti.
                </p>

                <div class="asesor">
                    <img src="Uploads/agentes/agente-6a3aea4d4bf61.webp" alt="Asesora">

                    <div>
                        <strong>María Fernanda</strong>
                        <span>Asesora inmobiliaria</span>
                    </div>
                </div>

                <a
                    href="https://wa.me/526441234567"
                    target="_blank"
                    class="btn-whatsapp"
                >
                    <i class="fa-brands fa-whatsapp"></i>
                    Enviar mensaje
                </a>
            </div>
        </div>
        
        <div class="mensaje-burbuja hover">
            👋 Estoy aquí para ayudarte
        </div>

        <button class="burbuja" id="abrirChat">
            <img src="Uploads/agentes/agente-6a3aea4d4bf61.webp" alt="Asesora">
        </button>

    </div>

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
                <li><a href="Terminos-condiciones.html" class="footer-link">Términos y condiciones</a></li>
                <li><a href="Catalogo.php" class="footer-link">Todas las propiedades</a></li>
                <li><a href="Contacto.html" class="footer-link">Contacto</a></li>
            </ul>
        </nav>

        <address class="footer-contacto">
            <h2 class="footer-title">Contacto</h2>

            <p class="footer-text">Ejército Nacional 1101 entre 5 de Febrero y Jalisco. Fracc. Primavera</p>

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
    const precioMin = document.getElementById('precioMin');
    const precioMax = document.getElementById('precioMax');
                
    const precioMinTexto = document.getElementById('precioMinTexto');
    const precioMaxTexto = document.getElementById('precioMaxTexto');
                
    const sliderTrack = document.querySelector('.slider-track');
                
    const diferenciaMinima = 50000;
                
    function formatearPrecio(valor) {
        return new Intl.NumberFormat('es-MX', {
            style: 'currency',
            currency: 'MXN',
            maximumFractionDigits: 0
        }).format(valor);
    }
                
    function actualizarSlider(evento = null) {
        let min = parseInt(precioMin.value, 10);
        let max = parseInt(precioMax.value, 10);
                
        if (max - min < diferenciaMinima) {
            if (evento?.target === precioMin) {
                precioMin.value = String(max - diferenciaMinima);
                min = parseInt(precioMin.value, 10);
            } else {
                precioMax.value = String(min + diferenciaMinima);
                max = parseInt(precioMax.value, 10);
            }
        }
                
        precioMinTexto.textContent = formatearPrecio(min);
        precioMaxTexto.textContent = formatearPrecio(max);
                
        const minPorcentaje = (min / precioMin.max) * 100;
        const maxPorcentaje = (max / precioMax.max) * 100;
                
        if (sliderTrack) {
            sliderTrack.style.background = `
                linear-gradient(
                    to right,
                    #ddd ${minPorcentaje}%,
                    #0f5132 ${minPorcentaje}%,
                    #0f5132 ${maxPorcentaje}%,
                    #ddd ${maxPorcentaje}%
                )
            `;
        }
    }
                
    if (precioMin && precioMax) {
        precioMin.addEventListener('input', (evento) => actualizarSlider(evento));
        precioMax.addEventListener('input', (evento) => actualizarSlider(evento));
    }
                
    actualizarSlider();

    const menu = document.getElementById("navbar");
    const boton = document.getElementById("menu-toggle");

    if (menu && boton) {
        boton.addEventListener("click", () => {
            menu.classList.toggle("active");
        });
    }

    const chatCard = document.getElementById("chatCard");
    const abrirChat = document.getElementById("abrirChat");

    // Se abre automáticamente después de 4 segundos
    setTimeout(() => {
        chatCard.classList.add("activo");
    }, 4000);

    // Abrir manualmente
    abrirChat.addEventListener("click", () => {
        chatCard.classList.toggle("activo");
    });

    // Cerrar
    cerrarChat.addEventListener("click", () => {
        chatCard.classList.remove("activo");
    });

    const mensajeBurbuja = document.querySelector(".mensaje-burbuja");

    abrirChat.addEventListener("click", () => {
        chatCard.classList.toggle("activo");

        if(mensajeBurbuja){
            mensajeBurbuja.style.display = "none";
        }
});

</script>
</body>
</html>