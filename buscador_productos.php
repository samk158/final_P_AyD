<?php

include("conexion.php");

/* --------- MODO AJAX: devolver JSON de productos --------- */
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    header('Content-Type: application/json; charset=utf-8');

    $q = trim($_GET['q'] ?? '');
    if ($q === '') {
        echo json_encode([]);
        exit;
    }

    // Buscar por nombre (solo publicados)
    $sql = "SELECT id, nombre, imagen, precio_menor, precio_mayor, codigo_vendedor 
            FROM productos
            WHERE estado = 'publicado' AND nombre LIKE ? 
            ORDER BY nombre ASC
            LIMIT 50";

    if (!$stmt = $conexion->prepare($sql)) {
        echo json_encode(["error" => "Error al preparar la consulta"]);
        exit;
    }

    $like = "%".$q."%";
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $res = $stmt->get_result();
    $productos = $res->fetch_all(MYSQLI_ASSOC);

    echo json_encode($productos);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Buscador de productos | BoliviaMarket</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{
    font-family:'Poppins',sans-serif;
    background:#f3f4f6;
    padding-top:90px;
}

/* Contenedor principal */
.main-wrapper{
    max-width:1100px;
    margin:0 auto;
    padding:20px;
}

/* Título */
.titulo-buscador{
    text-align:center;
    margin-bottom:20px;
}
.titulo-buscador h1{
    font-size:2rem;
    font-weight:700;
}
.titulo-buscador span{
    color:#2563eb;
}

/* Caja de búsqueda */
.search-box{
    display:flex;
    flex-wrap:wrap;
    gap:10px;
    justify-content:center;
    margin-bottom:25px;
}
.search-box input{
    flex:1 1 260px;
    max-width:500px;
    padding:12px 16px;
    border-radius:999px;
    border:1px solid #cbd5f5;
    font-size:1rem;
    outline:none;
    transition:0.2s;
}
.search-box input:focus{
    border-color:#2563eb;
    box-shadow:0 0 0 3px rgba(37,99,235,0.2);
}
.search-box button{
    padding:12px 22px;
    border-radius:999px;
    border:none;
    background:#2563eb;
    color:white;
    font-weight:600;
    cursor:pointer;
    transition:0.2s;
}
.search-box button:hover{
    background:#1e40af;
}

/* Contenedor de resultados */
.resultados{
    margin-top:10px;
}

/* Mensajes */
.msg{
    text-align:center;
    color:#6b7280;
    margin-top:15px;
}

/* Tarjetas de producto */
.grid-productos{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
    gap:18px;
}

.card-prod{
    background:white;
    border-radius:14px;
    overflow:hidden;
    box-shadow:0 4px 14px rgba(0,0,0,0.08);
    display:flex;
    flex-direction:column;
    transition:0.2s;
}
.card-prod:hover{
    transform:translateY(-3px);
    box-shadow:0 8px 20px rgba(0,0,0,0.12);
}
.card-img{
    width:100%;
    height:160px;
    object-fit:cover;
}
.card-body{
    padding:12px 14px 14px;
    display:flex;
    flex-direction:column;
    gap:6px;
}
.card-titulo{
    font-size:1.05rem;
    font-weight:600;
}
.card-precio{
    font-size:0.95rem;
}
.card-precio b{
    color:#16a34a;
}
.card-vendedor{
    font-size:0.85rem;
    color:#6b7280;
}
.card-controles{
    display:flex;
    align-items:center;
    gap:8px;
    margin-top:8px;
}
.card-controles input[type=number]{
    width:80px;
    padding:6px;
    border-radius:8px;
    border:1px solid #d1d5db;
}

/* Botón añadir */
.btn-add{
    flex:1;
    padding:8px 10px;
    border-radius:999px;
    border:none;
    background:#16a34a;
    color:white;
    font-weight:600;
    font-size:0.9rem;
    cursor:pointer;
    transition:0.2s;
}
.btn-add:hover{
    background:#15803d;
}

/* Toast */
.toast{
    position:fixed;
    bottom:18px;
    right:18px;
    background:#16a34a;
    color:white;
    padding:10px 16px;
    border-radius:999px;
    font-size:0.9rem;
    box-shadow:0 4px 14px rgba(0,0,0,0.2);
    opacity:0;
    transform:translateY(10px);
    pointer-events:none;
    transition:0.25s;
    z-index:9999;
}
.toast.show{
    opacity:1;
    transform:translateY(0);
}

/* Loader */
.loader{
    margin:15px auto 0;
    border:4px solid #e5e7eb;
    border-top:4px solid #2563eb;
    border-radius:50%;
    width:32px;
    height:32px;
    animation:spin 0.7s linear infinite;
}
@keyframes spin{
    0%{transform:rotate(0deg);}
    100%{transform:rotate(360deg);}
}

