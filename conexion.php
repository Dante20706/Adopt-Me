<?php
// conexion.php
$servername = "localhost:3307";
$username   = "root";
$password   = "";
$dbname     = "prueba"; // ajustá si tu DB se llama distinto

// Crear la conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar si hay errores
if ($conn->connect_error) {
    die("❌ Conexión fallida: " . $conn->connect_error);
}
?>
