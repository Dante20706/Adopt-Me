<?php
require_once "conexion.php";

if ($_POST['accion'] === 'agregar_ubicacion') {
    $nombre = $_POST['nombre'];
    $tipo = $_POST['tipo'];
    $lat = $_POST['latitud'];
    $lng = $_POST['longitud'];
    $dire = $_POST['dirreccion'];
    $tel = $_POST['telefono'];
    $horario = $_POST['horario'];
    $web = $_POST['web'];
    
    $conn->query("INSERT INTO ubicaciones (nombre, tipo, direccion, telefono, latitud, longitud, horario, sitio_web)
                VALUES ('$nombre', '$tipo', $lat, $lng, '$dire', '$tel', '$horario', '$web')");
}

if ()
header("Location: admin.php?tabla=ubicaciones");
