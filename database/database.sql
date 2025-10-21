-- Base de datos para sistema de evaluación de postulantes
-- Crear base de datos
CREATE DATABASE IF NOT EXISTS evaluacion_postulantes;
USE evaluacion_postulantes;

-- Tabla de usuarios
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nombre_completo VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    tipo_usuario ENUM('postulante', 'admin') DEFAULT 'postulante'
);

-- Tabla de comprobantes de pago
CREATE TABLE comprobantes_pago (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    nombre_archivo VARCHAR(255) NOT NULL,
    ruta_archivo VARCHAR(500) NOT NULL,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('pendiente', 'aprobado', 'rechazado') DEFAULT 'pendiente',
    comentarios TEXT,
    revisado_por INT,
    fecha_revision TIMESTAMP NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (revisado_por) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Tabla de módulos de evaluación
CREATE TABLE modulos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    duracion_minutos INT NOT NULL DEFAULT 25,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    orden_modulo INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de preguntas
CREATE TABLE preguntas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    modulo_id INT NOT NULL,
    pregunta TEXT NOT NULL,
    tipo_pregunta ENUM('multiple', 'verdadero_falso', 'texto') NOT NULL,
    opciones JSON, -- Para preguntas de opción múltiple
    respuesta_correcta TEXT,
    puntos INT DEFAULT 1,
    orden_pregunta INT NOT NULL,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (modulo_id) REFERENCES modulos(id) ON DELETE CASCADE
);

-- Tabla de evaluaciones de usuarios
CREATE TABLE evaluaciones_usuario (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    modulo_id INT NOT NULL,
    fecha_inicio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_fin TIMESTAMP NULL,
    tiempo_usado_segundos INT DEFAULT 0,
    estado ENUM('no_iniciado', 'en_progreso', 'completado', 'tiempo_agotado') DEFAULT 'no_iniciado',
    puntuacion DECIMAL(5,2) DEFAULT 0,
    camara_verificada BOOLEAN DEFAULT FALSE,
    ip_address VARCHAR(45),
    user_agent TEXT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (modulo_id) REFERENCES modulos(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_module (usuario_id, modulo_id)
);

-- Tabla de respuestas de usuarios
CREATE TABLE respuestas_usuario (
    id INT PRIMARY KEY AUTO_INCREMENT,
    evaluacion_id INT NOT NULL,
    pregunta_id INT NOT NULL,
    respuesta_usuario TEXT,
    es_correcta BOOLEAN DEFAULT FALSE,
    puntos_obtenidos DECIMAL(5,2) DEFAULT 0,
    tiempo_respuesta_segundos INT DEFAULT 0,
    fecha_respuesta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (evaluacion_id) REFERENCES evaluaciones_usuario(id) ON DELETE CASCADE,
    FOREIGN KEY (pregunta_id) REFERENCES preguntas(id) ON DELETE CASCADE
);

-- Tabla de logs de actividad
CREATE TABLE logs_actividad (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT,
    accion VARCHAR(100) NOT NULL,
    descripcion TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Insertar usuario administrador por defecto
-- Contraseña: password
INSERT INTO usuarios (username, email, password, nombre_completo, tipo_usuario) 
VALUES ('admin', 'admin@evaluacion.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador del Sistema', 'admin');

-- Insertar módulos de ejemplo
INSERT INTO modulos (nombre, descripcion, duracion_minutos, orden_modulo) VALUES
('Conocimientos Técnicos', 'Evaluación de conocimientos técnicos específicos del puesto', 45, 1),
('Habilidades Blandas', 'Evaluación de competencias interpersonales y de comunicación', 25, 2),
('Razonamiento Lógico', 'Evaluación de capacidades de análisis y resolución de problemas', 45, 3);

-- Insertar preguntas de ejemplo
INSERT INTO preguntas (modulo_id, pregunta, tipo_pregunta, opciones, respuesta_correcta, puntos, orden_pregunta) VALUES
(1, '¿Cuál es la diferencia principal entre HTTP y HTTPS?', 'multiple', '["HTTP es más rápido", "HTTPS incluye encriptación SSL/TLS", "HTTP es más seguro", "No hay diferencia"]', 'HTTPS incluye encriptación SSL/TLS', 2, 1),
(1, '¿Qué es una base de datos relacional?', 'multiple', '["Una base de datos sin estructura", "Una base de datos que usa tablas relacionadas", "Una base de datos en la nube", "Una base de datos de documentos"]', 'Una base de datos que usa tablas relacionadas', 2, 2),
(2, 'La comunicación efectiva es clave en el trabajo en equipo', 'verdadero_falso', '["Verdadero", "Falso"]', 'Verdadero', 1, 1),
(2, '¿Cómo manejarías un conflicto con un compañero de trabajo?', 'texto', NULL, 'Respuesta abierta evaluada manualmente', 3, 2),
(3, 'Si A = 2, B = 4, ¿cuál es el valor de A² + B²?', 'multiple', '["12", "16", "20", "24"]', '20', 2, 1),
(3, 'En una secuencia 2, 4, 8, 16, ¿cuál es el siguiente número?', 'multiple', '["24", "32", "28", "30"]', '32', 2, 2);

-- Crear índices para optimizar consultas
CREATE INDEX idx_usuarios_email ON usuarios(email);
CREATE INDEX idx_usuarios_username ON usuarios(username);
CREATE INDEX idx_comprobantes_usuario ON comprobantes_pago(usuario_id);
CREATE INDEX idx_comprobantes_estado ON comprobantes_pago(estado);
CREATE INDEX idx_preguntas_modulo ON preguntas(modulo_id);
CREATE INDEX idx_evaluaciones_usuario ON evaluaciones_usuario(usuario_id);
CREATE INDEX idx_evaluaciones_modulo ON evaluaciones_usuario(modulo_id);
CREATE INDEX idx_respuestas_evaluacion ON respuestas_usuario(evaluacion_id);
CREATE INDEX idx_logs_usuario ON logs_actividad(usuario_id);
CREATE INDEX idx_logs_fecha ON logs_actividad(fecha);