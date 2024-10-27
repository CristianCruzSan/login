<?php
session_start();
require 'db.php';

// Redirigir si no es admin
if ($_SESSION['tipo_usuario'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$mensaje = "";
$error = "";
$modalidad_actual = "";

// Cambiar o inscribir modalidad
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['cambiar_modalidad'])) {
        $usuario_estudiante = $_POST['usuario_estudiante'];
        $nueva_modalidad = $_POST['nueva_modalidad'];
        $fecha_inscripcion = date('Y-m-d');

        // Actualizar la modalidad en la base de datos
        $query = "UPDATE modalidades_ico SET modalidad = :modalidad, fecha = :fecha WHERE estudiante = :estudiante";
        $stmt = $conexion->prepare($query);
        $stmt->bindParam(':estudiante', $usuario_estudiante);
        $stmt->bindParam(':modalidad', $nueva_modalidad);
        $stmt->bindParam(':fecha', $fecha_inscripcion);

        if ($stmt->execute()) {
            $mensaje = "Modalidad actualizada correctamente.";
        } else {
            $mensaje = "Error al actualizar la modalidad.";
        }
    } elseif (isset($_POST['inscribir_modalidad'])) {
        $usuario_estudiante = $_POST['usuario_estudiante'];
        $nueva_modalidad = $_POST['nueva_modalidad'];
        $fecha_inscripcion = date('Y-m-d');

        // Insertar nueva modalidad en la base de datos
        $query = "INSERT INTO modalidades_ico (estudiante, modalidad, fecha) VALUES (:estudiante, :modalidad, :fecha)";
        $stmt = $conexion->prepare($query);
        $stmt->bindParam(':estudiante', $usuario_estudiante);
        $stmt->bindParam(':modalidad', $nueva_modalidad);
        $stmt->bindParam(':fecha', $fecha_inscripcion);

        if ($stmt->execute()) {
            $mensaje = "Modalidad inscrita correctamente.";
        } else {
            $mensaje = "Error al inscribir la modalidad.";
        }
    }
}

// Búsqueda de estudiante
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar_estudiante'])) {
    $numero_cuenta = $_POST['numero_cuenta'];

    // Consultar la modalidad actual del estudiante
    $query = "SELECT u.usuario, m.modalidad FROM usuarios_ico u 
              LEFT JOIN modalidades_ico m ON u.usuario = m.estudiante 
              WHERE u.usuario = :numero_cuenta";
    $stmt = $conexion->prepare($query);
    $stmt->bindParam(':numero_cuenta', $numero_cuenta);
    $stmt->execute();
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resultado) {
        $modalidad_actual = $resultado['modalidad'] ?: 'No inscrito';
        $usuario_estudiante = $resultado['usuario'];
    } else {
        $error = "Estudiante no encontrado.";
    }
}

// Cerrar sesión
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Admin - Inscripción de Modalidad</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .error-message {
            color: red;
        }
        .mensaje {
            color: green;
        }
    </style>
</head>
<body>
    <div class="contenedor">
        <h2>Bienvenido Admin: <?php echo $_SESSION['usuario']; ?></h2>

        <form method="POST" action="admin.php" class="logout-button">
            <button type="submit" name="logout">Cerrar Sesión</button>
        </form>

        <?php if ($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($mensaje): ?>
            <div class="mensaje"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <h3>Buscar Estudiante</h3>
        <form method="POST" action="admin.php">
            <input type="text" name="numero_cuenta" placeholder="Número de cuenta" required>
            <button type="submit" name="buscar_estudiante">Buscar</button>
        </form>

        <?php if ($modalidad_actual !== ""): ?>
            <h3>Estudiante: <?php echo $usuario_estudiante; ?></h3>
            <h3>Modalidad Actual: <?php echo $modalidad_actual; ?></h3>
            <form method="POST" action="admin.php">
                <input type="hidden" name="usuario_estudiante" value="<?php echo $usuario_estudiante; ?>">
                <label for="nueva_modalidad">Selecciona Nueva Modalidad:</label>
                <?php include 'menu_modalidades.php'; ?>
                <?php if ($modalidad_actual !== 'No inscrito'): ?>
                    <button type="submit" name="cambiar_modalidad">Cambiar Modalidad</button>
                <?php else: ?>
                    <button type="submit" name="inscribir_modalidad">Inscribir Modalidad</button>
                <?php endif; ?>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
