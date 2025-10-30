<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Mensajes
$mensaje_perfil = '';
$mensaje_error = '';
if (isset($_SESSION['success_perfil'])) {
    $mensaje_perfil = $_SESSION['success_perfil'];
    unset($_SESSION['success_perfil']);
}
if (isset($_SESSION['error_perfil'])) {
    $mensaje_error = $_SESSION['error_perfil'];
    unset($_SESSION['error_perfil']);
}

// Si se pidió borrar
if (isset($_GET['eliminar'])) {
    $id_pub = intval($_GET['eliminar']);
    $stmt = $conn->prepare("DELETE FROM publicaciones WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $id_pub, $usuario_id);
    $stmt->execute();
    $stmt->close();
    header("Location: profile.php");
    exit;
}

// Traer publicaciones
$stmt = $conn->prepare("SELECT id, nombre_mascota, especie, raza, edad, sexo, ubicacion, imagen, fecha_publicacion 
                        FROM publicaciones WHERE usuario_id = ? ORDER BY fecha_publicacion DESC");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
$publicaciones = $resultado->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Traer datos del usuario
$stmt = $conn->prepare("SELECT nombre, apellido, email, telefono, provincia, departamento, descripcion FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$stmt->close();

// Traer imagen de perfil (tabla imagen_perfil)
$stmt = $conn->prepare("SELECT archivo FROM imagen_perfil WHERE usuario_id = ? LIMIT 1");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$imagen_perfil = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Perfil - AdoptMe</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="footer.css">
    <link rel="stylesheet" href="profile.css">
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
                    <li><a href="blog.php">Blog</a></li>
                </ul>
                <div class="nav-buttons">
                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        <a href="profile.php" class="active">Hola, <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></a>
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

<div class="container">
    <h1>Mi Perfil</h1>

    <!-- Mensajes -->
    <?php if($mensaje_perfil): ?>
        <p class="mensaje exito"><?= htmlspecialchars($mensaje_perfil) ?></p>
    <?php endif; ?>
    <?php if($mensaje_error): ?>
        <p class="mensaje error"><?= htmlspecialchars($mensaje_error) ?></p>
    <?php endif; ?>

    <!-- Imagen de perfil -->
    <div class="card perfil">
        <h2>Imagen de Perfil</h2>
        <div class="perfil-img">
            <?php if ($imagen_perfil && !empty($imagen_perfil['archivo'])): ?>
                <img src="uploads/<?= htmlspecialchars($imagen_perfil['archivo']); ?>" alt="Imagen de perfil" style="width:150px;height:150px;border-radius:50%;object-fit:cover;">
            <?php else: ?>
                <img src="uploads/default_profile.png" alt="Sin imagen" style="width:150px;height:150px;border-radius:50%;object-fit:cover;">
            <?php endif; ?>
        </div>

        <form action="subir_imagen_perfil.php" method="POST" enctype="multipart/form-data">
            <input type="file" name="imagen" accept="image/*" required>
            <button type="submit" class="btn">Actualizar Imagen</button>
        </form>
    </div>

    <!-- Formulario de descripción -->
    <div class="card descripcion">
        <h2>Mi Descripción</h2>
        <form action="editar_descripcion.php" method="POST">
            <textarea name="descripcion" rows="5" placeholder="Contá un poco sobre vos y tu amor por los animales..."><?= htmlspecialchars($usuario['descripcion'] ?? ''); ?></textarea>
            <button type="submit" class="btn">Guardar Descripción</button>
        </form>
    </div>

    <!-- Formulario para editar datos -->
    <div class="card">
        <h2>Mis Datos</h2>
        <form action="editar_perfil.php" method="POST">
            <label>Nombre</label>
            <input type="text" name="nombre" value="<?= htmlspecialchars($usuario['nombre']); ?>" required>

            <label>Apellido</label>
            <input type="text" name="apellido" value="<?= htmlspecialchars($usuario['apellido']); ?>" required>

            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($usuario['email']); ?>" required>

            <label>Teléfono</label>
            <input type="text" name="telefono" value="<?= htmlspecialchars($usuario['telefono']); ?>">

            <label>Provincia</label>
            <input type="text" name="provincia" value="<?= htmlspecialchars($usuario['provincia']); ?>">

            <label>Departamento</label>
            <input type="text" name="departamento" value="<?= htmlspecialchars($usuario['departamento']); ?>">

            <button type="submit" class="btn">Actualizar Datos</button>
        </form>
    </div>

    <!-- Publicaciones -->
    <div class="card">
        <h2>Mis Publicaciones</h2>
        <?php if (count($publicaciones) === 0): ?>
            <p>No has publicado ninguna mascota aún.</p>
        <?php else: ?>
            <?php foreach ($publicaciones as $pub): ?>
                <div class="pub">
                    <?php if($pub['imagen']): ?>
                        <img src="uploads/<?= htmlspecialchars($pub['imagen']); ?>" alt="Mascota">
                    <?php endif; ?>
                    <div>
                        <h3><?= htmlspecialchars($pub['nombre_mascota']); ?> (<?= htmlspecialchars($pub['especie']); ?>)</h3>
                        <p><strong>Raza:</strong> <?= htmlspecialchars($pub['raza']); ?> |
                           <strong>Edad:</strong> <?= htmlspecialchars($pub['edad']); ?> |
                           <strong>Sexo:</strong> <?= htmlspecialchars($pub['sexo']); ?> |
                           <strong>Ubicación:</strong> <?= htmlspecialchars($pub['ubicacion']); ?></p>
                        <p><em>Publicado el <?= $pub['fecha_publicacion']; ?></em></p>
                        <div class="acciones">
                            <a href="editar_publicacion.php?id=<?= $pub['id']; ?>">Editar</a>
                            <a href="profile.php?eliminar=<?= $pub['id']; ?>" class="eliminar"
                               onclick="return confirm('¿Seguro que deseas eliminar esta publicación?');">Eliminar</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

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
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fas fa-envelope"></i></a>
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
