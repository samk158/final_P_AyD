<?php
session_start();
include('conexion.php');

if (!isset($_GET['id_venta'])) {
    echo "<script>alert('No hay orden generada'); window.location='index.php';</script>";
    exit;
}

$id_venta = (int)$_GET['id_venta'];

// Datos de venta + cliente
$sqlV = "SELECT v.*, c.nombre, c.telefono, c.direccion_entrega, c.codigo_cliente
         FROM ventas v
         JOIN clientes c ON v.id_cliente = c.id
         WHERE v.id = $id_venta";

$resV = mysqli_query($conexion, $sqlV);
$orden = mysqli_fetch_assoc($resV);

// Guardar sesión del cliente (para recomendaciones)
$_SESSION['tipo_usuario'] = "cliente";
$_SESSION['codigo'] = $orden['codigo_cliente'];

// Items
$items = [];
$resD = mysqli_query($conexion,"
    SELECT d.*, p.nombre 
    FROM detalle_ventas d
    JOIN productos p ON p.id = d.id_producto
    WHERE d.id_venta = $id_venta");

while($row = mysqli_fetch_assoc($resD)) $items[] = $row;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Orden de Entrega | BoliviaMarket</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
body{
    font-family:'Poppins',sans-serif;
    background:#f3f4f6;
    margin:0;padding:20px;
}
.container{
    max-width:700px;margin:auto;
    background:white;padding:25px;
    border-radius:15px;
    box-shadow:0 8px 25px rgba(0,0,0,0.1);
}
.item{border-bottom:1px solid #ddd;padding:10px 0;}
button{
    width:100%;padding:12px;margin-top:20px;
    background:#2563eb;border:none;color:white;
    font-size:16px;border-radius:10px;cursor:pointer;
}
button:hover{background:#1e40af;}
</style>
</head>
<body>

<div class="container">
    <h2>📦 Orden de Entrega</h2>

    <p><b>Código de orden:</b> ORD<?= $id_venta ?></p>
    <p><b>Cliente:</b> <?= htmlspecialchars($orden['nombre']) ?></p>
    <p><b>Teléfono:</b> <?= htmlspecialchars($orden['telefono']) ?></p>
    <p><b>Dirección:</b> <?= nl2br(htmlspecialchars($orden['direccion_entrega'])) ?></p>

    <h3>🛒 Productos comprados</h3>
    <?php foreach($items as $item): ?>
        <div class="item">
            <b><?= htmlspecialchars($item['nombre']) ?></b><br>
            Cantidad: <?= $item['cantidad'] ?><br>
            Subtotal: Bs. <?= number_format($item['cantidad'] * $item['precio'],2) ?>
        </div>
    <?php endforeach; ?>

    <h3>Total pagado: <span style="color:green;">Bs. <?= number_format($orden['total'],2) ?></span></h3>

    <button onclick="window.location.href='index.php'">Aceptar y volver al inicio</button>

    <!-- 🔥 RECOMENDACIONES DESPUÉS DE COMPRAR -->
    <h3 style="margin-top:40px;">🎁 Recomendaciones basadas en tu compra</h3>
    <?php include("recomendaciones.php"); ?>

</div>

</body>
</html>