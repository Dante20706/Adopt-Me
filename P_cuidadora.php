<?php
session_start();
require 'conexion.php';

// Verificamos si se pasó un ID
if (!isset($_GET['id'])) {
    echo "No se especificó un cuidador.";
    exit;
}

$cuidador_id = intval($_GET['id']);

// Consulta de datos del cuidador
$query = "SELECT nombre, apellido, tipo, provincia, departamento, direccion, telefono, descripcion 
          FROM usuarios 
          WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $cuidador_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "No se encontró el cuidador.";
    exit;
}

$cuidador = $result->fetch_assoc();

// Consulta de la imagen de perfil
$imagen_query = "SELECT archivo FROM imagen_perfil WHERE usuario_id = ?";
$img_stmt = $conn->prepare($imagen_query);
$img_stmt->bind_param("i", $cuidador_id);
$img_stmt->execute();
$img_result = $img_stmt->get_result();
$imagen = ($img_result->num_rows > 0) ? $img_result->fetch_assoc()['archivo'] : 'default_profile.png';

$img_stmt->close();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Cuidador - Adopt Me</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="footer.css">
    <link rel="stylesheet" href="P_cuidadora.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

    <main class="main-content">
        <div class="container">
            <section class="profile-section">
                <div class="profile-header">
                    <!-- Imagen de perfil -->
                    <div class="profile-image">
    <img src="uploads/<?php echo htmlspecialchars($imagen); ?>" alt="Imagen de perfil del cuidador">
</div>


                    <div class="profile-info">
                        <div class="profile-title">
                            <h1><?php echo htmlspecialchars($cuidador['nombre'] . ' ' . $cuidador['apellido']); ?></h1>
                        </div>
                        <span class="badge"><?php echo ucfirst(htmlspecialchars($cuidador['tipo'])); ?></span>
                        <div>
                            <p class="fas fa-map-marker-alt">
                                <?php echo htmlspecialchars($cuidador['provincia'] . ', ' . $cuidador['departamento']); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="info-grid">
                    <div class="info-box">
                        <h2>¿Quién soy?</h2>
                        <div class="content-placeholder">
                            <p style="padding: 15px; color:#5c3d2e;">
                                <?php 
                                if (!empty($cuidador['descripcion'])) {
                                    echo nl2br(htmlspecialchars($cuidador['descripcion']));
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
                            <li>📍 Dirección: <?php echo htmlspecialchars($cuidador['direccion']); ?></li>
                            <li>📞 Teléfono: <?php echo htmlspecialchars($cuidador['telefono']); ?></li>
                        </ul>
                    </div>
                </div>
            </section>
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
</body>
</html>
