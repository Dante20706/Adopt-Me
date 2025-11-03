<?php
session_start();
require 'conexion.php';

// Solo usuarios logueados
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// Si se envió el formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $titulo = $conn->real_escape_string($_POST['titulo']);
    $contenido = $conn->real_escape_string($_POST['contenido']);
    $categoria = $conn->real_escape_string($_POST['categoria']);
    $usuario_id = $_SESSION['usuario_id'];
    $imagen = "";

    // Manejo de imagen
    if (!empty($_FILES['imagen']['name'])) {
        $nombreArchivo = time() . "_" . basename($_FILES['imagen']['name']);
        $ruta = "uploads/blog/" . $nombreArchivo;
        if (!is_dir("uploads/blog")) mkdir("uploads/blog", 0777, true);
        move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta);
        $imagen = $ruta;
    }

    $conn->query("INSERT INTO blog_posts (usuario_id, titulo, contenido, categoria, imagen)
                  VALUES ('$usuario_id', '$titulo', '$contenido', '$categoria', '$imagen')");
    
    header("Location: blog.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva publicación - Adopt Me</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="blog_nuevo.css">
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    

</head>
<body>
    <header class="navbar">
        <div>
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
                    <?php if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'admin'): ?>
                        <li><a href="admin.php" class="admin-link"><i class="fas fa-tools"></i> Panel Admin</a></li>
                    <?php endif; ?>
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

    <main class="container nueva-publicacion">
        <h1>Crear nueva publicación</h1>
        <p class="descripcion">Compartí tus experiencias, consejos o noticias con la comunidad 🐾</p>

        <form method="POST" enctype="multipart/form-data" class="form-blog">
            <div class="form-group">
                <label for="titulo">Título</label>
                <input type="text" id="titulo" name="titulo" placeholder="Ej: La historia de Luna" required>
            </div>

            <div class="form-group">
                <label for="categoria">Categoría</label>
                <select id="categoria" name="categoria" required>
                    <option value="Historias">Historias</option>
                    <option value="Noticias">Noticias</option>
                    <option value="Consejos">Consejos</option>
                    <option value="Educación">Educación</option>
                    <option value="Otros">Otros</option>
                </select>
            </div>

            <div class="form-group">
                <label for="contenido">Contenido</label>
                <textarea id="contenido" name="contenido" rows="8" placeholder="Escribí tu publicación aquí..." required></textarea>
            </div>

            <div class="form-group">
                <label for="imagen">Imagen destacada (opcional)</label>
                <input type="file" id="imagen" name="imagen" accept="image/*">
            </div>

            <div class="form-buttons">
                <button type="submit" class="btn btn-primary">Publicar</button>
                <a href="blog.php" class="btn btn-cancelar">Cancelar</a>
            </div>
        </form>
    </main>

<footer class="footer">
    <div>
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
