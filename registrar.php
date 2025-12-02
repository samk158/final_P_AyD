<?php

include('conexion.php'); // Ajusta según tu ruta



$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $email = mysqli_real_escape_string($conexion, $_POST['email']);
    $password = $_POST['password'];
    $password2 = $_POST['password2'];

    if (empty($nombre) || empty($email) || empty($password) || empty($password2)) {
        $errors[] = "Completa todos los campos.";
    } elseif ($password !== $password2) {
        $errors[] = "Las contraseñas no coinciden.";
    } else {
        // Verificar si el email ya existe
        $check = "SELECT id FROM users WHERE email='$email'";
        $res = mysqli_query($conexion, $check);

        if (mysqli_num_rows($res) > 0) {
            $errors[] = "Ya existe un usuario con ese email.";
        } else {
            // Insertar nuevo admin
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (nombre, email, password_hash, role)
                    VALUES ('$nombre', '$email', '$hash', 'admin')";
            if (mysqli_query($conexion, $sql)) {
                $success = "Administrador creado correctamente.";
            } else {
                $errors[] = "Error al crear el administrador: " . mysqli_error($conexion);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Dashboard - Crear Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
<style>
:root{
  --indigo:#1E3A8A;
  --blue:#2563EB;
  --light:#F9FAFB;
  --white:#ffffff;
  --gray:#6B7280;
  --success:#34D399;
  --error:#F87171;
}
body{
  font-family:'Inter',sans-serif;
  margin:0;
  background:var(--light);
  display:flex;
  justify-content:center;
  align-items:flex-start;
  min-height:100vh;
  padding:50px 0;
}
.card{
  background:var(--white);
  width:450px;
  padding:40px;
  border-radius:16px;
  box-shadow:0 15px 35px rgba(0,0,0,0.1);
}
.card h2{color:var(--indigo); text-align:center; margin-bottom:20px;}
input{
  width:100%; padding:12px; margin-bottom:15px; border-radius:8px; border:1px solid #ccc;
}
button{
  width:100%; padding:12px; background:var(--blue); color:white; border:none; border-radius:8px; font-weight:600; cursor:pointer;
  transition:all 0.3s ease;
}
button:hover{
  background:var(--indigo);
}
.errors{background:var(--error); color:white; padding:10px; border-radius:6px; margin-bottom:15px;}
.success{background:var(--success); color:white; padding:10px; border-radius:6px; margin-bottom:15px;}
</style>
</head>
<body>

<div class="card">
<h2>Crear Nuevo Administrador</h2>

<?php if(!empty($errors)): ?>
<div class="errors">
    <?php foreach($errors as $e) echo $e."<br>"; ?>
</div>
<?php endif; ?>

<?php if($success != ""): ?>
<div class="success"><?= $success ?></div>
<?php endif; ?>

<form method="POST">
<input type="text" name="nombre" placeholder="Nombre completo" required>
<input type="email" name="email" placeholder="Correo electrónico" required>
<input type="password" name="password" placeholder="Contraseña" required>
<input type="password" name="password2" placeholder="Repetir contraseña" required>
<button type="submit">Crear Administrador</button>
</form>

</div>
</body>
</html>
