<?php
if (!defined('BASE_URL')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
}

require_once ROOT_PATH . '/Config/database.php';
$pdo = db();

$categorias = $pdo->query("
    SELECT id, nombre
    FROM categorias_propiedad
    ORDER BY nombre
")->fetchAll(PDO::FETCH_ASSOC);

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
    $sql .= " AND p.ciudad = ?";
    $params[] = $ciudad;
}

if ($tipoOperacion !== '') {
    $sql .= " AND p.tipo_operacion = ?";
    $params[] = $tipoOperacion;
}

if ($categoria > 0) {
    $sql .= " AND p.categoria_id = ?";
    $params[] = $categoria;
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
                        <input
                            type="range"
                            id="precio"
                            min="0"
                            max="10000000"
                            step="100000"
                            value="<?= e($precioMax !== '' ? $precioMax : '5000000') ?>"
                        >
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
                        src="<?= BASE_URL ?><?= e($imagen) ?>"
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
                            href="<?= BASE_URL ?>Usuario/PropiedadInfo.php?id=<?= e((string)$propiedad['id']) ?>"
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
            <img src="<?= BASE_URL ?>Uploads/agentes/agente-6a4caae0ba02f.webp" alt="Asesora">
        </button>

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
    const catalogo = document.querySelector(".catalogo-grid");

    const slider = document.getElementById("precio");
    const precioMin = document.getElementById("precio_min");
    const precioMax = document.getElementById("precio_max");

    function cargarPropiedades() {

        const datos = new FormData(formulario);

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

    // Precio mínimo
    if (precioMin) {
        precioMin.addEventListener("input", cargarPropiedades);
    }

    // Slider
    if (slider && precioMax) {

        slider.addEventListener("input", () => {

            precioMax.value = slider.value;
            cargarPropiedades();

        });

    }

    // Precio máximo
    if (precioMax && slider) {

        precioMax.addEventListener("input", () => {

            slider.value = precioMax.value;
            cargarPropiedades();

        });

    }

</script>