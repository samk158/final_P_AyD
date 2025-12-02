<?php
include("conexion.php");

$sector = isset($_GET['sector']) ? trim($_GET['sector']) : '';

if($sector != ''){
    // Consulta uniendo productos con vendedores
$sql = "SELECT id, nombre, imagen, precio_menor, precio_mayor, codigo_vendedor 
        FROM productos 
        WHERE LOWER(TRIM(sector)) = LOWER(?) AND estado = 'publicado'";

    
    $stmt = $conexion->prepare($sql);
    if(!$stmt){
        echo json_encode(["error" => "Error al preparar la consulta: " . $conexion->error]);
        exit;
    }

    $stmt->bind_param("s", $sector);
    $stmt->execute();
    $result = $stmt->get_result();
    $productos = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode($productos);
} else {
    echo json_encode([]);
}
?>
