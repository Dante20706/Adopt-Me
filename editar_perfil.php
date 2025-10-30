<?php
session_start();
require 'conexion.php';

// Verificar login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Validar que lleguen los datos por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $provincia = trim($_POST['provincia'] ?? '');
    $departamento = trim($_POST['departamento'] ?? '');

    if ($nombre === '' || $apellido === '' || $email === '') {
        $_SESSION['error_perfil'] = "Nombre, Apellido y Email son obligatorios.";
        header("Location: profile.php");
        exit;
    }

    // Validar que no exista otro usuario con el mismo email
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $usuario_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $_SESSION['error_perfil'] = "El email ya está en uso por otro usuario.";
        $stmt->close();
        header("Location: profile.php");
        exit;
    }
    $stmt->close();

    // Actualizar datos del usuario
    $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, apellido = ?, email = ?, telefono = ?, provincia = ?, departamento = ? WHERE id = ?");
    $stmt->bind_param("ssssssi", $nombre, $apellido, $email, $telefono, $provincia, $departamento, $usuario_id);

    if ($stmt->execute()) {
        $_SESSION['usuario_nombre'] = $nombre; // actualizar la sesión
        $_SESSION['success_perfil'] = "Tus datos se actualizaron correctamente.";
    } else {
        $_SESSION['error_perfil'] = "Error al actualizar tus datos: " . $stmt->error;
    }
    $stmt->close();
}

header("Location: profile.php");
exit;
