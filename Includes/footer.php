<?php
if (!defined('BASE_URL')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
}
?>

<footer class="site-footer">
        <div class="footer-container">

            <div class="footer-logo">
                <a href="<?= BASE_URL ?>index.php" aria-label="Ir al inicio">
                    <img src="<?= BASE_URL ?>Imagenes/Logosolo.png" alt="Logo de Primavera inmobiliaria">
                </a>
            </div>

            <nav class="footer-info" aria-label="Enlaces de información">
                <h2 class="footer-title">Información</h2>

                <ul class="footer-links">
                    <li><a href="<?= BASE_URL ?>Usuario/Politicas-privacidad.php" class="footer-link">Aviso de privacidad</a></li>
                    <li><a href="<?= BASE_URL ?>Usuario/Terminos-condiciones.php" class="footer-link">Términos y condiciones</a></li>
                    <li><a href="<?= BASE_URL ?>Usuario/Catalogo.php" class="footer-link">Todas las propiedades</a></li>
                    <li><a href="<?= BASE_URL ?>Usuario/Contacto.php" class="footer-link">Contacto</a></li>
                </ul>
            </nav>

            <address class="footer-contacto">
                <h2 class="footer-title">Contacto</h2>
                <p class="footer-text">Ejército Nacional 1101 entre 5 de Febrero y Jalisco. Fracc. Primavera</p>
                <p class="footer-text">
                    <a class="footer-text" href="tel:+526441435244">(644) 143 5244</a>
                </p>
                <p class="footer-text">
                    <a class="footer-text" href="mailto:sucorreo@gmail.com">sucorreo@gmail.com</a>
                </p>
            </address>

            <div class="footer-redes">
                <h2 class="footer-title">Redes sociales</h2>
                <div class="social-list">
                    <a href="https://www.facebook.com/share/14eyn1t5H3f/?mibextid=wwXIfr" target="_blank" rel="noopener noreferrer" aria-label="Facebook de Primavera inmobiliaria" class="social-link">
                        <span class="social-icon facebook"><i class="fa-brands fa-facebook-f"></i></span>
                        <span class="social-user">Primavera Inmobiliaria</span>
                    </a>
                    <a href="https://www.instagram.com/primavera.inmobiliariasc?igsh=dGZoajhrYjJpYXR6" target="_blank" rel="noopener noreferrer" aria-label="Instagram de Primavera inmobiliaria" class="social-link">
                        <span class="social-icon instagram"><i class="fa-brands fa-instagram"></i></span>
                        <span class="social-user">@primavera.inmobiliariasc</span>
                    </a>
                </div>
            </div>

        </div>

        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> Primavera inmobiliaria. Todos los derechos reservados.</p>
            <p>
                Desarrollado por 
                <a href="<?= BASE_URL ?>contacto-desarrolladores.php">ULSA North West</a>
            </p>
        </div>
</footer>

</body>
</html>