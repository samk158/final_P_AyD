<?php
include('barra_sup.php'); 
include('conexion.php');

if(!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] != 'vendedor'){
    header("Location: login.php");
    exit;
}

$codigo = $_SESSION['codigo'];

$productosResult = mysqli_query($conexion, "SELECT * FROM productos WHERE codigo_vendedor='$codigo' ORDER BY nombre ASC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Stock | BoliviaMarket</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
body { font-family:'Poppins',sans-serif; background:#f5f5f5; margin:0; padding-top:110px; }
.container { max-width:1400px; margin:0 auto; padding:20px; display:flex; flex-direction:column; gap:20px; }
h2 { color:#1e293b; margin-bottom:10px; }
.guia { background:#e0f2fe; color:#0369a1; padding:12px 15px; border-radius:10px; margin-bottom:20px; font-size:14px; }

/* GRID DE PRODUCTOS */
.grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(280px,1fr)); gap:20px; }

/* CARD */
.card {
    background:#fff;
    border-radius:12px;
    padding:15px;
    box-shadow:0 4px 15px rgba(0,0,0,0.08);
    display:flex;
    flex-direction:column;
    justify-content:space-between;
    transition:transform 0.2s, box-shadow 0.2s;
}
.card:hover { transform:translateY(-3px); box-shadow:0 6px 20px rgba(0,0,0,0.12); }

/* HEADER DE CARD */
.card-header { display:flex; align-items:center; gap:10px; margin-bottom:10px; }
.card-header img { width:60px; height:60px; border-radius:10px; object-fit:cover; border:1px solid #d1d5db; }

/* TITULO Y DESCRIPCION */
.card h3 { font-size:18px; margin:0; color:#1e293b; }
.card p.desc { font-size:13px; color:#4b5563; margin:3px 0 8px; }

/* BADGES DE STOCK Y ESTADO */
.badge { display:inline-block; padding:4px 8px; border-radius:8px; font-size:12px; color:white; margin-right:5px; }
.stock-bajo { background:#f97316; }
.stock-normal { background:#16a34a; }
.estado-publicado { background:#2563eb; }
.estado-agotado { background:#9ca3af; }

/* PRECIOS */
.precios { display:flex; gap:10px; margin:5px 0; font-size:14px; }
.precios span { font-weight:600; }

/* ACCIONES */
.actions { display:flex; gap:5px; flex-wrap:wrap; margin-top:10px; }
.actions button { flex:1; padding:6px 0; border:none; border-radius:8px; background:#2563eb; color:white; cursor:pointer; transition:0.3s; font-size:14px; }
.actions button:hover { background:#1e40af; }

/* INPUT STOCK */
input[type="number"] { width:60px; padding:5px; border-radius:5px; border:1px solid #ccc; text-align:center; }

/* RESPONSIVE */
@media(max-width:600px){ .grid { grid-template-columns:1fr; } .card-header img { width:50px; height:50px; } }
</style>
</head>
<body>

<div class="container">
    <h2>Gestión de Stock</h2>
    <div class="guia">
        🛈 Aquí puedes visualizar todos tus productos, actualizar cantidades, editar información o eliminar artículos. 
        <br>Los productos con stock bajo se mostrarán en naranja, y los agotados en gris.
    </div>

    <div class="grid">
        <?php while($prod = mysqli_fetch_assoc($productosResult)): 
            $claseStock = $prod['cantidad'] <=5 ? "stock-bajo" : "stock-normal";
            $estadoProd = $prod['cantidad'] == 0 ? "estado-agotado" : "estado-publicado";
        ?>
        <div class="card">
            <div class="card-header">
                <img src="<?= $prod['imagen'] ?? 'imagenes/no-disponible.png' ?>" alt="<?= htmlspecialchars($prod['nombre']) ?>">
                <div>
                    <h3><?= htmlspecialchars($prod['nombre']) ?></h3>
                    <?php if(!empty($prod['descripcion'])): ?>
                        <p class="desc"><?= htmlspecialchars($prod['descripcion']) ?></p>
                    <?php endif; ?>
                    <div>
                        <span class="badge <?= $claseStock ?>">Stock: <?= $prod['cantidad'] ?></span>
                        <span class="badge <?= $estadoProd ?>"><?= $prod['cantidad']==0?'Agotado':'Publicado' ?></span>
                    </div>
                </div>
            </div>
            <div class="precios">
                <span>Precio Mayor: Bs. <?= number_format($prod['precio_mayor'],2) ?></span>
                <span>Precio Menor: Bs. <?= number_format($prod['precio_menor'],2) ?></span>
            </div>
            <div class="actions">
                <form method="post" action="actualizar_stock.php" style="display:flex; gap:5px; flex:1;">
                    <input type="hidden" name="id_producto" value="<?= $prod['id'] ?>">
                    <input type="number" name="cantidad" min="0" value="<?= $prod['cantidad'] ?>">
                    <button type="submit">Actualizar</button>
                </form>
                <button onclick="window.location.href='editar_producto.php?id=<?= $prod['id'] ?>'">✏️ Editar</button>
                <button onclick="if(confirm('¿Eliminar producto?')) window.location.href='eliminar_producto.php?id=<?= $prod['id'] ?>'">🗑️ Eliminar</button>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

</body>
</html>
