<?php
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

<link rel="icon" href="favicon.ico">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<link rel="stylesheet" href="CSS/header.css">
<?php foreach ($cssPaginas as $css): ?>

    <link rel="stylesheet" href="<?= htmlspecialchars($css) ?>">

<?php endforeach; ?>
<link rel="stylesheet" href="CSS/Footer.css">

<meta property="og:title" content="<?= htmlspecialchars($titulo) ?>">
<meta property="og:description" content="<?= htmlspecialchars($descripcion) ?>">
<meta property="og:image" content="https://www.inmobiliariaprimavera.com/img/preview-inmobiliaria.jpg">
<meta property="og:url" content="https://www.inmobiliariaprimavera.com/">
<meta property="og:type" content="website">
<meta property="og:locale" content="es_MX">

</head>

<body>

<header class="site-header">

    <a href="index.html" class="navbar-logo movil">
        <img class="logo" src="Imagenes/Logosolo.png" alt="Logo de Primavera inmobiliaria">
    </a>
    
    <button class="menu-toggle" id="menu-toggle">
        <i class="fa-solid fa-bars"></i>
    </button>

    <nav class="navbar">
        <div class="navbar-left">
            <a href="index.php">Inicio</a>
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
        <a href="index.php" class="navbar-logo">
            <img class="logo" src="Imagenes/Logosolo.png" alt="Logo de Primavera inmobiliaria">
        </a>
        <div class="navbar-right">
            <a href="Contacto.php">Contacto</a>
        </div>
    </nav>
</header>
