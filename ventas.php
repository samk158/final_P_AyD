<?php

include('barra_sup.php'); // ✅ No tocar
include('conexion.php');

// Validar que sea vendedor
if(!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] != 'vendedor'){
    header("Location: login.php");
    exit;
}

$codigo_vendedor = $_SESSION['codigo'];

// Cambiar estado de venta
if(isset($_POST['update_venta'])){
    $id_venta = intval($_POST['update_venta']);
    $nuevo_estado = $_POST['estado'];
    mysqli_query($conexion,"UPDATE ventas SET estado='$nuevo_estado' WHERE id='$id_venta'");
    $success = "Estado de la venta actualizado.";
}

// Filtrado opcional
$filtro_cliente = $_GET['cliente'] ?? '';
$filtro_estado = $_GET['estado'] ?? '';

// Consulta de ventas
$ventasQuery = "SELECT v.id, v.fecha, v.total, v.estado, c.nombre AS cliente_nombre, c.telefono AS cliente_telefono 
                FROM ventas v 
                JOIN clientes c ON v.id_cliente=c.id 
                WHERE 1=1";

if($filtro_cliente) $ventasQuery .= " AND c.nombre LIKE '%".mysqli_real_escape_string($conexion,$filtro_cliente)."%' ";
if($filtro_estado) $ventasQuery .= " AND v.estado='".mysqli_real_escape_string($conexion,$filtro_estado)."' ";

$ventasQuery .= " ORDER BY v.fecha DESC";

$ventasResult = mysqli_query($conexion,$ventasQuery);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestión de Ventas | BoliviaMarket</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
body { font-family:'Poppins',sans-serif; background:#f9fafb; margin:0; padding:20px; }
h2 { text-align:center; color:#1e293b; margin-bottom:15px; }
.section { background:#fff; border-radius:15px; padding:20px; margin-bottom:20px; box-shadow:0 4px 25px rgba(0,0,0,0.08); }
.table-container { overflow-x:auto; }
table { width:100%; border-collapse:collapse; }
th,td { border:1px solid #d1d5db; padding:10px; text-align:center; }
th { background:#f1f3f5; font-weight:600; }
button { padding:8px 12px; border:none; border-radius:8px; background:#2563eb; color:white; font-weight:600; cursor:pointer; transition:0.3s; }
button:hover { background:#1e40af; }
.success { background:#16a34a; color:white; padding:10px; border-radius:6px; margin-bottom:10px; text-align:center; }
.error { background:#dc2626; color:white; padding:10px; border-radius:6px; margin-bottom:10px; text-align:center; }
/* Modal flotante */
.modal { display:none; position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);align-items:center;justify-content:center; }
.modal-content { background:#fff; padding:20px; border-radius:10px; max-width:400px; width:90%; }
/* Responsive móvil */
@media(max-width:768px){
    table,th,td { font-size:0.85em; }
    th:nth-child(3), td:nth-child(3), th:nth-child(5), td:nth-child(5) { display:none; }
}
</style>
</head>
<body>

<h2>Gestión de Ventas</h2>

<?php if(isset($success)) echo "<div class='success'>$success</div>"; ?>

<!-- Filtrado -->
<div class="section">
<form method="GET" style="display:flex; flex-wrap:wrap; gap:10px;">
    <input type="text" name="cliente" placeholder="Filtrar por cliente" value="<?= htmlspecialchars($filtro_cliente) ?>">
    <select name="estado">
        <option value="">Todos los estados</option>
        <option value="pendiente" <?= $filtro_estado=='pendiente'?'selected':'' ?>>Pendiente</option>
        <option value="enviado" <?= $filtro_estado=='enviado'?'selected':'' ?>>Enviado</option>
        <option value="entregado" <?= $filtro_estado=='entregado'?'selected':'' ?>>Entregado</option>
        <option value="cancelado" <?= $filtro_estado=='cancelado'?'selected':'' ?>>Cancelado</option>
    </select>
    <button type="submit">Filtrar</button>
</form>
</div>

<!-- Tabla de ventas -->
<div class="section">
<div class="table-container">
<table>
<thead>
<tr>
<th>ID Venta</th>
<th>Cliente</th>
<th>Teléfono</th>
<th>Total</th>
<th>Estado</th>
<th>Fecha</th>
</tr>
</thead>
<tbody>
<?php while($venta=mysqli_fetch_assoc($ventasResult)): ?>
<tr onclick="openModal(<?= $venta['id'] ?>,'<?= htmlspecialchars($venta['estado']) ?>')"
    style="cursor:pointer;">
    <td><?= $venta['id'] ?></td>
    <td><?= htmlspecialchars($venta['cliente_nombre']) ?></td>
    <td><?= htmlspecialchars($venta['cliente_telefono']) ?></td>
    <td>$<?= number_format($venta['total'],2) ?></td>
    <td><?= htmlspecialchars($venta['estado']) ?></td>
    <td><?= $venta['fecha'] ?></td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>
</div>

<!-- Modal para cambiar estado -->
<div class="modal" id="editModal">
<div class="modal-content">
<h3>Actualizar Estado de Venta</h3>
<form method="POST">
    <input type="hidden" name="update_venta" id="venta_id">
    <select name="estado" id="estadoSelect" required>
        <option value="pendiente">Pendiente</option>
        <option value="enviado">Enviado</option>
        <option value="entregado">Entregado</option>
        <option value="cancelado">Cancelado</option>
    </select>
    <button type="submit">💾 Guardar</button>
    <button type="button" onclick="closeModal()" style="background:#dc2626;">Cancelar</button>
</form>
</div>
</div>

<script>
function openModal(id,estado){
    document.getElementById('venta_id').value=id;
    document.getElementById('estadoSelect').value=estado;
    document.getElementById('editModal').style.display='flex';
}
function closeModal(){
    document.getElementById('editModal').style.display='none';
}
</script>

</body>
</html>
