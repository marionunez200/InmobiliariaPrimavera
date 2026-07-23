<?php
if (!defined('BASE_URL')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
}

require_once ROOT_PATH . '/Config/database.php';
$pdo = db();

require_once ROOT_PATH . '/Backend/cambio-moneda.php';

$monedaMostrar = strtoupper($_GET['moneda'] ?? 'MXN');

if (!in_array($monedaMostrar, ['MXN', 'USD'], true)) {
    $monedaMostrar = 'MXN';
}

$porPagina = 12;

$pagina = isset($_GET['pagina'])
    ? max(1, (int)$_GET['pagina'])
    : 1;

$offset = ($pagina - 1) * $porPagina;

$categorias = $pdo->query("
    SELECT id, nombre
    FROM categorias_propiedad
    ORDER BY nombre
")->fetchAll(PDO::FETCH_ASSOC);

$sqlCount = "
    SELECT COUNT(*)
    FROM propiedades p
    INNER JOIN categorias_propiedad c
        ON c.id = p.categoria_id
    WHERE p.estado_publicacion = 'activo'
";

$paramsCount = [];

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
$categoria = (int)($_GET['categoria'] ?? 0);
$precioMin = $_GET['precio_min'] ?? '';
$precioMax = $_GET['precio_max'] ?? '';

$sql = "
    SELECT
        p.*,
        c.nombre AS categoria_nombre,
        (
            SELECT ip.imagen_url
            FROM imagenes_propiedades ip
            WHERE ip.propiedad_id = p.id
            ORDER BY ip.es_principal DESC, ip.orden ASC, ip.id ASC
            LIMIT 1
        ) AS imagen_principal
    FROM propiedades p
    INNER JOIN categorias_propiedad c
        ON c.id = p.categoria_id
    WHERE p.estado_publicacion = 'activo'
";

$params = [];

if ($ciudad !== '') {
    $sqlCount .= " AND p.ciudad = ?";
    $sql      .= " AND p.ciudad = ?";
    $paramsCount[] = $ciudad;
    $params[]      = $ciudad;
}

if ($tipoOperacion !== '') {
    $sqlCount .= " AND p.tipo_operacion = ?";
    $sql      .= " AND p.tipo_operacion = ?";
    $paramsCount[] = $tipoOperacion;
    $params[]      = $tipoOperacion;
}

if ($categoria > 0) {
    $sqlCount .= " AND p.categoria_id = ?";
    $sql      .= " AND p.categoria_id = ?";
    $paramsCount[] = $categoria;
    $params[]      = $categoria;
}

if ($precioMin !== '' && is_numeric($precioMin)) {
    $sqlCount .= " AND p.precio >= ?";
    $sql      .= " AND p.precio >= ?";
    $paramsCount[] = $precioMin;
    $params[]      = $precioMin;
}

if ($precioMax !== '' && is_numeric($precioMax)) {
    $sqlCount .= " AND p.precio <= ?";
    $sql      .= " AND p.precio <= ?";
    $paramsCount[] = $precioMax;
    $params[]      = $precioMax;
}

$sql .= "
    ORDER BY p.destacada DESC, p.creado_en DESC
    LIMIT $porPagina
    OFFSET $offset
";

$stmtCount = $pdo->prepare($sqlCount);
$stmtCount->execute($paramsCount);

$totalPropiedades = $stmtCount->fetchColumn();

$totalPaginas = ceil($totalPropiedades / $porPagina);

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$propiedades = $stmt->fetchAll();
?>

<?php
$titulo = "Primavera inmobiliaria | Catálogo de propiedades";
$descripcion = "Encuentra casas, terrenos, departamentos y locales comerciales en venta y renta en Sonora.";
$cssPaginas = [BASE_URL . 'CSS/catalogo.css', BASE_URL . 'CSS/burbuja.css'];

require_once ROOT_PATH . '/Includes/header.php';

?>

<main class="site-main">

    <section class="filtro">
        
        <h2 class="titulo-filtros">
            Filtros
        </h2>

        <form class="filtro-form" action="<?= BASE_URL ?>Usuario/Catalogo.php" method="GET" aria-label="Formulario de filtrado de propiedades">

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
                    <option value="traspaso" <?= selectedOption($tipoOperacion, 'traspaso') ?>>Traspaso</option>
                </select>

                <label for="categoria">Categoría:</label>
                <select id="categoria" name="categoria">
                    <option value="0">Cualquiera</option>

                    <?php foreach ($categorias as $cat): ?>
                        <option
                            value="<?= $cat['id'] ?>"
                            <?= $categoria == $cat['id'] ? 'selected' : '' ?>
                        >
                            <?= e($cat['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="moneda">Moneda</label>

                <select name="moneda" id="moneda">

                    <option value="MXN"
                        <?= selectedOption($monedaMostrar, 'MXN') ?>>
                        MXN
                    </option>

                    <option value="USD"
                        <?= selectedOption($monedaMostrar, 'USD') ?>>
                        USD
                    </option>

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
                    <div>
                        <div class="slider-container">
                            <input class="range-slider" type="range" class="range-slider" id="min-range" min="0" max="10000000" value="0" step="100">
                            <input class="range-slider" type="range" class="range-slider" id="max-range" min="0" max="10000000" value="10000000" step="100">
                        </div>
                    </div>
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

                    // Usamos $tituloPropiedad para evitar sobrescribir $titulo de la página
                    $tituloPropiedad = limpiarTexto($propiedad['titulo']);
                    $ciudadLegible = ciudadTexto($propiedad['ciudad']);
                    $operacionLegible = operacionTexto($propiedad['tipo_operacion']);

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
                        $precio .= '/mes';
                    }
                ?>

                <article class="propiedad-card">

                    <img 
                        class="propiedad-img" 
                        src="<?= BASE_URL ?><?= e($imagen) ?>"
                        alt="<?= e($tituloPropiedad) ?>"
                    >

                    <div class="propiedad-info">

                        <h2>
                            <?= e($tituloPropiedad) ?>
                        </h2>

                        <p class="precio-mxn">
                            <?= e($precio) ?>
                        </p>

                        <div class="info_card">
                            <p>Baños: <?= e((int)$propiedad['banos']) ?></p>
                            <p>Recámaras: <?= e((string)$propiedad['recamaras']) ?></p>
                        </div>

                        <a
                            class="propiedad_detalles"
                            href="<?= BASE_URL ?>Usuario/propiedades/<?= e($propiedad['slug'] ?? '') ?>?moneda=<?= e($monedaMostrar) ?>"
                        >
                            Ver detalles
                        </a>
                    </div>

                </article>

            <?php endforeach; ?>
            
        </div>

        <?php if ($totalPaginas > 1): ?>

            <nav class="paginacion" aria-label="Navegación de páginas">

                <?php if ($pagina > 1): ?>
                    <?php 
                        $queryPrev = $_GET;
                        $queryPrev['pagina'] = $pagina - 1;
                    ?>
                    <a href="?<?= http_build_query($queryPrev) ?>" class="paginacion-btn">
                        &larr; Anterior
                    </a>
                <?php else: ?>
                    <span class="paginacion-btn deshabilitado">&larr; Anterior</span>
                <?php endif; ?>

                <?php
                    $rango = 1;
                    $inicio = max(1, $pagina - $rango);
                    $fin = min($totalPaginas, $pagina + $rango);
                ?>

                <?php if ($inicio > 1): ?>
                    <?php $q = $_GET; $q['pagina'] = 1; ?>
                    <a href="?<?= http_build_query($q) ?>" class="paginacion-numero">1</a>
                    <?php if ($inicio > 2): ?>
                        <span style="color:#122548; font-weight:bold;">...</span>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $inicio; $i <= $fin; $i++): ?>
                    <?php
                        $query = $_GET;
                        $query['pagina'] = $i;
                    ?>
                    <a
                        href="?<?= http_build_query($query) ?>"
                        class="paginacion-numero <?= $i == $pagina ? 'activo' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($fin < $totalPaginas): ?>
                    <?php if ($fin < $totalPaginas - 1): ?>
                        <span style="color:#122548; font-weight:bold;">...</span>
                    <?php endif; ?>
                    <?php $q = $_GET; $q['pagina'] = $totalPaginas; ?>
                    <a href="?<?= http_build_query($q) ?>" class="paginacion-numero"><?= $totalPaginas ?></a>
                <?php endif; ?>

                <?php if ($pagina < $totalPaginas): ?>
                    <?php 
                        $queryNext = $_GET;
                        $queryNext['pagina'] = $pagina + 1;
                    ?>
                    <a href="?<?= http_build_query($queryNext) ?>" class="paginacion-btn">
                        Siguiente &rarr;
                    </a>
                <?php else: ?>
                    <span class="paginacion-btn deshabilitado">Siguiente &rarr;</span>
                <?php endif; ?>

            </nav>

        <?php endif; ?>

    </section>
        
        <div class="chat-widget">
            <!-- Tarjeta desplegable (Modal de WhatsApp) -->
            <div class="chat-card" id="chatCard">
                <div class="chat-texto">
                    <h3>¿No encontraste lo que buscabas?</h3>

                    <p>
                        Contáctame y con gusto te ayudaré a encontrar
                        la propiedad ideal para ti.
                    </p>

                    <div class="asesor">
                        <img src="<?= BASE_URL ?>Uploads/agentes/agente-6a4caae0ba02f.webp" alt="Asesora">

                        <div>
                            <strong>María Fernanda</strong>
                            <span>Asesora inmobiliaria</span>
                        </div>
                    </div>

                    <a
                        href="https://wa.me/526441435244"
                        target="_blank"
                        class="btn-whatsapp"
                        rel="noopener noreferrer"
                    >
                        <i class="fa-brands fa-whatsapp"></i>
                        Enviar mensaje
                    </a>
                </div>
            </div>
            
            <!-- Contenedor alineado: Mensaje a la izquierda, foto a la derecha -->
            <div class="chat-burbuja-container">
                <button class="burbuja" id="abrirChat">
                    <img src="<?= BASE_URL ?>Uploads/agentes/agente-6a4caae0ba02f.webp" alt="Asesora">
                </button>
                
                <div class="mensaje-burbuja">👋 Estoy aquí para ayudarte</div>
            </div>
        </div>

</main>

<?php require_once ROOT_PATH . '/Includes/footer.php'; ?>

<script>

    // ==========================
    // MENÚ RESPONSIVO
    // ==========================
    const menu = document.getElementById("navbar");
    const boton = document.getElementById("menu-toggle");

    if (menu && boton) {
        boton.addEventListener("click", () => {
            menu.classList.toggle("active");
        });
    }

    // ==========================
    // CHAT
    // ==========================
    const chatCard = document.getElementById("chatCard");
    const abrirChat = document.getElementById("abrirChat");
    const cerrarChat = document.getElementById("cerrarChat");
    const mensajeBurbuja = document.querySelector(".mensaje-burbuja");

    if (chatCard) {
        setTimeout(() => {
            chatCard.classList.add("activo");
        }, 4000);
    }

    if (abrirChat) {
        abrirChat.addEventListener("click", () => {

            chatCard.classList.toggle("activo");

            if (mensajeBurbuja) {
                mensajeBurbuja.style.display = "none";
            }

        });
    }

    if (cerrarChat) {
        cerrarChat.addEventListener("click", () => {
            chatCard.classList.remove("activo");
        });
    }

    // ==========================
    // FILTROS
    // ==========================
    const formulario = document.querySelector(".filtro-form");
    const catalogo = document.querySelector(".catalogo");

    
    const precioMin = document.getElementById("precio_min");
    const precioMax = document.getElementById("precio_max");
    const minRange = document.getElementById("min-range");
    const maxRange = document.getElementById("max-range");
    
    
    function cargarPropiedades() {

        const datos = new FormData(formulario);

        // Actualizar la URL
        const nuevaURL = new URL(window.location);

        nuevaURL.searchParams.set("pagina", "1");

        for (const [clave, valor] of datos.entries()) {

            if (valor !== "") {
                nuevaURL.searchParams.set(clave, valor);
            } else {
                nuevaURL.searchParams.delete(clave);
            }

        }

        history.replaceState({}, "", nuevaURL);

        fetch("<?= BASE_URL ?>Usuario/catalogo-filtrado.php?" + new URLSearchParams(datos))
            .then(response => response.text())
            .then(html => {
                catalogo.innerHTML = html;
            })
            .catch(error => console.error(error));
    }

    // Selects
    document.querySelectorAll(".filtro-form select").forEach(select => {
        select.addEventListener("change", cargarPropiedades);
    });
    if (minRange && precioMin) {

        minRange.addEventListener("input", () => {

            precioMin.value = minRange.value;

            cargarPropiedades();

        });

    }


    // Slider precio máximo
    if (maxRange && precioMax) {

        maxRange.addEventListener("input", () => {

            precioMax.value = maxRange.value;

            cargarPropiedades();

        });

    }


    // Input precio mínimo
    if (precioMin && minRange) {

        precioMin.addEventListener("input", () => {

            minRange.value = precioMin.value;

            cargarPropiedades();

        });

    }


    // Input precio máximo
    if (precioMax && maxRange) {

        precioMax.addEventListener("input", () => {

            maxRange.value = precioMax.value;

            cargarPropiedades();

        });

    }

        // Esperar a que cargue el DOM
    document.addEventListener('DOMContentLoaded', function() {
        const cerrarBtn = document.getElementById('cerrarChat');
        const chatCard = document.getElementById('chatCard');
        // const abrirBtn = document.getElementById('abrirChat'); // Si lo necesitas

        // Función para cerrar la tarjeta
        cerrarBtn.addEventListener('click', function() {
            // Opción 1: Ocultar con display
            chatCard.style.display = 'none';

            // Opción 2: Si usas clases CSS para animar (ej: .active { display: block })
            // chatCard.classList.remove('active');
        });
    });
</script>