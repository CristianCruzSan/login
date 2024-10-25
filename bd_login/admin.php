<?php
session_start();
require 'db.php';

// Redirigir si no es administrador
if ($_SESSION['tipo_usuario'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Variable para mensajes
$mensaje = "";

// Manejar el cambio de modalidad
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_modalidad'])) {
    $usuario_alumno = $_POST['usuario_alumno'];
    $nueva_modalidad = $_POST['nueva_modalidad'];

    // Actualizar la modalidad del alumno
    $query = "UPDATE modalidades_ico SET modalidad = :nueva_modalidad WHERE alumno = :alumno";
    $stmt = $conexion->prepare($query);
    $stmt->bindParam(':nueva_modalidad', $nueva_modalidad);
    $stmt->bindParam(':alumno', $usuario_alumno);

    if ($stmt->execute()) {
        $mensaje = "Modalidad cambiada correctamente.";
    } else {
        $mensaje = "Error al cambiar la modalidad.";
    }
}

// Manejar búsqueda de alumnos
$search = isset($_POST['search']) ? $_POST['search'] : '';

// Consultar todos los alumnos y sus modalidades
$query = "SELECT u.usuario, m.modalidad FROM usuarios_ico u 
          LEFT JOIN modalidades_ico m ON u.usuario = m.alumno 
          WHERE u.tipo_usuario = 'alumno' AND (u.usuario LIKE :search OR m.modalidad LIKE :search)";
$stmt = $conexion->prepare($query);
$search_param = '%' . $search . '%';
$stmt->bindParam(':search', $search_param);
$stmt->execute();
$alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Manejar cerrar sesión
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
    <title>Administrador - Gestión de Alumnos</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Estilo para el botón de cerrar sesión */
        .logout-button {
            position: absolute;
            top: 20px;
            right: 20px;
        }
    </style>
</head>
<body>
    <div class="contenedor">
        <h2>Bienvenido Administrador: <?php echo $_SESSION['usuario']; ?></h2>

        <!-- Botón de cerrar sesión -->
        <form method="POST" action="admin.php" class="logout-button">
            <button type="submit" name="logout">Cerrar Sesión</button>
        </form>

        <?php if ($mensaje): ?>
            <div class="mensaje"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <h3>Buscar Alumnos</h3>
        <form method="POST" action="admin.php">
            <input type="text" name="search" placeholder="Buscar por usuario o modalidad" value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Buscar</button>
        </form>

        <h3>Lista de Alumnos</h3>
        <table>
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Modalidad Inscrita</th>
                    <th>Cambiar Modalidad</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($alumnos as $alumno): ?>
                    <tr>
                        <td><?php echo $alumno['usuario']; ?></td>
                        <td><?php echo $alumno['modalidad'] ?: 'No inscrito'; ?></td>
                        <td>
                            <form method="POST" action="admin.php">
                                <input type="hidden" name="usuario_alumno" value="<?php echo $alumno['usuario']; ?>">
                                <select name="nueva_modalidad" required>
                                    <option value="" disabled selected>Elige una opción</option>
                                    <option value="Tesis">Tesis</option>
                                    <option value="Tesina">Tesina</option>
                                    <option value="Examen General de Conocimientos">Examen General de Conocimientos</option>
                                    <option value="Informe de Prácticas Profesionales">Informe de Prácticas Profesionales</option>
                                    <option value="Proyecto Integral">Proyecto Integral</option>
                                    <option value="Créditos Complementarios">Créditos Complementarios</option>
                                </select>
                                <button type="submit" name="cambiar_modalidad">Cambiar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
