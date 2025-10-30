<?php
// register.php
session_start();
require 'conexion.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['firstName'] ?? '');
    $apellido = trim($_POST['lastName'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirmPassword'] ?? '';

    // Validaciones
    if ($nombre === '' || $apellido === '' || $email === '' || $password === '') {
        $error = "⚠️ Faltan campos obligatorios.";
    } elseif ($password !== $confirm) {
        $error = "❌ Las contraseñas no coinciden.";
    } else {
        // Verificar que no exista el email
        $check = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            $error = "❌ Ya existe una cuenta con ese correo.";
        }
        $check->close();
    }

    // Si no hubo errores hasta acá, insertamos como VISITANTE
    if ($error === "") {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, apellido, email, password, tipo, fecha_registro) VALUES (?, ?, ?, ?, 'visitante', NOW())");
        $stmt->bind_param("ssss", $nombre, $apellido, $email, $passwordHash);

        if ($stmt->execute()) {
            $usuario_id = $stmt->insert_id;

            // Guardar sesión
            $_SESSION['usuario_id'] = $usuario_id;
            $_SESSION['usuario_nombre'] = $nombre;
            $_SESSION['usuario_tipo'] = 'visitante';

            $stmt->close();
            $conn->close();

            header("Location: index.php");
            exit;
        } else {
            $error = "❌ Error al registrar: " . $conn->error;
        }

        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrarse - Adopt Me</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="footer.css">
    <link rel="stylesheet" href="auth.css">
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
                        <li><a href="protectoras.php">Protectoras</a></li>
                        <li><a href="map.php">Mapa</a></li>
                        <li><a href="blog.php">Blog</a></li>
                    </ul>
                    <div class="nav-buttons">
                        <a href="login.php" class="btn btn-outline">Iniciar sesión</a>
                        <a href="register.php" class="btn btn-primary active">Registrarse</a>
                    </div>
                </nav>
            </div>
        </div>
    </header>

    <!-- Contenido principal -->
    <main class="auth-main">
        <div class="auth-container">
            <!-- Lado izquierdo - Formulario -->
            <div class="auth-form-section">
                <div class="auth-form-container">
                    <h1>¡Únete a Adopt Me!</h1>
                    
                    <!-- Formulario -->
                    <form class="auth-form" action="register.php" method="POST">
                        <!-- Mostrar error -->
                        <?php if (!empty($error)): ?>
                            <div class="error-message">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <input type="text" name="firstName" placeholder="Nombre" required>
                        </div>
                        
                        <div class="form-group">
                            <input type="text" name="lastName" placeholder="Apellidos" required>
                        </div>
                        
                        <div class="form-group">
                            <input type="email" name="email" placeholder="Email" required>
                        </div>
                        
                        <div class="form-group">
                            <input type="password" name="password" placeholder="Contraseña" required>
                        </div>
                        
                        <div class="form-group">
                            <input type="password" name="confirmPassword" placeholder="Confirmar contraseña" required>
                        </div>

                        <!-- Eliminamos selección de tipo -->
                        <input type="hidden" name="tipo" value="visitante">

                        <!-- Checkboxes -->
                        <div class="checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="terms" required>
                                <span class="checkmark"></span>
                                He leído y acepto las <a href="#" class="link">condiciones de uso</a> y <a href="#" class="link">política de privacidad</a>.
                            </label>
                        </div>
                        
                        <button type="submit" class="btn-auth">Crear cuenta</button>
                        
                        <p class="auth-switch">
                            ¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a>
                        </p>
                    </form>
                </div>
            </div>
            
            <!-- Lado derecho - Imagen -->
            <div class="auth-image-section">
                <img src="Gato.jpg" alt="Mascotas felices" class="auth-image">
            </div>
        </div>
    </main>

    <script>
        // Menú móvil
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
