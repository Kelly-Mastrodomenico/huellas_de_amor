</main>

<footer>
    <div class="contenedor-footer">

        <!-- Columna 1: Logo y descripcion -->
        <div class="bloque-footer">
<a href="<?php echo $rutaBase; ?>index.php" class="logo-footer">
    <img src="<?php echo $rutaBase; ?>assets/img/Logo.png" alt="Huellas de Amor">
    <span>Huellas de Amor</span>
</a>
            <p>Dedicados a encontrar hogares amorosos para mascotas necesitadas. Tu ayuda hace la diferencia en miles de vidas.</p>


        <!-- Columna 2: Enlaces rapidos -->
        <div class="bloque-footer">
            <h3>Enlaces Rapidos</h3>
            <ul>
                <li><a href="<?php echo $rutaBase; ?>index.php">Inicio</a></li>
                <li><a href="<?php echo $rutaBase; ?>adoptar.php">Adoptar</a></li>
                <li><a href="<?php echo $rutaBase; ?>apadrinar.php">Apadrinar</a></li>
                <li><a href="<?php echo $rutaBase; ?>acogida.php">Acogida</a></li>
                <li><a href="<?php echo $rutaBase; ?>galeria.php">Galeria</a></li>
            </ul>
        </div>
        

        <!-- Columna 3: Informacion -->
        <div class="bloque-footer">
            <h3>Informacion</h3>
            <ul>
                <li><a href="<?php echo $rutaBase; ?>noticias.php">Noticias</a></li>
                <li><a href="<?php echo $rutaBase; ?>donaciones.php">Donaciones</a></li>
                <li><a href="<?php echo $rutaBase; ?>contacto.php">Contacto</a></li>
                <li><a href="#">Politica de privacidad</a></li>
            </ul>
        </div>

        <!-- Columna 4: Newsletter -->
        <div class="bloque-footer">
            <h3>Newsletter</h3>
            <p>Suscribete para recibir noticias de nuevas mascotas y eventos especiales.</p>
            <form class="form-newsletter" action="#" method="post">
                <input type="email" name="email_newsletter" placeholder="Tu email">
                <button type="submit">
                    <i class="fa-solid fa-paper-plane"></i>
                </button>
            </form>
        </div>
         <div class="redes-sociales">
        <a href="#"><i class="fa-brands fa-facebook-f"></i></a>
        <a href="#"><i class="fa-brands fa-instagram"></i></a>
        <a href="#"><i class="fa-brands fa-twitter"></i></a>
    </div>

    </div>

    <div class="footer-copyright">
        <p>&copy; <?php echo date('Y'); ?> Huellas de Amor &mdash; Proyecto Intermodular 2DAW &mdash; Kelly Rodriguez Mastrodomenico</p>
    </div>
</footer>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- jQuery UI -->
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<!-- FancyBox -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js"></script>
<!-- Nuestro JS -->
<script src="<?php echo $rutaBase; ?>js/main.js"></script>
<script src="<?php echo $rutaBase; ?>js/ajax.js"></script>

</body>
</html>