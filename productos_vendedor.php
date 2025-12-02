<?php

include('barra_sup.php'); 
include('conexion.php');


$codigo_vendedor = $_SESSION['codigo'];

$sectores = ['Alimentos','Bebidas','Lacteos','Verduras','Frutas'];

if(isset($_POST['new_id'])){
    $nombre = mysqli_real_escape_string($conexion,$_POST['nombre']);
    $codigo_producto = mysqli_real_escape_string($conexion,$_POST['codigo_producto']);
    $cantidad = intval($_POST['cantidad']);
    $precio_menor = floatval($_POST['precio_menor']);
    $precio_mayor = floatval($_POST['precio_mayor']);
    $sector = mysqli_real_escape_string($conexion,$_POST['sector']);
    $imagen = '';
    $estado = 'no publicado';

    if(isset($_FILES['imagen']) && $_FILES['imagen']['error']==0){
        $carpeta_subida='uploads/';
        if(!is_dir($carpeta_subida)) mkdir($carpeta_subida,0755,true);
        $nombre_archivo = time().'_'.basename($_FILES['imagen']['name']);
        $ruta_archivo = $carpeta_subida.$nombre_archivo;
        if(move_uploaded_file($_FILES['imagen']['tmp_name'],$ruta_archivo)) $imagen=$ruta_archivo;
    }

    mysqli_query($conexion,"INSERT INTO productos 
        (codigo_vendedor,nombre,codigo_producto,cantidad,precio_menor,precio_mayor,sector,imagen,estado)
        VALUES ('$codigo_vendedor','$nombre','$codigo_producto','$cantidad','$precio_menor','$precio_mayor','$sector','$imagen','$estado')");
    header("Location: productos_vendedor.php");
    exit;
}

if(isset($_POST['update_id'])){
    $id = intval($_POST['update_id']);
    $nombre = mysqli_real_escape_string($conexion,$_POST['nombre']);
    $codigo_producto = mysqli_real_escape_string($conexion,$_POST['codigo_producto']);
    $cantidad = intval($_POST['cantidad']);
    $precio_menor = floatval($_POST['precio_menor']);
    $precio_mayor = floatval($_POST['precio_mayor']);
    $sector = mysqli_real_escape_string($conexion,$_POST['sector']);
    $imagen = $_POST['current_image'];
    $estado = $_POST['estado'] ?? 'no publicado';

    if(isset($_FILES['imagen']) && $_FILES['imagen']['error']==0){
        $carpeta_subida='uploads/';
        if(!is_dir($carpeta_subida)) mkdir($carpeta_subida,0755,true);
        $nombre_archivo = time().'_'.basename($_FILES['imagen']['name']);
        $ruta_archivo_comp = $carpeta_subida.$nombre_archivo;
        if(move_uploaded_file($_FILES['imagen']['tmp_name'],$ruta_archivo_comp)) $imagen=$ruta_archivo_comp;
    }

    mysqli_query($conexion,"UPDATE productos SET 
        nombre='$nombre',
        codigo_producto='$codigo_producto',
        cantidad='$cantidad',
        precio_menor='$precio_menor',
        precio_mayor='$precio_mayor',
        sector='$sector',
        imagen='$imagen',
        estado='$estado'
        WHERE id='$id' AND codigo_vendedor='$codigo_vendedor'");
    header("Location: productos_vendedor.php");
    exit;
}

if(isset($_POST['toggle_publish'])){
    $id = intval($_POST['id']);
    $nuevo_estado = $_POST['nuevo_estado'];
    $sector_pub = $_POST['sector_pub'];

    mysqli_query($conexion,"UPDATE productos SET estado='$nuevo_estado', sector='$sector_pub' WHERE id='$id' AND codigo_vendedor='$codigo_vendedor'");
    echo "ok";
    exit;
}

$prodQuery = "SELECT * FROM productos WHERE codigo_vendedor='$codigo_vendedor'";
$prodResult = mysqli_query($conexion,$prodQuery);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestión de Productos | BoliviaMarket</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

<style>

/* ------- DISEÑO GENERAL ------- */
*{box-sizing:border-box;}

body {
    font-family: 'Poppins', sans-serif;
    background: #f1f5f9;
    margin: 0;
}

/* ------- GRID PROFESIONAL 2 COLUMNAS ------- */
#productos-content {
    width: 100%;
    max-width: 1400px;
    margin: 20px auto;
    padding: 20px;

    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 25px;
}

.section { 
    background: #ffffff;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 4px 25px rgba(0,0,0,0.1);
}

.section h2 {
    margin: 0 0 15px 0;
    color: #1e293b;
    font-size: 1.4rem;
    font-weight: 600;
}

/* ------- FORMULARIOS ------- */
form { 
    display: flex; 
    flex-direction: column; 
    gap: 12px; 
}

label {
    font-weight: 600;
    color: #374151;
}

input, select {
    padding: 10px 12px;
    border-radius: 10px;
    border: 1px solid #cbd5e1;
    background: #f8fafc;
    transition: 0.2s;
}

input:focus, select:focus {
    border-color: #2563eb;
    box-shadow: 0px 0px 0px 3px rgba(37,99,235,0.2);
}

button {
    padding: 10px;
    border-radius: 10px;
    background: #2563eb;
    color:white;
    font-weight: 600;
    border:none;
    cursor:pointer;
    transition:0.2s;
}
button:hover { background:#1e40af; }

/* ------- TABLA ------- */
.table-container {
    max-height: 70vh;
    overflow-y: auto;
    border-radius: 10px;
}

table {
    width: 100%;
    border-collapse: collapse;
}

table th {
    background: #eff6ff;
    padding: 12px;
    color: #1e3a8a;
    font-weight: 600;
}

table td {
    border-top: 1px solid #e2e8f0;
    padding: 10px;
    text-align: center;
}

.product-img {
    width:60px;
    height:60px;
    object-fit:cover;
    border-radius:10px;
}

/* ESTADOS */
.badge { 
    padding:5px 10px;
    border-radius:8px;
    color:white;
    font-size:0.8rem;
}
.estado-publicado { background:#16a34a; }
.estado-no-publicado { background:#f97316; }

/* ------- MODAL ------- */
.modal {
    position:fixed;
    top:0; left:0;
    width:100%; height:100%;
    background:rgba(0,0,0,0.5);
    display:none;
    justify-content:center;
    align-items:center;
}

.modal-content {
    background:white;
    padding:20px;
    border-radius:15px;
    width: 90%;
    max-width:400px;
    text-align:center;
}

/* ------- RESPONSIVE ------- */
@media(max-width: 900px){
    #productos-content {
        grid-template-columns: 1fr;
    }
}

</style>
</head>
<body>

<div id="productos-content">

    <!-- FORMULARIO -->
    <div class="section">
        <h2>Registrar Nuevo Producto</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="new_id" value="1">

            <label>Nombre</label>
            <input type="text" name="nombre" required>

            <label>Código</label>
            <input type="text" name="codigo_producto" required>

            <label>Cantidad</label>
            <input type="number" name="cantidad" min="0" required>

            <label>Precio Menor</label>
            <input type="number" step="0.01" name="precio_menor" required>

            <label>Precio Mayor</label>
            <input type="number" step="0.01" name="precio_mayor" required>

            <label>Sector</label>
            <select name="sector" required>
                <?php foreach($sectores as $sec): ?>
                    <option value="<?= $sec ?>"><?= $sec ?></option>
                <?php endforeach; ?>
            </select>

            <label>Imagen</label>
            <input type="file" name="imagen" accept="image/*">

            <button type="submit">➕ Agregar Producto</button>
        </form>
    </div>

    <!-- TABLA -->
    <div class="section">
        <h2>Productos Registrados</h2>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Imagen</th>
                        <th>Nombre</th>
                        <th>Código</th>
                        <th>Cantidad</th>
                        <th>Precio Menor</th>
                        <th>Precio Mayor</th>
                        <th>Sector</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    <?php while($prod=mysqli_fetch_assoc($prodResult)): ?>
                    <tr id="prod_row_<?= $prod['id'] ?>">
                        <td><?php if($prod['imagen']): ?><img src="<?= $prod['imagen'] ?>" class="product-img"><?php else: ?>Sin imagen<?php endif;?></td>
                        <td><?= $prod['nombre'] ?></td>
                        <td><?= $prod['codigo_producto'] ?></td>
                        <td><?= $prod['cantidad'] ?></td>
                        <td><?= $prod['precio_menor'] ?></td>
                        <td><?= $prod['precio_mayor'] ?></td>
                        <td><?= $prod['sector'] ?></td>
                        <td><span class="badge <?= $prod['estado']=='publicado'?'estado-publicado':'estado-no-publicado' ?>"><?= $prod['estado'] ?></span></td>

                        <td>
                            <button onclick="openPublishModal(<?= $prod['id'] ?>,'<?= $prod['estado'] ?>')">
                                <?= $prod['estado']=='publicado' ? 'Dejar de publicar':'Publicar' ?>
                            </button>


                        </td>
                    </tr>
                    <?php endwhile;?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- MODAL -->
<div class="modal" id="publishModal">
    <div class="modal-content">
        <h3>Selecciona el sector para publicar</h3>
        <select id="sectorSelect">
            <?php foreach($sectores as $sec): ?>
            <option value="<?= $sec ?>"><?= $sec ?></option>
            <?php endforeach; ?>
        </select>
        <button onclick="confirmPublish()">Confirmar</button>
        <button onclick="closeModal()">Cancelar</button>
    </div>
</div>

<script>

let currentProdId=0;
let currentEstado='no publicado';

function openPublishModal(id,estado){
    currentProdId=id;
    currentEstado=estado;
    document.getElementById('publishModal').style.display='flex';
}

function closeModal(){
    document.getElementById('publishModal').style.display='none';
}

function confirmPublish(){
    const sector=document.getElementById('sectorSelect').value;
    let nuevo_estado=currentEstado=='publicado'?'no publicado':'publicado';

    const xhr=new XMLHttpRequest();
    xhr.open('POST','productos_vendedor.php',true);
    xhr.setRequestHeader('Content-type','application/x-www-form-urlencoded');
    xhr.onload=function(){
        if (this.responseText == 'ok') {
            const row = document.getElementById('prod_row_' + currentProdId);

            let badge = row.querySelector('td:nth-child(8) .badge');
            badge.textContent = nuevo_estado;
            badge.className = 'badge ' + (nuevo_estado === 'publicado' ? 'estado-publicado' : 'estado-no-publicado');

            const btn = row.querySelector('td:last-child button');
            btn.innerText = nuevo_estado === 'publicado' ? 'Dejar de publicar' : 'Publicar';

            closeModal();
        }
    }
    xhr.send('toggle_publish=1&id='+currentProdId+'&nuevo_estado='+nuevo_estado+'&sector_pub='+sector);
}

function openEditForm(id){
    alert('Función de modificar producto ' + id);
}

</script>

</body>
</html>
