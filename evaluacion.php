<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config/config.php';
require_once 'classes/User.php';
require_once 'classes/ComprobantePago.php';
require_once 'classes/Evaluacion.php';

requireAuth();

$database = new Database();
$db = $database->getConnection();

$comprobante = new ComprobantePago($db);
$evaluacion = new Evaluacion($db);

// Verificar que tiene comprobante aprobado
if (!$comprobante->hasApprovedComprobante($_SESSION['user_id'])) {
    header('Location: dashboard.php?error=no_comprobante');
    exit;
}

$modulo_id = $_GET['modulo'] ?? null;
$camera_verified = $_GET['camera_verified'] ?? false;

if (!$modulo_id) {
    header('Location: dashboard.php');
    exit;
}

$modulo = $evaluacion->getModuloById($modulo_id);
if (!$modulo) {
    header('Location: dashboard.php?error=modulo_no_encontrado');
    exit;
}

// Verificar o crear evaluación
$evaluacion_usuario = $evaluacion->getEvaluacionUsuario($_SESSION['user_id'], $modulo_id);

if (!$evaluacion_usuario) {
    // Crear nueva evaluación
    $result = $evaluacion->iniciarEvaluacion($_SESSION['user_id'], $modulo_id, $camera_verified);
    if (!$result['success']) {
        header('Location: dashboard.php?error=' . urlencode($result['message']));
        exit;
    }
    $evaluacion_id = $result['evaluacion_id'];
    $evaluacion_usuario = $evaluacion->getEvaluacionById($evaluacion_id);
} else {
    // Verificar estado
    if (in_array($evaluacion_usuario['estado'], ['completado', 'tiempo_agotado'])) {
        header('Location: resultados.php?modulo=' . $modulo_id);
        exit;
    }
}

$preguntas = $evaluacion->getPreguntasModulo($modulo_id);
$tiempo_restante = $evaluacion->getTiempoRestante($evaluacion_usuario['id']);

