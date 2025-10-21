<?php
// Script simple para configurar la base de datos
header('Content-Type: text/html; charset=utf-8');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = $_POST['host'] ?? 'localhost';
    $username = $_POST['username'] ?? 'root';
    $password = $_POST['password'] ?? '';
    
    try {
        // Conectar a MySQL
        $pdo = new PDO("mysql:host=$host;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Crear base de datos
        $pdo->exec("CREATE DATABASE IF NOT EXISTS evaluacion_postulantes");
        $pdo->exec("USE evaluacion_postulantes");
        
        // Crear tabla usuarios
        $pdo->exec("CREATE TABLE IF NOT EXISTS usuarios (
            id INT PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            nombre_completo VARCHAR(100) NOT NULL,
            telefono VARCHAR(20),
            fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            estado ENUM('activo', 'inactivo') DEFAULT 'activo',
            tipo_usuario ENUM('postulante', 'admin') DEFAULT 'postulante'
        )");
        
        // Crear tabla comprobantes_pago
        $pdo->exec("CREATE TABLE IF NOT EXISTS comprobantes_pago (
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
        )");
        
        // Crear tabla modulos
        $pdo->exec("CREATE TABLE IF NOT EXISTS modulos (
            id INT PRIMARY KEY AUTO_INCREMENT,
            nombre VARCHAR(100) NOT NULL,
            descripcion TEXT,
            duracion_minutos INT NOT NULL DEFAULT 25,
            estado ENUM('activo', 'inactivo') DEFAULT 'activo',
            orden_modulo INT NOT NULL,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Crear tabla preguntas
        $pdo->exec("CREATE TABLE IF NOT EXISTS preguntas (
            id INT PRIMARY KEY AUTO_INCREMENT,
            modulo_id INT NOT NULL,
            pregunta TEXT NOT NULL,
            tipo_pregunta ENUM('multiple', 'verdadero_falso', 'texto') NOT NULL,
            opciones JSON,
            respuesta_correcta TEXT,
            puntos INT DEFAULT 1,
            orden_pregunta INT NOT NULL,
            estado ENUM('activo', 'inactivo') DEFAULT 'activo',
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (modulo_id) REFERENCES modulos(id) ON DELETE CASCADE
        )");
        
        // Crear tabla evaluaciones_usuario
        $pdo->exec("CREATE TABLE IF NOT EXISTS evaluaciones_usuario (
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
        )");
        
        // Crear tabla respuestas_usuario
        $pdo->exec("CREATE TABLE IF NOT EXISTS respuestas_usuario (
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
        )");
        
        // Crear tabla logs_actividad
        $pdo->exec("CREATE TABLE IF NOT EXISTS logs_actividad (
            id INT PRIMARY KEY AUTO_INCREMENT,
            usuario_id INT,
            accion VARCHAR(100) NOT NULL,
            descripcion TEXT,
            ip_address VARCHAR(45),
            user_agent TEXT,
            fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
        )");
        
        // Insertar usuario administrador si no existe
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE username = 'admin'");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            $pdo->exec("INSERT INTO usuarios (username, email, password, nombre_completo, tipo_usuario) 
                       VALUES ('admin', 'admin@evaluacion.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador del Sistema', 'admin')");
        }
        
        // Insertar módulos de ejemplo si no existen
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM modulos");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            $pdo->exec("INSERT INTO modulos (nombre, descripcion, duracion_minutos, orden_modulo) VALUES
                       ('Conocimientos Técnicos', 'Evaluación de conocimientos técnicos específicos del puesto', 45, 1),
                       ('Habilidades Blandas', 'Evaluación de competencias interpersonales y de comunicación', 25, 2),
                       ('Razonamiento Lógico', 'Evaluación de capacidades de análisis y resolución de problemas', 45, 3)");
        }
        
        // Insertar preguntas de ejemplo si no existen
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM preguntas");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            $preguntas = [
                [1, '¿Cuál es la diferencia principal entre HTTP y HTTPS?', 'multiple', '["HTTP es más rápido", "HTTPS incluye encriptación SSL/TLS", "HTTP es más seguro", "No hay diferencia"]', 'HTTPS incluye encriptación SSL/TLS', 2, 1],
                [1, '¿Qué es una base de datos relacional?', 'multiple', '["Una base de datos sin estructura", "Una base de datos que usa tablas relacionadas", "Una base de datos en la nube", "Una base de datos de documentos"]', 'Una base de datos que usa tablas relacionadas', 2, 2],
                [2, 'La comunicación efectiva es clave en el trabajo en equipo', 'verdadero_falso', '["Verdadero", "Falso"]', 'Verdadero', 1, 1],
                [2, '¿Cómo manejarías un conflicto con un compañero de trabajo?', 'texto', null, 'Respuesta abierta evaluada manualmente', 3, 2],
                [3, 'Si A = 2, B = 4, ¿cuál es el valor de A² + B²?', 'multiple', '["12", "16", "20", "24"]', '20', 2, 1],
                [3, 'En una secuencia 2, 4, 8, 16, ¿cuál es el siguiente número?', 'multiple', '["24", "32", "28", "30"]', '32', 2, 2]
            ];
            
            $stmt = $pdo->prepare("INSERT INTO preguntas (modulo_id, pregunta, tipo_pregunta, opciones, respuesta_correcta, puntos, orden_pregunta) VALUES (?, ?, ?, ?, ?, ?, ?)");
            foreach ($preguntas as $pregunta) {
                $stmt->execute($pregunta);
            }
        }
        
        // Crear directorios necesarios
        if (!file_exists('uploads')) {
            mkdir('uploads', 0755, true);
        }
        if (!file_exists('uploads/comprobantes')) {
            mkdir('uploads/comprobantes', 0755, true);
        }
        if (!file_exists('uploads/temp')) {
            mkdir('uploads/temp', 0755, true);
        }
        
        // Actualizar el archivo de configuración
        $config_content = "<?php
