<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔄 Reset Completo del Sistema</h1>";

// 1. Limpiar sesiones
echo "<h2>1. Limpiando sesiones...</h2>";
if (session_status() !== PHP_SESSION_NONE) {
    session_destroy();
}
if (session_status() === PHP_SESSION_NONE) { session_start(); }
session_unset();
session_destroy();
echo "✅ Sesiones limpiadas<br>";

// 2. Verificar y reparar base de datos
echo "<h2>2. Verificando base de datos...</h2>";
try {
    $pdo = new PDO("mysql:host=localhost", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Crear BD si no existe
    $pdo->exec("CREATE DATABASE IF NOT EXISTS evaluacion_postulantes");
    $pdo->exec("USE evaluacion_postulantes");
    echo "✅ Base de datos lista<br>";
    
    // Eliminar tabla usuarios si existe y recrear
    $pdo->exec("DROP TABLE IF EXISTS usuarios");
    echo "✅ Tabla usuarios eliminada<br>";
    
    $sql_usuarios = "CREATE TABLE usuarios (
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
    echo "✅ Tabla usuarios recreada<br>";
    
    // Crear usuario admin con password fresco
    $hash = password_hash('password', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO usuarios (username, email, password, nombre_completo, tipo_usuario) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute(['admin', 'admin@evaluacion.com', $hash, 'Administrador del Sistema', 'admin']);
    echo "✅ Usuario admin creado con password fresco<br>";
    
    // Verificar que el password funciona
    $stmt = $pdo->prepare("SELECT password FROM usuarios WHERE username = 'admin'");
    $stmt->execute();
    $stored_hash = $stmt->fetchColumn();
    
    if (password_verify('password', $stored_hash)) {
        echo "✅ Password verificado correctamente<br>";
    } else {
        echo "❌ Error en verificación de password<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error en base de datos: " . $e->getMessage() . "<br>";
}

// 3. Recrear archivo de configuración
echo "<h2>3. Recreando configuración...</h2>";
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
                $this->password,
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
            );
            return $this->conn;
        } catch(PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Error de conexión a la base de datos");
        }
    }
}

// Manejo seguro de sesiones
if (session_status() === PHP_SESSION_NONE) {
    if (session_status() === PHP_SESSION_NONE) { session_start(); }
}

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
    return isset($_SESSION["user_id"]) && !empty($_SESSION["user_id"]);
}

function isAdmin() {
    return isAuthenticated() && isset($_SESSION["tipo_usuario"]) && $_SESSION["tipo_usuario"] === "admin";
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
    // Log básico - puede expandirse
    error_log("Activity: User $usuario_id - $accion - $descripcion");
}

define("BASE_URL", "http://localhost/evaluacion/");
?>';

file_put_contents('config/config.php', $config_content);
echo "✅ Archivo de configuración recreado<br>";

// 4. Test completo del login
echo "<h2>4. Test del sistema de login...</h2>";
try {
    require_once 'config/config.php';
    require_once 'classes/User.php';
    
    $database = new Database();
    $db = $database->getConnection();
    $user = new User($db);
    
    // Test del login
    $result = $user->login('admin', 'password');
    
    if ($result['success']) {
        echo "✅ Login test exitoso<br>";
        echo "✅ Sesión configurada correctamente<br>";
        
        // Mostrar datos de sesión
        echo "Datos de sesión:<br>";
        echo "- User ID: " . ($_SESSION['user_id'] ?? 'No definido') . "<br>";
        echo "- Username: " . ($_SESSION['username'] ?? 'No definido') . "<br>";
        echo "- Tipo: " . ($_SESSION['tipo_usuario'] ?? 'No definido') . "<br>";
    } else {
        echo "❌ Login test falló: " . $result['message'] . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error en test: " . $e->getMessage() . "<br>";
}

echo "<h2>🎉 Reset Completado</h2>";
echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<strong>✅ Sistema completamente reiniciado</strong><br>";
echo "🔐 <strong>Credenciales:</strong><br>";
echo "• Usuario: <code>admin</code><br>";
echo "• Contraseña: <code>password</code><br>";
echo "<br>";
echo "🔗 <a href='login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Probar Login Ahora</a>";
echo "</div>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h1, h2 { color: #333; }
code { background: #f4f4f4; padding: 2px 4px; border-radius: 3px; }
</style>