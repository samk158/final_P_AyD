<?php


// AGREGAR DESDE AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && ($_POST['accion'] ?? '') === 'agregar'
    && !empty($_POST['id'])) {

    if (!isset($_SESSION['carrito'])) {
        $_SESSION['carrito'] = [];
    }

    $id       = (int)$_POST['id'];
    $nombre   = $_POST['nombre'];
    $precio   = (float)$_POST['precio'];
    $cantidad = (int)$_POST['cantidad'];
    
    $imagen   = $_POST['imagen'];

    if (!isset($_SESSION['carrito'][$id])) {
        $_SESSION['carrito'][$id] = [
            "nombre"   => $nombre,
            "precio"   => $precio,
            "cantidad" => $cantidad,
            
            "imagen"   => $imagen
        ];
    } else {
        $_SESSION['carrito'][$id]["cantidad"] += $cantidad;
    }

    echo "OK";
    exit;
}

// CONTADOR PARA ICONO (si lo usas)
if (isset($_GET['contador'])) {
    $n = 0;
    if (!empty($_SESSION['carrito'])) {
        foreach($_SESSION['carrito'] as $it){
            $n += $it['cantidad'];
        }
    }
    echo $n;
    exit;
}

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// ELIMINAR / VACIAR
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'eliminar') {
        unset($_SESSION['carrito'][$_POST['id']]);
        header("Location: carrito.php");
        exit;
    }
    if ($accion === 'vaciar') {
        $_SESSION['carrito'] = [];
        header("Location: carrito.php");
        exit;
    }
}

// TOTAL
$total = 0;
foreach ($_SESSION['carrito'] as $p) {
    $total += $p['precio'] * $p['cantidad'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Carrito</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
body{font-family:'Poppins';background:#f3f4f6;padding:40px;}
.carrito{
    background:white;
    padding:25px;
    border-radius:15px;
    max-width:900px;
    margin:auto;
    box-shadow:0 3px 12px rgba(0,0,0,0.08);
}
.item{
    display:flex;
    align-items:center;
    gap:20px;
    border-bottom:1px solid #ddd;
    padding:15px 0;
}
.item img{
    width:85px;
    height:85px;
    object-fit:cover;
    border-radius:8px;
}
.btn-eliminar{background:#dc2626;color:white;border:none;padding:8px 14px;border-radius:8px;cursor:pointer;}
.btn-volver{background:#2563eb;color:white;padding:10px 20px;border:none;border-radius:8px;font-weight:bold;cursor:pointer;}
.btn-vaciar{background:#475569;color:white;padding:10px 20px;border:none;border-radius:8px;font-weight:bold;cursor:pointer;}
.btn-pago{background:#16a34a;color:white;padding:12px 22px;border:none;border-radius:8px;font-weight:bold;cursor:pointer;}
.botones{display:flex;justify-content:space-between;margin-top:20px;}
</style>
</head>
<body>
<?php include("barra_sup.php"); ?>
<h1 style="text-align:center;">🛒 Tu Carrito</h1>

<div class="carrito">
<?php if(empty($_SESSION['carrito'])): ?>
    <p>No tienes productos en el carrito.</p>
    <button class="btn-volver" onclick="window.location.href='index.php'">
        🛒 Volver a compras
    </button>
<?php else: ?>
    <?php foreach($_SESSION['carrito'] as $id=>$p): ?>
        <div class="item">
            <img src="<?= htmlspecialchars($p['imagen']) ?>">
            <div style="flex:1;">
                <h3><?= htmlspecialchars($p['nombre']) ?></h3>

                <p><b>Precio:</b> Bs. <?= number_format($p['precio'],2) ?></p>
                <p><b>Cantidad:</b> <?= $p['cantidad'] ?></p>
                <p><b>Subtotal:</b> Bs. <?= number_format($p['precio'] * $p['cantidad'],2) ?></p>
            </div>
            <form method="POST">
                <input type="hidden" name="accion" value="eliminar">
                <input type="hidden" name="id" value="<?= $id ?>">
                <button class="btn-eliminar">Eliminar</button>
            </form>
        </div>
    <?php endforeach; ?>

    <h3 style="text-align:right; margin-top:15px;">
        Total: <b>Bs. <?= number_format($total,2) ?></b>
    </h3>

    <div class="botones">
        <button class="btn-volver" onclick="window.location.href='index.php'">⬅ Volver a compras</button>
        <form method="POST">
            <input type="hidden" name="accion" value="vaciar">
            <button class="btn-vaciar">Vaciar carrito</button>
        </form>
        <button class="btn-pago" onclick="window.location.href='checkout.php'">Proceder al Pago</button>
    </div>
<?php endif; ?>
</div>

</body>
</html>