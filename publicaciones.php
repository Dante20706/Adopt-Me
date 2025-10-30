<?php
session_start();
require 'conexion.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    die("Debes iniciar sesión para publicar.");
}

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_mascota = $_POST['nombre_mascota'];
    $especie = $_POST['especie'];
    $raza = $_POST['raza'];
    $edad = $_POST['edad'];
    $peso = $_POST['peso'];
    $tamaño = $_POST['tamaño'];
    $sexo = $_POST['sexo'];
    $descripcion = $_POST['descripcion'];
    $ubicacion = $_POST['ubicacion'];
    $desparasitado = isset($_POST['desparasitado']) ? 1 : 0;
    $esterilizado = isset($_POST['esterilizado']) ? 1 : 0;
    $vacunado = isset($_POST['vacunado']) ? 1 : 0;
    $imagen = "";

    // Subir imagen principal
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $nombreTmp = $_FILES['imagen']['tmp_name'];
        $nombreArchivo = basename($_FILES['imagen']['name']);
        $nombreArchivo = 'usuario' . $_SESSION['usuario_id'] . '_' . $nombreArchivo;
        $destino = 'uploads/' . $nombreArchivo;

        if (move_uploaded_file($nombreTmp, $destino)) {
            $imagen = $nombreArchivo;
        } else {
            $mensaje = "Error al subir la imagen.";
        }
    }

    // Insertar la publicación
    $stmt = $conn->prepare("
        INSERT INTO publicaciones 
        (usuario_id, nombre_mascota, especie, raza, edad, peso, tamaño, sexo, descripcion, ubicacion, desparasitado, esterilizado, vacunado, imagen, fecha_publicacion)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param(
        "issssssssssiis",
        $_SESSION['usuario_id'], $nombre_mascota, $especie, $raza, $edad, $peso, $tamaño, $sexo, $descripcion, $ubicacion, $desparasitado, $esterilizado, $vacunado, $imagen
    );

    if ($stmt->execute()) {
        $publicacion_id = $stmt->insert_id;

        // Subir imágenes adicionales (máx 5)
        if (isset($_FILES['imagenes'])) {
            $archivos = $_FILES['imagenes'];
            $count = count($archivos['name']);
            for ($i = 0; $i < $count && $i < 5; $i++) {
                if ($archivos['error'][$i] === UPLOAD_ERR_OK) {
                    $tmp_name = $archivos['tmp_name'][$i];
                    $filename = 'usuario' . $_SESSION['usuario_id'] . '_extra_' . basename($archivos['name'][$i]);
                    $destino = 'uploads/' . $filename;

                    if (move_uploaded_file($tmp_name, $destino)) {
                        $stmt_img = $conn->prepare("INSERT INTO imagenes (publicacion_id, archivo) VALUES (?, ?)");
                        $stmt_img->bind_param("is", $publicacion_id, $filename);
                        $stmt_img->execute();
                        $stmt_img->close();
                    }
                }
            }
        }

        // Redirigir al index después de publicar
        header("Location: index.php");
        exit;
    } else {
        $mensaje = "Error al guardar la publicación: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Publicar mascota - Adopt Me</title>
    <style>
        body { font-family: Arial; background-color: #fef8f0; padding: 2rem; }
        .container { max-width: 600px; margin: auto; background: #fdfd96; padding: 2rem; border-radius: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h1 { color: #ff9d3d; text-align: center; }
        form { display: flex; flex-direction: column; gap: 1rem; }
        label { font-weight: bold; }
        input, select, textarea { padding: 0.5rem; border-radius: 0.5rem; border: 1px solid #ccc; width: 100%; }
        input[type="checkbox"] { width: auto; }
        .btn { background-color: #ff9d3d; color: #fff; border: none; padding: 1rem; border-radius: 1rem; cursor: pointer; font-weight: bold; }
        .btn:hover { background-color: #e68a2e; }
        .mensaje { text-align:center; color:green; font-weight:bold; }
    </style>
</head>
<body>
    
    <div class="container">
        <h1>Publicar Mascota</h1>
        <?php if($mensaje): ?>
            <p class="mensaje"><?php echo htmlspecialchars($mensaje); ?></p>
        <?php endif; ?>
        <form action="" method="POST" enctype="multipart/form-data">
            <label>Nombre de la mascota</label>
            <input type="text" name="nombre_mascota" required>

            <label>Especie</label>
            <select name="especie" id="especie" required>
                <option value="">Seleccione especie</option>
                <option value="Perro">Perro</option>
                <option value="Gato">Gato</option>
                <option value="Ave">Ave</option>
                <option value="Otro">Otro</option>
            </select>

            <label>Raza</label>
            <select name="raza" id="raza" required>
                <option value="">Seleccione especie primero</option>
            </select>

            <label>Edad</label>
            <input type="text" name="edad">

            <label>Peso</label>
            <input type="text" name="peso">

            <label>Tamaño</label>
            <select name="tamaño">
                <option value="Pequeño">Pequeño</option>
                <option value="Mediano">Mediano</option>
                <option value="Grande">Grande</option>
            </select>

            <label>Sexo</label>
            <select name="sexo">
                <option value="Macho">Macho</option>
                <option value="Hembra">Hembra</option>
            </select>

            <label>Ubicación</label>
            <select name="ubicacion" required>
                <option value="">Seleccione ubicación</option>
                <option value="Salta Capital">Salta Capital</option>
                <option value="Orán">Orán</option>
                <option value="Cafayate">Cafayate</option>
                <option value="Otro">Otro</option>
            </select>

            <label>Descripción</label>
            <textarea name="descripcion" rows="4"></textarea>

            <label><input type="checkbox" name="desparasitado"> Desparasitado</label>
            <label><input type="checkbox" name="esterilizado"> Esterilizado</label>
            <label><input type="checkbox" name="vacunado"> Vacunado</label>

            <label>Imagen principal</label>
            <input type="file" name="imagen" accept="image/*">

            <label>Otras imágenes (máx 5)</label>
            <input type="file" name="imagenes[]" accept="image/*" multiple>

            <button type="submit" class="btn">Publicar</button>
        </form>
    </div>

    <script>
        const razas = {
            "Perro": ["Labrador", "Bulldog", "Pastor Alemán", "Otro"],
            "Gato": ["Siames", "Persa", "Maine Coon", "Otro"],
            "Ave": ["Canario", "Loro", "Periquito", "Otro"],
            "Otro": ["Otro"]
        };

        const especieSelect = document.getElementById('especie');
        const razaSelect = document.getElementById('raza');

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
</body>
</html>
