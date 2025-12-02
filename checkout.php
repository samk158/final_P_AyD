<?php
session_start();
include("conexion.php");

/* ============================
   1. VERIFICAR CARRITO
============================ */
if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
    echo "<script>alert('Tu carrito está vacío'); window.location='carrito.php';</script>";
    exit;
}

// Calcular total
$total = 0;
foreach ($_SESSION['carrito'] as $item) {
    $total += $item['precio'] * $item['cantidad'];
}


/* ============================================
   2. AJAX: VERIFICAR CLIENTE POR NOMBRE
============================================ */
if (isset($_POST['ajax_action']) && $_POST['ajax_action'] === 'verificar_nombre') {

    header("Content-Type: application/json");
    $nombre = trim($_POST['nombre'] ?? '');

    if ($nombre == "") {
        echo json_encode(['existe' => false]);
        exit;
    }

    $stmt = $conexion->prepare("SELECT id, telefono, correo, codigo_cliente, direccion_entrega, latitud, longitud 
                                FROM clientes WHERE nombre=? LIMIT 1");
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $c = $res->fetch_assoc();
        echo json_encode([
            'existe' => true,
            'id_cliente' => $c['id'],
            'telefono' => $c['telefono'],
            'correo' => $c['correo'],
            'direccion' => $c['direccion_entrega'],
            'latitud' => $c['latitud'],
            'longitud' => $c['longitud'],
            'codigo_cliente' => $c['codigo_cliente']
        ]);
    } else {
        echo json_encode(['existe' => false]);
    }
    exit;
}



