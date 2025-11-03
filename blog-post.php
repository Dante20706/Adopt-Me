<?php
session_start();
require 'conexion.php';

// Verificar que haya un ID en la URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: blog.php");
    exit;
}

$postId = intval($_GET['id']);

// Obtener la publicación principal
$query = $conn->prepare("SELECT p.*, u.nombre AS autor 
                             FROM blog_posts p 
                             JOIN usuarios u ON p.usuario_id = u.id 
                             WHERE p.id = ?");
$query->bind_param("i", $postId);
$query->execute();
$resultado = $query->get_result();
$post = $resultado->fetch_assoc();

if (!$post) {
    echo "<h2>Publicación no encontrada</h2>";
    exit;
}

// Obtener artículos relacionados (misma categoría, excluyendo el actual)
$relacionados = $conn->prepare("SELECT id, titulo, imagen, categoria, contenido 
                                   FROM blog_posts 
                                   WHERE categoria = ? AND id != ?
                                   ORDER BY fecha_publicacion DESC
                                   LIMIT 3");
$relacionados->bind_param("si", $post['categoria'], $postId);
$relacionados->execute();
$relacionadosResult = $relacionados->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['titulo']); ?> - Adopt Me</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="footer.css">
    <link rel="stylesheet" href="blog-post.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<header class="navbar">
    <div class="container">
        <div class="navbar-content">
            <a href="index.php" class="logo">
                <i class="fas fa-paw"></i><span>Adopt Me</span>
            </a>
            <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
            <nav class="nav-menu" id="navMenu">
                <ul class="nav-links">
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="protectoras.php">Protectoras</a></li>
                    <li><a href="map.php">Mapa</a></li>
                    <li><a href="blog.php" class="active">Blog</a></li>
                </ul>
                <div class="nav-buttons">
                    <?php if (isset($_SESSION['usuario_id'])): ?>
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
        <!-- Breadcrumb -->
        <nav class="breadcrumb">
            <a href="index.php">Inicio</a> <span>/</span>
            <a href="blog.php">Blog</a> <span>/</span>
            <span><?php echo htmlspecialchars($post['titulo']); ?></span>
        </nav>

        <!-- Artículo principal -->
        <article class="blog-post">
            <header class="post-header">
                <div class="post-category"><?php echo htmlspecialchars($post['categoria']); ?></div>
                <h1 class="post-title"><?php echo htmlspecialchars($post['titulo']); ?></h1>
                <div class="post-meta">
                    <div class="author-info">
                        <div class="author-avatar"><i class="fas fa-user"></i></div>
                        <div class="author-details">
                            <span class="author-name"><?php echo htmlspecialchars($post['autor']); ?></span>
                            <span class="post-date"><?php echo date("d M, Y", strtotime($post['fecha_publicacion'])); ?></span>
                        </div>
                    </div>
                </div>
            </header>

            <div class="post-image">
                <img src="<?php echo htmlspecialchars($post['imagen'] ?: 'mascota.webp'); ?>" alt="Imagen del artículo">
            </div>

            <div class="post-content">
                <?php echo nl2br($post['contenido']); ?>
            </div>
        </article>

        <!-- Artículos relacionados -->
        <?php if ($relacionadosResult->num_rows > 0): ?>
            <section class="related-posts">
                <h2>Artículos relacionados</h2>
                <div class="related-posts-grid">
                    <?php while ($rel = $relacionadosResult->fetch_assoc()): ?>
                        <article class="related-post-card">
                            <div class="related-post-image">
                                <img src="<?php echo htmlspecialchars($rel['imagen'] ?: 'https://placeimg.com/400/250/animals'); ?>" alt="Imagen relacionada">
                            </div>
                            <div class="related-post-content">
                                <span class="related-post-category"><?php echo htmlspecialchars($rel['categoria']); ?></span>
                                <h3 class="related-post-title"><?php echo htmlspecialchars($rel['titulo']); ?></h3>
                                <p class="related-post-excerpt">
                                    <?php echo nl2br(substr($rel['contenido'], 0, 120)) . '...'; ?>
                                </p>
                                <a href="blog-post.php?id=<?php echo $rel['id']; ?>" class="related-post-link">Leer más</a>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- Newsletter -->
        <section class="newsletter-section">
            <div class="newsletter-content">
                <h3>¿Te gustó este artículo?</h3>
                <p>Suscríbete a nuestro newsletter para recibir más consejos sobre el cuidado de mascotas</p>
                <form class="newsletter-form" action="suscribir.php" method="POST">
                    <input type="email" name="email" placeholder="Tu correo electrónico" required>
                    <button type="submit" class="btn btn-primary">Suscribirme</button>
                </form>
            </div>
        </section>
    </div>
</main>

<footer class="footer">
    <div class="container">
        <p>&copy; 2025 Adopt Me. Todos los derechos reservados.</p>
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
