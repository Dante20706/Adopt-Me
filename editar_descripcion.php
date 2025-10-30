<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$descripcion = trim($_POST['descripcion'] ?? '');

$stmt = $conn->prepare("UPDATE usuarios SET descripcion = ? WHERE id = ?");
$stmt->bind_param("si", $descripcion, $usuario_id);

if ($stmt->execute()) {
    $_SESSION['success_perfil'] = "Descripción actualizada correctamente.";
} else {
    $_SESSION['error_perfil'] = "Error al actualizar la descripción.";
}

$stmt->close();
header("Location: profile.php");
exit;