// Configuración de la base de datos
class Database {
    private \$host = '$host';
    private \$db_name = 'evaluacion_postulantes';
    private \$username = '$username';
    private \$password = '$password';
    private \$conn;

    public function getConnection() {
        \$this->conn = null;
        
        try {
            \$this->conn = new PDO(
                \"mysql:host=\" . \$this->host . \";dbname=\" . \$this->db_name . \";charset=utf8\",
                \$this->username,
                \$this->password
            );
            \$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            \$this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException \$exception) {
            echo \"Error de conexión: \" . \$exception->getMessage();
            exit();
        }
        
        return \$this->conn;
    }
}

// Configuración general del sistema
define('BASE_URL', 'http://localhost/evaluacion/');
define('UPLOAD_DIR', 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['pdf', 'jpg', 'jpeg', 'png', 'gif']);

// Configuración de sesión
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Cambiar a 1 en producción con HTTPS

// Zona horaria
date_default_timezone_set('America/Lima');

// Función para limpiar datos de entrada
function sanitizeInput(\$data) {
    \$data = trim(\$data);
    \$data = stripslashes(\$data);
    \$data = htmlspecialchars(\$data);
    return \$data;
}

// Función para validar email
function validateEmail(\$email) {
    return filter_var(\$email, FILTER_VALIDATE_EMAIL);
}

// Función para hash de contraseñas
function hashPassword(\$password) {
    return password_hash(\$password, PASSWORD_DEFAULT);
}

// Función para verificar contraseñas
function verifyPassword(\$password, \$hash) {
    return password_verify(\$password, \$hash);
}

