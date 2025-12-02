<?php

include('barra_sup.php');
include('conexion.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $telefono = mysqli_real_escape_string($conexion, $_POST['telefono']);
    $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
    $codigo_vendedor = mysqli_real_escape_string($conexion, $_POST['codigo_vendedor']);
    $codigo_confirm = mysqli_real_escape_string($conexion, $_POST['codigo_confirm']);
    $direccion = mysqli_real_escape_string($conexion, $_POST['direccion']);
    $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);
    $sectores = isset($_POST['sectores']) ? implode(",", $_POST['sectores']) : '';
    $latitud = mysqli_real_escape_string($conexion, $_POST['latitud']);
    $longitud = mysqli_real_escape_string($conexion, $_POST['longitud']);

    // Verificar que las contraseñas coincidan
    if ($codigo_vendedor !== $codigo_confirm) {
        echo "<script>alert('❌ Las contraseñas no coinciden. Intenta nuevamente.');</script>";
    } else {

        // Verificar que el código no exista
        $check = mysqli_query($conexion, "SELECT * FROM vendedores WHERE codigo_vendedor='$codigo_vendedor'");

        if (mysqli_num_rows($check) > 0) {
            echo "<script>alert('⚠️ El código de vendedor ya existe. Elija otro.');</script>";
        } else {

            // Insertar nuevo vendedor
            $sql = "INSERT INTO vendedores 
                    (nombre, telefono, correo, codigo_vendedor, direccion, sector, descripcion, latitud, longitud)
                    VALUES 
                    ('$nombre', '$telefono', '$correo', '$codigo_vendedor', '$direccion', '$sectores', '$descripcion', '$latitud', '$longitud')";

            if (mysqli_query($conexion, $sql)) {
                echo "<script>alert('✅ Registro exitoso. ¡Bienvenido, $nombre!'); window.location='index.php';</script>";
            } else {
                echo "<script>alert('❌ Error al registrar vendedor: " . mysqli_error($conexion) . "');</script>";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registro de Vendedor | BoliviaMarket</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

<style>

/* ===== Estilos SOLO para el contenido de vendedores ===== */

.contenido-vendedor * {
    box-sizing: border-box;
}

.contenido-vendedor {
    font-family: 'Poppins', sans-serif;
    background: #ffffff;
    color: #333;
    min-height: 100vh;
    padding-top: 120px; /* deja espacio para barra superior */
}

.contenido-vendedor h2 {
    text-align: center;
    color: #0f172a;
    font-weight: 600;
    margin-bottom: 10px;
}

.contenido-vendedor p.sub {
    text-align: center;
    color: #475569;
    margin-bottom: 30px;
}

.contenido-vendedor .container {
    width: 90%;
    max-width: 600px;
    margin: 0 auto;
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 4px 25px rgba(0,0,0,0.08);
    padding: 30px;
}

.contenido-vendedor label {
    display: block;
    margin-top: 15px;
    font-weight: 600;
    color: #1e293b;
}

.contenido-vendedor input,
.contenido-vendedor textarea {
    width: 100%;
    padding: 10px 12px;
    margin-top: 6px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 15px;
    color: #111827;
    background: #f9fafb;
}

.contenido-vendedor textarea {
    resize: none;
    height: 70px;
}

.contenido-vendedor #map {
    width: 100%;
    height: 250px;
    border-radius: 10px;
    margin-top: 10px;
}

.contenido-vendedor .sectores {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 6px;
}

.contenido-vendedor .sectores label {
    flex: 1 0 45%;
    font-weight: 500;
    cursor: pointer;
}

.contenido-vendedor button {
    width: 100%;
    margin-top: 25px;
    padding: 12px;
    font-size: 16px;
    font-weight: 600;
    color: white;
    background: #2563eb;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    transition: background 0.3s ease;
}

.contenido-vendedor button:hover {
    background: #1e40af;
}

@media (max-width: 600px) {

    .contenido-vendedor {
        padding-top: 100px;
    }

    .contenido-vendedor .container {
        padding: 20px;
    }

    .contenido-vendedor h2 {
        font-size: 1.5em;
    }

    .contenido-vendedor p.sub {
        font-size: 1em;
    }
}

</style>

<!-- Google Maps -->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA_Mw4zV5gxNVKldh34UeQIzzidnmvLx7c"></script>

<script>

let map, marker;

function initMap() {

    const defaultLocation = { lat: -16.5, lng: -68.15 };

    map = new google.maps.Map(document.getElementById("map"), {
        center: defaultLocation,
        zoom: 13
    });

    marker = new google.maps.Marker({
        position: defaultLocation,
        map: map,
        draggable: true,
        title: "Selecciona tu ubicación"
    });

    google.maps.event.addListener(marker, 'dragend', function() {
        document.getElementById('latitud').value = marker.getPosition().lat();
        document.getElementById('longitud').value = marker.getPosition().lng();
    });
}

</script>

</head>

<body onload="initMap()">

<div class="contenido-vendedor">
    <div class="container">

        <h2>Registro de Vendedor 🏪</h2>
        <p class="sub">Completa tus datos para empezar a vender en <strong>BoliviaMarket</strong></p>

        <form method="POST" action="">

            <label>Nombre completo</label>
            <input type="text" name="nombre" placeholder="Ej. Juan Pérez" required>

            <label>Teléfono</label>
            <input type="text" name="telefono" placeholder="Ej. 78945612" required>

            <label>Correo electrónico</label>
            <input type="email" name="correo" placeholder="Ej. juan@gmail.com" required>

            <label>Contraseña</label>
            <input type="password" name="codigo_vendedor" placeholder="Crea tu contraseña única" required>

            <label>Confirmar contraseña</label>
            <input type="password" name="codigo_confirm" placeholder="Repite la contraseña" required>

            <label>Descripción de tu tienda</label>
            <textarea name="descripcion" placeholder="Ej. Venta de productos bolivianos de alta calidad" required></textarea>

            <label>Selecciona los sectores donde venderás</label>

            <div class="sectores">
                <label><input type="checkbox" name="sectores[]" value="Alimentos"> Alimentos</label>
                <label><input type="checkbox" name="sectores[]" value="Bebidas"> Bebidas</label>
                <label><input type="checkbox" name="sectores[]" value="Verduras"> Verduras</label>
                <label><input type="checkbox" name="sectores[]" value="Hogar y Muebles"> Lacteos</label>
                <label><input type="checkbox" name="sectores[]" value="Tecnología y Electrónica"> Frutas</label>
            </div>

            <label>Ubicación de tu tienda</label>
            <textarea name="direccion" placeholder="Ej. Calle Aroma #45, Zona Central, Cochabamba" required></textarea>

            <div id="map"></div>

            <input type="hidden" id="latitud" name="latitud">
            <input type="hidden" id="longitud" name="longitud">

            <button type="submit">✅ Finalizar Registro</button>

        </form>

    </div>
</div>

</body>
</html>
