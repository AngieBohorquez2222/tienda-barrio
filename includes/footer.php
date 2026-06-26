<?php
// Cierre del contenido principal (main)
?>
</main>

<!-- Etiqueta semántica que define el pie de página de la web con espaciado vertical, texto centrado, color atenuado, fuente pequeña y borde superior -->
<footer class="py-4 text-center text-muted small border-top">
    <!-- Contenedor centrado de Bootstrap que limita el ancho máximo del texto para mantener la simetría visual -->
    <div class="container">
        <!-- Imprime el nombre comercial e inyecta dinámicamente el año actual en cuatro dígitos usando la función date('Y') de PHP -->
        Mini Tienda de Barrio - <?= date('Y') ?> - Hecho para aprender CRUD, sesiones y stock.
    <!-- Cierra la caja contenedora del pie de página -->
    </div>
<!-- Cierra la sección estructural del pie de página -->
</footer>

<!-- Carga el archivo JavaScript de Bootstrap (incluye Popper.js) para habilitar modales, menús desplegables y alertas interactivas -->
<script src="/tienda-barrio/js/bootstrap.bundle.min.js"></script>
<!-- Cierra el cuerpo del documento que contiene todos los elementos visibles de la página web -->
</body>

<!-- Cierra de forma definitiva la etiqueta raíz del árbol del documento estructurado en HTML -->
</html>