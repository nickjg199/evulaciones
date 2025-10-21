<?php
// Configuración simple y funcional
class Database {
    private $host = 'localhost';
    private $db_name = 'evaluacion_postulantes';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8",
                $this->username,
                $this->password,
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
            );
        } catch(PDOException $exception) {
            // Error más detallado para debug
            die("Error de conexión: " . $exception->getMessage() . 
                "<br>Host: " . $this->host . 
                "<br>Database: " . $this->db_name . 
                "<br>Username: " . $this->username);
        }
        
        return $this->conn;
    }
}

// Configuración básica
define('BASE_URL', 'http://localhost/evaluacion/');
define('UPLOAD_DIR', 'uploads/');

// Iniciar sesión
if (session_status() == PHP_SESSION_NONE) {
    if (session_status() === PHP_SESSION_NONE) { session_start(); }
}

// Funciones básicas
function sanitizeInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
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
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'admin';
}

function requireAuth() {
    if (!isAuthenticated()) {
        header('Location: ' . BASE_URL . 'login.php');
        exit();
    }
}

function requireAdmin() {
    requireAuth();
    if (!isAdmin()) {
        header('Location: ' . BASE_URL . 'dashboard.php');
        exit();
    }
}

function logActivity($usuario_id, $accion, $descripcion = '') {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "INSERT INTO logs_actividad (usuario_id, accion, descripcion, ip_address, user_agent) 
                  VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($query);
        $stmt->execute([
            $usuario_id,
            $accion,
            $descripcion,
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    } catch(Exception $e) {
        error_log("Error al registrar log: " . $e->getMessage());
    }
}

// Crear directorios si no existen
if (!file_exists('uploads')) {
    mkdir('uploads', 0755, true);
}
if (!file_exists('uploads/comprobantes')) {
    mkdir('uploads/comprobantes', 0755, true);
}
if (!file_exists('uploads/temp')) {
    mkdir('uploads/temp', 0755, true);
}
?>