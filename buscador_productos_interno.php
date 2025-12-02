<style>
/* --- BUSCADOR ULTRA PROFESIONAL (SIN TÍTULOS) --- */

.buscador-index {
    width: 100%;
    max-width: 850px;
    margin: 20px auto 35px auto;
    padding: 18px;
    background: #ffffff;
    border-radius: 20px;
    box-shadow: 0px 6px 22px rgba(0,0,0,0.12);
}

/* Caja principal con icono + input + botón */
.search-container {
    display: flex;
    align-items: center;
    gap: 12px;
}

/* Ícono de búsqueda */
.search-icon {
    font-size: 1.5rem;
    color: #2563eb;
    padding: 10px 14px;
    background: #e8efff;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
}

/* Caja de texto */
.search-container input {
    flex: 1;
    padding: 14px 18px;
    border-radius: 14px;
    border: 2px solid #d0d7e2;
    outline: none;
    font-size: 1rem;
    transition: 0.25s;
}

.search-container input:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 4px rgba(37,99,235,0.18);
}

/* Botón buscar */
.search-container button {
    padding: 14px 24px;
    border: none;
    border-radius: 14px;
    background: #2563eb;
    color: white;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: 0.25s;
}

.search-container button:hover {
    background: #1e40af;
}

/* RESULTADOS */
#resultadosBusqueda {
    margin-top: 20px;
    text-align: center;
}

/* Grid productos */
.grid-prods-int {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
    gap: 20px;
    margin-top: 15px;
    padding: 10px;
}

/* Tarjeta individual */
.card-int {
    background: #fff;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(0,0,0,0.10);
    transition: 0.22s;
}
.card-int:hover {
    transform: translateY(-4px);
    box-shadow: 0 6px 22px rgba(0,0,0,0.18);
}

.card-int img {
    width: 100%;
    height: 150px;
    object-fit: cover;
}

.card-int-body {
    padding: 12px 16px;
    text-align: left;
}

.card-int-body h3 {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 700;
}

.card-int-body p {
    margin: 3px 0;
    color: #4b5563;
    font-size: 0.9rem;
}

.card-int-body .precio-prod {
    font-size: 1.15rem;
    font-weight: 700;
    color: #16a34a;
}

.card-int-body button {
    width: 100%;
    margin-top: 10px;
    padding: 10px;
    border: none;
    background: #16a34a;
    color: white;
    border-radius: 10px;
    font-weight: bold;
    cursor: pointer;
    transition: 0.2s;
}

.card-int-body button:hover {
    background: #15803d;
}
</style>

<!-- BUSCADOR PROFESIONAL -->
<div class="buscador-index">
    
    <div class="search-container">
        <div class="search-icon">🔍</div>

        <input id="buscadorIndex" type="search" placeholder="Buscar productos…">

        <button onclick="buscarAhoraIndex()">Buscar</button>
    </div>

    <div id="resultadosBusqueda">
        <p id="mensajeBusqueda" style="color:#6b7280; margin-top:10px;">
            Escribe para comenzar a buscar...
        </p>

        <div id="productosEncontrados" class="grid-prods-int"></div>
    </div>
</div>

<script>
let timerIndex = null;
const inputIndex = document.getElementById("buscadorIndex");
const msgIndex   = document.getElementById("mensajeBusqueda");
const gridIndex  = document.getElementById("productosEncontrados");

inputIndex.addEventListener("input", () => {
    clearTimeout(timerIndex);
    timerIndex = setTimeout(buscarAhoraIndex, 350);
});

function buscarAhoraIndex() {
    let q = inputIndex.value.trim();

    if (q === "") {
        msgIndex.textContent = "Escribe para comenzar a buscar...";
        gridIndex.innerHTML = "";
        return;
    }

    msgIndex.textContent = "Buscando…";

    fetch("buscador_productos.php?ajax=1&q=" + encodeURIComponent(q))
        .then(r => r.json())
        .then(lista => mostrarResultados(lista));
}

function mostrarResultados(lista) {
    gridIndex.innerHTML = "";

    if (!lista.length) {
        msgIndex.textContent = "No se encontraron productos.";
        return;
    }

    msgIndex.textContent = "";

    lista.forEach(p => {
        const precio = p.precio_menor ?? 0;

        gridIndex.innerHTML += `
            <div class="card-int">
                <img src="${p.imagen}">
                <div class="card-int-body">
                    <h3>${p.nombre}</h3>
                    <p class="precio-prod">Bs. ${precio}</p>
                    <p>Vendedor: ${p.codigo_vendedor}</p>

                    <button onclick="agregarCarritoIndex(${p.id}, '${p.nombre}', ${precio}, '${p.imagen}')">
                        🛒 Añadir al carrito
                    </button>
                </div>
            </div>
        `;
    });
}

function agregarCarritoIndex(id,nombre,precio,imagen){
    let fd = new FormData();
    fd.append("accion","agregar");
    fd.append("id",id);
    fd.append("nombre",nombre);
    fd.append("precio",precio);
    fd.append("cantidad",1);
    fd.append("unidad","unidad");
    fd.append("imagen",imagen);

    fetch("carrito.php",{method:"POST",body:fd})
        .then(r=>r.text())
        .then(res=>{
            alert(res.trim()==="OK" ? "Añadido al carrito ✓" : "Error al añadir");
        });
}
</script>
