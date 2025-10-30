<?php
session_start();
require 'conexion.php';

// Verificamos si se pasó un ID
if (!isset($_GET['id'])) {
    echo "No se especificó una protectora.";
    exit;
}

$protectora_id = intval($_GET['id']);

// Consulta de datos de la protectora (solo tipo = 'protectora')
$query = "SELECT nombre, apellido, tipo, provincia, departamento, direccion, telefono, descripcion, pagina_web, contacto_responsable
          FROM usuarios
          WHERE id = ? AND tipo = 'protectora'";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Error en la preparación de la consulta: " . $conn->error);
}
$stmt->bind_param("i", $protectora_id);
$stmt->execute();
$result = $stmt->get_result();

// Validamos que exista y que sea una protectora
if ($result->num_rows === 0) {
    echo "No se encontró la protectora.";
    exit;
}

$protectora = $result->fetch_assoc();
$stmt->close();

// Obtener publicaciones de esa protectora (Asegurarse de pedir todas las columnas que vamos a mostrar)
$sql_publicaciones = "SELECT id, nombre_mascota, especie, edad, ubicacion, descripcion, imagen 
                      FROM publicaciones 
                      WHERE usuario_id = ? 
                      ORDER BY fecha_publicacion DESC";
$stmt_pub = $conn->prepare($sql_publicaciones);
if (!$stmt_pub) {
    die("Error en la preparación de publicaciones: " . $conn->error);
}
$stmt_pub->bind_param("i", $protectora_id);
$stmt_pub->execute();
$result_pub = $stmt_pub->get_result();

// Consulta de la imagen de perfil (ajusta el nombre de tabla si usas imagen_perfil o imagen_principal)
$imagen_query = "SELECT archivo FROM imagen_perfil WHERE usuario_id = ? LIMIT 1";
$img_stmt = $conn->prepare($imagen_query);
if ($img_stmt) {
    $img_stmt->bind_param("i", $protectora_id);
    $img_stmt->execute();
    $img_result = $img_stmt->get_result();
    $imagen = ($img_result && $img_result->num_rows > 0) ? $img_result->fetch_assoc()['archivo'] : 'default_profile.png';
    $img_stmt->close();
} else {
    $imagen = 'default_profile.png';
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adopt Me - <?php echo htmlspecialchars($protectora['nombre']); ?></title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="footer.css">
    <link rel="stylesheet" href="P_protectora.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <!-- Navbar -->
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
                        <li><a href="index.php" class="active">Inicio</a></li>
                        <li><a href="protectoras.php">Protectoras</a></li>
                        <li><a href="map.php">Mapa</a></li>
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

    <!-- Contenido principal -->
    <main class="main-content">
        <div class="container">
            <section class="profile-section">
                <div class="profile-header">
                    <div class="profile-image">
                        <img src="uploads/<?php echo htmlspecialchars($imagen); ?>" alt="Imagen de perfil de la protectora">
                    </div>
                    <div class="profile-info">
                        <div class="profile-title">
                            <h1><?php echo htmlspecialchars($protectora['nombre']); ?></h1>
                        </div>
                        <span class="badge">Asociación Protectora</span>
                        <p class="location">📍 <?php echo htmlspecialchars($protectora['provincia']); ?>, <?php echo htmlspecialchars($protectora['departamento']); ?></p>
                        <p class="person-charge">Persona a cargo: <?php echo htmlspecialchars($protectora['nombre']); ?> <?php echo htmlspecialchars($protectora['apellido']); ?></p>
                    </div>
                </div>

                <div class="info-grid">
                    <div class="info-box">
                        <h2>¿Quiénes somos?</h2>
                        <div class="content-placeholder">
                            <p style="padding: 15px; color:#5c3d2e;">
                                <?php 
                                if (!empty($protectora['descripcion'])) {
                                    echo nl2br(htmlspecialchars($protectora['descripcion']));
                                } else {
                                    echo "Este cuidador aún no agregó una descripción personal.";
                                }
                                ?>
                            </p>
                        </div>
                    </div>
                    <div class="info-box">
                        <h2>Más información:</h2>
                        <ul class="contact-list">
                            <li>📍 Dirección:<?php echo htmlspecialchars($protectora['direccion']); ?></li>
                            <li>📞 Telefono:<?php echo htmlspecialchars($protectora['telefono']); ?></li>
                            <li> 
                                <?php if (!empty($protectora['pagina_web'])): ?>
                            <p class="user-info">
                            <i class="fas fa-globe"></i>
                            <a href="<?php echo htmlspecialchars($protectora['pagina_web']); ?>" target="_blank">
                            Visitar sitio</a></p>
                            <?php else: ?>
                            <p class="user-info">
                            <i class="fas fa-globe"></i>
                            No disponible
                            </p>
                            <?php endif; ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- Publicaciones de la protectora -->
<section class="available-pets-section">
    <div class="section-header">
        <h2 class="section-title">Nuestros casos en adopción</h2>
    </div>

    <div class="pet-cards-grid">
        <?php if ($result_pub->num_rows > 0): ?>
            <?php while ($pub = $result_pub->fetch_assoc()): ?>
                <div class="pet-card">
                    <img src="uploads/<?php echo htmlspecialchars($pub['imagen'] ?? 'default.jpg'); ?>" 
                         alt="Foto de <?php echo htmlspecialchars($pub['nombre_mascota']); ?>" 
                         class="pet-card-image">

                    <div class="pet-card-info">
                        <span class="pet-card-species-tag"><?php echo htmlspecialchars($pub['especie']); ?></span>
                        <h3 class="pet-card-name"><?php echo htmlspecialchars($pub['nombre_mascota']); ?></h3>
                        <p class="pet-card-details">
                            <i class="fas fa-birthday-cake"></i> <?php echo htmlspecialchars($pub['edad']); ?><br>
                            <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($pub['ubicacion'] ?? 'Ubicación no especificada'); ?>
                        </p>
                        <a href="pet-detail.php?id=<?php echo $pub['id']; ?>" class="btn btn-sm btn-outline-primary">Ver detalles</a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No hay casos en adopción actualmente.</p>
        <?php endif; ?>
    </div>
</section>

        </div>
    </main>

    <!-- Footer -->
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
</body>
</html>
