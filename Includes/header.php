<?php
if (!defined('BASE_URL')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
}

$titulo = $titulo ?? "Primavera inmobiliaria";
$descripcion = $descripcion ?? "";
$cssPaginas = $cssPaginas ?? [];
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo) ?></title>
    <meta name="description" content="<?= htmlspecialchars($descripcion) ?>">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#ffffff">
    <link rel="canonical" href="https://www.inmobiliariaprimavera.com/">
    
    <link rel="icon" href="<?= BASE_URL ?>favicon.ico">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>CSS/header.css?v=<?= filemtime(ROOT_PATH . 'CSS/header.css') ?>">
    
    <?php foreach ($cssPaginas as $css):

            $rutaRelativa = str_replace(BASE_URL, '', $css);
            $rutaFisica = ROOT_PATH . str_replace('/', DIRECTORY_SEPARATOR, $rutaRelativa);

        ?>
            <link
                rel="stylesheet"
                href="<?= htmlspecialchars($css) ?>?v=<?= file_exists($rutaFisica) ? filemtime($rutaFisica) : time() ?>">
        <?php endforeach; ?>
    
    <link rel="stylesheet" href="<?= BASE_URL ?>CSS/Footer.css?v=<?= filemtime(ROOT_PATH . 'CSS/Footer.css') ?>">

    <meta property="og:title" content="<?= htmlspecialchars($titulo) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($descripcion) ?>">
    <meta property="og:image" content="https://www.inmobiliariaprimavera.com/img/preview-inmobiliaria.jpg">
    <meta property="og:url" content="https://www.inmobiliariaprimavera.com/">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="es_MX">
</head>
<body>

<header class="site-header">
    <a href="<?= BASE_URL ?>index" class="navbar-logo movil">
        <img class="logo" src="<?= BASE_URL ?>Imagenes/Logosolo.png" alt="Logo de Primavera inmobiliaria">
    </a>
    
    <button class="menu-toggle" id="menu-toggle">
        <i class="fa-solid fa-bars"></i>
    </button>

    <nav class="navbar">
        <div class="navbar-left">
            <a href="<?= BASE_URL ?>index">Inicio</a>
            
            <div>
                <a href="<?= BASE_URL ?>Usuario/Catalogo?tipo_operacion=venta">Venta</a>
                <ul class="submenu">
                    <li><a href="<?= BASE_URL ?>Usuario/Catalogo?tipo_operacion=venta&categoria=2">Casas en venta</a></li>
                    <li><a href="<?= BASE_URL ?>Usuario/Catalogo?tipo_operacion=venta&categoria=3">Departamentos en venta</a></li>
                    <li><a href="<?= BASE_URL ?>Usuario/Catalogo?tipo_operacion=venta&categoria=4">Locales comerciales en venta</a></li>
                    <li><a href="<?= BASE_URL ?>Usuario/Catalogo?tipo_operacion=venta&categoria=5">Terrenos en venta</a></li>
                </ul>
            </div>

            <div>
                <a href="<?= BASE_URL ?>Usuario/Catalogo?tipo_operacion=renta">Renta</a>
                <ul class="submenu">
                    <li><a href="<?= BASE_URL ?>Usuario/Catalogo?tipo_operacion=renta&categoria=1">Casas en renta</a></li>
                    <li><a href="<?= BASE_URL ?>Usuario/Catalogo?tipo_operacion=renta&categoria=2">Departamentos en renta</a></li>
                    <li><a href="<?= BASE_URL ?>Usuario/Catalogo?tipo_operacion=renta&categoria=4">Locales comerciales en renta</a></li>
                    <li><a href="<?= BASE_URL ?>Usuario/Catalogo?tipo_operacion=renta&categoria=5">Terrenos en renta</a></li>
                </ul>
            </div>
            

            <div>
                <a href="<?= BASE_URL ?>Usuario/Catalogo?tipo_operacion=traspaso">Traspaso</a>
                <ul class="submenu">
                    <li><a href="<?= BASE_URL ?>Usuario/Catalogo?tipo_operacion=traspaso&categoria=2">Casas en traspaso</a></li>
                    <li><a href="<?= BASE_URL ?>Usuario/Catalogo?tipo_operacion=traspaso&categoria=3">Departamentos en traspaso</a></li>
                    <li><a href="<?= BASE_URL ?>Usuario/Catalogo?tipo_operacion=traspaso&categoria=4">Locales comerciales en traspaso</a></li>
                </ul>
            </div>
        </div>
        
        <a href="<?= BASE_URL ?>index" class="navbar-logo desktop">
            <img class="logo" src="<?= BASE_URL ?>Imagenes/Logosolo.png" alt="Logo de Primavera inmobiliaria">
        </a>
        
        <div class="navbar-right">
            <a href="<?= BASE_URL ?>Usuario/Contacto">Contacto</a>
        </div>
    </nav>
</header>
<script src="<?= BASE_URL ?>JS/header.js"></script>
</body>
</html>
