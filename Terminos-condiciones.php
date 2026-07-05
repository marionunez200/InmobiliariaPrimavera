<?php 
$titulo = "Términos y condiciones - Primavera inmobiliaria";
$descripcion = "Consulta los términos y condiciones de uso del sitio web de Primavera inmobiliaria.";
$cssPaginas = ["CSS/terminos.css"];

require 'Includes/header.php'; ?>
<main class="legal-page">

    <article class="legal-content">

        <header class="legal-header">
            <h1 class="terms-h1">Términos y condiciones</h1>
            <p class="terms-p">Última actualización: 2027</p>
        </header>

        <section class="terms-section">
            <h2 class="terms-h2">Uso del sitio web</h2>
            <p class="terms-p">
                Este sitio web tiene como finalidad mostrar información sobre propiedades
                disponibles en venta y renta. Al navegar en este sitio, el usuario acepta
                utilizar la información de manera responsable y conforme a estos términos
                y condiciones.
            </p>
        </section>

        <section class="terms-section">
            <h2 class="terms-h2">Información de las propiedades</h2>
            <p class="terms-p">
                Los precios, ubicaciones, características, medidas, imágenes y disponibilidad
                de las propiedades publicadas en este sitio pueden cambiar sin previo aviso.
                La información mostrada tiene fines informativos y deberá confirmarse
                directamente con Primavera inmobiliaria.
            </p>
        </section>

        <section class="terms-section">
            <h2 class="terms-h2">Disponibilidad y precios</h2>
            <p class="terms-p">
                La disponibilidad de las propiedades puede variar en cualquier momento.
                Los precios publicados pueden estar sujetos a cambios, negociaciones,
                ajustes del propietario o condiciones específicas de venta o renta.
            </p>
        </section>

        <section class="terms-section">
            <h2 class="terms-h2">Responsabilidad del usuario</h2>
            <p class="terms-p">
                El usuario se compromete a proporcionar información verdadera y actualizada
                al utilizar formularios de contacto, solicitudes de información o cualquier
                otro medio disponible dentro del sitio web.
            </p>
        </section>

        <section class="terms-section">
            <h2 class="terms-h2">Uso de imágenes y contenido</h2>
            <p class="terms-p">
                Las imágenes, textos, logotipos y demás contenido publicado en este sitio
                pertenecen a Primavera inmobiliaria o se utilizan con autorización. Queda
                prohibida su reproducción, distribución o uso no autorizado.
            </p>
        </section>

        <section class="terms-section">
            <h2 class="terms-h2">Enlaces externos</h2>
            <p class="terms-p">
                Este sitio puede incluir enlaces hacia redes sociales u otros sitios externos.
                Primavera inmobiliaria no se hace responsable por el contenido, políticas o
                prácticas de privacidad de sitios ajenos.
            </p>
        </section>

        <section class="terms-section">
            <h2 class="terms-h2">Modificaciones</h2>
            <p class="terms-p">
                Primavera inmobiliaria se reserva el derecho de modificar estos términos y
                condiciones en cualquier momento. Los cambios serán publicados en esta misma
                página.
            </p>
        </section>

        <section class="terms-section">
            <h2 class="terms-h2">Contacto</h2>
            <p class="terms-p">
                Para cualquier duda relacionada con estos términos y condiciones, puedes
                comunicarte con nosotros mediante la página de contacto.
            </p>
        </section>

    </article>

</main>

<?php require 'Includes/footer.php'; ?>

    <script>
        const menu = document.getElementById("navbar");
        const boton = document.getElementById("menu-toggle");

        if (menu && boton) {
            boton.addEventListener("click", () => {
                menu.classList.toggle("active");
            });
        }
    </script>



