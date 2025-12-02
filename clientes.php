<?php
session_start();
include(__DIR__ . '/../conexion.php');
 // ← se sube un nivel

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = mysqli_real_escape_string($conexion, $_POST['email']);
    $password = $_POST['password'];

    if ($email == "" || $password == "") {
        $errors[] = "Completa todos los campos.";
    } else {
        // Buscar usuario admin
        $sql = "SELECT id, nombre, email, password_hash, role FROM users WHERE email = '$email' LIMIT 1";
        $result = $conexion->query($sql);

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();

            if ($user['role'] === 'admin' && password_verify($password, $user['password_hash'])) {
                
                // Guardar sesión
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['nombre'];
                $_SESSION['tipo_usuario'] = 'admin';

                header("Location: dashboard.php");
                exit;
            } else {
                $errors[] = "Credenciales incorrectas o no tiene permisos de administrador.";
            }
        } else {
            $errors[] = "Usuario no encontrado.";
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Admin - Login</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
/* Colores pedidos */
:root {
  --indigo:#1E3A8A;
  --blue:#2563EB;
  --bg:#F3F4F6;
  --white:#ffffff;
}
*{box-sizing:border-box;font-family:Inter;}
body{
  margin:0;
  background:linear-gradient(180deg,var(--indigo),var(--blue));
  height:100vh;
  display:flex;
  justify-content:center;
  align-items:center;
  padding:20px;
}
.card{
  width:380px;
  background:var(--white);
  padding:30px;
  border-radius:12px;
  box-shadow:0 8px 30px rgba(0,0,0,0.15);
}
.card h2{
  margin-top:0;
  color:var(--indigo);
}
label{
  font-size:14px;
  font-weight:600;
}
input{
  width:100%;
  padding:12px;
  border:1px solid #ccc;
  border-radius:8px;
  margin-top:6px;
  margin-bottom:15px;
}
.btn{
  width:100%;
  padding:12px;
  background:var(--blue);
  border:none;
  border-radius:8px;
  color:white;
  font-weight:700;
  cursor:pointer;
}
.btn:hover{
  background:#1D4ED8;
}
.errors{
  background:#FFE5E5;
  padding:10px;
  border-radius:6px;
  color:#991B1B;
  margin-bottom:15px;
}
a{
  text-decoration:none;
  color:var(--blue);
  font-size:14px;
}
</style>
</head>
<body>

<div class="card">
  <h2>Panel Administrativo</h2>
  <p>Inicia sesión para continuar</p>

  <?php if(!empty($errors)): ?>
  <div class="errors">
      <?php foreach($errors as $e): ?>
          <div><?= $e ?></div>
      <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <form method="POST">
      <label>Email</label>
      <input type="email" name="email" placeholder="admin@empresa.com" required>

      <label>Contraseña</label>
      <input type="password" name="password" placeholder="••••••••" required>

      <button class="btn" type="submit">Ingresar</button>
  </form>

  <p style="text-align:center;margin-top:10px;">
      <a href="../index.php">Volver a la tienda</a>
  </p>
</div>

</body>
</html>
