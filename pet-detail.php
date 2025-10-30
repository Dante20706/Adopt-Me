<?php
session_start();
require 'conexion.php';

// Verificar si se pasó el ID de la publicación
$publicacion_id = $_GET['id'] ?? null;
if (!$publicacion_id) {
    die("Publicación no encontrada.");
}

// 1. Obtener datos de la publicación
$stmt = $conn->prepare("SELECT p.*, u.nombre AS publicador_nombre, u.tipo AS publicador_tipo
                        FROM publicaciones p
                        JOIN usuarios u ON p.usuario_id = u.id
                        WHERE p.id = ?");
$stmt->bind_param("i", $publicacion_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Publicación no encontrada.");
}

$publicacion = $result->fetch_assoc();
$stmt->close();


// 2. Obtener TODAS las imágenes
$imagenes = [];
$stmt_img = $conn->prepare("SELECT archivo FROM imagenes WHERE publicacion_id=? ORDER BY id ASC");
$stmt_img->bind_param("i", $publicacion_id);
$stmt_img->execute();
$result_img = $stmt_img->get_result();
while($row_img = $result_img->fetch_assoc()) {
    $imagenes[] = $row_img['archivo'];
}

// 3. Obtener "Otras Publicaciones" (CORREGIDO: Eliminada la cláusula p.estado)
$mascotas_disponibles = [];
// ESTA ES LA PARTE CLAVE: la consulta que trae las mascotas relacionadas
$stmt_related = $conn->prepare("SELECT p.id, p.nombre_mascota, p.especie, p.edad, p.ubicacion, 
                                       (SELECT i.archivo FROM imagen_principal i WHERE i.publicacion_id = p.id ORDER BY i.id ASC LIMIT 1) AS imagen_principal
                                FROM publicaciones p 
                                WHERE p.id != ?  /* Filtra para NO incluir la mascota actual */
                                ORDER BY RAND() LIMIT 3"); // Selecciona 3 al azar
$stmt_related->bind_param("i", $publicacion_id);
$stmt_related->execute();
$result_related = $stmt_related->get_result();
while($row_related = $result_related->fetch_assoc()) {
    $mascotas_disponibles[] = $row_related;
}

$stmt_img->close();
$conn->close();



// Determinar si el usuario puede adoptar
$puede_adoptar = isset($_SESSION['usuario_id']) && ($_SESSION['usuario_tipo'] ?? '') === 'adoptante';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($publicacion['nombre_mascota']); ?> - Adopt Me</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="footer.css">
    <link rel="stylesheet" href="pet-detail.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Estilos base de las columnas */
        .pet-detail-container {
            display: flex;
            gap: 30px;
            flex-wrap: wrap; /* Para que se apilen en móvil */
            padding: 20px 0;
        }
        .pet-image-column, .pet-info-column {
            flex: 1;
            min-width: 300px; /* Ancho mínimo para que no se colapsen */
        }
        
        /* Contenedor del Slider */
        .image-slider-container {
            position: relative;
            width: 100%; 
            aspect-ratio: 4 / 3; /* Relación de aspecto común para imágenes */
            overflow: hidden;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .image-slides-wrapper {
            position: relative;
            height: 100%;
        }
        
        /* Controla la visibilidad de los slides */
        .image-slide {
            display: none; /* OCULTA todos los slides por defecto */
            position: absolute; 
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
        }
        .image-slide.active {
            display: block; /* MUESTRA solo el slide activo */
        }
        
        .pet-main-image {
             width: 100%;
             height: 100%;
             object-fit: cover;
             display: block;
        }
        
        /* Botones de navegación */
        .slider-button {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0,0,0,0.5);
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            padding: 10px 15px;
            border-radius: 50%;
            z-index: 10;
            transition: background 0.3s;
        }
        .slider-button.prev { left: 10px; }
        .slider-button.next { right: 10px; }
        .slider-button:hover { background: rgba(0,0,0,0.7); }

        /* Puntos indicadores */
        .slider-dots {
            text-align: center;
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
            display: flex;
            gap: 8px;
        }
        .dot {
            height: 12px;
            width: 12px;
            display: inline-block;
            background: rgba(255, 255, 255, 0.7);
            border-radius: 50%;
            cursor: pointer;
            transition: background 0.3s, transform 0.3s;
        }
        .dot.active {
            background: #ff9800; /* Color primario o destacado */
            transform: scale(1.2);
        }
        
        @media (max-width: 768px) {
            .pet-detail-container {
                flex-direction: column;
            }
        }

        
    </style>
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

<main class="pet-detail-main">
    <div class="container">
        <div class="pet-detail-container">
            
            <div class="pet-image-column">
                <div class="image-slider-container">
                    <div class="image-slides-wrapper">
                        <?php if (empty($imagenes)): ?>
                            <div class="image-slide active">
                                <img src="uploads/default.jpg" alt="No hay imagen" class="pet-main-image">
                            </div>
                        <?php else: ?>
                            <?php foreach($imagenes as $index => $imagen): ?>
                                <div class="image-slide <?php echo ($index === 0) ? 'active' : ''; ?>">
                                    <img src="uploads/<?php echo htmlspecialchars($imagen); ?>" 
                                         alt="Foto <?php echo $index+1; ?>" 
                                         class="pet-main-image">
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <?php if (count($imagenes) > 1): ?>
                        <button class="slider-button prev" onclick="moveSlide(-1)">&#10094;</button>
                        <button class="slider-button next" onclick="moveSlide(1)">&#10095;</button>
                        <div class="slider-dots" id="sliderDots"></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="pet-info-column">
                <div class="pet-header-info">
                    <h1 class="pet-name-title">
                        <?php echo htmlspecialchars($publicacion['nombre_mascota']); ?>
                        <?php if(strtolower($publicacion['sexo']) === 'macho'): ?>
                            <i class="fas fa-mars" title="Macho"></i>
                        <?php elseif(strtolower($publicacion['sexo']) === 'hembra'): ?>
                            <i class="fas fa-venus" title="Hembra"></i>
                        <?php endif; ?>
                    </h1>
                    <p class="pet-location-text">
                        <i class="fas fa-map-marker-alt"></i>
                        <?php echo htmlspecialchars($publicacion['ubicacion']); ?>
                    </p>
                </div>

                <div class="pet-data-section">
                    <h2 class="section-title">Mis datos</h2>
                    <div class="data-grid">
                        <div class="data-box"><p class="data-main"><?php echo htmlspecialchars($publicacion['especie']); ?></p><p class="data-label">Especie</p></div>
                        <div class="data-box"><p class="data-main"><?php echo htmlspecialchars($publicacion['edad']); ?></p><p class="data-label">Edad</p></div>
                        <div class="data-box"><p class="data-main"><?php echo htmlspecialchars($publicacion['raza']); ?></p><p class="data-label">Raza</p></div>
                        <div class="data-box"><p class="data-main"><?php echo htmlspecialchars($publicacion['peso']); ?></p><p class="data-label">Peso(kg)</p></div>
                        <div class="data-box"><p class="data-main"><?php echo htmlspecialchars($publicacion['tamaño']); ?></p><p class="data-label">Tamaño</p></div>
                    </div>
                </div>

                <div class="pet-data-section">
                    <h2 class="section-title" >Descripción</h2>
                    <div class="description-text">
                        <?php
                        $desc = trim($publicacion['descripcion'] ?? '');
                        if ($desc === '') {
                            echo "<p class=\"muted\">El publicador no dejó descripción adicional.</p>";
                        } else {
                            echo '<p>' . nl2br(htmlspecialchars($desc)) . '</p>';
                        }
                        ?>
                    </div>
                </div>

                <div class="health-section">
                    <h2 class="section-title">Me entregan</h2>
                    <div class="health-list">
                        <div class="health-item"><i class="fas fa-check-circle"></i><span><?php echo $publicacion['desparasitado'] ? 'Desparasitado' : 'No desparasitado'; ?></span></div>
                        <div class="health-item"><i class="fas fa-check-circle"></i><span><?php echo $publicacion['esterilizado'] ? 'Esterilizado' : 'No esterilizado'; ?></span></div>
                        <div class="health-item"><i class="fas fa-check-circle"></i><span><?php echo $publicacion['vacunado'] ? 'Vacunado' : 'No vacunado'; ?></span></div>
                    </div>
                </div>

                <div class="publisher-section">
                    <div class="publisher-card">
                        <div class="publisher-header">
                            <div class="publisher-info">
                                <h3><?php echo htmlspecialchars($publicacion['publicador_nombre']); ?></h3>
                                <p class="publisher-role"><?php echo htmlspecialchars($publicacion['publicador_tipo']); ?></p>
                            </div>
                            <div class="publisher-avatar"><i class="fas fa-user"></i></div>
                        </div>
                    </div>
                </div>

                <div class="adoption-button-section">
                    <?php if($puede_adoptar): ?>
                        <form action="adopt.php" method="POST">
                            <input type="hidden" name="publicacion_id" value="<?php echo $publicacion['id']; ?>">
                            <button type="submit" class="btn-adopt">¡Quiero Adoptarlo!</button>
                        </form>
                    <?php elseif(isset($_SESSION['usuario_id'])): ?>
                        <button class="btn-adopt" onclick="alert('Solo las cuentas tipo adoptante pueden adoptar.')">¡Quiero Adoptarlo!</button>
                    <?php else: ?>
                        <button class="btn-adopt" onclick="alert('Debes iniciar sesión para adoptar.')">¡Quiero Adoptarlo!</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

<section class="available-pets-section">
            <div class="section-header">
                <h2 class="section-title">Otras Publicaciones</h2>
                <a href="mascotas.php" class="view-all-link">Ver todas <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="pet-cards-grid">
                <?php if (empty($mascotas_disponibles)): ?>
                    <p>No hay otras mascotas disponibles en este momento.</p>
                <?php else: ?>
                    <?php foreach ($mascotas_disponibles as $pet): ?>
                        <div class="pet-card">
                            <img src="uploads/<?php echo htmlspecialchars($pet['imagen_principal'] ?? 'default.jpg'); ?>" 
                                 alt="Foto de <?php echo htmlspecialchars($pet['nombre_mascota']); ?>" 
                                 class="pet-card-image">
                                 
                            <div class="pet-card-info">
                                <span class="pet-card-species-tag"><?php echo htmlspecialchars($pet['especie']); ?></span>
                                <h3 class="pet-card-name"><?php echo htmlspecialchars($pet['nombre_mascota']); ?></h3>
                                <p class="pet-card-details">
                                    <i class="fas fa-birthday-cake"></i> <?php echo htmlspecialchars($pet['edad']); ?><br>
                                    <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($pet['ubicacion']); ?>
                                </p>
                                <a href="pet-detail.php?id=<?php echo $pet['id']; ?>" class="btn btn-sm btn-outline-primary">Ver detalles</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
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

<script>
    let currentSlide = 0;
    const slides = document.querySelectorAll('.image-slide');
    const totalSlides = slides.length;
    const dotsContainer = document.getElementById('sliderDots');

    function showImage(index) {
        if (index < 0) currentSlide = totalSlides - 1;
        else if (index >= totalSlides) currentSlide = 0;
        else currentSlide = index;

        slides.forEach((slide, i) => {
            slide.classList.toggle('active', i === currentSlide);
        });

        updateDots();
    }

    function moveSlide(step) {
        showImage(currentSlide + step);
    }

    function updateDots() {
        if (dotsContainer) {
            const dots = dotsContainer.querySelectorAll('.dot');
            dots.forEach((dot, i) => {
                dot.classList.toggle('active', i === currentSlide);
            });
        }
    }

    function initializeSlider() {
        if (totalSlides > 1 && dotsContainer) {
            for (let i = 0; i < totalSlides; i++) {
                const dot = document.createElement('span');
                dot.classList.add('dot');
                dot.onclick = () => showImage(i);
                dotsContainer.appendChild(dot);
            }
        }
        if (totalSlides > 0) {
            showImage(0); 
        }
    }

    if (totalSlides > 0) {
        initializeSlider();
    }
    
    // Funcionalidad del menú móvil
    const menuToggle = document.getElementById('menuToggle');
    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
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
    }
</script>
</body>
</html>