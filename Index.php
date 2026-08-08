<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

$titulo = "Primavera inmobiliaria | Casas y propiedades en venta y renta en Sonora";
$descripcion = "Encuentra casas, terrenos, departamentos y locales comerciales en venta y renta en Sonora. Propiedades disponibles en Ciudad Obregón, San Carlos y Guaymas.";
$cssPaginas = [BASE_URL . "CSS/index.css"];

require_once 'Config/database.php';
$pdo = db();

$conteos = [];
$conteosPorCategoria = [];

// 1. Obtener lista de ciudades
$stmtCiudades = $pdo->query("SELECT id, nombre FROM ciudades ORDER BY nombre ASC");
$ciudades = $stmtCiudades->fetchAll(PDO::FETCH_ASSOC);

// 2. Obtener conteos por tipo de operación
$stmt = $pdo->query("
    SELECT
        categoria_id,
        tipo_operacion,
        COUNT(*) AS total
    FROM propiedades
    WHERE estado_publicacion = 'activo'
    GROUP BY categoria_id, tipo_operacion
");

while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $conteos[$fila['tipo_operacion']][$fila['categoria_id']] = (int)$fila['total'];
}

// 3. Obtener conteos totales por categoría
$stmtCat = $pdo->query("
    SELECT
        categoria_id,
        COUNT(*) AS total
    FROM propiedades
    WHERE estado_publicacion = 'activo'
    GROUP BY categoria_id
");

while ($fila = $stmtCat->fetch(PDO::FETCH_ASSOC)) {
    $conteosPorCategoria[$fila['categoria_id']] = (int)$fila['total'];
}

/**
 * Función auxiliar para generar la clase CSS correspondiente a cada ciudad
 */
function obtenerClaseCssCiudad($nombre) {
    $nombreLimpio = mb_strtolower(trim($nombre), 'UTF-8');
    
    // Si contiene 'obreg', forzamos 'obregon' para que coincida con .city-obregon
    if (strpos($nombreLimpio, 'obreg') !== false) {
        return 'obregon';
    }
    
    // Remueve tildes, eñes y espacios
    $remplazos = [
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
        'ñ' => 'n', ' ' => '-'
    ];
    $clase = strtr($nombreLimpio, $remplazos);
    
    // Remueve el prefijo "ciudad-" si existe
    return str_replace('ciudad-', '', $clase);
}

require_once ROOT_PATH . '/Includes/header.php';
?>

    <main class="main-content">

        <!-- SECCIÓN HERO -->
        <section class="hero" aria-labelledby="hero-title">
        
            <div class="hero-content">
            
                <div class="hero-center">
                    <p class="hero-brand">PRIMAVERA INMOBILIARIA, S.C</p>
                
                    <h1 id="hero-title">
                        Contigo en cada paso hacia tu nuevo proyecto
                    </h1>
                
                    <form class="search-form" action="<?= BASE_URL ?>Usuario/Catalogo.php" method="GET">
                        <div class="select-wrapper">
                            <select name="ciudad" id="ciudad" aria-label="Selecciona una ubicación">
                                <option value="">Seleccione ubicación...</option>
                                <?php foreach ($ciudades as $ciudad): ?>
                                    <option value="<?= htmlspecialchars($ciudad['id']) ?>">
                                        <?= htmlspecialchars($ciudad['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <i class="fa-solid fa-chevron-down"></i>
                        </div>

                        <button type="submit" class="form-boton">
                            Buscar
                        </button>
                    </form>
                </div>
            
            </div>
        
        </section>

        <!-- CATEGORÍAS DE PROPIEDADES -->
        <section class="property-categories">

            <div class="section1-heading">
                <h2 id="categories-title">Encuentra un nuevo hogar</h2>

                <div class="line"></div>

                <p>
                    Explora nuestro catálogo de propiedades en Sonora. Contamos con opciones
                    para compra, renta e inversión inmobiliaria.
                </p>

                <a href="<?= BASE_URL ?>Usuario/Catalogo.php" class="view-all-button">
                    Ver propiedades
                </a>
            </div>

            <div class="categories-grid">
                <!-- 1. CASAS HABITACIONAL -->
                <a href="<?= BASE_URL ?>Usuario/Catalogo.php?categoria=2" class="category-card card-casa movcard">
                    <h3>Casas Habitacional</h3>
                    <p>
                        Disponibles:
                        <?= $conteosPorCategoria[2] ?? 0 ?>
                    </p>
                </a>

                <!-- 2. TERRENOS DISPONIBLES -->
                <a href="<?= BASE_URL ?>Usuario/Catalogo.php?categoria=4" class="category-card card-terreno movcard">
                    <h3>Terrenos Disponibles</h3>
                    <p>
                        Disponibles:
                        <?= $conteosPorCategoria[4] ?? 0 ?>
                    </p>
                </a>

                <!-- 3. LOCAL COMERCIAL -->
                <a href="<?= BASE_URL ?>Usuario/Catalogo.php?categoria=5" class="category-card card-renta movcard">
                    <h3>Local Comercial</h3>
                    <p>
                        Disponibles:
                        <?= $conteosPorCategoria[5] ?? 0 ?>
                    </p>
                </a>
            </div>
        </section>

        <!-- CIUDADES -->
        <section class="cities-section">
            <div class="cities-header">
                <h2>Busca en tu ciudad</h2>
                <p>Encuentra un hogar cerca de ti</p>
            </div>

            <div class="cities-carousel">
                <div class="cities-track">
                    <?php if (!empty($ciudades)): ?>
                        <!-- Primer bloque de tarjetas -->
                        <?php foreach ($ciudades as $ciudad): 
                            $claseCss = obtenerClaseCssCiudad($ciudad['nombre']);
                        ?>
                            <a href="<?= BASE_URL ?>Usuario/Catalogo.php?ciudad=<?= htmlspecialchars($ciudad['id']) ?>" class="city-card city-<?= htmlspecialchars($claseCss) ?>">
                                <h3><?= htmlspecialchars($ciudad['nombre']) ?></h3>
                            </a>
                        <?php endforeach; ?>

                        <!-- Bloque duplicado para el carrusel infinito -->
                        <?php foreach ($ciudades as $ciudad): 
                            $claseCss = obtenerClaseCssCiudad($ciudad['nombre']);
                        ?>
                            <a href="<?= BASE_URL ?>Usuario/Catalogo.php?ciudad=<?= htmlspecialchars($ciudad['id']) ?>" class="city-card city-<?= htmlspecialchars($claseCss) ?>">
                                <h3><?= htmlspecialchars($ciudad['nombre']) ?></h3>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- QUIÉNES SOMOS -->
        <section class="about-section" aria-labelledby="about-title">

            <div class="about-image">
                <img 
                    src="<?= BASE_URL ?>Imagenes/Quienes_somos.webp"
                    alt="Equipo de Primavera inmobiliaria atendiendo clientes"
                    class="about-img"
                >
            </div>

            <div class="about-content">
                <h2 id="about-title">¿Quiénes somos?</h2>

                <p>
                    Somos una empresa inmobiliaria dedicada a brindar asesoría y acompañamiento integral en la compra y venta de propiedades. Nuestro propósito es hacer de cada proceso una experiencia clara, segura y eficiente, ofreciendo soluciones que se adapten a las necesidades de cada cliente.
                </p>

                <p>
                    Contamos con un equipo de asesores profesionales comprometidos con brindar un servicio cercano, confiable y profesional, acompañando a cada cliente en cada etapa para toma decisiones seguras.
                </p>
            </div>

        </section>

        <!-- MISIÓN, VISIÓN Y VALORES -->
        <section class="company-values" aria-labelledby="values-title">

            <div class="company-card">
                <h2 id="values-title">Lo que nos define</h2>

                <div class="values-grid">

                    <article class="value-card">
                        <div class="value-icon">
                            <i class="fa-solid fa-bullseye"></i>
                        </div>

                        <h3>Misión</h3>

                        <p>
                            Acompañar a cada cliente en su proceso inmobiliario de forma confiable, responsable y eficaz, brindando opciones alineadas a sus necesidades y guiándolos para adquirir su nueva propiedad.
                        </p>
                    </article>

                    <article class="value-card">
                        <div class="value-icon">
                            <i class="fa-solid fa-eye"></i>
                        </div>

                        <h3>Visión</h3>

                        <p>
                            Consolidarse como una inmobiliaria reconocida en Sonora por su cercanía con el cliente y su capacidad de ofrecer soluciones confiables, con proyección de crecimiento hacia nuevas zonas.
                        </p>
                    </article>

                    <article class="value-card">
                        <div class="value-icon">
                            <i class="fa-solid fa-handshake"></i>
                        </div>

                        <h3>Valores</h3>

                        <p>
                            Nos guiamos por la confianza, el compromiso y la responsabilidad para brindar un buen servicio, acompañando a cada cliente a tomar decisiones seguras al encontrar su nueva propiedad.
                        </p>
                    </article>

                </div>
            </div>

        </section>

    </main>

<?php require_once ROOT_PATH . '/Includes/footer.php'; ?>