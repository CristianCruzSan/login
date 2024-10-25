SET NAMES 'latin1';
DROP DATABASE IF EXISTS modalidades;
CREATE DATABASE IF NOT EXISTS modalidades DEFAULT CHARACTER SET utf8;
USE modalidades;

-- Tabla de usuarios (alumnos,admins y jefe de carrera)
CREATE TABLE usuarios_ico (
    usuario VARCHAR(50) PRIMARY KEY,
    apellido_p VARCHAR(50),
    apellido_m VARCHAR(50),
	nombre VARCHAR(50),
    password VARCHAR(50),
    tipo_usuario ENUM('alumno', 'admin', 'jefe') NOT NULL
);

-- Tabla de modalidades, asociada al registro de los alumnos
CREATE TABLE modalidades_ico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alumno VARCHAR(50),
    modalidad VARCHAR(100),
    fecha DATE,
    FOREIGN KEY (alumno) REFERENCES usuarios_ico(usuario)
        ON DELETE CASCADE ON UPDATE CASCADE
);


source inserts_modalidades.sql;