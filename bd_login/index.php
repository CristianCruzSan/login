<?php
session_start();
require 'db.php';

$error = ""; // Variable para almacenar el mensaje de error

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];

    // Consulta para verificar usuario y contraseña
    $query = "SELECT usuario, tipo_usuario FROM usuarios_ico 
              WHERE usuario = :usuario AND password = :password";
    $stmt = $conexion->prepare($query);
    $stmt->bindParam(':usuario', $usuario);
    $stmt->bindParam(':password', $password);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Si las credenciales son correctas, guardar en sesión y redirigir
        $_SESSION['usuario'] = $user['usuario'];
        $_SESSION['tipo_usuario'] = $user['tipo_usuario'];

        // Redirigir según el tipo de usuario
        if ($user['tipo_usuario'] === 'alumno') {
            header('Location: alumno.php');
        } elseif ($user['tipo_usuario'] === 'admin') {
            header('Location: admin.php');
        } elseif ($user['tipo_usuario'] === 'jefe') {
            header('Location: jefe.php');
        }
        exit; // Asegura que no se ejecute más código después de la redirección
    } else {
        // Si las credenciales son incorrectas, asignar mensaje de error
        $error = "Usuario o contraseña incorrectos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="contenedor">
        <h2>Login de Usuarios</h2>

        <!-- Mostrar mensaje de error solo si existe -->
        <?php if ($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="index.php" method="POST">
            <label for="usuario">Usuario:</label>
            <input type="text" id="usuario" name="usuario" required><br>

            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required><br>

            <button type="submit">Iniciar Sesión</button>
        </form>
    </div>
</body>
</html>
