<?php
session_start();
require 'conexion.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adopt Me - Plataforma de Adopción de Mascotas</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="footer.css">
    <link rel="stylesheet" href="home.css">
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

    <main>
        <section class="hero">
            <div class="container">
                <h1>Adopt Me</h1>
                <p>Encuentra a tu compañero perfecto o ayuda a una mascota a encontrar un hogar amoroso</p>
                <div class="hero-buttons">
                    <a href="mascotas.php" class="btn btn-primary btn-lg">Adoptar una mascota</a>

                    <?php
                    // Recargar la conexión si se cerró en pet-detail
                    if (!isset($conn) || $conn->ping() === false) {
                        require 'conexion.php';
                    }
                    
                    if (isset($_SESSION['usuario_id'])) {
                        $usuario_id = $_SESSION['usuario_id'];
                        $sql = "SELECT tipo FROM usuarios WHERE id = $usuario_id";
                        $result_user_type = $conn->query($sql);
                        $row = $result_user_type->fetch_assoc();

                        if ($row['tipo'] === 'publicador') {
                            echo '<a href="publicaciones.php" class="btn btn-outline btn-lg">Poner en adopción</a>';
                        } else {
                            // Para adoptantes o si el tipo no está definido correctamente
                            echo '<a href="publicaciones.php" class="btn btn-outline btn-lg" title="Poner en adopción">Poner en adopción</a>';
                        }
                    } else {
                        echo '<a href="login.php" class="btn btn-outline btn-lg">Poner en adopción</a>';
                    }
                    ?>
                </div>
            </div>
        </section>

        <section class="pets-section">
            <div class="container">
                <div class="section-header">
                    <h2>Mascotas Disponibles</h2>
                    <a href="mascotas.php" class="view-all">Ver todas</a>
                </div>
                <div class="pets-grid">
                    <?php
                    // RE-Asegurar la conexión antes de la consulta principal
                    if (!isset($conn) || $conn->ping() === false) {
                        require 'conexion.php';
                    }

                    // Consulta CORREGIDA para obtener la primera imagen subida (menor ID)
                    $sql = "SELECT p.*, u.nombre AS nombre_usuario, 
                                   (SELECT archivo FROM imagen_principal WHERE publicacion_id = p.id ORDER BY id ASC LIMIT 1) AS imagen_principal
                            FROM publicaciones p
                            JOIN usuarios u ON p.usuario_id = u.id
                            ORDER BY p.fecha_publicacion DESC
                            LIMIT 6"; // Limitar a 6 para la vista de inicio
                    $result = $conn->query($sql);

                    if ($result && $result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            // Usamos el alias 'imagen_principal' de la subconsulta
                            $imagen = $row['imagen_principal'] ?: 'default.jpg';
                            $edad_texto = $row['edad'] ? $row['edad'] . ' años' : 'Edad Desconocida';
                            $raza_texto = $row['raza'] ?: 'Raza Desconocida';

                            echo '
                            <div class="pet-card">
                                <div class="pet-image">
                                    <img src="uploads/'.htmlspecialchars($imagen).'" alt="'.$row['nombre_mascota'].'">
                                    <span class="pet-type">'.$row['especie'].'</span>
                                </div>
                                <div class="pet-content">
                                    <h3 class="pet-name">'.htmlspecialchars($row['nombre_mascota']).'</h3>
                                    <p class="pet-breed">'.htmlspecialchars($raza_texto).' · '.htmlspecialchars($edad_texto).'</p>
                                    <p class="pet-location">
                                        <span class="location-dot"></span>
                                        '.htmlspecialchars($row['ubicacion']).'
                                    </p>
                                    <p class="pet-description">'.htmlspecialchars(substr($row['descripcion'], 0, 70)) . (strlen($row['descripcion']) > 70 ? '...' : '').'</p>
                                </div>
                                <div class="pet-footer">
                                    <button class="btn btn-primary" onclick="window.location.href=\'pet-detail.php?id='.$row['id'].'\'">Ver detalles</button>
                                </div>
                            </div>';
                        }
                    } else {
                        echo "<p>No hay publicaciones aún.</p>";
                    }
                    
                    // Cerrar la conexión aquí si no se va a usar más
                    if (isset($conn)) {
                        $conn->close();
                    }
                    ?>
                </div>
            </div>
        </section>

        <section class="features-section">
            <div class="container">
                <div class="features-grid">
                    <a href="mascotas.php" class="feature-card">
                        <i class="fas fa-paw"></i>
                        <h3>Adopciones</h3>
                        <p>Explora mascotas disponibles para adopción o publica tu mascota</p>
                    </a>
                    <a href="map.php" class="feature-card">
                        <i class="fas fa-map-marker-alt"></i>
                        <h3>Mapa Interactivo</h3>
                        <p>Encuentra veterinarias y protectoras cercanas a tu ubicación</p>
                    </a>
                    <a href="blog.php" class="feature-card">
                        <i class="fas fa-book-open"></i>
                        <h3>Blog</h3>
                        <p>Consejos, tips e historias de adopción de nuestra comunidad</p>
                    </a>
                </div>
            </div>
        </section>

        <section class="why-adopt-section">
            <div class="container">
                <h2>¿Por qué adoptar?</h2>
                <div class="reasons-grid">
                    <div class="reason-card">
                        <h3>Salvas una vida</h3>
                        <p>Al adoptar, le das una segunda oportunidad a un animal que necesita un hogar.</p>
                    </div>
                    <div class="reason-card">
                        <h3>Compañía incondicional</h3>
                        <p>Las mascotas ofrecen amor y lealtad, mejorando tu bienestar emocional.</p>
                    </div>
                    <div class="reason-card">
                        <h3>Combates el abandono</h3>
                        <p>Ayudas a reducir el problema de animales sin hogar en tu comunidad.</p>
                    </div>
                </div>
            </div>
        </section>
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