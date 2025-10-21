<?php
// No incluir config.php aquí - se incluye donde se use la clase

class Evaluacion {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todos los módulos disponibles
    public function getModulos() {
        $query = "SELECT * FROM modulos WHERE estado = 'activo' ORDER BY orden_modulo";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener módulo por ID
    public function getModuloById($modulo_id) {
        $query = "SELECT * FROM modulos WHERE id = :modulo_id AND estado = 'activo'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':modulo_id', $modulo_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Iniciar evaluación de un módulo
    public function iniciarEvaluacion($user_id, $modulo_id, $camara_verificada = false) {
        try {
            // Verificar si ya existe una evaluación para este usuario y módulo
            $existing = $this->getEvaluacionUsuario($user_id, $modulo_id);
            
            if ($existing) {
                if ($existing['estado'] == 'completado' || $existing['estado'] == 'tiempo_agotado') {
                    return ['success' => false, 'message' => 'Ya has completado esta evaluación'];
                }
                
                // Si está en progreso, continuar
                if ($existing['estado'] == 'en_progreso') {
                    return ['success' => true, 'message' => 'Evaluación continuada', 'evaluacion_id' => $existing['id']];
                }
            }

            // Crear nueva evaluación
            $query = "INSERT INTO evaluaciones_usuario 
                      (usuario_id, modulo_id, estado, camara_verificada, ip_address, user_agent) 
                      VALUES (:user_id, :modulo_id, 'en_progreso', :camara_verificada, :ip, :user_agent)
                      ON DUPLICATE KEY UPDATE 
                      fecha_inicio = NOW(), estado = 'en_progreso', camara_verificada = :camara_verificada";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':modulo_id', $modulo_id);
            $stmt->bindParam(':camara_verificada', $camara_verificada, PDO::PARAM_BOOL);
            $stmt->bindParam(':ip', $_SERVER['REMOTE_ADDR']);
            $stmt->bindParam(':user_agent', $_SERVER['HTTP_USER_AGENT']);

            if ($stmt->execute()) {
                $evaluacion_id = $existing ? $existing['id'] : $this->conn->lastInsertId();
                
                logActivity($user_id, 'START_EVALUATION', "Evaluación iniciada para módulo $modulo_id");
                
                return ['success' => true, 'message' => 'Evaluación iniciada', 'evaluacion_id' => $evaluacion_id];
            }

            return ['success' => false, 'message' => 'Error al iniciar evaluación'];
        } catch(Exception $e) {
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }

    // Obtener evaluación del usuario
    public function getEvaluacionUsuario($user_id, $modulo_id) {
        $query = "SELECT * FROM evaluaciones_usuario 
                  WHERE usuario_id = :user_id AND modulo_id = :modulo_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':modulo_id', $modulo_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtener preguntas de un módulo
    public function getPreguntasModulo($modulo_id) {
        $query = "SELECT id, pregunta, tipo_pregunta, opciones, puntos, orden_pregunta 
                  FROM preguntas 
                  WHERE modulo_id = :modulo_id AND estado = 'activo' 
                  ORDER BY orden_pregunta";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':modulo_id', $modulo_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Guardar respuesta del usuario
    public function guardarRespuesta($evaluacion_id, $pregunta_id, $respuesta_usuario, $tiempo_respuesta = 0) {
        try {
            // Obtener la pregunta para verificar respuesta
            $query = "SELECT respuesta_correcta, puntos FROM preguntas WHERE id = :pregunta_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':pregunta_id', $pregunta_id);
            $stmt->execute();
            $pregunta = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$pregunta) {
                return ['success' => false, 'message' => 'Pregunta no encontrada'];
            }

            // Verificar si la respuesta es correcta
            $es_correcta = false;
            $puntos_obtenidos = 0;

            if (trim(strtolower($respuesta_usuario)) === trim(strtolower($pregunta['respuesta_correcta']))) {
                $es_correcta = true;
                $puntos_obtenidos = $pregunta['puntos'];
            }

            // Guardar o actualizar respuesta
            $query = "INSERT INTO respuestas_usuario 
                      (evaluacion_id, pregunta_id, respuesta_usuario, es_correcta, puntos_obtenidos, tiempo_respuesta_segundos) 
                      VALUES (:evaluacion_id, :pregunta_id, :respuesta_usuario, :es_correcta, :puntos_obtenidos, :tiempo_respuesta)
                      ON DUPLICATE KEY UPDATE 
                      respuesta_usuario = :respuesta_usuario, es_correcta = :es_correcta, 
                      puntos_obtenidos = :puntos_obtenidos, tiempo_respuesta_segundos = :tiempo_respuesta,
                      fecha_respuesta = NOW()";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':evaluacion_id', $evaluacion_id);
            $stmt->bindParam(':pregunta_id', $pregunta_id);
            $stmt->bindParam(':respuesta_usuario', $respuesta_usuario);
            $stmt->bindParam(':es_correcta', $es_correcta, PDO::PARAM_BOOL);
            $stmt->bindParam(':puntos_obtenidos', $puntos_obtenidos);
            $stmt->bindParam(':tiempo_respuesta', $tiempo_respuesta);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Respuesta guardada', 'es_correcta' => $es_correcta];
            }

            return ['success' => false, 'message' => 'Error al guardar respuesta'];
        } catch(Exception $e) {
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }

    // Finalizar evaluación
    public function finalizarEvaluacion($evaluacion_id, $tiempo_usado_segundos) {
        try {
            // Calcular puntuación total
            $query = "SELECT SUM(puntos_obtenidos) as puntuacion_total 
                      FROM respuestas_usuario 
                      WHERE evaluacion_id = :evaluacion_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':evaluacion_id', $evaluacion_id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $puntuacion = $result['puntuacion_total'] ?? 0;

            // Actualizar evaluación
            $query = "UPDATE evaluaciones_usuario 
                      SET estado = 'completado', fecha_fin = NOW(), 
                          tiempo_usado_segundos = :tiempo_usado, puntuacion = :puntuacion
                      WHERE id = :evaluacion_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':tiempo_usado', $tiempo_usado_segundos);
            $stmt->bindParam(':puntuacion', $puntuacion);
            $stmt->bindParam(':evaluacion_id', $evaluacion_id);

            if ($stmt->execute()) {
                // Obtener info de la evaluación para log
                $eval_info = $this->getEvaluacionById($evaluacion_id);
                logActivity($eval_info['usuario_id'], 'FINISH_EVALUATION', 
                           "Evaluación finalizada. Módulo: {$eval_info['modulo_id']}, Puntuación: $puntuacion");
                
                return ['success' => true, 'message' => 'Evaluación finalizada', 'puntuacion' => $puntuacion];
            }

            return ['success' => false, 'message' => 'Error al finalizar evaluación'];
        } catch(Exception $e) {
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }

    // Marcar evaluación como tiempo agotado
    public function marcarTiempoAgotado($evaluacion_id, $tiempo_usado_segundos) {
        try {
            // Calcular puntuación con respuestas actuales
            $query = "SELECT SUM(puntos_obtenidos) as puntuacion_total 
                      FROM respuestas_usuario 
                      WHERE evaluacion_id = :evaluacion_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':evaluacion_id', $evaluacion_id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $puntuacion = $result['puntuacion_total'] ?? 0;

            // Actualizar evaluación
            $query = "UPDATE evaluaciones_usuario 
                      SET estado = 'tiempo_agotado', fecha_fin = NOW(), 
                          tiempo_usado_segundos = :tiempo_usado, puntuacion = :puntuacion
                      WHERE id = :evaluacion_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':tiempo_usado', $tiempo_usado_segundos);
            $stmt->bindParam(':puntuacion', $puntuacion);
            $stmt->bindParam(':evaluacion_id', $evaluacion_id);

            if ($stmt->execute()) {
                $eval_info = $this->getEvaluacionById($evaluacion_id);
                logActivity($eval_info['usuario_id'], 'TIMEOUT_EVALUATION', 
                           "Tiempo agotado en evaluación. Módulo: {$eval_info['modulo_id']}, Puntuación: $puntuacion");
                
                return ['success' => true, 'message' => 'Tiempo agotado', 'puntuacion' => $puntuacion];
            }

            return ['success' => false, 'message' => 'Error al marcar tiempo agotado'];
        } catch(Exception $e) {
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }

    // Obtener evaluación por ID
    public function getEvaluacionById($evaluacion_id) {
        $query = "SELECT e.*, m.nombre as modulo_nombre, m.duracion_minutos, u.username, u.nombre_completo
                  FROM evaluaciones_usuario e
                  INNER JOIN modulos m ON e.modulo_id = m.id
                  INNER JOIN usuarios u ON e.usuario_id = u.id
                  WHERE e.id = :evaluacion_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':evaluacion_id', $evaluacion_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtener progreso del usuario
    public function getProgresoUsuario($user_id) {
        $query = "SELECT e.*, m.nombre as modulo_nombre, m.duracion_minutos 
                  FROM evaluaciones_usuario e
                  INNER JOIN modulos m ON e.modulo_id = m.id
                  WHERE e.usuario_id = :user_id
                  ORDER BY m.orden_modulo";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener respuestas de una evaluación
    public function getRespuestasEvaluacion($evaluacion_id) {
        $query = "SELECT r.*, p.pregunta, p.tipo_pregunta, p.opciones, p.respuesta_correcta, p.puntos
                  FROM respuestas_usuario r
                  INNER JOIN preguntas p ON r.pregunta_id = p.id
                  WHERE r.evaluacion_id = :evaluacion_id
                  ORDER BY p.orden_pregunta";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':evaluacion_id', $evaluacion_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener todas las evaluaciones (para admin)
    public function getAllEvaluaciones($limit = 50, $offset = 0) {
        $query = "SELECT e.*, m.nombre as modulo_nombre, u.username, u.nombre_completo
                  FROM evaluaciones_usuario e
                  INNER JOIN modulos m ON e.modulo_id = m.id
                  INNER JOIN usuarios u ON e.usuario_id = u.id
                  ORDER BY e.fecha_inicio DESC
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Verificar tiempo restante de evaluación
    public function getTiempoRestante($evaluacion_id) {
        $evaluacion = $this->getEvaluacionById($evaluacion_id);
        
        if (!$evaluacion || $evaluacion['estado'] !== 'en_progreso') {
            return 0;
        }

        $tiempo_transcurrido = time() - strtotime($evaluacion['fecha_inicio']);
        $tiempo_limite = $evaluacion['duracion_minutos'] * 60;
        $tiempo_restante = $tiempo_limite - $tiempo_transcurrido;

        return max(0, $tiempo_restante);
    }
}
?>