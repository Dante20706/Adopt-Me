<?php
session_start();
require_once "conexion.php";



// --- CARGAR TABLAS DISPONIBLES ---
$tablas = ['usuarios', 'publicaciones', 'imagenes', 'imagen_principal', 'imagen_perfil', 'ubicaciones'];

// --- DETERMINAR TABLA SELECCIONADA ---
$tabla_activa = isset($_GET['tabla']) && in_array($_GET['tabla'], $tablas)
    ? $_GET['tabla']
    : 'usuarios';

// --- CONSULTA GENERAL ---
$resultado = $conn->query("SELECT * FROM $tabla_activa");

// --- FILTRO POR TIPO (solo para ubicaciones) ---
if ($tabla_activa === 'ubicaciones' && isset($_GET['tipo']) && $_GET['tipo'] !== 'todos') {
    $tipo = $conn->real_escape_string($_GET['tipo']);
    $resultado = $conn->query("SELECT * FROM ubicaciones WHERE tipo='$tipo'");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración - AdoptMe</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fb;
            margin: 0;
            color: #333;
        }

        header {
            background: linear-gradient(90deg, #36c, #6cf);
            color: white;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            margin: 0;
            font-weight: 600;
        }

        .btn {
            padding: 8px 14px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            transition: 0.2s;
        }
        .btn:hover { opacity: 0.9; }

        .btn-primary { background-color: #4a90e2; color: white; }
        .btn-secondary { background-color: #f0ad4e; color: white; }
        .btn-danger { background-color: #e74c3c; color: white; }

        main {
            padding: 30px 40px;
        }

        .table-selector {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 30px;
        }

        .table-selector button {
            flex: 1;
            min-width: 120px;
            background-color: #eee;
            border-radius: 10px;
            padding: 10px;
            border: 1px solid #ccc;
        }

        .table-selector button.active {
            background-color: #4a90e2;
            color: white;
            border: none;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }

        .data-table th, .data-table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        .data-table th {
            background-color: #f2f2f2;
            font-weight: 600;
        }

        .data-table tr:hover {
            background-color: #f9f9f9;
        }

        .filter-bar {
            margin-bottom: 20px;
        }

        #map {
            height: 300px;
            margin-top: 20px;
            border-radius: 10px;
        }
    </style>
</head>
<body>

<header>
    <h1>Panel de Administración</h1>
    <a href="logout.php" class="btn btn-danger">Cerrar sesión</a>
</header>

<main>
    <div class="table-selector">
        <?php foreach ($tablas as $t): ?>
            <a href="?tabla=<?=$t?>">
                <button class="<?= $tabla_activa === $t ? 'active' : '' ?>">
                    <?= ucfirst($t) ?>
                </button>
            </a>
        <?php endforeach; ?>
    </div>

    <h2>📋 Tabla: <?= ucfirst($tabla_activa) ?></h2>

    <?php if ($tabla_activa === 'ubicaciones'): ?>
        <div class="filter-bar">
            <form method="GET" style="display: flex; gap: 10px; align-items: center;">
                <input type="hidden" name="tabla" value="ubicaciones">
                <label for="tipo">Filtrar por tipo:</label>
                <select name="tipo" id="tipo" onchange="this.form.submit()">
                    <option value="todos">Todos</option>
                    <option value="veterinaria">Veterinaria</option>
                    <option value="petshop">Petshop</option>
                    <option value="parque">Parque</option>
                    <option value="protectora">Protectora</option>
                </select>
            </form>
        </div>
    <?php endif; ?>

    <table class="data-table">
        <tr>
            <?php while ($col = $resultado->fetch_field()): ?>
                <th><?= htmlspecialchars($col->name) ?></th>
            <?php endwhile; ?>
            <th>Acciones</th>
        </tr>

        <?php 
        $resultado->data_seek(0);
        while ($fila = $resultado->fetch_assoc()): 
        ?>
            <tr>
                <?php foreach ($fila as $valor): ?>
                    <td><?= htmlspecialchars($valor) ?></td>
                <?php endforeach; ?>
                <td>
                    <button class="btn btn-secondary">Editar</button>
                    <button class="btn btn-danger">Eliminar</button>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <?php if ($tabla_activa === 'ubicaciones'): ?>
        <h3>Agregar nueva ubicación</h3>
        <form method="POST" action="admin_acciones.php" class="form-grid">
            <input type="hidden" name="accion" value="agregar_ubicacion">
            <input type="text" name="nombre" placeholder="Nombre" required>
            <select name="tipo" required>
                <option value="">Tipo</option>
                <option value="veterinaria">Veterinaria</option>
                <option value="petshop">Petshop</option>
                <option value="parque">Parque</option>
                <option value="protectora">Protectora</option>
            </select>
            <input type="text" name="latitud" id="latitud" placeholder="Latitud" required>
            <input type="text" name="longitud" id="longitud" placeholder="Longitud" required>
            <input type="text" name="horario" placeholder="Horario (ej: 9:00-18:00)">
            <textarea name="descripcion" placeholder="Descripción"></textarea>
            <button class="btn btn-primary" type="submit">Agregar</button>
        </form>

        <div id="map"></div>
    <?php endif; ?>
</main>

<script>
<?php if ($tabla_activa === 'ubicaciones'): ?>
const map = L.map('map').setView([-24.79, -65.41], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 18 }).addTo(map);

let marker;
map.on('click', function(e) {
    const { lat, lng } = e.latlng;
    document.getElementById('latitud').value = lat;
    document.getElementById('longitud').value = lng;

    if (marker) map.removeLayer(marker);
    marker = L.marker([lat, lng]).addTo(map);
});
<?php endif; ?>
</script>

</body>
</html>
