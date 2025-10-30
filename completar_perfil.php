<?php
session_start();
require 'conexion.php';

// Si no está logueado, lo mandamos al login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Primero obtenemos los datos actuales del usuario
$sql = "SELECT * FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

// Si ya no es visitante, no tiene sentido estar acá
if ($usuario['tipo'] !== 'visitante') {
    header("Location: index.php");
    exit;
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = $_POST['tipo'];

    if ($tipo === 'adoptante') {
        $nombre = $_POST['nombre'];
        $apellido = $_POST['apellido'];
        $edad = $_POST['edad'];
        $dni = $_POST['dni'];
        $provincia = $_POST['provincia'];
        $departamento = $_POST['departamento'];
        $direccion = $_POST['direccion'];
        $telefono = $_POST['telefono'];

        if ($nombre && $apellido && $edad && $dni && $provincia && $departamento && $direccion && $telefono) {
            $sql = "UPDATE usuarios 
                    SET tipo='adoptante', nombre=?, apellido=?, edad=?, dni=?, provincia=?, departamento=?, direccion=?, telefono=? 
                    WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssisssssi", $nombre, $apellido, $edad, $dni, $provincia, $departamento, $direccion, $telefono, $usuario_id);
            $stmt->execute();

            header("Location: index.php");
            exit;
        } else {
            $error = "Todos los campos son obligatorios para ser adoptante.";
        }
    }

    if ($tipo === 'protectora') {
        $nombre_protectora = $_POST['nombre_protectora'];
        $contacto_responsable = $_POST['contacto_responsable'];
        $pagina_web = $_POST['pagina_web'];
        $telefono = $_POST['telefono'];
        $provincia = $_POST['provincia'];
        $departamento = $_POST['departamento'];
        $direccion = $_POST['direccion'];

        if ($nombre_protectora && $contacto_responsable && $telefono && $provincia && $departamento && $direccion) {
            $sql = "UPDATE usuarios 
                    SET tipo='protectora', nombre_protectora=?, contacto_responsable=?, pagina_web=?, telefono=?, provincia=?, departamento=?, direccion=? 
                    WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssi", $nombre_protectora, $contacto_responsable, $pagina_web, $telefono, $provincia, $departamento, $direccion, $usuario_id);
            $stmt->execute();

            header("Location: index.php");
            exit;
        } else {
            $error = "Todos los campos son obligatorios para ser protectora.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Completar perfil</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Completar perfil</h1>

    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <h2>Registrarme como Adoptante</h2>
    <form method="POST">
        <input type="hidden" name="tipo" value="adoptante">
        <label>Nombre: <input type="text" name="nombre" required></label><br>
        <label>Apellido: <input type="text" name="apellido" required></label><br>
        <label>Edad: <input type="number" name="edad" required></label><br>
        <label>DNI: <input type="text" name="dni" required></label><br>
        <label>Provincia: <input type="text" name="provincia" required></label><br>
        <label>Departamento: <input type="text" name="departamento" required></label><br>
        <label>Dirección: <input type="text" name="direccion" required></label><br>
        <label>Teléfono: <input type="text" name="telefono" required></label><br>
        <button type="submit">Guardar como Adoptante</button>
    </form>

    <hr>

    <h2>Registrarme como Protectora</h2>
    <form method="POST">
        <input type="hidden" name="tipo" value="protectora">
        <label>Nombre de la protectora: <input type="text" name="nombre_protectora" required></label><br>
        <label>Responsable: <input type="text" name="contacto_responsable" required></label><br>
        <label>Página web: <input type="url" name="pagina_web"></label><br>
        <label>Teléfono: <input type="text" name="telefono" required></label><br>
        <label>Provincia: <input type="text" name="provincia" required></label><br>
        <label>Departamento: <input type="text" name="departamento" required></label><br>
        <label>Dirección: <input type="text" name="direccion" required></label><br>
        <button type="submit">Guardar como Protectora</button>
    </form>
</body>
</html>
