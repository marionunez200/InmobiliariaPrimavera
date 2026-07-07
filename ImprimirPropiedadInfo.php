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

function operacionDetalleTexto(?string $operacion): string
{
    return match ($operacion) {
        'venta'    => 'Venta',
        'renta'    => 'Renta',
        'traspaso' => 'Traspaso',
        default    => ''
    };
}

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    exit('Propiedad no encontrada');
}

$stmt = $pdo->prepare("
    SELECT
        p.*,
        c.nombre AS categoria_nombre,
        a.nombre AS agente_nombre,
        a.telefono AS agente_telefono,
        a.email AS agente_email,
        a.foto_url AS agente_foto
    FROM propiedades p
    LEFT JOIN categorias_propiedad c
        ON p.categoria_id = c.id
    LEFT JOIN agentes a
        ON p.agente_id = a.id
    WHERE p.id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$propiedad = $stmt->fetch();

if (!$propiedad) {
    exit('Propiedad no encontrada');
}

$stmtImagenes = $pdo->prepare("
    SELECT
        imagen_url,
        texto_alternativo
    FROM imagenes_propiedades
    WHERE propiedad_id = ?
    ORDER BY es_principal DESC,
        orden ASC,
        id ASC
");
$stmtImagenes->execute([$id]);

$imagenes = $stmtImagenes->fetchAll();

if (empty($imagenes)) {

    $imagenes[] = [

        'imagen_url' => 'Imagenes/casa2.jpg',

        'texto_alternativo' => $propiedad['titulo']

    ];

}

$imagenPrincipal = $imagenes[0]['imagen_url'];

$precioTexto = '$' .
number_format((float)$propiedad['precio'],2)
. ' '
. $propiedad['moneda'];

if($propiedad['tipo_operacion']=='renta'){
    $precioTexto .= '/mes';
}

$ciudadTexto = ciudadDetalleTexto($propiedad['ciudad']);

$tipoTexto = $propiedad['categoria_nombre'] ?? 'Propiedad';

$operacionTexto = operacionDetalleTexto($propiedad['tipo_operacion']);

$fotoAgente = !empty($propiedad['agente_foto'])
    ? $propiedad['agente_foto']
    : 'Imagenes/agente1.webp';

?>
<!DOCTYPE html>

<html lang="es">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>

<?= e($tipoTexto) ?>

<?= strtolower($operacionTexto) ?>

|

Primavera Inmobiliaria

</title>

<link rel="stylesheet"
href="./CSS/imprimir.css">

<link
rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body>

<div class="hoja">

<header class="encabezado">

    <div class="logo">

        <img
        src="Imagenes/Logosolo.png"
        alt="Primavera">

        <div>

            <h1>

                Primavera
                Inmobiliaria

            </h1>

            <p>

                Ficha de propiedad

            </p>

        </div>

    </div>

    <div class="tipo_operacion">

        <?= e($operacionTexto) ?>

    </div>

</header>

<section class="hero">

    <img

    class="imagen_principal"

    src="<?= e($imagenPrincipal) ?>"

    alt="<?= e($propiedad['titulo']) ?>">

</section>

<section class="galeria">

<?php foreach($imagenes as $imagen): ?>

    <img

    src="<?= e($imagen['imagen_url']) ?>"

    alt="<?= e($imagen['texto_alternativo']) ?>">

<?php endforeach; ?>

</section>

<section class="informacion">

    <div class="titulo">

        <h2>

            <?= e($tipoTexto) ?>

            en

            <?= strtolower(e($operacionTexto)) ?>

        </h2>

        <h3>

            <?= e($ciudadTexto) ?>

        </h3>

        <p>

            <?= e($propiedad['direccion_completa']) ?>

        </p>

    </div>

    <div class="precio">

        <?= e($precioTexto) ?>

    </div>

</section>

<section class="caracteristicas">

    <div class="card">

        <i class="fa-solid fa-house"></i>

        <span>Construcción</span>

        <strong>

            <?= e((string)$propiedad['construccion_m2']) ?> m²

        </strong>

    </div>

    <div class="card">

        <i class="fa-solid fa-ruler-combined"></i>

        <span>Terreno</span>

        <strong>

            <?= e((string)$propiedad['terreno_m2']) ?> m²

        </strong>

    </div>

    <div class="card">

        <i class="fa-solid fa-bed"></i>

        <span>Recámaras</span>

        <strong>

            <?= e((string)$propiedad['recamaras']) ?>

        </strong>

    </div>

    <div class="card">

        <i class="fa-solid fa-toilet"></i>

        <span>Baños</span>

        <strong>

            <?= e((string)$propiedad['banos']) ?>

        </strong>

    </div>

    <div class="card">

        <i class="fa-solid fa-car"></i>

        <span>Cochera</span>

        <strong>

            <?= e((string)$propiedad['estacionamientos']) ?>

        </strong>

    </div>

    <div class="card">

        <i class="fa-solid fa-tag"></i>

        <span>Tipo</span>

        <strong>

            <?= e($tipoTexto) ?>

        </strong>

    </div>

</section>

<?php if(!empty($propiedad['descripcion'])): ?>

<section class="descripcion">

    <h2>

        Descripción

    </h2>

    <p>

        <?= nl2br(e($propiedad['descripcion'])) ?>

    </p>

</section>

<?php endif; ?>

<section class="agente">

    <img

        src="<?= e($fotoAgente) ?>"

        alt="<?= e($propiedad['agente_nombre']) ?>">

    <div>

        <h2>

            <?= e($propiedad['agente_nombre']) ?>

        </h2>

        <p>

            Asesor Inmobiliario

        </p>

        <p>

            <i class="fa-solid fa-phone"></i>

            <?= e($propiedad['agente_telefono']) ?>

        </p>

        <p>

            <i class="fa-solid fa-envelope"></i>

            <?= e($propiedad['agente_email']) ?>

        </p>

    </div>

</section>

<footer class="pie">

    <div>

        <img

        src="Imagenes/Logosolo.png"

        alt="Primavera">

    </div>

    <div>

        <strong>

            Primavera Inmobiliaria

        </strong>

        <p>

            Ciudad Obregón, Sonora

        </p>

        <p>

            Encuentra tu próximo hogar con nosotros.

        </p>

    </div>

</footer>

</div>

<script>

window.onload = function(){

    window.print();

};

window.onafterprint = function(){

    window.close();

};

</script>

</body>

</html>