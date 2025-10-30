<?php
session_start();
require 'conexion.php'; 

$error_critico = false;
$mensaje = "";

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}
$usuario_id = $_SESSION['usuario_id'];
$publicacion_id = $_GET['id'] ?? null;

if (!$publicacion_id) {
    die("Error: ID de publicación no proporcionado.");
}

// Función para obtener los datos de la publicación
function obtenerDatosPublicacion($conn, $publicacion_id, $usuario_id) {
    // A. Traer datos principales
    $stmt = $conn->prepare("SELECT * FROM publicaciones WHERE id=? AND usuario_id=?");
    $stmt->bind_param("ii", $publicacion_id, $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        die("Publicación no encontrada o no tienes permiso para editarla.");
    }
    $publicacion = $result->fetch_assoc();
    $stmt->close();
    
    // B. Traer IMAGEN PRINCIPAL (desde la nueva tabla)
    $stmt = $conn->prepare("SELECT archivo FROM imagen_principal WHERE publicacion_id=?");
    $stmt->bind_param("i", $publicacion_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $imagen_principal = $result->fetch_assoc()['archivo'] ?? null;
    $stmt->close();

    // C. Traer imágenes extras
    $imagenes_extra = [];
    $stmt = $conn->prepare("SELECT archivo FROM imagenes WHERE publicacion_id=?");
    $stmt->bind_param("i", $publicacion_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()) {
        $imagenes_extra[] = $row['archivo'];
    }
    $stmt->close();

    // DEVOLVEMOS los datos con la imagen principal separada
    return [
        'publicacion' => $publicacion, 
        'imagen_principal' => $imagen_principal, 
        'imagenes_extra' => $imagenes_extra
    ];
}

// Obtener datos iniciales
$datos = obtenerDatosPublicacion($conn, $publicacion_id, $usuario_id);
$publicacion = $datos['publicacion'];
$imagen_principal = $datos['imagen_principal'];
$imagenes_extra = $datos['imagenes_extra'];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (empty($_POST) && $_SERVER['CONTENT_LENGTH'] > 0) {
        $mensaje = "Error Crítico: El tamaño de los archivos excede el límite. Revisa php.ini.";
        $error_critico = true;
    }
    
    if (!$error_critico) {
        // 2. Obtener datos (usando null-coalescing para evitar errores si falló el POST)
        $nombre_mascota = $_POST['nombre_mascota'] ?? $publicacion['nombre_mascota'];
        $especie = $_POST['especie'] ?? $publicacion['especie'];
        $raza = $_POST['raza'] ?? $publicacion['raza'];
        $edad = $_POST['edad'] ?? $publicacion['edad'];
        $peso = $_POST['peso'] ?? $publicacion['peso'];
        $tamaño = $_POST['tamaño'] ?? $publicacion['tamaño'];
        $sexo = $_POST['sexo'] ?? $publicacion['sexo'];
        $descripcion = $_POST['descripcion'] ?? $publicacion['descripcion'];
        $ubicacion = $_POST['ubicacion'] ?? $publicacion['ubicacion'];
        $desparasitado = isset($_POST['desparasitado']) ? 1 : 0;
        $esterilizado = isset($_POST['esterilizado']) ? 1 : 0;
        $vacunado = isset($_POST['vacunado']) ? 1 : 0;

        // 3. Manejo de IMAGEN PRINCIPAL (NUEVA LÓGICA, usando INSERCIÓN como las extras)
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $nombreTmp = $_FILES['imagen']['tmp_name'];
            $nombreArchivoBase = basename($_FILES['imagen']['name']);
            $nombreArchivoNuevo = 'principal_' . $usuario_id . '_' . time() . '_' . $nombreArchivoBase; 
            $destino = 'uploads/' . $nombreArchivoNuevo;

            if (move_uploaded_file($nombreTmp, $destino)) {
                
                // 1. Borrar registro anterior (e imagen física si es necesario)
                // Usamos REPLACE INTO o DELETE/INSERT. Usaremos DELETE/INSERT para ser explícitos.
                $conn->query("DELETE FROM imagen_principal WHERE publicacion_id = $publicacion_id");

                // 2. Insertar nueva imagen
                $stmt_img = $conn->prepare("INSERT INTO imagen_principal (publicacion_id, archivo) VALUES (?, ?)");
                $stmt_img->bind_param("is", $publicacion_id, $nombreArchivoNuevo);
                if (!$stmt_img->execute()) {
                    $mensaje = "Error al insertar la imagen principal: " . $stmt_img->error;
                    $error_critico = true;
                }
                $stmt_img->close();

                // Opcional: Borrar el archivo anterior del servidor
                // if ($imagen_principal && file_exists('uploads/' . $imagen_principal)) {
                //     unlink('uploads/' . $imagen_principal);
                // }
                
            } else {
                $mensaje = "Error al mover la nueva imagen principal al servidor (permisos/tamaño).";
                $error_critico = true;
            }
        }

        // 4. Actualizar publicación de datos de texto (el UPDATE es más simple ahora)
        if (!$error_critico) {
            $stmt = $conn->prepare("
                UPDATE publicaciones SET
                nombre_mascota=?, especie=?, raza=?, edad=?, peso=?, tamaño=?, sexo=?, descripcion=?, ubicacion=?, desparasitado=?, esterilizado=?, vacunado=?
                WHERE id=? AND usuario_id=?
            ");
            
            // ¡Ya no se incluye la variable de imagen aquí!
            $stmt->bind_param(
                "sssssssssiiiii",
                $nombre_mascota, $especie, $raza, $edad, $peso, $tamaño, $sexo, $descripcion, $ubicacion,
                $desparasitado, $esterilizado, $vacunado,
                $publicacion_id, $usuario_id
            );

            if (!$stmt->execute()) {
                $mensaje = "Error al actualizar la base de datos (datos de texto): " . $stmt->error;
                $error_critico = true;
            }
            $stmt->close();
        }

        // 5. Manejo de Imágenes extras (Se mantiene el código anterior, ¡que funcionaba!)
        if (isset($_FILES['imagenes']) && !empty(array_filter($_FILES['imagenes']['name']))) {
            
            $nuevos_archivos_subidos = [];
            $archivos = $_FILES['imagenes'];
            $count = count($archivos['name']);
            
            for ($i = 0; $i < $count && $i < 5; $i++) {
                if ($archivos['error'][$i] === UPLOAD_ERR_OK) {
                    $tmp_name = $archivos['tmp_name'][$i];
                    $nombreArchivoExtra = basename($archivos['name'][$i]);
                    $nombreArchivoExtra = 'extra_' . $usuario_id . '_' . $i . '_' . time() . '_' . $nombreArchivoExtra;
                    $destino = 'uploads/' . $nombreArchivoExtra;

                    if (move_uploaded_file($tmp_name, $destino)) {
                        $nuevos_archivos_subidos[] = $nombreArchivoExtra;
                    }
                }
            }
            
            if (!empty($nuevos_archivos_subidos)) {
                $conn->query("DELETE FROM imagenes WHERE publicacion_id = $publicacion_id");
                
                foreach ($nuevos_archivos_subidos as $filename) {
                    $stmt = $conn->prepare("INSERT INTO imagenes (publicacion_id, archivo) VALUES (?, ?)");
                    $stmt->bind_param("is", $publicacion_id, $filename);
                    $stmt->execute();
                    $stmt->close();
                }
            } else {
                $mensaje .= ($mensaje ? " | " : "") . "Atención: La subida de imágenes extras falló, se mantuvieron las anteriores.";
            }
        }
        
        if (!$error_critico && $mensaje === "") {
             $mensaje = "Publicación actualizada correctamente. ✅";
        }
    }

    // 6. Refrescar datos para la vista
    $datos = obtenerDatosPublicacion($conn, $publicacion_id, $usuario_id);
    $publicacion = $datos['publicacion'];
    $imagen_principal = $datos['imagen_principal'];
    $imagenes_extra = $datos['imagenes_extra'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar Publicación - Adopt Me</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="footer.css">
    <link rel="stylesheet" href="editar_publicacion.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<header class="navbar">
        <div class=".container">
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

<main>
<div class="container">
<h1>Editar Publicación</h1>
<?php if($mensaje): ?>
<p class="mensaje <?php echo $error_critico ? 'error' : 'success'; ?>"><?php echo htmlspecialchars($mensaje); ?></p>
<?php endif; ?>

<form action="" method="POST" enctype="multipart/form-data">
    <label>Nombre de la mascota</label>
    <input type="text" name="nombre_mascota" value="<?php echo htmlspecialchars($publicacion['nombre_mascota']); ?>" required>

    <label>Especie</label>
    <select name="especie" id="especie" required>
        <option value="">Seleccione especie</option>
        <?php foreach(["Perro","Gato","Ave","Otro"] as $esp): ?>
            <option value="<?php echo $esp; ?>" <?php if($publicacion['especie']==$esp) echo "selected"; ?>><?php echo $esp; ?></option>
        <?php endforeach; ?>
    </select>

    <label>Raza</label>
    <select name="raza" id="raza" required>
        <option value="<?php echo htmlspecialchars($publicacion['raza']); ?>"><?php echo htmlspecialchars($publicacion['raza']); ?></option>
    </select>

    <label>Edad</label>
    <input type="text" name="edad" value="<?php echo htmlspecialchars($publicacion['edad']); ?>">

    <label>Peso</label>
    <input type="text" name="peso" value="<?php echo htmlspecialchars($publicacion['peso']); ?>">

    <label>Tamaño</label>
    <select name="tamaño">
        <?php foreach(["Pequeño","Mediano","Grande"] as $t): ?>
            <option value="<?php echo $t; ?>" <?php if($publicacion['tamaño']==$t) echo "selected"; ?>><?php echo $t; ?></option>
        <?php endforeach; ?>
    </select>

    <label>Sexo</label>
    <select name="sexo">
        <?php foreach(["Macho","Hembra"] as $s): ?>
            <option value="<?php echo $s; ?>" <?php if($publicacion['sexo']==$s) echo "selected"; ?>><?php echo $s; ?></option>
        <?php endforeach; ?>
    </select>

    <label>Ubicación</label>
    <select name="ubicacion" required>
        <?php foreach(["Salta Capital","Orán","Cafayate","Otro"] as $u): ?>
            <option value="<?php echo $u; ?>" <?php if($publicacion['ubicacion']==$u) echo "selected"; ?>><?php echo $u; ?></option>
        <?php endforeach; ?>
    </select>

    <label>Descripción</label>
    <textarea name="descripcion" rows="4"><?php echo htmlspecialchars($publicacion['descripcion']); ?></textarea>

    <label><input type="checkbox" name="desparasitado" <?php if($publicacion['desparasitado']) echo "checked"; ?>> Desparasitado</label>
    <label><input type="checkbox" name="esterilizado" <?php if($publicacion['esterilizado']) echo "checked"; ?>> Esterilizado</label>
    <label><input type="checkbox" name="vacunado" <?php if($publicacion['vacunado']) echo "checked"; ?>> Vacunado</label>
    
    <hr>
    
    <h2>Editar Imágenes</h2>

    <label>Imagen principal actual</label>
    <?php if($imagen_principal): ?>
        <div class="imagenes-actuales">
            <img src="uploads/<?php echo htmlspecialchars($imagen_principal); ?>" alt="Principal">
        </div>
    <?php else: ?>
        <p>No hay imagen principal actual.</p>
    <?php endif; ?>
    <input type="file" name="imagen" accept="image/*">
    <small>Sube una nueva imagen principal para reemplazar la anterior.</small>
    
    <hr>

    <label>Imágenes extras actuales</label>
    <?php if(!empty($imagenes_extra)): ?>
        <div class="imagenes-actuales">
            <?php foreach($imagenes_extra as $img): ?>
                <img src="uploads/<?php echo htmlspecialchars($img); ?>" alt="Extra">
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No hay imágenes extras actuales.</p>
    <?php endif; ?>
    
    <input type="file" name="imagenes[]" accept="image/*" multiple>
    <small>Sube hasta 5 nuevas imágenes extras. **Esto reemplazará todas las imágenes extras actuales.**</small>

    <button type="submit" class="btn">Actualizar publicación</button>
</form>
</div>

<script>
// ... (Se mantiene el script de razas del código anterior) ...
const razas = {
    "Perro": ["Labrador", "Bulldog", "Pastor Alemán", "Otro"],
    "Gato": ["Siames", "Persa", "Maine Coon", "Otro"],
    "Ave": ["Canario", "Loro", "Periquito", "Otro"],
    "Otro": ["Otro"]
};
const especieSelect = document.getElementById('especie');
const razaSelect = document.getElementById('raza');

function inicializarRazas() {
    const especieActual = especieSelect.value;
    razaSelect.innerHTML = '';
    const razaActual = "<?php echo htmlspecialchars($publicacion['raza']); ?>";

    if(razas[especieActual]) {
        razas[especieActual].forEach(r => {
            const option = document.createElement('option');
            option.value = r;
            option.textContent = r;
            if (r === razaActual) {
                option.selected = true;
            }
            razaSelect.appendChild(option);
        });
    } else {
        const option = document.createElement('option');
        option.value = razaActual;
        option.textContent = razaActual;
        option.selected = true;
        razaSelect.appendChild(option);
    }
}

document.addEventListener('DOMContentLoaded', inicializarRazas);

especieSelect.addEventListener('change', function() {
    const especie = this.value;
    razaSelect.innerHTML = '';
    if(razas[especie]) {
        razas[especie].forEach(r => {
            const option = document.createElement('option');
            option.value = r;
            option.textContent = r;
            razaSelect.appendChild(option);
        });
    } else {
        const option = document.createElement('option');
        option.value = '';
        option.textContent = 'Seleccione especie primero';
        razaSelect.appendChild(option);
    }
});
</script>
</main>



</body>
</html>