<?php
session_start();
require 'conexion.php';
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// Cargar ubicaciones desde la BD
$sql = "SELECT id, nombre, tipo, direccion, telefono, horario, latitud, longitud, sitio_web FROM ubicaciones";
$result = $conn->query($sql);
$ubicaciones = [];
if ($result && $result->num_rows > 0) {
    $ubicaciones = $result->fetch_all(MYSQLI_ASSOC);
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mapa Interactivo - Adopt Me</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="footer.css">
    <link rel="stylesheet" href="map.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" 
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" 
          crossorigin=""/>
</head>
<body>
<header class="navbar">
    <div class="container">
        <div class="navbar-content">
            <a href="index.php" class="logo">
                <i class="fas fa-paw"></i>
                <span>Adopt Me</span>
            </a>
            <button class="menu-toggle" id="menuToggle" aria-label="Abrir menú">
                <i class="fas fa-bars"></i>
            </button>
            <nav class="nav-menu" id="navMenu">
                <ul class="nav-links">
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="protectoras.php">Protectoras</a></li>
                    <li><a href="map.php" class="active">Mapa</a></li>
                    <li><a href="blog.php">Blog</a></li>
                </ul>
                <div class="nav-buttons">
                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        <a href="profile.php">Hola, <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></a>
                        <a href="logout.php" class="btn btn-outline">Cerrar sesión</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline">Iniciar sesión</a>
                        <a href="register.php" class="btn btn-primary">Registrarse</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </div>
</header>

<main>
    <div class="container">
        <h1>Mapa Interactivo</h1>
        <p class="page-description">
            Encuentra veterinarias, protectoras y refugios de animales cercanos a tu ubicación.
        </p>

        <div class="filters-container">
            <h2>Filtros</h2>
            <div class="filters-buttons">
                <button class="filter-btn active" data-type="todos">Todos</button>
                <button class="filter-btn" data-type="veterinaria">Veterinarias</button>
                <button class="filter-btn" data-type="protectora">Protectoras</button>
                <button class="filter-btn" data-type="petshop">Tiendas</button>
                <button class="filter-btn" data-type="parque">Parques</button>
            </div>
        </div>

        <div class="map-container">
            <div id="mapContainer" style="height: 600px; width: 100%; border-radius: var(--border-radius);"></div>
        </div>

        <div id="locationsContainer" class="locations-grid"></div>
    </div>
</main>

<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-column">
                <div class="footer-logo">
                    <i class="fas fa-paw"></i>
                    <span>Adopt Me</span>
                </div>
                <p>Conectando mascotas con familias amorosas desde 2025.</p>
                <div class="social-links">
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" aria-label="Email"><i class="fas fa-envelope"></i></a>
                </div>
            </div>
            
            <div class="footer-column">
                <h3>Enlaces rápidos</h3>
                <ul>
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="protectoras.php">Protectoras</a></li>
                    <li><a href="map.php">Mapa interactivo</a></li>
                    <li><a href="blog.php">Blog</a></li>
                    <li><a href="mascotas.php">Adoptar</a></li>
                    <li><a href="publicaciones.php">Dar en adopción</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3>Recursos</h3>
                <ul>
                    <li><a href="#">Guía de adopción</a></li>
                    <li><a href="#">Cuidados básicos</a></li>
                    <li><a href="#">Veterinarias</a></li>
                    <li><a href="#">Protectoras</a></li>
                    <li><a href="#">Preguntas frecuentes</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3>Contacto</h3>
                <ul class="contact-info">
                    <li>info@adoptme.com</li>
                    <li>+54 387 123-4567</li>
                    <li>Av. Belgrano 123, Salta Capital</li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; 2025 Adopt Me. Todos los derechos reservados.</p>
        </div>
    </div>
</footer>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>

<!-- Pasamos las ubicaciones de PHP a JS -->
<script>
const ubicacionesBD = <?php echo json_encode($ubicaciones, JSON_UNESCAPED_UNICODE); ?>;
</script>

<script src="map.js"></script>

<script>
document.getElementById('menuToggle').addEventListener('click', function() {
    const navMenu = document.getElementById('navMenu');
    const icon = this.querySelector('i');
    navMenu.classList.toggle('active');
    icon.classList.toggle('fa-bars');
    icon.classList.toggle('fa-times');
});
</script>
</body>
</html>
