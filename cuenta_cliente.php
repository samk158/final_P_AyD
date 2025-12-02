<?php

include('barra_sup.php');
include('conexion.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $codigo = mysqli_real_escape_string($conexion, $_POST['codigo']);

    // Buscar en clientes
    $query_cliente = "SELECT * FROM clientes WHERE nombre='$nombre' AND codigo_cliente='$codigo'";
    $result_cliente = mysqli_query($conexion, $query_cliente);

    if (mysqli_num_rows($result_cliente) == 1) {
        $row = mysqli_fetch_assoc($result_cliente);
        $_SESSION['tipo_usuario'] = 'cliente';
        $_SESSION['nombre'] = $row['nombre'];
        $_SESSION['codigo'] = $row['codigo_cliente'];

        echo "<script>alert('✅ Bienvenido, " . htmlspecialchars(explode(' ', $row['nombre'])[0]) . "!'); window.location='index.php';</script>";
        exit;
    } else {
        // Buscar en vendedores
        $query_vendedor = "SELECT * FROM vendedores WHERE nombre='$nombre' AND codigo_vendedor='$codigo'";
        $result_vendedor = mysqli_query($conexion, $query_vendedor);

        if (mysqli_num_rows($result_vendedor) == 1) {
            $row = mysqli_fetch_assoc($result_vendedor);
            $_SESSION['tipo_usuario'] = 'vendedor';
            $_SESSION['nombre'] = $row['nombre'];
            $_SESSION['codigo'] = $row['codigo_vendedor'];

            echo "<script>alert('✅ Bienvenido Vendedor, " . htmlspecialchars(explode(' ', $row['nombre'])[0]) . "!'); window.location='perfil_vendedor.php';</script>";
            exit;
        } else {
            echo "<script>alert('❌ Nombre o código incorrecto. Intenta nuevamente.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ingreso | BoliviaMarket</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
/* 🔹 Estilos aislados para el login */
.login-page {
  font-family: 'Poppins', sans-serif;
  background-color: #ffffff;
  min-height: 100vh;
  padding-top: 130px; /* espacio para barra superior */
  display: flex;
  justify-content: center;
  align-items: flex-start;
}

.login-box {
  width: 100%;
  max-width: 480px;
  background: #fff;
  border-radius: 15px;
  box-shadow: 0 4px 25px rgba(0,0,0,0.08);
  padding: 30px;
}

.login-box h2 {
  text-align: center;
  color: #0f172a;
  font-weight: 600;
  margin-bottom: 10px;
}

.login-box p.sub {
  text-align: center;
  color: #475569;
  margin-bottom: 30px;
}

.login-box label {
  display: block;
  margin-top: 15px;
  font-weight: 600;
  color: #1e293b;
}

.login-box input {
  width: 100%;
  padding: 10px 12px;
  margin-top: 6px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 15px;
  color: #111827;
  background: #f9fafb;
}

.login-box button {
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

.login-box button:hover {
  background: #1e40af;
}

@media (max-width: 600px){
  .login-page { padding-top: 120px; }
  .login-box { padding: 20px; }
}
</style>
</head>

<body>
  <div class="login-page">
    <div class="login-box">
      <h2>Ingreso 🛒</h2>
      <p class="sub">Ingresa tus datos para acceder a tu cuenta en <strong>BoliviaMarket</strong></p>

      <form method="POST" action="">
        <label>Nombre completo</label>
        <input type="text" name="nombre" placeholder="Ej. María Quispe" required>

        <label>Código o contraseña</label>
        <input type="password" name="codigo" placeholder="Tu código único o contraseña" required>

        <button type="submit">🔑 Ingresar</button>
      </form>
    </div>
  </div>
</body>
</html>
