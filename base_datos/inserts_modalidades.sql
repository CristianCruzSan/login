-- Estudiantes
INSERT INTO usuarios_ico (usuario, apellido_p, apellido_m, nombre, password, tipo_usuario) 
VALUES 
('319008858', 'Cruz', 'Sanchez', 'Cristian Jair', 'pass123', 'estudiante'),
('318563421', 'Ramirez', 'Gonzalez', 'Maria', 'pass121', 'estudiante');

-- Administradores
INSERT INTO usuarios_ico  (usuario, apellido_p, apellido_m, nombre, password, tipo_usuario)
VALUES 
('admin1', 'Garcia', 'Muratalla', 'Luis Angel', 'adminpass', 'admin'),
('admin2', 'Alvarado', 'Castillo', 'Alejandro', 'adminpass2', 'admin');

-- Jefe de carrera
INSERT INTO usuarios_ico (usuario, apellido_p, apellido_m, nombre, password, tipo_usuario) 
VALUES 
('jefe1', 'Lopez', 'Hernandez', 'Jorge Arturo', 'jefepass', 'jefe');

-- Modalidades de titulación para los estudiantes
INSERT INTO modalidades_ico (estudiante, modalidad, fecha) 
VALUES 
('318563421', 'Tesis', '2024-10-25');
