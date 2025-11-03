<?php
session_start();
require 'conexion.php';

// Redirigir si no está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// Filtro de categoría (opcional)
$categoriaSeleccionada = isset($_GET['categoria']) ? $_GET['categoria'] : null;

// Obtener publicación destacada (última subida)
$destacadaQuery = "SELECT p.*, u.nombre AS autor 
                   FROM blog_posts p 
                   JOIN usuarios u ON p.usuario_id = u.id 
                   ORDER BY fecha_publicacion DESC 
                   LIMIT 1";
$destacada = $conn->query($destacadaQuery)->fetch_assoc();

// Obtener publicaciones recientes (excluyendo la destacada)
if ($categoriaSeleccionada) {
    $postsQuery = $conn->prepare("SELECT p.*, u.nombre AS autor 
                                      FROM blog_posts p 
                                      JOIN usuarios u ON p.usuario_id = u.id 
                                      WHERE p.id != ? AND p.categoria = ?
                                      ORDER BY p.fecha_publicacion DESC");
    $postsQuery->bind_param("is", $destacada['id'], $categoriaSeleccionada);
} else {
    $postsQuery = $conn->prepare("SELECT p.*, u.nombre AS autor 
                                      FROM blog_posts p 
                                      JOIN usuarios u ON p.usuario_id = u.id 
                                      WHERE p.id != ?
                                      ORDER BY p.fecha_publicacion DESC");
    $postsQuery->bind_param("i", $destacada['id']);
}
$postsQuery->execute();
$posts = $postsQuery->get_result();
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
                        <li><a href="blog.php "class="active">Blog</a></li>
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
        <h1>Blog de Adopt Me</h1>
        <p class="page-description">
            Historias, consejos y experiencias compartidas por nuestra comunidad.
        </p>

        <div class="blog-layout">
            <div class="main-content">
                <!-- Publicación destacada -->
                <?php if ($destacada): ?>
                    <article class="featured-post">
                        <div class="featured-image">
                            <img src="<?php echo htmlspecialchars($destacada['imagen'] ?: 'mascota.webp'); ?>" alt="Artículo destacado">
                        </div>
                        <div class="featured-content">
                            <span class="post-category"><?php echo htmlspecialchars($destacada['categoria']); ?></span>
                            <h2><?php echo htmlspecialchars($destacada['titulo']); ?></h2>
                            <p><?php echo nl2br(substr($destacada['contenido'], 0, 150)) . '...'; ?></p>
                            <div class="post-meta">
                                <span class="post-date"><?php echo date("d M, Y", strtotime($destacada['fecha_publicacion'])); ?></span>
                                <span class="post-author"><i class="far fa-user"></i> <?php echo htmlspecialchars($destacada['autor']); ?></span>
                            </div>
                            <a href="blog-post.php?id=<?php echo $destacada['id']; ?>" class="read-more">Leer más</a>
                        </div>
                    </article>
                <?php endif; ?>

                <section class="recent-posts">
                    <h2>Artículos recientes</h2>
                    <div class="posts-grid">
                        <?php while ($post = $posts->fetch_assoc()): ?>
                            <div class="post-card">
                                <div class="post-image">
                                    <img src="<?php echo htmlspecialchars($post['imagen'] ?: 'https://placeimg.com/800/400/animals'); ?>" alt="Imagen de publicación">
                                </div>
                                <div class="post-content">
                                    <span class="post-category"><?php echo htmlspecialchars($post['categoria']); ?></span>
                                    <h3 class="post-title"><?php echo htmlspecialchars($post['titulo']); ?></h3>
                                    <p class="post-excerpt"><?php echo nl2br(substr($post['contenido'], 0, 120)) . '...'; ?></p>
                                    <div class="post-info">
                                        <span class="post-date"><i class="far fa-calendar"></i> <?php echo date("d M, Y", strtotime($post['fecha_publicacion'])); ?></span>
                                        <span class="post-author"><i class="far fa-user"></i> <?php echo htmlspecialchars($post['autor']); ?></span>
                                    </div>
                                </div>
                                <div class="post-footer">
                                    <a href="blog-post.php?id=<?php echo $post['id']; ?>" class="read-more">Leer artículo completo</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </section>
            </div>

            <aside class="sidebar">
                <div class="sidebar-widget categories">
                    <h3>Categorías</h3>
                    <ul>
                        <?php
                        $categorias = ['Historias', 'Noticias', 'Consejos', 'Educación', 'Otros'];
                        foreach ($categorias as $cat):
                        ?>
                            <li><a href="?categoria=<?php echo urlencode($cat); ?>" class="<?php echo $categoriaSeleccionada === $cat ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($cat); ?>
                            </a></li>
                        <?php endforeach; ?>
                        <li><a href="blog.php">Mostrar todas</a></li>
                    </ul>
                </div>

                <div class="sidebar-widget newsletter">
                    <h3>Únete a nuestra newsletter</h3>
                    <p>Recibe las mejores historias y consejos sobre el cuidado de mascotas.</p>
                    <form class="newsletter-form" action="newsletter_guardar.php" method="POST">
                        <input type="email" name="email" placeholder="Tu correo electrónico" required>
                        <button type="submit" class="btn btn-primary">Suscribirme</button>
                    </form>
                </div>

                <div class="sidebar-widget">
                    <a href="blog_nuevo.php" class="btn btn-primary" style="width:100%; text-align:center;">Publicar Artículo</a>
                </div>
            </aside>
        </div>
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
