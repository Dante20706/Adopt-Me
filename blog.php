<?php
session_start();
require 'conexion.php';
if (!isset($_SESSION['usuario_id'])) {
    // Si no está logueado, redirigir al login
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - Adopt Me</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="footer.css">
    <link rel="stylesheet" href="blog.css">
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
                        <li><a href="index.php">Inicio</a></li>
                        <li><a href="protectoras.php">Protectoras</a></li>
                        <li><a href="map.php">Mapa</a></li>
                        <li><a href="blog.php" class="active">Blog</a></li>
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
            <h1>Blog de Adopt Me</h1>
            <p class="page-description">
                Encuentra consejos, tips e historias inspiradoras sobre mascotas y adopción.
                Aprende sobre el cuidado adecuado y comparte tus experiencias con nuestra comunidad.
            </p>
            
            <div class="blog-layout">
                <div class="main-content">
                    <article class="featured-post">
                        <div class="featured-image">
                            <img src="mascota.webp" alt="Artículo destacado">
                        </div>
                        <div class="featured-content">
                            <span class="post-category">Destacado</span>
                            <h2>Cómo preparar tu hogar para la llegada de un nuevo miembro peludo</h2>
                            <p>
                                Consejos prácticos para adaptar tu espacio y hacer que la transición sea lo más cómoda posible para tu nueva mascota en Salta.
                            </p>
                            <div class="post-meta">
                                <span class="post-date">15 de Mayo, 2025</span>
                                <a href="blog-post.php?id=5" class="read-more">Leer más</a>
                            </div>
                        </div>
                    </article>
                </div>
                
                <aside class="sidebar">
                    <div class="sidebar-widget categories">
                        <h3>Categorías</h3>
                        <ul>
                            <li><a href="#">Consejos de cuidado</a></li>
                            <li><a href="#">Historias de adopción</a></li>
                            <li><a href="#">Salud y bienestar</a></li>
                            <li><a href="#">Entrenamiento</a></li>
                            <li><a href="#">Alimentación</a></li>
                        </ul>
                    </div>
                    
                    <div class="sidebar-widget newsletter">
                        <h3>Únete a nuestra newsletter</h3>
                        <p>
                            Recibe los mejores consejos y novedades sobre el cuidado de mascotas.
                        </p>
                        <form class="newsletter-form">
                            <input type="email" placeholder="Tu correo electrónico" required>
                            <button type="submit" class="btn btn-primary">Suscribirme</button>
                        </form>
                    </div>
                </aside>
            </div>
            
            <section class="recent-posts">
                <h2>Artículos recientes</h2>
                <div class="posts-grid">
                    <!-- Artículo 1 -->
                    <div class="post-card">
                        <div class="post-image">
                            <img src="calormascotas.jpg" alt="10 consejos para cuidar a tu mascota en verano">
                        </div>
                        <div class="post-content">
                            <span class="post-category">Consejos</span>
                            <h3 class="post-title">10 consejos para cuidar a tu mascota en verano</h3>
                            <p class="post-excerpt">El calor en Salta puede ser peligroso para nuestras mascotas. Aprende cómo mantenerlas frescas y seguras durante los meses de verano.</p>
                            <div class="post-info">
                                <span class="post-date">
                                    <i class="far fa-calendar"></i> 10 Mayo, 2025
                                </span>
                                <span class="post-author">
                                    <i class="far fa-user"></i> María González
                                </span>
                            </div>
                        </div>
                        <div class="post-footer">
                            <a href="blog-post.php?id=1" class="read-more">Leer artículo completo</a>
                        </div>
                    </div>

                    <!-- Artículo 2 -->
                    <div class="post-card">
                        <div class="post-image">
                            <img src="toby.jpg" alt="La historia de Toby">
                        </div>
                        <div class="post-content">
                            <span class="post-category">Historias</span>
                            <h3 class="post-title">La historia de Toby: de la calle a un hogar amoroso</h3>
                            <p class="post-excerpt">Conoce la increíble transformación de Toby, un perro que pasó de vivir en las calles de Salta a encontrar una familia que lo ama.</p>
                            <div class="post-info">
                                <span class="post-date">
                                    <i class="far fa-calendar"></i> 5 Mayo, 2025
                                </span>
                                <span class="post-author">
                                    <i class="far fa-user"></i> Carlos Rodríguez
                                </span>
                            </div>
                        </div>
                        <div class="post-footer">
                            <a href="blog-post.php?id=2" class="read-more">Leer artículo completo</a>
                        </div>
                    </div>

                    <!-- Artículo 3 -->
                    <div class="post-card">
                        <div class="post-image">
                            <img src="comiendo.jpg" alt="Alimentación adecuada para gatos">
                        </div>
                        <div class="post-content">
                            <span class="post-category">Alimentación</span>
                            <h3 class="post-title">Alimentación adecuada para gatos: mitos y realidades</h3>
                            <p class="post-excerpt">Descubre qué deberías y qué no deberías darle de comer a tu gato para mantenerlo saludable en el clima de Salta.</p>
                            <div class="post-info">
                                <span class="post-date">
                                    <i class="far fa-calendar"></i> 1 Mayo, 2025
                                </span>
                                <span class="post-author">
                                    <i class="far fa-user"></i> Laura Martínez
                                </span>
                            </div>
                        </div>
                        <div class="post-footer">
                            <a href="blog-post.php?id=3" class="read-more">Leer artículo completo</a>
                        </div>
                    </div>

                    <!-- Artículo 4 -->
                    <div class="post-card">
                        <div class="post-image">
                            <img src="https://placeimg.com/800/400/animals?id=4" alt="Entrenar a tu perro">
                        </div>
                        <div class="post-content">
                            <span class="post-category">Entrenamiento</span>
                            <h3 class="post-title">Cómo entrenar a tu perro con refuerzo positivo</h3>
                            <p class="post-excerpt">Técnicas efectivas y amigables para enseñar nuevos trucos y comportamientos a tu perro sin usar castigos.</p>
                            <div class="post-info">
                                <span class="post-date">
                                    <i class="far fa-calendar"></i> 28 Abril, 2025
                                </span>
                                <span class="post-author">
                                    <i class="far fa-user"></i> Pedro Sánchez
                                </span>
                            </div>
                        </div>
                        <div class="post-footer">
                            <a href="blog-post.php?id=4" class="read-more">Leer artículo completo</a>
                        </div>
                    </div>

                    <!-- Artículo 5 -->
                    <div class="post-card">
                        <div class="post-image">
                            <img src="https://placeimg.com/800/400/animals?id=5" alt="Señales veterinario">
                        </div>
                        <div class="post-content">
                            <span class="post-category">Salud</span>
                            <h3 class="post-title">Señales de que tu mascota necesita visitar al veterinario</h3>
                            <p class="post-excerpt">Aprende a reconocer los signos que indican que tu mascota podría estar enferma y necesita atención médica en Salta.</p>
                            <div class="post-info">
                                <span class="post-date">
                                    <i class="far fa-calendar"></i> 25 Abril, 2025
                                </span>
                                <span class="post-author">
                                    <i class="far fa-user"></i> Ana López
                                </span>
                            </div>
                        </div>
                        <div class="post-footer">
                            <a href="blog-post.php?id=5" class="read-more">Leer artículo completo</a>
                        </div>
                    </div>

                    <!-- Artículo 6 -->
                    <div class="post-card">
                        <div class="post-image">
                            <img src="https://placeimg.com/800/400/animals?id=6" alt="Adoptar mascota adulta">
                        </div>
                        <div class="post-content">
                            <span class="post-category">Adopción</span>
                            <h3 class="post-title">Los beneficios de adoptar una mascota adulta</h3>
                            <p class="post-excerpt">Descubre por qué adoptar una mascota adulta puede ser una excelente opción para ti y tu familia en Salta.</p>
                            <div class="post-info">
                                <span class="post-date">
                                    <i class="far fa-calendar"></i> 20 Abril, 2025
                                </span>
                                <span class="post-author">
                                    <i class="far fa-user"></i> Javier Fernández
                                </span>
                            </div>
                        </div>
                        <div class="post-footer">
                            <a href="blog-post.php?id=6" class="read-more">Leer artículo completo</a>
                        </div>
                    </div>
                </div>
                
            </section>
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
        // Solo funcionalidad básica para el menú móvil y newsletter
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

        // Funcionalidad básica para el formulario de newsletter
        document.querySelector('.newsletter-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const email = this.querySelector('input[type="email"]').value;
            if (email) {
                alert('¡Gracias por suscribirte! Te enviaremos nuestras mejores noticias a: ' + email);
                this.reset();
            }
        });
    </script>
</body>
</html>
