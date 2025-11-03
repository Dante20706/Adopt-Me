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
    <link rel="stylesheet" href="blog.css">
</head>
<body>
    <div class="container nueva-publicacion">
        <h1>Crear nueva publicación</h1>
        <form method="POST" enctype="multipart/form-data" class="form-blog">
            <label>Título</label>
            <input type="text" name="titulo" required>

            <label>Categoría</label>
            <select name="categoria" required>
                <option value="Historias">Historias</option>
                <option value="Noticias">Noticias</option>
                <option value="Consejos">Consejos</option>
                <option value="Educación">Educación</option>
                <option value="Otros">Otros</option>
            </select>

            <label>Contenido</label>
            <textarea name="contenido" rows="6" required></textarea>

            <label>Imagen</label>
            <input type="file" name="imagen" accept="image/*">

            <button type="submit" class="btn btn-primary">Publicar</button>
        </form>
    </div>
</body>
</html>
