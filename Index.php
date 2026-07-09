<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

$titulo = "Primavera inmobiliaria | Casas y propiedades en venta y renta en Sonora";
$descripcion = "Encuentra casas, terrenos, departamentos y locales comerciales en venta y renta en Sonora. Propiedades disponibles en Ciudad Obregón, San Carlos y Guaymas.";
$cssPaginas = [BASE_URL . "CSS/index.css"];

$conteos = [];

require_once 'Config/database.php';
$pdo = db();

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
    $conteos[$fila['tipo_operacion']][$fila['categoria_id']] = $fila['total'];
}

require_once ROOT_PATH . '/Includes/header.php';
?>

    <main class="main-content">

        <!-- SECCIÓN HERO -->
        <section class="hero" aria-labelledby="hero-title">
        
            <div class="hero-content">
            
                <div class="hero-center">
                    <p class="hero-brand">Primavera inmobiliaria</p>
                
                    <h1 id="hero-title">
                        Casas, terrenos y propiedades en venta y renta en Sonora
                    </h1>
                
                    <form class="search-form" action="<?= BASE_URL ?>Usuario/Catalogo.php" method="GET">
                        <div class="select-wrapper">
                            <select name="ciudad" id="ciudad" aria-label="Selecciona una ubicación">
                                <option value="">Seleccione ubicación...</option>
                                <option value="ciudad_obregon">Ciudad Obregón</option>
                                <option value="navojoa">Navojoa</option>
                                <option value="san_carlos">San Carlos</option>
                                <option value="guaymas">Guaymas</option>
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

                <a href="<?= BASE_URL ?>Usuario/Catalogo.php?tipo_operacion=venta&categoria=2" class="category-card card-casa movcard">
                    <h3>Casas en venta</h3>
                    <p>
                        Disponibles:
                        <?= $conteos['venta'][2] ?? 0 ?>
                    </p>
                </a>

                <a href="<?= BASE_URL ?>Usuario/Catalogo.php?tipo_operacion=venta&categoria=4" class="category-card card-terreno movcard">
                    <h3>Terrenos en venta</h3>
                    <p>
                        Disponibles:
                        <?= $conteos['venta'][4] ?? 0 ?>
                    </p>
                </a>

                <a href="<?= BASE_URL ?>Usuario/Catalogo.php?tipo_operacion=renta" class="category-card card-renta movcard">
                    <h3>Propiedades en renta</h3>
                    <p>
                        Disponibles:
                        <?= array_sum($conteos['renta'] ?? []) ?>
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

                    <a href="<?= BASE_URL ?>Usuario/Catalogo.php?ciudad=ciudad_obregon" class="city-card city-obregon">
                        <h3>Obregón</h3>
                    </a>

                    <a href="<?= BASE_URL ?>Usuario/Catalogo.php?ciudad=san_carlos" class="city-card city-san-carlos">
                        <h3>San Carlos</h3>
                    </a>

                    <a href="<?= BASE_URL ?>Usuario/Catalogo.php?ciudad=guaymas" class="city-card city-guaymas">
                        <h3>Guaymas</h3>
                    </a>

                    <a href="<?= BASE_URL ?>Usuario/Catalogo.php?ciudad=navojoa" class="city-card city-navojoa">
                        <h3>Navojoa</h3>
                    </a>

                    <!-- Se repiten para que el carrusel sea infinito -->
                    <a href="<?= BASE_URL ?>Usuario/Catalogo.php?ciudad=ciudad_obregon" class="city-card city-obregon">
                        <h3>Obregón</h3>
                    </a>

                    <a href="<?= BASE_URL ?>Usuario/Catalogo.php?ciudad=san_carlos" class="city-card city-san-carlos">
                        <h3>San Carlos</h3>
                    </a>

                    <a href="<?= BASE_URL ?>Usuario/Catalogo.php?ciudad=guaymas" class="city-card city-guaymas">
                        <h3>Guaymas</h3>
                    </a>

                    <a href="<?= BASE_URL ?>Usuario/Catalogo.php?ciudad=navojoa" class="city-card city-navojoa">
                        <h3>Navojoa</h3>
                    </a>

                </div>
            </div>
        </section>
        <!-- QUIÉNES SOMOS -->
        <section class="about-section" aria-labelledby="about-title">

            <div class="about-image">
                <img 
                    src="<?= BASE_URL ?>Imagenes/Quienes_somos.jpeg"
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

<?php require 'includes/footer.php'; ?>