/* ============================================
   3. PROCESAR PEDIDO FINAL
============================================ */
if ($_SERVER['REQUEST_METHOD'] === "POST" && $_POST['action'] === "checkout") {

    $modo = ($_POST['modo'] === 'tienda') ? "tienda" : "delivery";

    $nombre = trim($_POST['nombre']);
    $telefono = trim($_POST['telefono'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $lat = floatval($_POST['latitud'] ?? 0);
    $lng = floatval($_POST['longitud'] ?? 0);
    $codigo_cliente = trim($_POST['codigo_cliente'] ?? '');

    $id_cliente = intval($_POST['id_cliente'] ?? 0);


    /* ============================================
       3.1 SI EL CLIENTE EXISTE
    ============================================ */
    if ($id_cliente > 0) {
        // Nada que hacer
    } else {

        /* ============================================
           3.2 BUSCAR POR NOMBRE
        ============================================ */
        $stmt = $conexion->prepare("SELECT id, codigo_cliente FROM clientes WHERE nombre=? LIMIT 1");
        $stmt->bind_param("s", $nombre);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $r = $res->fetch_assoc();
            $id_cliente = $r['id'];
            $codigo_cliente = $r['codigo_cliente'];
        } else {

            /* ============================================
               3.3 REGISTRAR CLIENTE NUEVO
            ============================================ */
            if ($codigo_cliente == "") {
                echo "<script>alert('Debe ingresar un código de cliente'); history.back();</script>";
                exit;
            }

            $stmt = $conexion->prepare("
                INSERT INTO clientes (nombre, telefono, correo, codigo_cliente, direccion_entrega, latitud, longitud, fecha_registro)
                VALUES (?,?,?,?,?,?,?,NOW())
            ");
            $stmt->bind_param("ssssddd", $nombre, $telefono, $correo, $codigo_cliente, $direccion, $lat, $lng);
            $stmt->execute();

            $id_cliente = $conexion->insert_id;
        }
    }


    /* ============================================
       4. INSERTAR VENTA
    ============================================ */
    $stmt = $conexion->prepare("
        INSERT INTO ventas (id_cliente, total, estado, fecha, estado_entrega)
        VALUES (?, ?, 'pendiente', NOW(), 'Pendiente')
    ");
    $stmt->bind_param("id", $id_cliente, $total);
    $stmt->execute();

    $id_venta = $conexion->insert_id;


    /* ============================================
       5. DETALLE DE VENTA + SEGUIMIENTO
    ============================================ */
    foreach ($_SESSION['carrito'] as $id_prod => $it) {

        $id_prod = intval($id_prod);
        $cantidad = intval($it['cantidad']);
        $precio = floatval($it['precio']);

        $stmt = $conexion->prepare("
            INSERT INTO detalle_ventas (id_venta, id_producto, cantidad, precio)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("iiid", $id_venta, $id_prod, $cantidad, $precio);
        $stmt->execute();


        // Obtener vendedor
        $stmtV = $conexion->prepare("SELECT codigo_vendedor FROM productos WHERE id=?");
        $stmtV->bind_param("i", $id_prod);
        $stmtV->execute();
        $resV = $stmtV->get_result();
        $codigo_vendedor = ($r = $resV->fetch_assoc()) ? $r['codigo_vendedor'] : "";


        // Insertar seguimiento
        $stmtS = $conexion->prepare("
            INSERT INTO seguimiento_pedidos
            (id_venta, id_producto, codigo_vendedor, codigo_cliente, estado, entrega, latitud_cliente, longitud_cliente, fecha_registro)
            VALUES (?, ?, ?, ?, 'Pendiente', ?, ?, ?, NOW())
        ");

        $stmtS->bind_param("iisssdd", 
            $id_venta, 
            $id_prod, 
            $codigo_vendedor,
            $codigo_cliente,
            $modo,
            $lat,
            $lng
        );

        $stmtS->execute();
    }

    $_SESSION['carrito'] = [];

    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Checkout | BoliviaMarket</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

<style>
body{font-family:'Poppins',sans-serif;background:#f3f4f6;margin:0;padding:20px}
.container{max-width:700px;margin:0 auto;background:#fff;padding:25px;border-radius:15px;box-shadow:0 8px 25px rgba(0,0,0,0.1)}
h2{text-align:center;font-size:26px;margin-bottom:10px}
h3{margin-top:25px}
.item{border-bottom:1px solid #ddd;padding:10px 0}
label{font-weight:600;display:block;margin-top:10px}
input,textarea{width:100%;padding:10px;border:1px solid #ccc;border-radius:8px}
button{margin-top:20px;width:100%;padding:12px;background:#2563eb;border:none;color:white;font-size:17px;border-radius:10px;cursor:pointer;font-weight:600}
button:hover{background:#1e40af}
.modal{display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.4)}
.modal-content{background:#fff;margin:10% auto;padding:20px;border-radius:10px;width:90%;max-width:500px;position:relative}
.modal-close{position:absolute;right:10px;top:10px;font-size:22px;cursor:pointer;color:#777}
#map{height:220px;width:100%;border:1px solid #ccc;border-radius:8px;margin-top:10px}
#nombreMsg{font-size:14px;margin-top:4px}
</style>

</head>
<body>
<?php include("barra_sup.php"); ?>
<div class="container">
<h2>Confirmar Pedido</h2>

<h3>🛒 Productos</h3>
<?php foreach($_SESSION['carrito'] as $item): ?>
<div class="item">
<strong><?= htmlspecialchars($item['nombre']) ?></strong><br>
Cantidad: <?= $item['cantidad'] ?><br>
Subtotal: Bs. <?= number_format($item['precio'] * $item['cantidad'], 2) ?>
</div>
<?php endforeach; ?>

<h3>Total a pagar: <span style="color:green;">Bs. <?= number_format($total,2) ?></span></h3>

<!-- FORMULARIO -->
<form id="checkoutForm" method="POST">

<h3>👤 Datos del cliente</h3>

<label>Nombre completo</label>
<input type="text" id="nombre" name="nombre" required>
<span id="nombreMsg"></span>

<h3>📦 Método de entrega</h3>
<label><input type="radio" name="modo" value="delivery" checked> Delivery</label>
<label><input type="radio" name="modo" value="tienda"> Recojo en tienda</label>

<input type="hidden" name="action" value="checkout">

<button type="submit">Confirmar Pedido</button>
</form>
</div>



<!-- MODAL CLIENTE NUEVO -->
<div id="modalCliente" class="modal">
<div class="modal-content">
<span class="modal-close" id="modalClose">&times;</span>

<h3>Registrar nuevo cliente</h3>

<label>Código de cliente</label>
<input type="text" id="codigo_cliente">

<label>Teléfono</label>
<input type="text" id="tel">

<label>Correo</label>
<input type="email" id="email">

<label>Dirección de entrega</label>
<textarea id="dir"></textarea>

<label>Ubicación en mapa</label>
<div id="map"></div>

<button id="guardarModal">Guardar y continuar</button>
</div>
</div>



<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// Elementos
let form = document.getElementById("checkoutForm");
let nombreInput = document.getElementById("nombre");
let nombreMsg = document.getElementById("nombreMsg");
let modal = document.getElementById("modalCliente");
let modalClose = document.getElementById("modalClose");
let guardarModal = document.getElementById("guardarModal");

// Mapa
let map = L.map("map").setView([-16.5, -68.15], 5);
L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png").addTo(map);
let marker = L.marker([-16.5, -68.15], {draggable:true}).addTo(map);

// Cerrar modal
modalClose.onclick = ()=> modal.style.display = "none";
window.onclick = e => { if(e.target == modal) modal.style.display="none"; }

// ===============================
// 1. BUSCAR CLIENTE EN TIEMPO REAL
// ===============================
nombreInput.addEventListener("keyup", ()=> {
    let nombre = nombreInput.value.trim();

    if(nombre.length < 2){
        nombreMsg.textContent = "";
        return;
    }

    fetch("checkout.php", {
        method:"POST",
        headers:{ "Content-Type":"application/x-www-form-urlencoded" },
        body: "ajax_action=verificar_nombre&nombre=" + encodeURIComponent(nombre)
    })
    .then(r=>r.json())
    .then(data=>{
        if(data.existe){
            nombreMsg.style.color="green";
            nombreMsg.textContent="Cliente registrado ✔";
        } else {
            nombreMsg.style.color="red";
            nombreMsg.textContent="Cliente NO registrado ✖";
        }
    });
});


// ===============================
// 2. SUBMIT DEL FORMULARIO
// ===============================
form.addEventListener("submit", async e => {
    e.preventDefault();

    let nombre = nombreInput.value.trim();

    // Última verificación
    let res = await fetch("checkout.php", {
        method:"POST",
        headers:{ "Content-Type":"application/x-www-form-urlencoded" },
        body:"ajax_action=verificar_nombre&nombre="+encodeURIComponent(nombre)
    });

    let data = await res.json();

    if(data.existe){

        let campos = {
            telefono:data.telefono,
            correo:data.correo,
            direccion:data.direccion,
            latitud:data.latitud,
            longitud:data.longitud,
            id_cliente:data.id_cliente,
            codigo_cliente:data.codigo_cliente
        };

        for(let k in campos){
            let inp=document.createElement("input");
            inp.type="hidden";
            inp.name=k;
            inp.value=campos[k];
            form.appendChild(inp);
        }

        form.submit();
        return;
    }

    // Cliente nuevo → abrir modal
    modal.style.display="block";
});


// ===============================
// 3. GUARDAR CLIENTE NUEVO
// ===============================
guardarModal.onclick = ()=> {

    let codigo = document.getElementById("codigo_cliente").value.trim();
    let tel = document.getElementById("tel").value.trim();
    let email = document.getElementById("email").value.trim();
    let dir = document.getElementById("dir").value.trim();

    if(!codigo || !tel || !email || !dir){
        alert("Completa todos los campos");
        return;
    }

    let lat = marker.getLatLng().lat;
    let lng = marker.getLatLng().lng;

    let campos = {
        telefono: tel,
        correo: email,
        direccion: dir,
        latitud: lat,
        longitud: lng,
        codigo_cliente: codigo,
        id_cliente: 0
    };

    for(let k in campos){
        let inp=document.createElement("input");
        inp.type="hidden";
        inp.name=k;
        inp.value=campos[k];
        form.appendChild(inp);
    }

    modal.style.display="none";
    form.submit();
};

</script>

</body>
</html>
