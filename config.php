<?php
// 1. RUTA PARA EL NAVEGADOR (Para HTML: href, src, action)
// Si en tu barra de direcciones entras directo con 'http://localhost/index.php', déjalo como ''.
// Si tu proyecto está en una carpeta como 'htdocs/inmobiliaria', pon '/inmobiliaria'.
define('BASE_URL', '/'); 

// 2. RUTA PARA EL SERVIDOR (Para PHP: require, require_once, include)
// __DIR__ detecta automáticamente que estás en 'C:\xampp\htdocs'
define('ROOT_PATH', __DIR__ . '/');
?>