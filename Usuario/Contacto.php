<?php 
if (!defined('BASE_URL')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
}

$titulo = "Contacto - Primavera inmobiliaria";
$descripcion = "Encuentra la ubicación, horario de atención y formas de contacto de Primavera inmobiliaria.";
$cssPaginas = [BASE_URL . 'CSS/contacto.css'];

require ROOT_PATH . 'Includes/header.php';
?>
    <main class="contacto_conteiner">
        <!-- Card con la información de contacto y horario -->
        <section class="card_contacto">
            <article class="art_info">
                <h2>¿Dónde encontrarnos?</h2>
                <p>Ejército nacional 1101 entre 5 de febrero y Jalisco. Fracc. Primavera</p>
                <p class="social-link">
                    <i class="fa-solid fa-phone col"></i>
                    <a href="tel:+526441435244">(644) 143 5244</a>
                </p>
                <div class="social-list">
                    <a
                        href="https://wa.me/526441435244"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="social-link"
                    >
                        <span class="social-icon whatsapp">
                            <i class="fa-brands fa-whatsapp"></i>
                        </span>
                        <span class="social-user">(644) 143 5244</span>
                    </a>

            </article>

            <article class="art_horario">
                <h2>Horario de atención</h2>
                <ul>
                    <li>
                        <p><strong>Lunes - Viernes</strong></p>
                        <p>9:00 AM - 6:00 PM</p>
                    </li>
                    <li>
                        <p><strong>Sábado</strong></p>
                        <p>9:00 AM - 3:00 PM</p>
                    </li>
                    <li>
                        <p><strong>Domingo</strong></p>
                        <p>9:00 AM - 1:00 PM</p>
                    </li>
                </ul>
            </article>
        </section>

        <!-- Mapa -->
        <section class="ubicacion card-mapa">
            <h2>Ubicacion</h2>
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d7080.911508897659!2d-109.92697067063644!3d27.455067279184366!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x86c8163e0b97c5e3%3A0xcb761a2bd8b3205f!2sC.%20Ej%C3%A9rcito%20Nacional%201101%2C%2085000%20Cdad.%20Obreg%C3%B3n%2C%20Son.!5e0!3m2!1ses!2smx!4v1782251447719!5m2!1ses!2smx" width="615" height="575" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>        </section>
    </main>

<?php require ROOT_PATH . 'Includes/footer.php'; ?>

    <script>
        const menu = document.getElementById("navbar");
        const boton = document.getElementById("menu-toggle");

        if (menu && boton) {
            boton.addEventListener("click", () => {
                menu.classList.toggle("active");
            });
        }
    </script>



