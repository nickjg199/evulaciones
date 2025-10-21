<?php
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

// Start session only if none active to avoid duplicate session_start warnings
if (session_status() === PHP_SESSION_NONE) {
    session_start();
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
?>