// Función para generar token CSRF
function generateCSRFToken() {
    if (!isset(\$_SESSION['csrf_token'])) {
        \$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return \$_SESSION['csrf_token'];
}

// Función para verificar token CSRF
function verifyCSRFToken(\$token) {
    return isset(\$_SESSION['csrf_token']) && hash_equals(\$_SESSION['csrf_token'], \$token);
}

// Función para registrar logs
function logActivity(\$usuario_id, \$accion, \$descripcion = '') {
    try {
        \$database = new Database();
        \$db = \$database->getConnection();
        
        \$query = \"INSERT INTO logs_actividad (usuario_id, accion, descripcion, ip_address, user_agent) 
                  VALUES (:usuario_id, :accion, :descripcion, :ip, :user_agent)\";
        
        \$stmt = \$db->prepare(\$query);
        \$stmt->bindParam(':usuario_id', \$usuario_id);
        \$stmt->bindParam(':accion', \$accion);
        \$stmt->bindParam(':descripcion', \$descripcion);
        \$stmt->bindParam(':ip', \$_SERVER['REMOTE_ADDR']);
        \$stmt->bindParam(':user_agent', \$_SERVER['HTTP_USER_AGENT']);
        
        \$stmt->execute();
    } catch(Exception \$e) {
        error_log(\"Error al registrar log: \" . \$e->getMessage());
    }
}

// Función para verificar autenticación
function isAuthenticated() {
    return isset(\$_SESSION['user_id']) && !empty(\$_SESSION['user_id']);
}

// Función para verificar si es administrador
function isAdmin() {
    return isset(\$_SESSION['tipo_usuario']) && \$_SESSION['tipo_usuario'] === 'admin';
}

// Función para requerir autenticación
function requireAuth() {
    if (!isAuthenticated()) {
        header('Location: ' . BASE_URL . 'login.php');
        exit();
    }
}

// Función para requerir privilegios de administrador
function requireAdmin() {
    requireAuth();
    if (!isAdmin()) {
        header('Location: ' . BASE_URL . 'dashboard.php');
        exit();
    }
}

// Crear directorio de uploads si no existe
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// Crear subdirectorios
\$subdirs = ['comprobantes', 'temp'];
foreach (\$subdirs as \$subdir) {
    \$path = UPLOAD_DIR . \$subdir;
    if (!file_exists(\$path)) {
        mkdir(\$path, 0755, true);
    }
}
?>";
        
        if (!file_exists('config')) {
            mkdir('config', 0755, true);
        }
        file_put_contents('config/config.php', $config_content);
        
        $success = "¡Base de datos configurada correctamente! Ya puedes usar el sistema.";
        
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración Rápida - Sistema de Evaluación</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 10px;
        }
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            box-sizing: border-box;
        }
        input:focus {
            outline: none;
            border-color: #4f46e5;
        }
        .btn {
            width: 100%;
            padding: 12px;
            background: #4f46e5;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #3730a3;
        }
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .info {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .credentials {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 15px;
            margin-top: 20px;
        }
        .credentials h3 {
            margin-top: 0;
            color: #856404;
        }
        .links {
            text-align: center;
            margin-top: 20px;
        }
        .links a {
            display: inline-block;
            margin: 0 10px;
            padding: 10px 20px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: background 0.3s;
        }
        .links a:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Configuración Rápida</h1>
        <p class="subtitle">Sistema de Evaluación de Postulantes</p>

        <?php if ($error): ?>
        <div class="alert alert-error">
            <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="alert alert-success">
            <strong>¡Éxito!</strong> <?php echo htmlspecialchars($success); ?>
        </div>
        
        <div class="credentials">
            <h3>Credenciales de Administrador:</h3>
            <ul>
                <li><strong>Usuario:</strong> admin</li>
                <li><strong>Contraseña:</strong> password</li>
                <li><strong>Email:</strong> admin@evaluacion.com</li>
            </ul>
            <p><strong>⚠️ Importante:</strong> Cambia la contraseña después del primer login.</p>
        </div>
        
        <div class="links">
            <a href="login.php">🔑 Acceder al Sistema</a>
            <a href="register.php">👤 Registrar Usuario</a>
        </div>

        <?php else: ?>
        
        <div class="info">
            <strong>ℹ️ Información:</strong> Esta herramienta configurará automáticamente la base de datos y creará las tablas necesarias para el sistema.
        </div>

        <form method="POST">
            <div class="form-group">
                <label for="host">Servidor MySQL:</label>
                <input type="text" id="host" name="host" value="localhost" required>
            </div>

            <div class="form-group">
                <label for="username">Usuario MySQL:</label>
                <input type="text" id="username" name="username" value="root" required>
            </div>

            <div class="form-group">
                <label for="password">Contraseña MySQL:</label>
                <input type="password" id="password" name="password" placeholder="Dejar vacío si no hay contraseña">
            </div>

            <button type="submit" class="btn">
                🛠️ Configurar Sistema
            </button>
        </form>
        
        <div class="info" style="margin-top: 20px;">
            <small>
                <strong>Nota:</strong> Este script creará la base de datos "evaluacion_postulantes" automáticamente si no existe.
            </small>
        </div>
        
        <?php endif; ?>
    </div>
</body>
</html>