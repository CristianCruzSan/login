<?php
session_start();
if ($_SESSION['tipo_usuario'] !== 'alumno') {
    header('Location: index.php');
    exit;
}

// Conexión a la base de datos
require 'db.php';

$mensaje = "";

// envío del formulario de inscripción
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alumno = $_SESSION['usuario'];
    $modalidad = $_POST['modalidad'];

    // Verificar si el alumno ya tiene una modalidad registrada
    $query = "SELECT * FROM modalidades_ico WHERE alumno = :alumno";
    $stmt = $conexion->prepare($query);
    $stmt->bindParam(':alumno', $alumno);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $mensaje = "Ya has inscrito una modalidad.";
    } else {
        // Insertar la modalidad seleccionada
        $query = "INSERT INTO modalidades_ico (alumno, modalidad, fecha) 
                  VALUES (:alumno, :modalidad, CURDATE())";
        $stmt = $conexion->prepare($query);
        $stmt->bindParam(':alumno', $alumno);
        $stmt->bindParam(':modalidad', $modalidad);

        if ($stmt->execute()) {
            $mensaje = "Modalidad inscrita correctamente.";
        } else {
            $mensaje = "Error al inscribir la modalidad.";
        }
    }
}

// cerrar sesión
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
    <title>Alumno - Inscripción Modalidad</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .logout-button {
            position: absolute;
            top: 20px;
            right: 20px;
        }
    </style>
</head>
<body>
    <div class="contenedor">
        <h2>Bienvenido Alumno: <?php echo $_SESSION['usuario']; ?></h2>

        <form method="POST" action="alumno.php" class="logout-button">
            <button type="submit" name="logout">Cerrar Sesión</button>
        </form>

        <?php if ($mensaje): ?>
            <div class="mensaje"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <form method="POST" action="alumno.php">
            <label for="modalidad">Selecciona una modalidad:</label>
            <select id="modalidad" name="modalidad" required>
                <option value="" disabled selected>Elige una opción</option>
                <option value="Tesis">Tesis</option>
                <option value="Tesina">Tesina</option>
                <option value="Examen General de Conocimientos">Examen General de Conocimientos</option>
                <option value="Informe de Prácticas Profesionales">Informe de Prácticas Profesionales</option>
                <option value="Proyecto Integral">Proyecto Integral</option>
                <option value="Créditos Complementarios">Créditos Complementarios</option>
            </select>
            <br><br>
            <button type="submit">Inscribir Modalidad</button>
        </form>
    </div>
</body>
</html>