@media(max-width:600px){
    .card-img{height:140px;}
}
</style>
</head>
<body>

<?php include("barra_sup.php"); ?>

<div class="main-wrapper">


    <div class="search-box">
        <input type="search" id="inputBuscar" placeholder="Buscar por nombre de producto...">
        <button type="button" onclick="forzarBusqueda()">Buscar</button>
    </div>

    <div class="resultados">
        <div id="estadoBusqueda" class="msg">Escribe algo para comenzar a buscar.</div>
        <div id="loader" class="loader" style="display:none;"></div>
        <div id="contenedorProductos" class="grid-productos"></div>
    </div>
</div>

<div id="toast" class="toast">Producto añadido al carrito</div>

<script>
const inputBuscar   = document.getElementById('inputBuscar');
const contenedor    = document.getElementById('contenedorProductos');
const estado        = document.getElementById('estadoBusqueda');
const loader        = document.getElementById('loader');
const toast         = document.getElementById('toast');

let ultimoTermino = '';
let timeoutBusqueda = null;

/* BÚSQUEDA EN TIEMPO REAL */
inputBuscar.addEventListener('input', () => {
    clearTimeout(timeoutBusqueda);
    timeoutBusqueda = setTimeout(realizarBusqueda, 350); // pequeño delay
});

function forzarBusqueda(){
    realizarBusqueda();
}

function realizarBusqueda(){
    const termino = inputBuscar.value.trim();

    if(termino === ''){
        contenedor.innerHTML = '';
        estado.textContent = 'Escribe algo para comenzar a buscar.';
        loader.style.display = 'none';
        return;
    }

    // Evitar repetir misma búsqueda
    if(termino === ultimoTermino) return;
    ultimoTermino = termino;

    loader.style.display = 'block';
    estado.textContent = '';

    fetch('buscador_productos.php?ajax=1&q=' + encodeURIComponent(termino))
        .then(r => r.json())
        .then(data => {
            loader.style.display = 'none';
            pintarResultados(data);
        })
        .catch(err => {
            loader.style.display = 'none';
            estado.textContent = 'Error al buscar productos.';
            console.error(err);
        });
}

function pintarResultados(lista){
    contenedor.innerHTML = '';

    if(!lista || lista.length === 0){
        estado.textContent = 'No se encontraron productos con ese nombre.';
        return;
    }

    estado.textContent = '';

    lista.forEach(p => {
        const precio = p.precio_menor ?? 0;
        const card = document.createElement('div');
        card.className = 'card-prod';

        card.innerHTML = `
            <img src="${p.imagen}" class="card-img" alt="${p.nombre}">
            <div class="card-body">
                <div class="card-titulo">${p.nombre}</div>
                <div class="card-precio"><b>Bs. ${precio}</b> ${p.precio_mayor ? ' / mayor: Bs. '+p.precio_mayor : ''}</div>
                <div class="card-vendedor">Vendedor: ${p.codigo_vendedor}</div>
                <div class="card-controles">
                    <input type="number" min="1" value="1" id="cant_${p.id}">
                    <button class="btn-add" onclick="agregarCarrito(${p.id}, '${escapeHtml(p.nombre)}', ${precio}, '${p.imagen}', 'unidad', 'cant_${p.id}')">
                        Añadir al carrito
                    </button>
                </div>
            </div>
        `;
        contenedor.appendChild(card);
    });
}

/* AÑADIR AL CARRITO (mismo formato que ya usas) */
function agregarCarrito(id,nombre,precio,imagen,unidad,idCantidad){
    const cantInput = document.getElementById(idCantidad);
    if(!cantInput) return;
    const cant = cantInput.value || 1;

    const fd = new FormData();
    fd.append('accion','agregar');
    fd.append('id', id);
    fd.append('nombre', nombre);
    fd.append('precio', precio);
    fd.append('cantidad', cant);
    fd.append('unidad', unidad);
    fd.append('imagen', imagen);

    fetch('carrito.php', {
        method:'POST',
        body: fd
    })
    .then(r => r.text())
    .then(resp => {
        if(resp.trim() === 'OK'){
            mostrarToast('Producto añadido al carrito');
        }else{
            mostrarToast('Error al añadir: ' + resp, true);
        }
    })
    .catch(err => {
        console.error(err);
        mostrarToast('Error de conexión', true);
    });
}

/* Toast simple */
function mostrarToast(mensaje, error=false){
    toast.textContent = mensaje;
    toast.style.background = error ? '#dc2626' : '#16a34a';
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 2200);
}

/* Escape básico para nombres en HTML/JS */
function escapeHtml(text){
    return text.replace(/&/g,"&amp;")
               .replace(/</g,"&lt;")
               .replace(/>/g,"&gt;")
               .replace(/"/g,"&quot;")
               .replace(/'/g,"&#039;");
}
</script>

</body>
</html>
