<?php
include('barra_sup.php'); // ✅ No tocar, ya inicia sesión y conexión
include('conexion.php');

// Validar que sea vendedor
if(!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] != 'vendedor'){
    header("Location: login.php");
    exit;
}

$codigo = $_SESSION['codigo'];

// Función para ejecutar consultas
function ejecutarConsulta($conexion, $query){
    $res = mysqli_query($conexion, $query);
    if(!$res) die("Error en la consulta: ".mysqli_error($conexion));
    return $res;
}

// Contar total de productos
$totalProductos = mysqli_fetch_assoc(ejecutarConsulta($conexion, "SELECT COUNT(*) as total FROM productos WHERE codigo_vendedor='$codigo'"))['total'];

// Productos bajos en stock (cantidad <=5)
$productosBajos = mysqli_fetch_assoc(ejecutarConsulta($conexion, "SELECT COUNT(*) as bajo FROM productos WHERE codigo_vendedor='$codigo' AND cantidad<=5"))['bajo'];

// Obtener todos los productos
$productosResult = ejecutarConsulta($conexion, "SELECT * FROM productos WHERE codigo_vendedor='$codigo' ORDER BY nombre ASC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Almacén | BoliviaMarket</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
body {
    font-family:'Poppins',sans-serif;
    background:#f5f5f5;
    margin:0;
    padding-top:110px; /* espacio para barra superior */
}

.container { max-width:1400px; margin:0 auto; display:flex; flex-wrap:wrap; gap:20px; padding:20px; }
.section { background:#fff; border-radius:15px; padding:20px; box-shadow:0 4px 25px rgba(0,0,0,0.08); flex:1 1 300px; }

.section h2 { margin-bottom:15px; color:#1e293b; }
button { padding:8px 14px; border:none; border-radius:8px; background:#2563eb; color:white; cursor:pointer; transition:0.3s; }
button:hover { background:#1e40af; }

table { width:100%; border-collapse:collapse; margin-top:10px; }
table th, table td { border:1px solid #d1d5db; padding:10px; text-align:center; }
input[type="search"] { padding:8px 12px; border-radius:6px; border:1px solid #ccc; width:100%; margin-bottom:10px; }

.badge { display:inline-block; padding:4px 8px; border-radius:8px; background:#f97316; color:#fff; font-size:12px; }

@media(max-width:1200px){.container{flex-direction:column;}}
</style>
</head>
<body>

<div class="container">

    <!-- Resumen de inventario -->
    <div class="section">
        <h2>Resumen de Inventario</h2>
        <p><strong>Total de productos:</strong> <?= $totalProductos ?></p>
        <p><strong>Productos bajos en stock (≤5):</strong> <span class="badge"><?= $productosBajos ?></span></p>
        <button onclick="window.location.href='nuevo_producto.php'">➕ Agregar Producto</button>
    </div>

    <!-- Lista de productos -->
    <div class="section">
        <h2>Productos</h2>
        <input type="search" id="buscar" placeholder="Buscar producto por nombre..." onkeyup="filtrarProductos()">
        <table id="tabla-productos">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Precio Mayor</th>
                    <th>Precio Menor</th>
                    <th>Cantidad</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while($prod = mysqli_fetch_assoc($productosResult)): ?>
                    <tr>
                        <td><?= htmlspecialchars($prod['nombre']) ?></td>
                        <td><?= htmlspecialchars($prod['precio_mayor']) ?></td>
                        <td><?= htmlspecialchars($prod['precio_menor']) ?></td>
                        <td><?= htmlspecialchars($prod['cantidad']) ?></td>
                        <td>
                            <button onclick="window.location.href='editar_producto.php?id=<?= $prod['id'] ?>'">✏️ Editar</button>
                            <button onclick="window.location.href='eliminar_producto.php?id=<?= $prod['id'] ?>'">🗑️ Eliminar</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</div>

<script>
// Filtro de búsqueda
function filtrarProductos(){
    const input = document.getElementById("buscar");
    const filtro = input.value.toLowerCase();
    const tabla = document.getElementById("tabla-productos");
    const filas = tabla.getElementsByTagName("tr");

    for(let i=1; i<filas.length; i++){
        const td = filas[i].getElementsByTagName("td")[0];
        if(td){
            const txt = td.textContent || td.innerText;
            filas[i].style.display = txt.toLowerCase().includes(filtro) ? "" : "none";
        }
    }
}
</script>

</body>
</html>
