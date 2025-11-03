<?php
require_once "conexion.php";
session_start();
if (!isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'admin') {
    http_response_code(403);
    die('Acceso denegado');
}

$accion = $_POST['accion'] ?? '';

if ($accion === 'agregar_ubicacion') {
    $nombre = $_POST['nombre'] ?? '';
    $tipo = $_POST['tipo'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $horario = $_POST['horario'] ?? '';
    $lat = $_POST['latitud'] ?? '';
    $lng = $_POST['longitud'] ?? '';
    $web = $_POST['sitio_web'] ?? '';

    // validaciones básicas
    if ($nombre === '' || $tipo === '' || $lat === '' || $lng === '') {
        $_SESSION['admin_msg'] = 'Faltan campos obligatorios';
        header("Location: admin.php?tabla=ubicaciones");
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO ubicaciones (nombre, tipo, direccion, telefono, latitud, longitud, horario, sitio_web) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssddss", $nombre, $tipo, $direccion, $telefono, $lat, $lng, $horario, $web);
    $ok = $stmt->execute();
    $stmt->close();

    $_SESSION['admin_msg'] = $ok ? 'Ubicación agregada' : ('Error: '.$conn->error);
    header("Location: admin.php?tabla=ubicaciones");
    exit;
}

if ($accion === 'eliminar') {
    $tabla = $_POST['tabla'] ?? '';
    $id = $_POST['id'] ?? '';
    if (!in_array($tabla, ['usuarios','publicaciones','blog_posts','newsletter','ubicaciones'])) {
        $_SESSION['admin_msg'] = 'Tabla no permitida';
        header("Location: admin.php");
        exit;
    }
    // seguridad: id entero
    $id = intval($id);
    $stmt = $conn->prepare("DELETE FROM `$tabla` WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $ok = $stmt->affected_rows >= 0;
    $stmt->close();
    $_SESSION['admin_msg'] = $ok ? 'Registro eliminado' : 'Error al eliminar';
    header("Location: admin.php?tabla=" . urlencode($tabla));
    exit;
}

if ($accion === 'editar') {
    $tabla = $_POST['tabla'] ?? '';
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0 || !in_array($tabla, ['usuarios','publicaciones','imagenes','imagen_principal','imagen_perfil','ubicaciones'])) {
        $_SESSION['admin_msg'] = 'Parámetros inválidos';
        header("Location: admin.php?tabla=" . urlencode($tabla));
        exit;
    }

    // construir SET dinámico desde campos col_*
    $sets = [];
    $types = '';
    $values = [];
    foreach ($_POST as $k=>$v) {
        if (strpos($k, 'col_') !== 0) continue;
        $col = substr($k,4);
        if (in_array($col, ['id','password','descripcion','fecha_registro'])) continue; // no editar estas
        $sets[] = "`$col` = ?";
        $types .= 's';
        $values[] = $v;
    }

    if (empty($sets)) {
        $_SESSION['admin_msg'] = 'Nada para actualizar';
        header("Location: admin.php?tabla=" . urlencode($tabla));
        exit;
    }

    $sql = "UPDATE `$tabla` SET " . implode(", ", $sets) . " WHERE id = ?";
    $types .= 'i';
    $values[] = $id;

    $stmt = $conn->prepare($sql);
    // bind dinámico
    $bind_names[] = $types;
    for ($i=0;$i<count($values);$i++) $bind_name = 'bind'.$i;
    // usar call_user_func_array
    $a_params = [];
    $a_params[] = & $types;
    for ($i=0;$i<count($values);$i++) {
        $a_params[] = & $values[$i];
    }
    // mysqli_stmt::bind_param requires references
    $stmt->bind_param(...$a_params);
    $ok = $stmt->execute();
    $stmt->close();

    $_SESSION['admin_msg'] = $ok ? 'Registro actualizado' : 'Error: '.$conn->error;
    header("Location: admin.php?tabla=" . urlencode($tabla));
    exit;
}

if ($accion === 'agregar_generico') {
    $tabla = $_POST['tabla'] ?? '';
    if (!in_array($tabla, ['usuarios','publicaciones','imagenes','imagen_principal','imagen_perfil','ubicaciones'])) {
        $_SESSION['admin_msg'] = 'Tabla no permitida';
        header("Location: admin.php");
        exit;
    }
    // insert con campos no nulos pasados col_* o campos directos
    $cols = [];
    $vals = [];
    $types = '';
    $params = [];
    foreach ($_POST as $k=>$v) {
        if ($k === 'accion' || $k === 'tabla') continue;
        if (strpos($k, 'col_') === 0) {
            $col = substr($k,4);
            if (in_array($col, ['id','password','descripcion','fecha_registro'])) continue;
            $cols[] = "`$col`";
            $vals[] = '?';
            $types .= 's';
            $params[] = $v;
        }
    }
    if (empty($cols)) {
        $_SESSION['admin_msg'] = 'No hay campos para insertar';
        header("Location: admin.php?tabla=" . urlencode($tabla));
        exit;
    }
    $sql = "INSERT INTO `$tabla` (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ")";
    $stmt = $conn->prepare($sql);
    $bind = array_merge([$types], $params);
    $stmt->bind_param(...$bind);
    $ok = $stmt->execute();
    $stmt->close();
    $_SESSION['admin_msg'] = $ok ? 'Registro agregado' : 'Error: '.$conn->error;
    header("Location: admin.php?tabla=" . urlencode($tabla));
    exit;
}

// acción por defecto
$_SESSION['admin_msg'] = 'Acción no reconocida';
header("Location: admin.php");
exit;
