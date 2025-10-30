<?php
session_start();
require 'conexion.php';

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// Obtener todas las cuentas con tipo 'protectora'
$query = "SELECT id, nombre_protectora, contacto_responsable, provincia, departamento, pagina_web, email, telefono, fecha_registro 
          FROM usuarios 
          WHERE tipo = 'protectora'";
$resultado = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Protectoras - AdoptMe</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="footer.css">
    <link rel="stylesheet" href="protectoras.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<!-- Barra de navegación -->
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
                    <li><a href="protectoras.php" class="active">Protectoras</a></li>
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
<main>
    <div class="container">
        <section class="section">
            <div class="section-header">
                <h2>Protectoras registradas</h2>
            </div>

            <div class="cards-grid">
                <?php if ($resultado && $resultado->num_rows > 0): ?>
                    <?php while ($fila = $resultado->fetch_assoc()): ?>
                        <div class="user-card association-card">
                            <div class="card-header">
                                <div class="user-avatar association-avatar">
                                    <i class="fas fa-hand-holding-heart"></i>
                                </div>
                                <span class="user-badge association-badge">Protectora</span>
                            </div>

                            <div class="card-content">
                                <h3 class="user-name">
                                    <?php echo htmlspecialchars($fila['nombre_protectora'] ?: 'Sin nombre registrado'); ?>
                                </h3>
                                <p class="user-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo htmlspecialchars($fila['provincia'] . ', ' . $fila['departamento']); ?>
                                </p>
                                <p class="user-info">
                                    <i class="fas fa-phone"></i>
                                    <?php echo htmlspecialchars($fila['telefono'] ?: 'Sin teléfono'); ?>
                                </p>
                                <p class="user-info">
                                    <i class="fas fa-envelope"></i>
                                    <?php echo htmlspecialchars($fila['email']); ?>
                                </p>
                                <?php if (!empty($fila['pagina_web'])): ?>
                                    <p class="user-info">
                                        <i class="fas fa-globe"></i>
                                        <a href="<?php echo htmlspecialchars($fila['pagina_web']); ?>" target="_blank">
                                            Visitar sitio
                                        </a>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="card-footer">
                                <a href="P_protectora.php?id=<?php echo $fila['id']; ?>" class="btn btn-primary">
                                    Ver perfil
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No hay protectoras registradas aún.</p>
                <?php endif; ?>
            </div>
        </section>
        <!-- Sección Cuidadores -->
<section class="section">
    <div class="section-header">
        <h2>Cuidadores</h2>
    </div>

    <?php
    // Ejecutamos la consulta de cuidadores
    $sql_cuidadores = "SELECT id, nombre, apellido, provincia, departamento, telefono, email 
                       FROM usuarios WHERE tipo = 'cuidador' ORDER BY fecha_registro DESC";
    $result_cuidadores = $conn->query($sql_cuidadores);
    ?>

    <div class="cards-grid">
        <?php
        if ($result_cuidadores === false) {
            echo '<p class="sin-resultados">Error al cargar los cuidadores. Intenta nuevamente más tarde.</p>';
        } elseif ($result_cuidadores->num_rows === 0) {
            echo '<p class="sin-resultados">No hay cuidadores registrados.</p>';
        } else {
            while ($row = $result_cuidadores->fetch_assoc()):
        ?>
                <div class="user-card caregiver-card">
                    <div class="card-header">
                        <div class="user-avatar caregiver-avatar caregiver-1">
                            <i class="fas fa-user"></i>
                        </div>
                        <span class="user-badge caregiver-badge">Cuidador</span>
                    </div>
                    <div class="card-content">
                        <h3 class="user-name"><?php echo htmlspecialchars($row['nombre'] . ' ' . $row['apellido']); ?></h3>
                        <p class="user-location">
                            <?php echo htmlspecialchars($row['provincia'] . ($row['departamento'] ? ' - ' . $row['departamento'] : '')); ?>
                        </p>
                        <p class="user-info">
                            <i class="fas fa-phone"></i> <?php echo htmlspecialchars($row['telefono'] ?: '-'); ?>
                        </p>
                        <p class="user-info">
                            <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($row['email']); ?>
                        </p>
                    </div>
                    <div class="card-footer">
                        <a href="P_cuidadora.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">Ver perfil</a>
                    </div>
                </div>
        <?php
            endwhile;
        }
        ?>
    </div>
</section>


    
</main>


<!-- Pie de página -->
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
