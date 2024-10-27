<?php
session_start();
if ($_SESSION['tipo_usuario'] !== 'jefe') {
    header('Location: index.php');
    exit;
}

require 'db.php';

if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

$error = "";
$mensaje = "";
$administradores = [];
$modalidad_actual = "";
$usuario_estudiante = "";

// Nuevo administrador
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_admin'])) {
    $nuevo_usuario = $_POST['nuevo_usuario'];
    $nueva_contrasena = $_POST['nueva_contrasena'];

    // Verificar si el usuario ya existe
    $query = "SELECT * FROM usuarios_ico WHERE usuario = :usuario";
    $stmt = $conexion->prepare($query);
    $stmt->bindParam(':usuario', $nuevo_usuario);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $error = "El usuario ya existe.";
    } else {
        // Insertar nuevo administrador
        $query = "INSERT INTO usuarios_ico (usuario, password, tipo_usuario) VALUES (:usuario, :password, 'admin')";
        $stmt = $conexion->prepare($query);
        $stmt->bindParam(':usuario', $nuevo_usuario);
        $stmt->bindParam(':password', $nueva_contrasena);

        if ($stmt->execute()) {
            $mensaje = "Administrador creado correctamente.";
        } else {
            $error = "Error al crear el administrador.";
        }
    }
}

// Obtener la lista de administradores
$query = "SELECT usuario FROM usuarios_ico WHERE tipo_usuario = 'admin'";
$stmt = $conexion->prepare($query);
$stmt->execute();
$administradores = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Eliminación de un administrador
if (isset($_POST['delete_admin'])) {
    $usuario_a_eliminar = $_POST['usuario'];

    // Eliminar administrador
    $query = "DELETE FROM usuarios_ico WHERE usuario = :usuario";
    $stmt = $conexion->prepare($query);
    $stmt->bindParam(':usuario', $usuario_a_eliminar);

    if ($stmt->execute()) {
        $mensaje = "Administrador eliminado correctamente.";
    } else {
        $error = "Error al eliminar el administrador.";
    }
}

// Búsqueda y actualización de estudiantes
if (isset($_POST['buscar_estudiante'])) {
    $usuario_estudiante = $_POST['usuario_estudiante'];

    // Verificar si el estudiante existe
    $query = "SELECT * FROM usuarios_ico WHERE usuario = :usuario";
    $stmt = $conexion->prepare($query);
    $stmt->bindParam(':usuario', $usuario_estudiante);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        $error = "Estudiante no encontrado."; // Mensaje si el estudiante no existe
        $modalidad_actual = ""; // Reiniciar modalidad actual
    } else {
        // Obtener la modalidad actual del estudiante
        $query = "SELECT modalidad FROM modalidades_ico WHERE estudiante = :usuario";
        $stmt = $conexion->prepare($query);
        $stmt->bindParam(':usuario', $usuario_estudiante);
        $stmt->execute();

        // Obtener la modalidad actual
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $modalidad_actual = $resultado ? $resultado['modalidad'] : '';
    }
} elseif (isset($_POST['cambiar_modalidad']) || isset($_POST['inscribir_modalidad'])) {
    $usuario_estudiante = $_POST['usuario_estudiante'];
    $nueva_modalidad = $_POST['nueva_modalidad'];

    if (isset($_POST['cambiar_modalidad'])) {
        // Actualizar modalidad
        $query = "UPDATE modalidades_ico SET modalidad = :modalidad WHERE estudiante = :usuario";
    } else {
        // Inscribir nueva modalidad
        $query = "INSERT INTO modalidades_ico (estudiante, modalidad) VALUES (:usuario, :modalidad)";
    }

    $stmt = $conexion->prepare($query);
    $stmt->bindParam(':modalidad', $nueva_modalidad);
    $stmt->bindParam(':usuario', $usuario_estudiante);

    if ($stmt->execute()) {
        $mensaje = isset($_POST['cambiar_modalidad']) ? "Modalidad actualizada correctamente." : "Modalidad inscrita correctamente.";
        $modalidad_actual = $nueva_modalidad; 
    } else {
        $error = "Error al procesar la solicitud.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Jefe de Carrera</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .logout-button {
            position: absolute;
            top: 20px;
            right: 20px;
        }
        .administradores, .estudiantes {
            margin-top: 20px;
        }
        .administradores table, .estudiantes table {
            width: 100%;
            border-collapse: collapse;
        }
        .administradores th, .administradores td, .estudiantes th, .estudiantes td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }
        .administradores th, .estudiantes th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="contenedor">
        <h2>Bienvenido Jefe de Carrera: <?php echo $_SESSION['usuario']; ?></h2>

        <form method="POST" action="jefe.php" class="logout-button">
            <button type="submit" name="logout">Cerrar Sesión</button>
        </form>

        <?php if ($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($mensaje): ?>
            <div class="mensaje"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <h3>Administradores Actuales</h3>
        <div class="administradores">
            <table>
                <tr>
                    <th>Usuario</th>
                    <th>Eliminar</th>
                </tr>
                <?php foreach ($administradores as $admin): ?>
                <tr>
                    <td><?php echo $admin['usuario']; ?></td>
                    <td>
                        <form method="POST" action="jefe.php" style="display:inline;">
                            <input type="hidden" name="usuario" value="<?php echo $admin['usuario']; ?>">
                            <button type="submit" name="delete_admin">Eliminar</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <h3>Agregar Nuevo Administrador</h3>
        <form method="POST" action="jefe.php">
            <label for="nuevo_usuario">Usuario:</label>
            <input type="text" id="nuevo_usuario" name="nuevo_usuario" required><br>

            <label for="nueva_contrasena">Contraseña:</label>
            <input type="password" id="nueva_contrasena" name="nueva_contrasena" required><br>

            <button type="submit" name="add_admin">Agregar Administrador</button>
        </form>

        <h3>Buscar Estudiante y Cambiar Modalidad</h3>
        <form method="POST" action="jefe.php">
            <label for="usuario_estudiante">Buscar Estudiante:</label>
            <input type="text" id="usuario_estudiante" name="usuario_estudiante" required>
            <button type="submit" name="buscar_estudiante">Buscar</button>
        </form>

        <?php if (!empty($usuario_estudiante)): ?>
            <h3>Estudiante: <?php echo $usuario_estudiante; ?></h3>
            <h3>Modalidad Actual: <?php echo $modalidad_actual ?: 'No inscrito'; ?></h3>
            <form method="POST" action="jefe.php">
                <input type="hidden" name="usuario_estudiante" value="<?php echo $usuario_estudiante; ?>">
                <label for="nueva_modalidad">Selecciona Nueva Modalidad:</label>
                <?php include 'menu_modalidades.php'; ?>
                <button type="submit" name="<?php echo $modalidad_actual ? 'cambiar_modalidad' : 'inscribir_modalidad'; ?>">
                    <?php echo $modalidad_actual ? 'Cambiar Modalidad' : 'Inscribir Modalidad'; ?>
                </button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
