<?php
session_start();
require 'conexion.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mascotas Disponibles - Adopt Me</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="footer.css">
    <link rel="stylesheet" href="mascotas.css">
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
    <main>
        <div class="container">
            <div class="page-header">
                <h1>Mascotas Disponibles</h1>
                <p class="page-description">
                    Encuentra a tu compañero perfecto entre todas nuestras mascotas disponibles para adopción.
                </p>
            </div>

            <!-- Filtros -->
            <div class="filters-section">
                <div class="filters-container">
                    <div class="filter-group">
                        <label>Tipo de mascota:</label>
                        <select id="petTypeFilter">
                            <option value="all">Todos</option>
                            <option value="Perro">Perros</option>
                            <option value="Gato">Gatos</option>
                            <option value="Ave">Aves</option>
                            <option value="Otro">Otros</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Edad:</label>
                        <select id="ageFilter">
                            <option value="all">Todas las edades</option>
                            <option value="cachorro">Cachorro</option>
                            <option value="joven">Joven</option>
                            <option value="adulto">Adulto</option>
                            <option value="senior">Senior</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Tamaño:</label>
                        <select id="sizeFilter">
                            <option value="all">Todos los tamaños</option>
                            <option value="small">Pequeño</option>
                            <option value="medium">Mediano</option>
                            <option value="large">Grande</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Ubicación:</label>
                        <select id="locationFilter">
                            <option value="all">Todas las ubicaciones</option>
                            <!-- Aquí podrían agregarse dinámicamente las ubicaciones desde DB -->
                        </select>
                    </div>
                </div>
            </div>

            <!-- Grid de mascotas -->
            <div class="pets-grid" id="petsGrid">
                <?php
                // Conexión a la base de datos
                $conn = new mysqli('localhost:3307', 'root', '', 'prueba');
                if ($conn->connect_error) {
                    die("Conexión fallida: " . $conn->connect_error);
                }

                // Consulta para obtener todas las publicaciones
                $sql = "SELECT p.*, u.nombre AS nombre_usuario 
                        FROM publicaciones p 
                        JOIN usuarios u ON p.usuario_id = u.id 
                        ORDER BY p.fecha_publicacion DESC";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $imagen = $row['imagen'] ?: 'default.jpg';
                        $raza = $row['raza'] ?: 'Desconocida';
                        $edad = $row['edad'] ?: '?';
                        $ubicacion = $row['ubicacion'] ?: 'Desconocida';
                        echo '
                        <div class="pet-card" data-type="'.$row['especie'].'" data-age="'.$edad.'" data-size="medium" data-location="'.$ubicacion.'">
                            <div class="pet-image">
                                <img src="uploads/'.$imagen.'" alt="'.$row['nombre_mascota'].'">
                                <span class="pet-type">'.$row['especie'].'</span>
                            </div>
                            <div class="pet-content">
                                <h3 class="pet-name">'.$row['nombre_mascota'].'</h3>
                                <p class="pet-breed">'.$raza.' · '.$edad.' años</p>
                                <p class="pet-location">
                                    <span class="location-dot"></span>
                                    '.$ubicacion.'
                                </p>
                            </div>
                            <div class="pet-footer">
                                <a href="pet-detail.php?id='.$row['id'].'" class="btn btn-primary">Ver detalles</a>
                            </div>
                        </div>';
                    }
                } else {
                    echo "<p>No hay mascotas disponibles actualmente.</p>";
                }

                $conn->close();
                ?>
            </div>

        </div>
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
                        <li><a href="register.php">Dar en adopción</a></li>
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
                        <li>+1 (123) 456-7890</li>
                        <li>Av. Principal 123, Ciudad</li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 Adopt Me. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <script src="mascotas.js"></script>
    <script>
        document.getElementById('menuToggle').addEventListener('click', function() {
            const navMenu = document.getElementById('navMenu');
            const icon = this.querySelector('i');
            
            navMenu.classList.toggle('active');
            
            if (icon.classList.contains('fa-bars')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });
    </script>
</body>
</html>
