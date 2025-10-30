<?php
// login.php
session_start();
require 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = "Faltan datos.";
    } else {
        // Incluimos el tipo de usuario
        $stmt = $conn->prepare("SELECT id, nombre, apellido, password, tipo FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res && $res->num_rows === 1) {
            $user = $res->fetch_assoc();

            // Visitante no tiene contraseña
            if ($user['tipo'] === 'visitante') {
                $_SESSION['usuario_id'] = $user['id'];
                $_SESSION['usuario_nombre'] = $user['nombre'];
                $_SESSION['usuario_tipo'] = $user['tipo'];

                header("Location: index.php");
                exit;
            }

            // Para adoptante o protectora
            if (password_verify($password, $user['password'])) {
                $_SESSION['usuario_id'] = $user['id'];
                $_SESSION['usuario_nombre'] = $user['nombre'];
                $_SESSION['usuario_tipo'] = $user['tipo'];

                header("Location: index.php");
                exit;
            } else {
                $error = "Contraseña incorrecta.";
            }
        } else {
            $error = "No existe una cuenta con ese correo.";
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
    <title>Iniciar Sesión - Adopt Me</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="footer.css">
    <link rel="stylesheet" href="auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .error-message {
            background: #f8d7da;
            color: #842029;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 14px;
            text-align: center;
        }
    </style>
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
                        <a href="login.php" class="btn btn-outline active">Iniciar sesión</a>
                        <a href="register.php" class="btn btn-primary">Registrarse</a>
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
            <h1>Iniciar Sesión</h1>
            <p class="auth-subtitle">¡Nos alegra tenerte de vuelta!</p>

            <!-- Formulario -->
            <form class="auth-form" action="login.php" method="POST">
              <p class="form-description">
                Si ya tienes cuenta en Adopt Me puedes iniciar sesión con los mismos datos
              </p>

              <!-- Cartel de error -->
              <?php if (!empty($error)): ?>
                <div class="error-message">
                  <?php echo htmlspecialchars($error); ?>
                </div>
              <?php endif; ?>

              <div class="form-group">
                <input type="email" name="email" placeholder="Email" required>
              </div>

              <div class="form-group">
                <input type="password" name="password" placeholder="Contraseña">
              </div>

              <div class="form-links">
                <a href="#" class="forgot-password">¿Has olvidado la contraseña?</a>
              </div>

              <button type="submit" class="btn-auth">Iniciar sesión</button>

              <p class="auth-switch">
                ¿Aún no tienes cuenta? <a href="register.php">¡Únete a Adopt Me!</a>
              </p>
            </form>
          </div>
        </div>

        <!-- Lado derecho - Imagen -->
        <div class="auth-image-section">
          <img src="Mascotas.png" alt="Mascotas felices" class="auth-image">
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
