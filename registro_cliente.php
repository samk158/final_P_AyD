<?php

include('barra_sup.php');
include('conexion.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $telefono = mysqli_real_escape_string($conexion, $_POST['telefono']);
    $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
    $codigo_cliente = mysqli_real_escape_string($conexion, $_POST['codigo_cliente']);
    $direccion = mysqli_real_escape_string($conexion, $_POST['direccion']);
    $latitud = mysqli_real_escape_string($conexion, $_POST['latitud']);
    $longitud = mysqli_real_escape_string($conexion, $_POST['longitud']);

    $check = mysqli_query($conexion, "SELECT * FROM clientes WHERE codigo_cliente='$codigo_cliente'");
    if (mysqli_num_rows($check) > 0) {
        echo "<script>alert('⚠️ El código de cliente ya existe. Elija otro.');</script>";
    } else {
        $sql = "INSERT INTO clientes (nombre, telefono, correo, codigo_cliente, direccion_entrega, latitud, longitud)
                VALUES ('$nombre', '$telefono', '$correo', '$codigo_cliente', '$direccion', '$latitud', '$longitud')";

        if (mysqli_query($conexion, $sql)) {
            $_SESSION['nombre'] = explode(" ", $nombre)[0]; // Guarda el primer nombre en sesión
            echo "<script>alert('✅ Registro exitoso. ¡Bienvenido, {$nombre}!'); window.location='index.php';</script>";
        } else {
            echo "<script>alert('❌ Error al registrar cliente: " . mysqli_error($conexion) . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registro de Cliente | BoliviaMarket</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

<style>
/* 🔹 Encapsulamos todos los estilos de registro dentro del contenedor */
.registro-container {
  font-family: 'Poppins', sans-serif;
  background-color: #ffffff;
  color: #333;
  min-height: 100vh;
  padding: 130px 20px 30px 20px; /* espacio para barra superior */
  display: flex;
  justify-content: center;
  align-items: flex-start;
}

.registro-box {
  width: 100%;
  max-width: 600px;
  background: #fff;
  border-radius: 15px;
  box-shadow: 0 4px 25px rgba(0,0,0,0.08);
  padding: 30px;
}

.registro-box h2 {
  text-align: center;
  color: #0f172a;
  font-weight: 600;
  margin-bottom: 10px;
}

.registro-box p.sub {
  text-align: center;
  color: #475569;
  margin-bottom: 30px;
}

.registro-box label {
  display: block;
  margin-top: 15px;
  font-weight: 600;
  color: #1e293b;
}

.registro-box input, .registro-box textarea {
  width: 100%;
  padding: 10px 12px;
  margin-top: 6px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 15px;
  color: #111827;
  background: #f9fafb;
}

.registro-box textarea {
  resize: none;
  height: 70px;
}

#map {
  width: 100%;
  height: 250px;
  border-radius: 10px;
  margin-top: 10px;
}

.registro-box button {
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

.registro-box button:hover {
  background: #1e40af;
}

@media (max-width: 600px) {
  .registro-container {
    padding: 120px 10px 20px 10px;
  }
  .registro-box { padding: 20px; }
  .registro-box h2 { font-size: 1.5em; }
  .registro-box p.sub { font-size: 1em; }
}
</style>

<!-- Google Maps -->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA_Mw4zV5gxNVKldh34UeQIzzidnmvLx7c"></script>
<script>
let map, marker;
function initMap() {
  const defaultLocation = { lat: -16.5, lng: -68.15 }; // La Paz
  map = new google.maps.Map(document.getElementById("map"), {
    center: defaultLocation,
    zoom: 13,
  });

  marker = new google.maps.Marker({
    position: defaultLocation,
    map: map,
    draggable: true,
    title: "Selecciona tu ubicación de entrega",
  });

  google.maps.event.addListener(marker, 'dragend', function() {
    document.getElementById('latitud').value = marker.getPosition().lat();
    document.getElementById('longitud').value = marker.getPosition().lng();
  });
}
</script>
</head>

<body onload="initMap()">
  <div class="registro-container">
    <div class="registro-box">
      <h2>Registro de Cliente 🛍️</h2>
      <p class="sub">Completa tus datos para comenzar a comprar en <strong>BoliviaMarket</strong></p>

      <form method="POST" action="">
        <label>Nombre completo</label>
        <input type="text" name="nombre" placeholder="Ej. María Quispe" required>

        <label>Teléfono</label>
        <input type="text" name="telefono" placeholder="Ej. 78945612" required>

        <label>Correo electrónico</label>
        <input type="email" name="correo" placeholder="Ej. maria@gmail.com" required>

        <label>Código de cliente (contraseña)</label>
        <input type="password" name="codigo_cliente" placeholder="Crea un código único" required>

        <label>Ubicación de entrega del pedido</label>
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
