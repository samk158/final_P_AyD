<?php
include("conexion.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ========================================
   1) VALIDAR SESIÓN DEL CLIENTE
======================================== */

if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'cliente') {
    // Solo mostramos recomendaciones a clientes
    return;
}

if (!isset($_SESSION['codigo'])) {
    // No se encuentra el código del cliente en sesión
    return;
}

$codigo_cliente = $_SESSION['codigo'];

/* ========================================
   2) OBTENER ID REAL DEL CLIENTE
======================================== */

$stmt = $conexion->prepare("
    SELECT id 
    FROM clientes 
    WHERE codigo_cliente = ?
    LIMIT 1
");
$stmt->bind_param("s", $codigo_cliente);
$stmt->execute();
$qCliente = $stmt->get_result();

if ($qCliente->num_rows == 0) {
    return;
}

$cliente     = $qCliente->fetch_assoc();
$id_cliente  = (int)$cliente['id'];

/* ========================================
   3) DETECTAR COMPRA RECIENTE (id_venta)
======================================== */

$id_venta_actual = null;
if (isset($_GET['id_venta'])) {
    $id_venta_actual = (int)$_GET['id_venta'];

    // Verificamos que la venta le pertenezca al cliente logueado
    $stmt = $conexion->prepare("
        SELECT id 
        FROM ventas 
        WHERE id = ? AND id_cliente = ?
        LIMIT 1
    ");
    $stmt->bind_param("ii", $id_venta_actual, $id_cliente);
    $stmt->execute();
    $checkVenta = $stmt->get_result();

    if ($checkVenta->num_rows == 0) {
        $id_venta_actual = null; // No es su venta, ignorar
    }
}

/* ========================================
   4) SECTOR FAVORITO Y PRODUCTOS COMPRADOS
======================================== */

$sectorFavorito     = null;
$productosComprados = [];

// Si tenemos una venta reciente válida, usamos esa como base
if ($id_venta_actual) {

    // Sectores de los productos de ESA venta
    $stmt = $conexion->prepare("
        SELECT p.sector, COUNT(*) AS total
        FROM detalle_ventas dv
        JOIN productos p ON p.id = dv.id_producto
        WHERE dv.id_venta = ?
        GROUP BY p.sector
        ORDER BY total DESC
        LIMIT 1
    ");
    $stmt->bind_param("i", $id_venta_actual);
    $stmt->execute();
    $qSectVenta = $stmt->get_result();

    if ($qSectVenta->num_rows > 0) {
        $sectorFavorito = $qSectVenta->fetch_assoc()['sector'];
    }

    // IDs de productos de esa venta (para no recomendarlos de nuevo)
    $stmt = $conexion->prepare("
        SELECT dv.id_producto
        FROM detalle_ventas dv
        WHERE dv.id_venta = ?
    ");
    $stmt->bind_param("i", $id_venta_actual);
    $stmt->execute();
    $qProdVenta = $stmt->get_result();

    while ($row = $qProdVenta->fetch_assoc()) {
        $productosComprados[] = (int)$row['id_producto'];
    }
}

// Si no se pudo determinar sector por esta venta, usamos historial completo
if (!$sectorFavorito) {
    $stmt = $conexion->prepare("
        SELECT p.sector, COUNT(*) AS total
        FROM ventas v
        JOIN detalle_ventas dv ON dv.id_venta = v.id
        JOIN productos p ON p.id = dv.id_producto
        WHERE v.id_cliente = ?
        GROUP BY p.sector
        ORDER BY total DESC
        LIMIT 1
    ");
    $stmt->bind_param("i", $id_cliente);
    $stmt->execute();
    $sectorQuery = $stmt->get_result();

    if ($sectorQuery->num_rows > 0) {
        $sectorFavorito = $sectorQuery->fetch_assoc()['sector'];
    } else {
        // Si nunca compró nada, tomamos el sector más vendido del sistema
        $topSector = $conexion->query("
            SELECT p.sector, SUM(dv.cantidad) AS vendidos
            FROM productos p
            LEFT JOIN detalle_ventas dv ON dv.id_producto = p.id
            GROUP BY p.sector
            ORDER BY vendidos DESC
            LIMIT 1
        ");
        if ($topSector->num_rows > 0) {
            $sectorFavorito = $topSector->fetch_assoc()['sector'];
        } else {
            $sectorFavorito = "General"; // fallback
        }
    }
}

/* ========================================
   Helper para cláusula "NOT IN"
======================================== */

$clausulaNotIn = "";
if (!empty($productosComprados)) {
    // todos vienen de la BD, son enteros
    $ids = implode(",", $productosComprados);
    $clausulaNotIn = " AND p.id NOT IN ($ids) ";
}

/* ========================================
   5) REGLA 1: SIMILARES POR SECTOR (sin repetir comprados)
======================================== */
$sqlSimilares = "
    SELECT p.*
    FROM productos p
    WHERE p.sector = ?
    $clausulaNotIn
    ORDER BY RAND()
    LIMIT 8
";
$stmt = $conexion->prepare($sqlSimilares);
$stmt->bind_param("s", $sectorFavorito);
$stmt->execute();
$similares = $stmt->get_result();

/* ========================================
   6) REGLA 2: MÁS VENDIDOS GLOBALMENTE
======================================== */
$masVendidos = $conexion->query("
    SELECT p.*, COALESCE(SUM(dv.cantidad),0) AS vendidos
    FROM productos p
    LEFT JOIN detalle_ventas dv ON dv.id_producto = p.id
    GROUP BY p.id
    ORDER BY vendidos DESC
    LIMIT 8
");

/* ========================================
   7) REGLA 3: PRODUCTOS DE VENDEDORES CONOCIDOS
======================================== */
$stmt = $conexion->prepare("
    SELECT DISTINCT p.codigo_vendedor
    FROM ventas v
    JOIN detalle_ventas dv ON dv.id_venta = v.id
    JOIN productos p ON p.id = dv.id_producto
    WHERE v.id_cliente = ?
");
$stmt->bind_param("i", $id_cliente);
$stmt->execute();
$vendedores = $stmt->get_result();

$listaV = [];
while ($row = $vendedores->fetch_assoc()) {
    if ($row['codigo_vendedor'] !== null && $row['codigo_vendedor'] !== '') {
        $listaV[] = "'" . $conexion->real_escape_string($row['codigo_vendedor']) . "'";
    }
}

$productosVendedores = null;
if (!empty($listaV)) {
    $cadena = implode(",", $listaV);
    $productosVendedores = $conexion->query("
        SELECT *
        FROM productos
        WHERE codigo_vendedor IN ($cadena)
        ORDER BY RAND()
        LIMIT 8
    ");
}

/* ========================================
   8) REGLA 4: POPULARES DEL SECTOR FAVORITO
======================================== */
$sqlPopSector = "
    SELECT p.*, COALESCE(SUM(dv.cantidad),0) AS vendidos
    FROM productos p
    LEFT JOIN detalle_ventas dv ON dv.id_producto = p.id
    WHERE p.sector = ?
    $clausulaNotIn
    GROUP BY p.id
    ORDER BY vendidos DESC
    LIMIT 8
";
$stmt = $conexion->prepare($sqlPopSector);
$stmt->bind_param("s", $sectorFavorito);
$stmt->execute();
$popularesSector = $stmt->get_result();

/* ========================================
   9) UNIFICAR RESULTADOS SIN DUPLICADOS
======================================== */

$recomendados = [];
$idsUsados = [];

$bloques = [$similares, $popularesSector, $masVendidos];
if ($productosVendedores) $bloques[] = $productosVendedores;

foreach ($bloques as $bloque) {
    if (!$bloque) continue;
    while ($p = $bloque->fetch_assoc()) {
        $idProd = (int)$p['id'];
        if (isset($idsUsados[$idProd])) continue;
        $idsUsados[$idProd] = true;
        $recomendados[] = $p;
    }
}

if (empty($recomendados)) {
    // Nada que recomendar
    return;
}
?>

<div class="recomendaciones">
  <h2>🛍 Productos recomendados para ti</h2>
  <div class="cards-scroll">
    <?php foreach ($recomendados as $p): ?>
      <div class="card">
        <div class="card-img">
          <img src="<?= htmlspecialchars($p['imagen'] ?? 'imagenes/default.jpg', ENT_QUOTES) ?>"
               alt="<?= htmlspecialchars($p['nombre'], ENT_QUOTES) ?>">
        </div>
        <div class="card-info">
          <h3><?= htmlspecialchars($p['nombre']) ?></h3>
          <p>Bs <?= number_format($p['precio_menor'], 2) ?></p>

<!-- 🔥 AÑADIR AL CARRITO (COMPATIBLE CON TU carrito.php) -->
<form method="POST" action="carrito.php">

    <input type="hidden" name="accion" value="agregar">

    <input type="hidden" name="id" value="<?= $p['id'] ?>">
    <input type="hidden" name="nombre" value="<?= htmlspecialchars($p['nombre'], ENT_QUOTES) ?>">
    <input type="hidden" name="precio" value="<?= $p['precio_menor'] ?>">
    <input type="hidden" name="cantidad" value="1">

    <input type="hidden" name="imagen" value="<?= htmlspecialchars($p['imagen'], ENT_QUOTES) ?>">

    <button type="submit">Añadir al carrito</button>
</form>


        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<style>
.recomendaciones {
  margin-top: 30px;
  padding: 20px;
  background-color: #f8f8f8;
  border-radius: 12px;
}
.recomendaciones h2 {
  margin-top: 0;
  margin-bottom: 15px;
}
.cards-scroll {
  display: flex;
  gap: 15px;
  overflow-x: auto;
  padding-bottom: 10px;
}
.cards-scroll::-webkit-scrollbar {
  height: 8px;
}
.cards-scroll::-webkit-scrollbar-thumb {
  background: #cbd5f5;
  border-radius: 4px;
}
.card {
  min-width: 180px;
  max-width: 200px;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.12);
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
}
.card-img img {
  width: 100%;
  height: 150px;
  object-fit: cover;
  border-radius: 12px 12px 0 0;
}
.card-info {
  padding: 10px;
  text-align: center;
}
.card-info h3 {
  font-size: 15px;
  margin: 5px 0;
}
.card-info p {
  margin: 5px 0;
  font-weight: 600;
  color: #16a34a;
}
.card-info button {
  background-color: #2563eb;
  color: white;
  border: none;
  padding: 6px 10px;
  border-radius: 6px;
  cursor: pointer;
  margin-top: 8px;
  font-size: 14px;
}
.card-info button:hover {
  background-color: #1e40af;
}
</style>
