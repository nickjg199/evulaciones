<?php
// No incluir config.php aquí - se incluye donde se use la clase

class ComprobantePago {
    private $conn;
    private $table_name = "comprobantes_pago";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Subir comprobante de pago
    public function uploadComprobante($user_id, $file) {
        try {
            // Validar archivo
            $validation = $this->validateFile($file);
            if (!$validation['valid']) {
                return ['success' => false, 'message' => $validation['message']];
            }

            // Generar nombre único para el archivo
            $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $new_filename = 'comprobante_' . $user_id . '_' . time() . '.' . $file_extension;
            $target_dir = rtrim(UPLOAD_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'comprobantes' . DIRECTORY_SEPARATOR;
            if (!is_dir($target_dir)) {
                if (!mkdir($target_dir, 0755, true)) {
                    return ['success' => false, 'message' => 'No se pudo crear el directorio de subida'];
                }
            }

            $upload_path = $target_dir . $new_filename;

            // Mover archivo
            if (is_uploaded_file($file['tmp_name']) && move_uploaded_file($file['tmp_name'], $upload_path)) {
                // Guardar en base de datos
                // Guardar ruta relativa en DB (para mayor portabilidad)
                $relative_path = 'uploads/comprobantes/' . $new_filename;

                $query = "INSERT INTO " . $this->table_name . " 
                          (usuario_id, nombre_archivo, ruta_archivo) 
                          VALUES (:user_id, :nombre_archivo, :ruta_archivo)";

                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':nombre_archivo', $file['name']);
                $stmt->bindParam(':ruta_archivo', $relative_path);

                if ($stmt->execute()) {
                    logActivity($user_id, 'UPLOAD_COMPROBANTE', 'Comprobante de pago subido: ' . $file['name']);
                    return ['success' => true, 'message' => 'Comprobante subido exitosamente'];
                }
            }
            // Si llegó aquí, el archivo no se movió o no se pudo guardar en DB
            $err = error_get_last();
            error_log('Upload failed for user ' . $user_id . ' - tmp_name: ' . ($file['tmp_name'] ?? 'n/a') . ' - last_error: ' . print_r($err, true));
            return ['success' => false, 'message' => 'Error al subir el archivo (comprueba permisos y espacio en disco)'];
        } catch(Exception $e) {
            error_log('Exception in uploadComprobante: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }

    // Validar archivo
    private function validateFile($file) {
        // Verificar errores de upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'message' => 'Error al subir el archivo'];
        }

        // Verificar tamaño
        if ($file['size'] > MAX_FILE_SIZE) {
            return ['valid' => false, 'message' => 'El archivo es demasiado grande (máximo 5MB)'];
        }

        // Verificar tipo de archivo
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($file_extension, ALLOWED_FILE_TYPES)) {
            return ['valid' => false, 'message' => 'Tipo de archivo no permitido. Solo se permiten: ' . implode(', ', ALLOWED_FILE_TYPES)];
        }

        // Verificar que es una imagen o PDF real
        if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            $image_info = getimagesize($file['tmp_name']);
            if ($image_info === false) {
                return ['valid' => false, 'message' => 'El archivo no es una imagen válida'];
            }
        }

        return ['valid' => true, 'message' => 'Archivo válido'];
    }

    // Obtener comprobantes del usuario
    public function getUserComprobantes($user_id) {
        // incluir ruta_archivo para poder mostrar/descargar el archivo desde la interfaz del postulante
        $query = "SELECT id, nombre_archivo, ruta_archivo, fecha_subida, estado, comentarios, fecha_revision, revisado_por 
                  FROM " . $this->table_name . " 
                  WHERE usuario_id = :user_id 
                  ORDER BY fecha_subida DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Verificar si usuario tiene comprobante aprobado
    public function hasApprovedComprobante($user_id) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                  WHERE usuario_id = :user_id AND estado = 'aprobado'";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    // Obtener todos los comprobantes pendientes (para admin)
    public function getPendingComprobantes() {
        $query = "SELECT c.*, u.username, u.nombre_completo, u.email 
                  FROM " . $this->table_name . " c
                  INNER JOIN usuarios u ON c.usuario_id = u.id 
                  WHERE c.estado = 'pendiente' 
                  ORDER BY c.fecha_subida ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Revisar comprobante (para admin)
    public function reviewComprobante($comprobante_id, $estado, $comentarios, $admin_id) {
        try {
            $query = "UPDATE " . $this->table_name . " 
                      SET estado = :estado, comentarios = :comentarios, 
                          revisado_por = :admin_id, fecha_revision = NOW() 
                      WHERE id = :comprobante_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':comentarios', $comentarios);
            $stmt->bindParam(':admin_id', $admin_id);
            $stmt->bindParam(':comprobante_id', $comprobante_id);

            if ($stmt->execute()) {
                logActivity($admin_id, 'REVIEW_COMPROBANTE', 
                           "Comprobante $comprobante_id revisado: $estado");
                return ['success' => true, 'message' => 'Comprobante revisado exitosamente'];
            }

            return ['success' => false, 'message' => 'Error al revisar comprobante'];
        } catch(Exception $e) {
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }

    // Obtener comprobante por ID
    public function getComprobanteById($comprobante_id) {
        $query = "SELECT c.*, u.username, u.nombre_completo 
                  FROM " . $this->table_name . " c
                  INNER JOIN usuarios u ON c.usuario_id = u.id 
                  WHERE c.id = :comprobante_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':comprobante_id', $comprobante_id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Eliminar comprobante
    public function deleteComprobante($comprobante_id, $user_id) {
        try {
            // Obtener información del archivo
            $comprobante = $this->getComprobanteById($comprobante_id);
            
            if (!$comprobante || $comprobante['usuario_id'] != $user_id) {
                return ['success' => false, 'message' => 'Comprobante no encontrado'];
            }

            // Eliminar archivo físico: intentamos con la ruta tal como está en la BD (puede ser relativa)
            // y como alternativa construimos una ruta absoluta basada en UPLOAD_DIR
            if (!empty($comprobante['ruta_archivo'])) {
                $stored = $comprobante['ruta_archivo'];
                // Si el archivo existe tal cual (ruta relativa o absoluta), eliminar
                if (file_exists($stored)) {
                    @unlink($stored);
                } else {
                    // Construir ruta absoluta desde UPLOAD_DIR
                    $candidate = rtrim(UPLOAD_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($stored, '/\\');
                    if (file_exists($candidate)) {
                        @unlink($candidate);
                    }
                }
            }

            // Eliminar registro de base de datos
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :comprobante_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':comprobante_id', $comprobante_id);

            if ($stmt->execute()) {
                logActivity($user_id, 'DELETE_COMPROBANTE', 'Comprobante eliminado: ' . $comprobante['nombre_archivo']);
                return ['success' => true, 'message' => 'Comprobante eliminado exitosamente'];
            }

            return ['success' => false, 'message' => 'Error al eliminar comprobante'];
        } catch(Exception $e) {
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
}
?>