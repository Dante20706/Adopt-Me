<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
    $nombreArchivo = uniqid("perfil_") . "." . pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
    $rutaDestino = "uploads/" . $nombreArchivo;

    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaDestino)) {

        // Buscar imagen anterior
        $stmt = $conn->prepare("SELECT archivo FROM imagen_perfil WHERE usuario_id = ?");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            // Borrar imagen vieja del servidor
            if (file_exists("uploads/" . $row['archivo'])) {
                unlink("uploads/" . $row['archivo']);
            }

            // Actualizar
            $stmt = $conn->prepare("UPDATE imagen_perfil SET archivo = ? WHERE usuario_id = ?");
            $stmt->bind_param("si", $nombreArchivo, $usuario_id);
        } else {
            // Insertar nueva
            $stmt = $conn->prepare("INSERT INTO imagen_perfil (usuario_id, archivo) VALUES (?, ?)");
            $stmt->bind_param("is", $usuario_id, $nombreArchivo);
        }

        $stmt->execute();
        $_SESSION['success_perfil'] = "Imagen de perfil actualizada correctamente.";
    } else {
        $_SESSION['error_perfil'] = "Error al subir la imagen.";
    }
} else {
    $_SESSION['error_perfil'] = "No se seleccionó ninguna imagen.";
}

header("Location: profile.php");
exit;
?>
