<?php

include("conexion.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>BoliviaMarket</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

<style>
body {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    background: #f3f4f6;
    padding-top: 90px;
}

/* TITULO */
.contenedor-titulo {
    text-align: center;
    margin-top: 20px;
}
.contenedor-titulo h1 {
    font-size: 3rem;
    font-weight: 700;
}
.contenedor-titulo span {
    color: #0ea5e9;
}

/* TARJETAS CATEGORIAS */
.categorias {
    margin-top: 40px;
    display: flex;
    justify-content: center;
    gap: 40px;
    flex-wrap: wrap;
}

.tarjeta {
    width: 250px;
    height: 150px;
    background: white;
    border-radius: 12px;
    overflow: hidden;
    position: relative;
    cursor: pointer;
    box-shadow: 0px 4px 12px rgba(0,0,0,0.15);
    transition: 0.3s;
}
.tarjeta:hover {
    transform: scale(1.06);
}
.tarjeta img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.tarjeta-texto {
    position: absolute;
    bottom: 8px;
    width: 100%;
    text-align: center;
    background: rgba(0,0,0,0.45);
    color: white;
    padding: 4px;
    border-radius: 6px;
}

/* MODAL */
#modalProductos {
    display:none;
    position:fixed;
    top:0; left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.65);
    justify-content:center;
    align-items:flex-start;
    padding-top:60px;
}
.modal-contenido {
    background: white;
    width: 28%;
    max-width: 900px;
    padding: 20px;
    border-radius: 14px;

    max-height: 80vh;
    overflow-y: auto;
    overflow-x: hidden;

    box-shadow: 0 6px 22px rgba(0,0,0,0.25);
    transition: 0.3s ease;
}

/* 📱 VISTA CELULAR */
@media (max-width: 768px) {
    .modal-contenido {
        width: 90%;         /* ocupa casi toda la pantalla móvil */
        padding: 16px;
        border-radius: 12px;
    }
}

/* 📱 VISTA CELULAR PEQUEÑO (SÚPER PEQUEÑO) */
@media (max-width: 480px) {
    .modal-contenido {
        width: 95%;
        padding: 14px;
        max-height: 85vh;   /* más alto para celulares pequeños */
    }
}

/* 📱 Mejorar scroll en móviles */
.modal-contenido::-webkit-scrollbar {
    width: 6px;
}
.modal-contenido::-webkit-scrollbar-thumb {
    background: #2563eb;
    border-radius: 10px;
}
.producto {
    display:flex;
    gap:15px;
    padding:12px;
    margin-bottom:10px;
    border-bottom:1px solid #ddd;
}
.producto img {
    width:110px;
    height:110px;
    border-radius:8px;
    object-fit:cover;
}

.btn-add {
    background:#16a34a;
    color:white;
    border:none;
    padding:10px 16px;
    border-radius:8px;
    cursor:pointer;
    font-weight:bold;
}

.btn-cerrar {
    background:#dc2626;
    color:white;
    border:none;
    padding:8px 14px;
    border-radius:8px;
    cursor:pointer;
}

input[type=number]{
    padding:6px;
    width:80px;
    border-radius:6px;
    border:1px solid #ccc;
}
</style>

</head>
<body>

<?php include("barra_sup.php"); ?>

<!-- TÍTULO -->
<div class="contenedor-titulo">
    <h1>Bienvenido a <span>BoliviaMarket</span> 🇧🇴</h1>
    <p>Compra productos frescos directo del productor.</p>
</div>

<?php

include("buscador_productos.php");
?>
<!-- CATEGORÍAS -->
<div class="categorias">

    <?php 
    $categorias = [
        ["nombre"=>"Verduras", "imagen"=>"hortalizas.jpg"],
        ["nombre"=>"Frutas", "imagen"=>"frutas.jpg"],
        ["nombre"=>"Bebidas", "imagen"=>"bebidas.jpg"],
        ["nombre"=>"Lácteos", "imagen"=>"lacteos.jpg"],
        ["nombre"=>"Alimentos", "imagen"=>"comida.jpg"]
    ];

    foreach($categorias as $cat){
        echo '
        <div class="tarjeta" onclick="verCategoria(\''.$cat['nombre'].'\')">
            <img src="imagenes/'.$cat['imagen'].'" alt="'.$cat['nombre'].'">
            <div class="tarjeta-texto">'.$cat['nombre'].'</div>
        </div>';
    }
    ?>

</div>

<!-- MODAL -->
<div id="modalProductos">
    <div class="modal-contenido" id="contenidoModal">
        <button class="btn-cerrar" onclick="cerrarModal()">Cerrar</button>
        <h2 id="tituloCat"></h2>
        <div id="listaProductos"></div>
    </div>
</div>

<script>
function verCategoria(sector){
    document.getElementById("modalProductos").style.display = "flex";
    document.getElementById("tituloCat").innerText = sector;

    fetch("productos_ajax.php?sector=" + sector)
    .then(r => r.json())
    .then(data => mostrarProductos(data));
}

function cerrarModal(){
    document.getElementById("modalProductos").style.display = "none";
}

/* MOSTRAR PRODUCTOS */
function mostrarProductos(lista){
    let div = document.getElementById("listaProductos");
    div.innerHTML = "";

    if(lista.length === 0){
        div.innerHTML = "<p>No hay productos disponibles.</p>";
        return;
    }

    lista.forEach(p => {
        let precio = p.precio_menor;

        div.innerHTML += `
        <div class="producto">

            <img src="${p.imagen}" />

            <div style="flex:1">
                <h3>${p.nombre}</h3>

                <p><b>Precio:</b> Bs. ${precio}</p>
            
                <p><b>Vendedor:</b> ${p.codigo_vendedor}</p>

                <label>Cantidad:</label>
                <input type="number" min="1" value="1" id="cant_${p.id}">
                
                <button class="btn-add" onclick="agregarCarrito(${p.id}, '${p.nombre}', ${precio}, '${p.imagen}', 'unidad', 'cant_${p.id}')">
                    Añadir al carrito
                </button>
            </div>
        </div>
        `;
    });
}

/* AÑADIR AL CARRITO */
function agregarCarrito(id, nombre, precio, imagen, unidad, idCantidad){
    let cant = document.getElementById(idCantidad).value;

    let fd = new FormData();
    fd.append("accion","agregar");
    fd.append("id", id);
    fd.append("nombre", nombre);
    fd.append("precio", precio);
    fd.append("cantidad", cant);
    fd.append("unidad", unidad);
    fd.append("imagen", imagen);

    fetch("carrito.php", {
        method:"POST",
        body: fd
    })
    .then(r => r.text())
    .then(resp => {
        if(resp.trim() === "OK"){
            alert("Producto añadido ✔");
        } else {
            alert("Error al añadir ❌\n\n" + resp);
        }
    });
}
</script> 


</body>
</html>
