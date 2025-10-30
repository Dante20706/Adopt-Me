<?php
require_once "conexion.php";

if ($_POST['accion'] === 'agregar_ubicacion') {
    $nombre = $_POST['nombre'];
    $tipo = $_POST['tipo'];
    $lat = $_POST['latitud'];
    $lng = $_POST['longitud'];
    $desc = $_POST['descripcion'];
    $horario = $_POST['horario'];
    
    $conn->query("INSERT INTO ubicaciones (nombre, tipo, latitud, longitud, horario)
                  VALUES ('$nombre', '$tipo', $lat, $lng, '$horario')");
}

header("Location: admin.php?tabla=ubicaciones");
