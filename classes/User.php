<?php
// No incluir config.php aquí - se incluye donde se use la clase

class User {
    private $conn;
    private $table_name = "usuarios";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Registrar nuevo usuario
    public function register($username, $email, $password, $nombre_completo, $telefono = '') {
        try {
            // Verificar si el usuario o email ya existen
            if ($this->userExists($username, $email)) {
                return ['success' => false, 'message' => 'El usuario o email ya están registrados'];
            }

            $query = "INSERT INTO " . $this->table_name . " 
                      (username, email, password, nombre_completo, telefono) 
                      VALUES (:username, :email, :password, :nombre_completo, :telefono)";

            $stmt = $this->conn->prepare($query);
            
            $hashed_password = hashPassword($password);
            
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':nombre_completo', $nombre_completo);
            $stmt->bindParam(':telefono', $telefono);

            if ($stmt->execute()) {
                $user_id = $this->conn->lastInsertId();
                logActivity($user_id, 'REGISTRO', 'Usuario registrado exitosamente');
                return ['success' => true, 'message' => 'Usuario registrado exitosamente', 'user_id' => $user_id];
            }
            
            return ['success' => false, 'message' => 'Error al registrar usuario'];
        } catch(Exception $e) {
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }

    // Verificar si usuario existe
    private function userExists($username, $email) {
        $query = "SELECT id FROM " . $this->table_name . " 
                  WHERE username = :username OR email = :email";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    // Iniciar sesión
    public function login($username, $password) {
        try {
            $query = "SELECT id, username, email, password, nombre_completo, tipo_usuario, estado 
                      FROM " . $this->table_name . " 
                      WHERE (username = :username OR email = :username) AND estado = 'activo'";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (verifyPassword($password, $user['password'])) {
                    // Configurar sesión (la sesión ya está iniciada)
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['nombre_completo'] = $user['nombre_completo'];
                    $_SESSION['tipo_usuario'] = $user['tipo_usuario'];
                    
                    logActivity($user['id'], 'LOGIN', 'Inicio de sesión exitoso');
                    
                    return ['success' => true, 'message' => 'Inicio de sesión exitoso', 'user' => $user];
                }
            }
            
            logActivity(null, 'LOGIN_FAILED', 'Intento de inicio de sesión fallido para: ' . $username);
            return ['success' => false, 'message' => 'Credenciales incorrectas'];
        } catch(Exception $e) {
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }

    // Obtener información del usuario
    public function getUserById($user_id) {
        $query = "SELECT id, username, email, nombre_completo, telefono, fecha_registro, tipo_usuario 
                  FROM " . $this->table_name . " 
                  WHERE id = :user_id AND estado = 'activo'";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Cambiar contraseña
    public function changePassword($user_id, $current_password, $new_password) {
        try {
            // Verificar contraseña actual
            $query = "SELECT password FROM " . $this->table_name . " WHERE id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!verifyPassword($current_password, $user['password'])) {
                return ['success' => false, 'message' => 'Contraseña actual incorrecta'];
            }

            // Actualizar contraseña
            $query = "UPDATE " . $this->table_name . " 
                      SET password = :new_password 
                      WHERE id = :user_id";

            $stmt = $this->conn->prepare($query);
            $hashed_password = hashPassword($new_password);
            $stmt->bindParam(':new_password', $hashed_password);
            $stmt->bindParam(':user_id', $user_id);

            if ($stmt->execute()) {
                logActivity($user_id, 'PASSWORD_CHANGE', 'Contraseña cambiada exitosamente');
                return ['success' => true, 'message' => 'Contraseña actualizada exitosamente'];
            }
            
            return ['success' => false, 'message' => 'Error al actualizar contraseña'];
        } catch(Exception $e) {
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }

    // Cerrar sesión
    public function logout() {
    if (session_status() === PHP_SESSION_NONE) { session_start(); }
        
        if (isset($_SESSION['user_id'])) {
            logActivity($_SESSION['user_id'], 'LOGOUT', 'Cierre de sesión');
        }
        
        session_unset();
        session_destroy();
        
        return ['success' => true, 'message' => 'Sesión cerrada exitosamente'];
    }

    // Obtener todos los usuarios (para admin)
    public function getAllUsers($limit = 50, $offset = 0) {
        $query = "SELECT id, username, email, nombre_completo, telefono, fecha_registro, estado, tipo_usuario 
                  FROM " . $this->table_name . " 
                  ORDER BY fecha_registro DESC 
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Activar/desactivar usuario
    public function toggleUserStatus($user_id, $status) {
        $query = "UPDATE " . $this->table_name . " 
                  SET estado = :status 
                  WHERE id = :user_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':user_id', $user_id);

        if ($stmt->execute()) {
            logActivity($_SESSION['user_id'], 'USER_STATUS_CHANGE', 
                       "Estado de usuario $user_id cambiado a $status");
            return ['success' => true, 'message' => 'Estado actualizado exitosamente'];
        }
        
        return ['success' => false, 'message' => 'Error al actualizar estado'];
    }
}
?>