// Si el tiempo se agotó, redirigir
if ($tiempo_restante <= 0) {
    $evaluacion->marcarTiempoAgotado($evaluacion_usuario['id'], $modulo['duracion_minutos'] * 60);
    header('Location: resultados.php?modulo=' . $modulo_id . '&timeout=1');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($modulo['nombre']); ?> - Evaluación</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f8fafc;
            overflow-x: hidden;
        }
        
        .evaluation-header {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            padding: 1rem 0;
        }
        
        .evaluation-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        
        .question-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
            border-left: 4px solid var(--primary-color);
        }
        
        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .question-number {
            background: var(--primary-color);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .question-points {
            background: var(--success-color);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }
        
        .question-text {
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
            color: var(--dark-gray);
        }
        
        .answer-option {
            margin-bottom: 1rem;
        }
        
        .answer-option label {
            display: flex;
            align-items: center;
            padding: 1rem;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }
        
        .answer-option label:hover {
            border-color: var(--primary-color);
            background: rgba(79, 70, 229, 0.05);
        }
        
        .answer-option input[type="radio"]:checked + label {
            border-color: var(--primary-color);
            background: rgba(79, 70, 229, 0.1);
        }
        
        .answer-option input[type="radio"] {
            margin-right: 1rem;
            width: 20px;
            height: 20px;
        }
        
        .text-answer {
            width: 100%;
            min-height: 120px;
            padding: 1rem;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            resize: vertical;
            transition: border-color 0.3s ease;
        }
        
        .text-answer:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        
        .navigation-buttons {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--border-color);
        }
        
        .timer-warning {
            background: var(--warning-color);
        }
        
        .timer-danger {
            background: var(--danger-color);
            animation: pulse 1s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .camera-indicator {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1001;
            background: var(--success-color);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .camera-indicator.inactive {
            background: var(--danger-color);
        }
        
        .progress-bar-container {
            background: var(--light-gray);
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 1rem;
        }
        
        .progress-bar-fill {
            background: var(--primary-color);
            height: 100%;
            transition: width 0.3s ease;
        }
    </style>
</head>
<body>
    <!-- Header de evaluación -->
    <div class="evaluation-header">
        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h2 style="margin: 0; color: var(--dark-gray);">
                        <i class="fas fa-clipboard-check"></i>
                        <?php echo htmlspecialchars($modulo['nombre']); ?>
                    </h2>
                    <p style="margin: 0.5rem 0 0 0; color: var(--medium-gray);">
                        Duración: <?php echo $modulo['duracion_minutos']; ?> minutos
                    </p>
                </div>
                
                <div id="timer" class="timer">
                    <i class="fas fa-clock"></i>
                    <span id="timeRemaining"><?php echo gmdate('H:i:s', $tiempo_restante); ?></span>
                </div>
            </div>
            
            <!-- Barra de progreso -->
            <div class="progress-bar-container">
                <div class="progress-bar-fill" id="progressBar" style="width: 0%;"></div>
            </div>
        </div>
    </div>

    <!-- Indicador de cámara -->
    <div id="cameraIndicator" class="camera-indicator">
        <i class="fas fa-video"></i>
        <span>Cámara Activa</span>
    </div>

    <div class="evaluation-container">
        <form id="evaluationForm">
            <input type="hidden" name="evaluacion_id" value="<?php echo $evaluacion_usuario['id']; ?>">
            
            <?php foreach ($preguntas as $index => $pregunta): ?>
            <div class="question-card" data-question="<?php echo $index + 1; ?>" style="<?php echo $index > 0 ? 'display: none;' : ''; ?>">
                <div class="question-header">
                    <div class="question-number"><?php echo $index + 1; ?></div>
                    <div class="question-points"><?php echo $pregunta['puntos']; ?> punto<?php echo $pregunta['puntos'] > 1 ? 's' : ''; ?></div>
                </div>
                
                <div class="question-text">
                    <?php echo nl2br(htmlspecialchars($pregunta['pregunta'])); ?>
                </div>
                
                <div class="answer-section">
                    <?php if ($pregunta['tipo_pregunta'] === 'multiple'): ?>
                        <?php 
                        $opciones = json_decode($pregunta['opciones'], true);
                        foreach ($opciones as $i => $opcion): 
                        ?>
                        <div class="answer-option">
                            <input type="radio" name="pregunta_<?php echo $pregunta['id']; ?>" 
                                   value="<?php echo htmlspecialchars($opcion); ?>" 
                                   id="q<?php echo $pregunta['id']; ?>_<?php echo $i; ?>" 
                                   style="display: none;">
                            <label for="q<?php echo $pregunta['id']; ?>_<?php echo $i; ?>">
                                <span class="option-letter"><?php echo chr(65 + $i); ?>)</span>
                                <span><?php echo htmlspecialchars($opcion); ?></span>
                            </label>
                        </div>
                        <?php endforeach; ?>
                        
                    <?php elseif ($pregunta['tipo_pregunta'] === 'verdadero_falso'): ?>
                        <div class="answer-option">
                            <input type="radio" name="pregunta_<?php echo $pregunta['id']; ?>" 
                                   value="Verdadero" id="q<?php echo $pregunta['id']; ?>_v" style="display: none;">
                            <label for="q<?php echo $pregunta['id']; ?>_v">
                                <i class="fas fa-check text-success"></i>
                                <span>Verdadero</span>
                            </label>
                        </div>
                        <div class="answer-option">
                            <input type="radio" name="pregunta_<?php echo $pregunta['id']; ?>" 
                                   value="Falso" id="q<?php echo $pregunta['id']; ?>_f" style="display: none;">
                            <label for="q<?php echo $pregunta['id']; ?>_f">
                                <i class="fas fa-times text-danger"></i>
                                <span>Falso</span>
                            </label>
                        </div>
                        
                    <?php elseif ($pregunta['tipo_pregunta'] === 'texto'): ?>
                        <textarea name="pregunta_<?php echo $pregunta['id']; ?>" 
                                  class="text-answer" 
                                  placeholder="Escribe tu respuesta aquí..."
                                  maxlength="1000"></textarea>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            
            <div class="navigation-buttons">
                <button type="button" id="prevBtn" onclick="previousQuestion()" class="btn btn-secondary" style="display: none;">
                    <i class="fas fa-arrow-left"></i> Anterior
                </button>
                
                <div>
                    <span id="questionCounter">Pregunta 1 de <?php echo count($preguntas); ?></span>
                </div>
                
                <button type="button" id="nextBtn" onclick="nextQuestion()" class="btn btn-primary">
                    Siguiente <i class="fas fa-arrow-right"></i>
                </button>
                
                <button type="button" id="finishBtn" onclick="finishEvaluation()" class="btn btn-success" style="display: none;">
                    <i class="fas fa-check"></i> Finalizar Evaluación
                </button>
            </div>
        </form>
    </div>

    <!-- Modal de confirmación -->
    <div id="finishModal" class="modal-overlay" style="display: none;">
        <div class="modal">
            <div class="modal-header">
                <h3>Finalizar Evaluación</h3>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que quieres finalizar la evaluación?</p>
                <p><strong>Nota:</strong> Una vez finalizada, no podrás modificar tus respuestas.</p>
                <div id="unansweredQuestions" style="display: none;">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Atención:</strong> Tienes preguntas sin responder. ¿Deseas continuar?
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button onclick="closeFinishModal()" class="btn btn-secondary">Cancelar</button>
                <button onclick="confirmFinish()" class="btn btn-success">
                    <i class="fas fa-check"></i> Sí, Finalizar
                </button>
            </div>
        </div>
    </div>

    <script>
        // Variables globales
        let currentQuestion = 1;
        const totalQuestions = <?php echo count($preguntas); ?>;
        let timeRemaining = <?php echo $tiempo_restante; ?>;
        let timerInterval;
        let cameraStream = null;
        let questionStartTime = Date.now();
        const evaluacionId = <?php echo $evaluacion_usuario['id']; ?>;
        
        // Inicializar
        document.addEventListener('DOMContentLoaded', function() {
            startTimer();
            initializeCamera();
            updateProgress();
            
            // Prevenir salida accidental
            window.addEventListener('beforeunload', function(e) {
                if (timeRemaining > 0) {
                    e.preventDefault();
                    e.returnValue = '¿Estás seguro de que quieres salir? Se perderá tu progreso.';
                    return e.returnValue;
                }
            });
            
            // Detectar cambio de pestaña/ventana
            document.addEventListener('visibilitychange', function() {
                if (document.hidden) {
                    logActivity('TAB_CHANGE', 'Usuario cambió de pestaña/ventana durante evaluación');
                }
            });
        });
        
        function startTimer() {
            const timerElement = document.getElementById('timeRemaining');
            const timerContainer = document.getElementById('timer');
            
            timerInterval = setInterval(function() {
                timeRemaining--;
                
                const hours = Math.floor(timeRemaining / 3600);
                const minutes = Math.floor((timeRemaining % 3600) / 60);
                const seconds = timeRemaining % 60;
                
                timerElement.textContent = 
                    `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                
                // Cambiar color según tiempo restante
                if (timeRemaining <= 300) { // 5 minutos
                    timerContainer.className = 'timer timer-danger';
                } else if (timeRemaining <= 600) { // 10 minutos
                    timerContainer.className = 'timer timer-warning';
                } else {
                    timerContainer.className = 'timer';
                }
                
                if (timeRemaining <= 0) {
                    clearInterval(timerInterval);
                    timeUp();
                }
            }, 1000);
        }
        
        function timeUp() {
            alert('¡Tiempo agotado! La evaluación se finalizará automáticamente.');
            submitEvaluation(true);
        }
        
        function initializeCamera() {
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(function(stream) {
                    cameraStream = stream;
                    updateCameraIndicator(true);
                })
                .catch(function(err) {
                    console.error('Error accessing camera:', err);
                    updateCameraIndicator(false);
                    alert('No se pudo acceder a la cámara. Esto puede afectar la validez de tu evaluación.');
                });
        }
        
        function updateCameraIndicator(active) {
            const indicator = document.getElementById('cameraIndicator');
            if (active) {
                indicator.className = 'camera-indicator';
                indicator.innerHTML = '<i class="fas fa-video"></i><span>Cámara Activa</span>';
            } else {
                indicator.className = 'camera-indicator inactive';
                indicator.innerHTML = '<i class="fas fa-video-slash"></i><span>Cámara Inactiva</span>';
            }
        }
        
        function showQuestion(questionNum) {
            // Ocultar todas las preguntas
            document.querySelectorAll('.question-card').forEach(card => {
                card.style.display = 'none';
            });
            
            // Mostrar pregunta actual
            const currentCard = document.querySelector(`[data-question="${questionNum}"]`);
            if (currentCard) {
                currentCard.style.display = 'block';
            }
            
            // Actualizar botones
            document.getElementById('prevBtn').style.display = questionNum > 1 ? 'block' : 'none';
            document.getElementById('nextBtn').style.display = questionNum < totalQuestions ? 'block' : 'none';
            document.getElementById('finishBtn').style.display = questionNum === totalQuestions ? 'block' : 'none';
            
            // Actualizar contador
            document.getElementById('questionCounter').textContent = `Pregunta ${questionNum} de ${totalQuestions}`;
            
            // Actualizar progreso
            updateProgress();
            
            // Resetear tiempo de pregunta
            questionStartTime = Date.now();
        }
        
        function updateProgress() {
            const progress = ((currentQuestion - 1) / totalQuestions) * 100;
            document.getElementById('progressBar').style.width = progress + '%';
        }
        
        function nextQuestion() {
            saveCurrentAnswer();
            
            if (currentQuestion < totalQuestions) {
                currentQuestion++;
                showQuestion(currentQuestion);
            }
        }
        
        function previousQuestion() {
            saveCurrentAnswer();
            
            if (currentQuestion > 1) {
                currentQuestion--;
                showQuestion(currentQuestion);
            }
        }
        
        function saveCurrentAnswer() {
            const currentCard = document.querySelector(`[data-question="${currentQuestion}"]`);
            const formElements = currentCard.querySelectorAll('input[type="radio"]:checked, textarea');
            
            formElements.forEach(element => {
                if ((element.type === 'radio' && element.checked) || element.type === 'textarea') {
                    const preguntaId = element.name.replace('pregunta_', '');
                    const respuesta = element.value;
                    const tiempoRespuesta = Math.floor((Date.now() - questionStartTime) / 1000);
                    
                    // Guardar respuesta vía API
                    if (respuesta.trim() !== '') {
                        fetch('api/evaluacion/save-answer.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                evaluacion_id: evaluacionId,
                                pregunta_id: preguntaId,
                                respuesta: respuesta,
                                tiempo_respuesta: tiempoRespuesta
                            })
                        })
                        .catch(error => console.error('Error saving answer:', error));
                    }
                }
            });
        }
        
        function finishEvaluation() {
            // Verificar preguntas sin responder
            const unanswered = getUnansweredQuestions();
            
            if (unanswered.length > 0) {
                document.getElementById('unansweredQuestions').style.display = 'block';
            } else {
                document.getElementById('unansweredQuestions').style.display = 'none';
            }
            
            document.getElementById('finishModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
        
        function closeFinishModal() {
            document.getElementById('finishModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        
        function confirmFinish() {
            saveCurrentAnswer();
            submitEvaluation(false);
        }
        
        function submitEvaluation(timeOut = false) {
            const tiempoUsado = (<?php echo $modulo['duracion_minutos']; ?> * 60) - timeRemaining;
            
            fetch('api/evaluacion/finish.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    evaluacion_id: evaluacionId,
                    tiempo_usado: tiempoUsado,
                    timeout: timeOut
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    clearInterval(timerInterval);
                    if (cameraStream) {
                        cameraStream.getTracks().forEach(track => track.stop());
                    }
                    window.location.href = `resultados.php?modulo=<?php echo $modulo_id; ?>${timeOut ? '&timeout=1' : ''}`;
                } else {
                    alert('Error al finalizar evaluación: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión al finalizar evaluación');
            });
        }
        
        function getUnansweredQuestions() {
            const unanswered = [];
            
            for (let i = 1; i <= totalQuestions; i++) {
                const card = document.querySelector(`[data-question="${i}"]`);
                const hasAnswer = card.querySelector('input[type="radio"]:checked') || 
                                 (card.querySelector('textarea') && card.querySelector('textarea').value.trim() !== '');
                
                if (!hasAnswer) {
                    unanswered.push(i);
                }
            }
            
            return unanswered;
        }
        
        function logActivity(action, description) {
            fetch('api/evaluacion/log-activity.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: action,
                    description: description,
                    evaluacion_id: evaluacionId
                })
            })
            .catch(error => console.error('Error logging activity:', error));
        }
        
        // Inicializar primera pregunta
        showQuestion(1);
    </script>
</body>
</html>