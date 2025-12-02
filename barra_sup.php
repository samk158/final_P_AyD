<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('conexion.php');

$nombre_usuario = '';
$tipo_usuario = 'cliente';

if(isset($_SESSION['codigo'])){
    $codigo = $_SESSION['codigo'];

    // Verificar clientes
    $sql_cliente = "SELECT nombre FROM clientes WHERE codigo_cliente='$codigo' LIMIT 1";
    $res_cliente = mysqli_query($conexion, $sql_cliente);
    if(mysqli_num_rows($res_cliente) > 0){
        $row = mysqli_fetch_assoc($res_cliente);
        $nombre_usuario = $row['nombre'];
        $tipo_usuario = 'cliente';
    } else {
        // Verificar vendedores
        $sql_vendedor = "SELECT nombre FROM vendedores WHERE codigo_vendedor='$codigo' LIMIT 1";
        $res_vendedor = mysqli_query($conexion, $sql_vendedor);
        if(mysqli_num_rows($res_vendedor) > 0){
            $row = mysqli_fetch_assoc($res_vendedor);
            $nombre_usuario = $row['nombre'];
            $tipo_usuario = 'vendedor';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mercado Azul Fresco</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{
    font-family:'Inter',sans-serif;
    background:#f3f4f6;
    padding-top:78px;
}

/* ---------------------------------------------------
    BARRA SUPERIOR AZUL + VERDE (Diseño profesional)
---------------------------------------------------- */
.barra-superior{
    width:100%;
    height:78px;
    position:fixed;
    top:0; left:0;
    background:linear-gradient(135deg,#1E3A8A,#2563EB);
    color:white;
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:0 26px;
    z-index:1000;
    box-shadow:0 6px 18px rgba(0,0,0,0.25);
}

/* LOGO con icono verde que combina con azul */
.logo{
    font-size:1.7rem;
    font-weight:700;
    display:flex;
    align-items:center;
    gap:10px;
    cursor:pointer;
    letter-spacing:0.5px;
}

.logo::before{
    content:"🥬";
    font-size:1.5rem;
    filter:drop-shadow(0 1px 1px rgba(0,0,0,0.3));
}

/* Botón menú móvil */
.menu-toggle{
    display:none;
    background:none;
    border:none;
    font-size:2rem;
    color:white;
    cursor:pointer;
}

/* Contenedor del menú */
.menu-contenedor{
    display:flex;
    align-items:center;
    gap:14px;
}

/* Botones con azul translúcido */
button{
    padding:8px 16px;
    border:none;
    border-radius:50px;
    background:rgba(255,255,255,0.15);
    color:white;
    font-weight:500;
    font-size:0.95rem;
    cursor:pointer;
    transition:0.25s ease;
}
button:hover{
    background:rgba(255,255,255,0.30);
    transform:translateY(-1px);
}

/* Buscador con verde menta */
input[type="search"]{
    padding:8px 14px;
    border:none;
    border-radius:50px;
    background:rgba(255,255,255,0.18);
    color:white;
    width:180px;
    transition:0.25s;
}
input::placeholder{
    color:#dbeafe;
}
input:focus{
    background:white;
    color:#2563EB;
    width:220px;
    outline:none;
}

/* Icono carrito */
.icono-carrito{
    font-size:1.4rem;
    cursor:pointer;
    transition:0.2s ease;
}
.icono-carrito:hover{
    transform:scale(1.18);
}

/* ---------------------------------------------------
    RESPONSIVE
---------------------------------------------------- */
@media(max-width:900px){

    .menu-toggle{
        display:block;
    }

    .menu-contenedor{
        display:none;
        flex-direction:column;
        align-items:flex-start;
        gap:18px;
        position:fixed;
        top:78px;
        left:0;
        width:100%;
        height:calc(100vh - 78px);
        padding:22px;
        background:rgba(30,58,138,0.95); /* Azul oscuro transparente */
        backdrop-filter:blur(8px);
        overflow-y:auto;
    }

    .menu-contenedor.show{
        display:flex;
    }

    .menu-contenedor button,
    .menu-contenedor input{
        width:100%;
        font-size:1.05rem;
    }
}
</style>
</head>

<body>

<header class="barra-superior">
    <div class="logo" onclick="window.location.href='index.php'">
        <?= $nombre_usuario ? htmlspecialchars(explode(' ', $nombre_usuario)[0]) : 'Mi Mercado'; ?>
    </div>

    <button class="menu-toggle" onclick="toggleMenu()">☰</button>

<div class="menu-contenedor" id="menu">

    <?php if(!$nombre_usuario): ?>
        <!-- -------------------------------
             USUARIO INVITADO (SIN SESIÓN)
             ------------------------------ -->
        <button onclick="window.location.href='index.php'">Inicio</button>
        <button onclick="window.location.href='vender.php'">Vender</button>
        <button onclick="window.location.href='soporte.php'">Ayuda</button>

        <button onclick="window.location.href='cuenta_cliente.php'">Mi Cuenta</button>
        <button onclick="window.location.href='registro_cliente.php'">Registrarse</button>

        <div class="icono-carrito" onclick="window.location.href='carrito.php'">🛒</div>
        <input type="search" placeholder="Buscar productos...">

    <?php elseif($tipo_usuario == 'cliente'): ?>
        <!-- -------------------------------
             CLIENTE LOGEADO
             ------------------------------ -->
        <button onclick="window.location.href='index.php'">Inicio</button>
        <button onclick="window.location.href='soporte.php'">Ayuda</button>

        <button onclick="window.location.href='cerrar_sesion.php'">Cerrar sesión</button>

        <div class="icono-carrito" onclick="window.location.href='carrito.php'">🛒</div>
 

    <?php elseif($tipo_usuario == 'vendedor'): ?>
        <!-- -------------------------------
             VENDEDOR LOGEADO
             ------------------------------ -->
        <button onclick="window.location.href='ventas.php'">Gestión de Ventas</button>
        <button onclick="window.location.href='perfil_vendedor.php'">Perfil</button>
        <button onclick="window.location.href='cerrar_sesion.php'">Cerrar sesión</button>

    <?php endif; ?>

</div>

</header>

<script>
function toggleMenu(){
    document.getElementById("menu").classList.toggle("show");
}
</script>

</body>
</html>
