<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'p_analisisydiseño'; // Base de datos que usarás

$conexion = mysqli_connect($host, $user, $password, $database);

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}
?>
