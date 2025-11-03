<?php
session_start();
require_once "conexion.php";

// --- SOLO ADMIN ---
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Tablas administrables
$tablas = ['usuarios', 'publicaciones','blog_posts','newsletter', 'ubicaciones'];

// Tabla activa
$tabla_activa = (isset($_GET['tabla']) && in_array($_GET['tabla'], $tablas)) ? $_GET['tabla'] : 'usuarios';

// Cargar datos de la tabla (limit pequeño para comodidad)
$limit = 200;
if ($tabla_activa === 'ubicaciones' && isset($_GET['tipo']) && $_GET['tipo'] !== 'todos') {
    $tipo = $conn->real_escape_string($_GET['tipo']);
    $resultado = $conn->query("SELECT * FROM ubicaciones WHERE tipo = '$tipo' LIMIT $limit");
} else {
    $resultado = $conn->query("SELECT * FROM `$tabla_activa` LIMIT $limit");
}

// Para el mapa: traer todas ubicaciones (sin límite)
$ubicaciones_json = "[]";
if (in_array('ubicaciones', $tablas)) {
    $res_u = $conn->query("SELECT id, nombre, tipo, direccion, telefono, latitud, longitud, horario, sitio_web FROM ubicaciones");
    $arr_u = [];
    if ($res_u) {
        while ($r = $res_u->fetch_assoc()) $arr_u[] = $r;
    }
    $ubicaciones_json = json_encode($arr_u, JSON_HEX_APOS|JSON_HEX_QUOT);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Admin • AdoptMe</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
    /* ----------------------
       Reset / base
       ---------------------- */
    :root{
        --bg:#fbfaf8; --card:#fff; --accent:#f7bd56; --accent-dark:#e6a83a; --muted:#6b6b6b;
        --primary:#ff9d3d; --danger:#e74c3c;
    }
    *{box-sizing:border-box}
    body{font-family:Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; margin:0; background:var(--bg); color:#222}
    a { color: inherit; text-decoration:none }

    /* header */
    header.topbar{display:flex;justify-content:space-between;align-items:center;padding:18px 28px;background:linear-gradient(90deg,#fff 0,#fff 100%);border-bottom:1px solid #eee}
    header .brand{display:flex;gap:12px;align-items:center}
    header .brand .logo{width:44px;height:44px;border-radius:10px;background:var(--primary);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:18px}
    header h1{font-size:18px;margin:0;color:#3c3c3c}
    header .actions{display:flex;gap:10px;align-items:center}

    /* layout */
    .wrap{display:flex;gap:20px;padding:22px;max-width:1200px;margin:18px auto}
    aside.sidebar{width:220px;background:var(--card);border-radius:12px;padding:14px;box-shadow:0 6px 18px rgba(0,0,0,0.04)}
    .sidebar h3{margin:0 0 12px 0;font-size:14px;color:#333}
    .table-list{display:flex;flex-direction:column;gap:8px}
    .table-list a{display:block;padding:10px;border-radius:8px;color:#333;border:1px solid transparent}
    .table-list a.active{background:linear-gradient(90deg,var(--accent),var(--accent-dark));color:#fff;border-color:transparent;box-shadow:0 6px 16px rgba(231,135,46,0.12)}
    main.content{flex:1}

    /* header area inside content */
    .top-controls{display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;gap:12px}
    .filters-inline{display:flex;gap:8px;align-items:center}

    /* table */
    .card{background:var(--card);border-radius:12px;padding:14px;box-shadow:0 6px 18px rgba(0,0,0,0.04);margin-bottom:16px}
    table.admin-table{width:100%;border-collapse:collapse;font-size:14px}
    table.admin-table th, table.admin-table td{padding:10px;border-bottom:1px solid #f0f0f0;text-align:left;vertical-align:middle}
    table.admin-table th{background:#fff;font-weight:600;color:#444}
    .row-actions{display:flex;gap:6px}
    .btn{display:inline-flex;align-items:center;gap:8px;padding:8px 10px;border-radius:8px;border:none;cursor:pointer}
    .btn-primary{background:var(--primary);color:#fff}
    .btn-ghost{background:transparent;border:1px solid #ddd;color:#333}
    .btn-danger{background:var(--danger);color:#fff}

    /* form grid */
    .form-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:10px;margin-top:10px}
    .form-grid input, .form-grid select{padding:10px;border-radius:8px;border:1px solid #ddd;width:100%}

    /* map */
    #admin-map{height:320px;border-radius:10px;overflow:hidden}

    /* responsive */
    @media (max-width:900px){ .wrap{padding:12px;flex-direction:column} aside.sidebar{width:100%} }
    </style>
</head>
<body>

<header class="topbar">
    <div class="brand">
        <div class="logo">AM</div>
        <div>
            <h1>Panel de administración — AdoptMe</h1>
            <small style="color:var(--muted)">Sesión: <?php echo htmlspecialchars($_SESSION['usuario_nombre'] ?? 'admin'); ?></small>
        </div>
    </div>

    <div class="actions">
        <a href="index.php" class="btn btn-ghost"><i class="fas fa-home"></i> Ver sitio</a>
        <a href="logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
    </div>
</header>

<div class="wrap">
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <h3>Tablas</h3>
        <div class="table-list">
            <?php foreach ($tablas as $t): $active = $t === $tabla_activa; ?>
                <a href="?tabla=<?php echo urlencode($t) ?>" class="<?php echo $active ? 'active' : '' ?>">
                    <?php echo ucfirst($t) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if ($tabla_activa === 'ubicaciones'): ?>
            <hr style="margin:12px 0;border:none;border-top:1px solid #eee">
            <h3>Filtrar ubicaciones</h3>
            <form method="GET">
                <input type="hidden" name="tabla" value="ubicaciones">
                <select name="tipo" onchange="this.form.submit()" style="width:100%;padding:8px;border-radius:8px;border:1px solid #ddd;">
                    <?php $opts = ['todos'=>'Todos','veterinaria'=>'Veterinaria','petshop'=>'Petshop','parque'=>'Parque','protectora'=>'Protectora']; ?>
                    <?php foreach ($opts as $k=>$v): ?>
                        <option value="<?php echo $k ?>" <?php if(isset($_GET['tipo']) && $_GET['tipo']===$k) echo 'selected'; ?>><?php echo $v ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        <?php endif; ?>
    </aside>

    <!-- MAIN -->
    <main class="content">
        <div class="top-controls">
            <h2>Tabla: <?php echo htmlspecialchars(ucfirst($tabla_activa)); ?></h2>
            <div class="filters-inline">
                <button class="btn btn-primary" onclick="document.getElementById('new-record').scrollIntoView({behavior:'smooth'})">
                    <i class="fas fa-plus"></i> Nuevo registro
                </button>
            </div>
        </div>

        <div class="card">
            <!-- Tabla de datos -->
            <?php if (!$resultado): ?>
                <p>Error al ejecutar la consulta: <?php echo htmlspecialchars($conn->error); ?></p>
            <?php else: ?>
                <div style="overflow:auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <?php
                            // mostrar columnas excepto: password, descripcion, fecha_registro (y id editable)
                            $fields = [];
                            while ($col = $resultado->fetch_field()) {
                                $fields[] = $col->name;
                            }
                            // volver al inicio de result
                            $resultado->data_seek(0);
                            foreach ($fields as $f) {
                                // columnas que no queremos mostrar grandes
                                if (in_array($f, ['password'])) continue;
                                echo "<th>".htmlspecialchars($f)."</th>";
                            }
                            ?>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($fila = $resultado->fetch_assoc()): ?>
                            <tr data-id="<?php echo htmlspecialchars($fila['id'] ?? '') ?>">
                                <?php foreach ($fields as $f):
                                    if ($f === 'password') continue;
                                    // abreviar textos largos
                                    $val = $fila[$f];
                                    if (is_null($val) || $val === '') $display = '<span style="color:#999">—</span>';
                                    else {
                                        $display = htmlspecialchars($val);
                                        if (strlen($display) > 80) $display = htmlspecialchars(substr($val,0,80)).'…';
                                    }
                                ?>
                                    <td><?php echo $display ?></td>
                                <?php endforeach; ?>
                                <td class="row-actions">
                                    <button class="btn btn-ghost" onclick="openEdit(<?php echo htmlspecialchars(json_encode($fila, JSON_HEX_APOS|JSON_HEX_QUOT)); ?>, '<?php echo $tabla_activa ?>')">
                                        <i class="fas fa-edit"></i> Editar
                                    </button>
                                    <button class="btn btn-danger" onclick="confirmDelete('<?php echo $tabla_activa ?>', '<?php echo htmlspecialchars($fila['id'] ?? '') ?>')">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- AREA: NUEVO REGISTRO -->
        <div class="card" id="new-record">
            <h3>Agregar nuevo registro — <?php echo htmlspecialchars($tabla_activa) ?></h3>

            <?php if ($tabla_activa === 'ubicaciones'): ?>
                <form method="POST" action="admin_acciones.php" class="form-grid">
                    <input type="hidden" name="accion" value="agregar_ubicacion">
                    <input name="nombre" placeholder="Nombre" required>
                    <select name="tipo" required>
                        <option value="">Tipo</option>
                        <option value="veterinaria">Veterinaria</option>
                        <option value="petshop">Petshop</option>
                        <option value="parque">Parque</option>
                        <option value="protectora">Protectora</option>
                    </select>
                    <input name="direccion" placeholder="Dirección" required>
                    <input name="telefono" placeholder="Teléfono">
                    <input name="horario" placeholder="Horario (ej: 9:00-18:00)">
                    <input name="latitud" id="latitud" placeholder="Latitud" required>
                    <input name="longitud" id="longitud" placeholder="Longitud" required>
                    <input name="sitio_web" placeholder="Sitio web">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-plus"></i> Agregar ubicación</button>
                </form>

                <div style="margin-top:12px" class="card">
                    <h4>Mapa — clic para fijar coordenadas</h4>
                    <div id="admin-map"></div>
                </div>

            <?php else: ?>
                <!-- Formulario genérico simple para otras tablas (solo campos básicos) -->
                <form method="POST" action="admin_acciones.php" class="form-grid">
                    <input type="hidden" name="accion" value="agregar_generico">
                    <input type="hidden" name="tabla" value="<?php echo htmlspecialchars($tabla_activa) ?>">

                    <!-- Intentamos tomar 4 campos visibles de la tabla para crear; si querés más, lo iteramos -->
                    <?php
                    // obtener columnas para esta tabla (mostramos los primeros 4 editables)
                    $colsRes = $conn->query("SHOW COLUMNS FROM `$tabla_activa`");
                    $count = 0;
                    while ($col = $colsRes->fetch_assoc()) {
                        $colName = $col['Field'];
                        if (in_array($colName, ['id','password','descripcion','fecha_registro'])) continue;
                        if ($count >= 6) break;
                        echo '<input name="col_'.$colName.'" placeholder="'.htmlspecialchars($colName).'">';
                        $count++;
                    }
                    ?>
                    <button class="btn btn-primary" type="submit"><i class="fas fa-plus"></i> Agregar</button>
                </form>
            <?php endif; ?>
        </div>

        <!-- EDIT MODAL SIMPLIFICADO -->
        <div id="editModal" class="card">
            <h3>Editar registro</h3>
            <form id="editForm" method="POST" action="admin_acciones.php" class="form-grid">
                <input type="hidden" name="accion" value="editar">
                <input type="hidden" name="tabla" id="edit_tabla">
                <input type="hidden" name="id" id="edit_id">
                <div id="editFields"></div>
                <div style="grid-column:1/-1;display:flex;gap:8px;justify-content:flex-end">
                    <button type="button" class="btn btn-ghost" onclick="document.getElementById('editModal').style.display='none'">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                </div>
            </form>
        </div>

    </main>
</div>

<script>
const ubicaciones = <?php echo $ubicaciones_json ?>;

// MAP: inicializar mapa admin (si estamos en tabla ubicaciones)
<?php if ($tabla_activa === 'ubicaciones'): ?>
let adminMap = L.map('admin-map').setView([-24.7821, -65.4232], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19}).addTo(adminMap);

let adminMarker;
adminMap.on('click', function(e){
    const lat = e.latlng.lat.toFixed(7);
    const lng = e.latlng.lng.toFixed(7);
    document.getElementById('latitud').value = lat;
    document.getElementById('longitud').value = lng;
    if (adminMarker) adminMap.removeLayer(adminMarker);
    adminMarker = L.marker([lat,lng]).addTo(adminMap);
});

// agregar marcadores de la DB
if (ubicaciones && Array.isArray(ubicaciones)) {
    ubicaciones.forEach(u=>{
        if (!u.latitud || !u.longitud) return;
        const mk = L.marker([parseFloat(u.latitud), parseFloat(u.longitud)]).addTo(adminMap);
        mk.bindPopup(`<strong>${escapeHTML(u.nombre)}</strong><br>${escapeHTML(u.direccion || '')}<br>${escapeHTML(u.tipo || '')}`);
    });
}
<?php endif; ?>

function escapeHTML(s){ return String(s||'').replace(/[&<>"']/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])) }

// ACCIONES: eliminar
function confirmDelete(tabla, id){
    if (!id) { alert('ID no válido'); return; }
    if (!confirm('¿Eliminar registro #' + id + ' de ' + tabla + '? Esta acción no se puede deshacer.')) return;
    // mandar formulario POST
    const f = document.createElement('form');
    f.method='POST'; f.action='admin_acciones.php';
    let inpA = (n,v)=>{ let i=document.createElement('input'); i.type='hidden'; i.name=n; i.value=v; f.appendChild(i); };
    inpA('accion','eliminar');
    inpA('tabla',tabla);
    inpA('id',id);
    document.body.appendChild(f); f.submit();
}

// abrir editor con campos editables
function openEdit(filaJson, tabla) {
    const forbidden = ['id','password','descripcion','fecha_registro'];
    const editModal = document.getElementById('editModal');
    document.getElementById('edit_tabla').value = tabla;
    document.getElementById('edit_id').value = filaJson.id || '';
    const cont = document.getElementById('editFields');
    cont.innerHTML = '';
    for (const k in filaJson) {
        if (forbidden.includes(k)) continue;
        if (k === 'id') continue;
        // crear input
        const wrapper = document.createElement('div');
        const label = document.createElement('label');
        label.textContent = k;
        const input = document.createElement('input');
        input.name = 'col_'+k;
        input.value = filaJson[k] ?? '';
        wrapper.appendChild(label);
        wrapper.appendChild(input);
        cont.appendChild(wrapper);
    }
    editModal.style.display = 'block';
    editModal.scrollIntoView({behavior:'smooth'});
}
</script>

</body>
</html>
