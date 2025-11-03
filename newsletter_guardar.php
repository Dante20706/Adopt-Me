<?php
require 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Correo inválido'); window.history.back();</script>";
        exit;
    }

    $existe = $conn->query("SELECT id FROM newsletter WHERE email='$email'");
    if ($existe->num_rows === 0) {
        $conn->query("INSERT INTO newsletter (email) VALUES ('$email')");
        echo "<script>alert('¡Gracias por suscribirte!'); window.location='blog.php';</script>";
    } else {
        echo "<script>alert('Ya estás suscrito con ese correo.'); window.location='blog.php';</script>";
    }
}
?>
