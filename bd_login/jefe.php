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
$alumnos = [];
$modalidades = ['Tesis', 'Tesina', 'Examen General de Conocimientos', 'Informe de Prácticas Profesionales', 'Proyecto Integral', 'Créditos Complementarios'];

// nuevo administrador
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

// eliminación de un administrador
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

// búsqueda y actualización de alumnos
if (isset($_POST['buscar_alumno'])) {
    $usuario_alumno = $_POST['usuario_alumno'];
    
    // Obtener el alumno
    $query = "SELECT * FROM modalidades_ico WHERE alumno = :usuario";
    $stmt = $conexion->prepare($query);
    $stmt->bindParam(':usuario', $usuario_alumno);
    $stmt->execute();
    
    $alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif (isset($_POST['actualizar_modalidad'])) {
    $usuario_alumno = $_POST['usuario_alumno'];
    $nueva_modalidad = $_POST['nueva_modalidad'];
    
    // Actualizar modalidad
    $query = "UPDATE modalidades_ico SET modalidad = :modalidad WHERE alumno = :usuario";
    $stmt = $conexion->prepare($query);
    $stmt->bindParam(':modalidad', $nueva_modalidad);
    $stmt->bindParam(':usuario', $usuario_alumno);
    
    if ($stmt->execute()) {
        $mensaje = "Modalidad actualizada correctamente.";
    } else {
        $error = "Error al actualizar la modalidad.";
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
        .administradores, .alumnos {
            margin-top: 20px;
        }
        .administradores table, .alumnos table {
            width: 100%;
            border-collapse: collapse;
        }
        .administradores th, .administradores td, .alumnos th, .alumnos td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }
        .administradores th, .alumnos th {
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

        <h3>Buscar Alumno y Cambiar Modalidad</h3>
        <form method="POST" action="jefe.php">
            <label for="usuario_alumno">Buscar Alumno:</label>
            <input type="text" id="usuario_alumno" name="usuario_alumno" required>
            <button type="submit" name="buscar_alumno">Buscar</button>
        </form>

        <?php if (!empty($alumnos)): ?>
            <h4>Resultados de Búsqueda:</h4>
            <div class="alumnos">
                <table>
                    <tr>
                        <th>Alumno</th>
                        <th>Modalidad Actual</th>
                        <th>Cambiar Modalidad</th>
                    </tr>
                    <?php foreach ($alumnos as $alumno): ?>
                    <tr>
                        <td><?php echo $alumno['alumno']; ?></td>
                        <td><?php echo $alumno['modalidad']; ?></td>
                        <td>
                            <form method="POST" action="jefe.php">
                                <input type="hidden" name="usuario_alumno" value="<?php echo $alumno['alumno']; ?>">
                                <select name="nueva_modalidad" required>
                                    <option value="" disabled selected>Selecciona nueva modalidad</option>
                                    <?php foreach ($modalidades as $modalidad): ?>
                                        <option value="<?php echo $modalidad; ?>"><?php echo $modalidad; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" name="actualizar_modalidad">Actualizar</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
