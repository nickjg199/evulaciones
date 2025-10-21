<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔧 Diagnóstico y Reparación del Sistema</h1>";

// Verificar extensiones PHP
echo "<h2>1. Verificando extensiones PHP...</h2>";
$extensiones = ['pdo', 'pdo_mysql', 'mysqli'];
foreach ($extensiones as $ext) {
    echo "- $ext: " . (extension_loaded($ext) ? "✅ Disponible" : "❌ No disponible") . "<br>";
}

// Verificar conexión MySQL
echo "<h2>2. Probando conexión a MySQL...</h2>";
try {
    $pdo = new PDO("mysql:host=localhost;charset=utf8", 'root', '');
    echo "✅ Conexión a MySQL: Exitosa<br>";
    
    // Crear base de datos
    echo "<h2>3. Creando base de datos...</h2>";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS evaluacion_postulantes");
    echo "✅ Base de datos 'evaluacion_postulantes' creada<br>";
    
    // Seleccionar base de datos
    $pdo->exec("USE evaluacion_postulantes");
    echo "✅ Base de datos seleccionada<br>";
    
    // Crear tabla usuarios (simplificada)
    echo "<h2>4. Creando tablas...</h2>";
    $sql_usuarios = "CREATE TABLE IF NOT EXISTS usuarios (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        nombre_completo VARCHAR(100) NOT NULL,
        telefono VARCHAR(20),
        fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        estado ENUM('activo', 'inactivo') DEFAULT 'activo',
        tipo_usuario ENUM('postulante', 'admin') DEFAULT 'postulante'
    )";
    $pdo->exec($sql_usuarios);
    echo "✅ Tabla usuarios creada<br>";
    
    // Verificar si existe el usuario admin
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE username = 'admin'");
    $stmt->execute();
    $admin_exists = $stmt->fetchColumn() > 0;
    
    if (!$admin_exists) {
        echo "<h2>5. Creando usuario administrador...</h2>";
        $hash = password_hash('password', PASSWORD_DEFAULT);
        $sql_admin = "INSERT INTO usuarios (username, email, password, nombre_completo, tipo_usuario) 
                      VALUES ('admin', 'admin@evaluacion.com', ?, 'Administrador del Sistema', 'admin')";
        $stmt = $pdo->prepare($sql_admin);
        $stmt->execute([$hash]);
        echo "✅ Usuario administrador creado<br>";
        echo "📝 Credenciales: admin / password<br>";
    } else {
        echo "✅ Usuario administrador ya existe<br>";
    }
    
    // Crear archivo de configuración simplificado
    echo "<h2>6. Creando archivo de configuración...</h2>";
    
    $config_content = '<?php
class Database {
    private $host = "localhost";
    private $db_name = "evaluacion_postulantes";
    private $username = "root";
    private $password = "";
    private $conn;

    public function getConnection() {
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $this->conn;
        } catch(PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }
}

if (session_status() === PHP_SESSION_NONE) { session_start(); }

function sanitizeInput($data) {
    return htmlspecialchars(trim($data));
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function isAuthenticated() {
    return isset($_SESSION["user_id"]);
}

function isAdmin() {
    return isset($_SESSION["tipo_usuario"]) && $_SESSION["tipo_usuario"] === "admin";
}

function requireAuth() {
    if (!isAuthenticated()) {
        header("Location: login.php");
        exit();
    }
}

function requireAdmin() {
    requireAuth();
    if (!isAdmin()) {
        header("Location: dashboard.php");
        exit();
    }
}

function logActivity($usuario_id, $accion, $descripcion = "") {
    // Log simplificado
}

define("BASE_URL", "http://localhost/evaluacion/");
?>';
    
    if (!file_exists('config')) {
        mkdir('config', 0755, true);
    }
    
    file_put_contents('config/config.php', $config_content);
    echo "✅ Archivo config/config.php creado<br>";
    
    // Crear directorios
    echo "<h2>7. Creando directorios...</h2>";
    $dirs = ['uploads', 'uploads/comprobantes', 'uploads/temp'];
    foreach ($dirs as $dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
            echo "✅ Directorio $dir creado<br>";
        } else {
            echo "✅ Directorio $dir ya existe<br>";
        }
    }
    
    echo "<h2>🎉 ¡Reparación Completada!</h2>";
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<strong>✅ Sistema reparado exitosamente</strong><br>";
    echo "🔐 <strong>Credenciales de administrador:</strong><br>";
    echo "• Usuario: <code>admin</code><br>";
    echo "• Contraseña: <code>password</code><br>";
    echo "<br>";
    echo "🔗 <a href='login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ir al Login</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error en la reparación:</h2>";
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; color: #721c24;'>";
    echo "<strong>Error:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>Solución:</strong><br>";
    echo "1. Asegúrate de que XAMPP esté ejecutándose<br>";
    echo "2. Verifica que MySQL esté activo<br>";
    echo "3. Comprueba que no haya otras aplicaciones usando el puerto 3306<br>";
    echo "</div>";
    
    echo "<h3>🛠️ Configuración manual:</h3>";
    echo "<ol>";
    echo "<li>Abre phpMyAdmin: <a href='http://localhost/phpmyadmin' target='_blank'>http://localhost/phpmyadmin</a></li>";
    echo "<li>Crea una base de datos llamada: <code>evaluacion_postulantes</code></li>";
    echo "<li>Ejecuta este SQL:</li>";
    echo "</ol>";
    
    echo "<textarea style='width: 100%; height: 200px; font-family: monospace;'>
CREATE DATABASE IF NOT EXISTS evaluacion_postulantes;
USE evaluacion_postulantes;

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

INSERT INTO usuarios (username, email, password, nombre_completo, tipo_usuario) 
VALUES ('admin', 'admin@evaluacion.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador del Sistema', 'admin');
</textarea>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1 { color: #333; }
h2 { color: #666; border-bottom: 1px solid #eee; padding-bottom: 5px; }
code { background: #f4f4f4; padding: 2px 4px; border-radius: 3px; }
</style>