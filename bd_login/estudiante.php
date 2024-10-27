<?php
session_start();
require 'db.php';

// Redirigir si no es estudiante
if ($_SESSION['tipo_usuario'] !== 'estudiante') {
    header('Location: index.php');
    exit;
}

$mensaje = "";

// Inscribir modalidad
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['inscribir_modalidad'])) {
    $usuario_estudiante = $_SESSION['usuario'];
    $nueva_modalidad = $_POST['nueva_modalidad'];
    $fecha_inscripcion = date('Y-m-d'); // Captura la fecha actual

    // Insertar la modalidad en la base de datos
    $query = "INSERT INTO modalidades_ico (estudiante, modalidad, fecha) VALUES (:estudiante, :modalidad, :fecha)";
    $stmt = $conexion->prepare($query);
    $stmt->bindParam(':estudiante', $usuario_estudiante);
    $stmt->bindParam(':modalidad', $nueva_modalidad);
    $stmt->bindParam(':fecha', $fecha_inscripcion); // Se añade la fecha

    if ($stmt->execute()) {
        $mensaje = "Modalidad inscrita correctamente.";
    } else {
        $mensaje = "Error al inscribir la modalidad.";
    }
}

// Obtener modalidad del estudiante
$usuario_estudiante = $_SESSION['usuario'];
$query = "SELECT modalidad FROM modalidades_ico WHERE estudiante = :estudiante";
$stmt = $conexion->prepare($query);
$stmt->bindParam(':estudiante', $usuario_estudiante);
$stmt->execute();
$modalidad_actual = $stmt->fetchColumn();

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
    <title>Estudiante - Inscripción de Modalidad</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="contenedor">
        <h2>Bienvenido Estudiante: <?php echo $_SESSION['usuario']; ?></h2>

        <form method="POST" action="estudiante.php" class="logout-button">
            <button type="submit" name="logout">Cerrar Sesión</button>
        </form>

        <?php if ($mensaje): ?>
            <div class="mensaje"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <h3>Tu Modalidad Actual</h3>
        <p><?php echo $modalidad_actual ?: 'No tienes modalidad inscrita.'; ?></p>

        <?php if (!$modalidad_actual): ?>
            <h3>Inscribir Nueva Modalidad</h3>
            <form method="POST" action="estudiante.php">
                <?php include 'menu_modalidades.php'; ?>
                <button type="submit" name="inscribir_modalidad">Inscribir Modalidad</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
