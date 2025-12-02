<?php
session_start();

if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
    header("Location: hortalizas.php");
    exit;
}

// Calcular total
$total = 0;
foreach ($_SESSION['carrito'] as $item) {
    $total += $item['precio'] * $item['cantidad'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Generar Orden</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
body{font-family:'Poppins',sans-serif;background:#f3f4f6;padding:20px;}
.contenedor{
    max-width:700px;margin:auto;background:white;padding:20px;
    border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,0.1);
}
h2{text-align:center;margin-bottom:20px;}
input,textarea,select{
    width:100%;padding:10px;margin-top:5px;margin-bottom:15px;
    border:1px solid #ccc;border-radius:8px;font-size:15px;
}
.btn{
    background:#2563eb;color:white;padding:12px;
    border:none;width:100%;border-radius:10px;
    font-weight:600;cursor:pointer;font-size:16px;
}
</style>
</head>
<body>

<div class="contenedor">
    <h2>📦 Método de Entrega</h2>

    <form action="procesar_entrega.php" method="POST">

        <label><b>Seleccione una opción:</b></label>

        <select name="entrega" required>
            <option value="">-- Seleccione --</option>
            <option value="recojo">🚶 Recojo en almacén</option>
            <option value="delivery">🚚 Delivery</option>
        </select>

        <div id="deliveryData" style="display:none;">
            <label><b>Dirección de entrega:</b></label>
            <textarea name="direccion"></textarea>

            <label><b>Teléfono de contacto:</b></label>
            <input type="text" name="telefono">
        </div>

        <button type="submit" class="btn">Generar Orden</button>
    </form>
</div>

<script>
document.querySelector("select[name='entrega']").addEventListener("change", function(){
    document.getElementById("deliveryData").style.display =
        this.value === "delivery" ? "block" : "none";
});
</script>

</body>
</html>

