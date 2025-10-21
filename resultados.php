<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config/config.php';
require_once 'classes/User.php';
require_once 'classes/Evaluacion.php';

requireAuth();

$database = new Database();
$db = $database->getConnection();

$user = new User($db);
$evaluacion = new Evaluacion($db);

$modulo_id = $_GET['modulo'] ?? null;
$timeout = $_GET['timeout'] ?? false;

if (!$modulo_id) {
    header('Location: dashboard.php');
    exit;
}

$modulo = $evaluacion->getModuloById($modulo_id);
$evaluacion_usuario = $evaluacion->getEvaluacionUsuario($_SESSION['user_id'], $modulo_id);
$user_info = $user->getUserById($_SESSION['user_id']);

if (!$modulo || !$evaluacion_usuario) {
    header('Location: dashboard.php?error=evaluacion_no_encontrada');
    exit;
}

// Verificar que la evaluación está completada
if (!in_array($evaluacion_usuario['estado'], ['completado', 'tiempo_agotado'])) {
    header('Location: evaluacion.php?modulo=' . $modulo_id);
    exit;
}

$respuestas = $evaluacion->getRespuestasEvaluacion($evaluacion_usuario['id']);

// Calcular estadísticas
$total_preguntas = count($respuestas);
$respuestas_correctas = array_filter($respuestas, function($r) { return $r['es_correcta']; });
$num_correctas = count($respuestas_correctas);
$porcentaje_acierto = $total_preguntas > 0 ? ($num_correctas / $total_preguntas) * 100 : 0;

// Calcular puntuación máxima posible
$puntuacion_maxima = array_sum(array_column($respuestas, 'puntos'));
$puntuacion_obtenida = $evaluacion_usuario['puntuacion'];
$porcentaje_puntuacion = $puntuacion_maxima > 0 ? ($puntuacion_obtenida / $puntuacion_maxima) * 100 : 0;

// Determinar nivel de rendimiento
if ($porcentaje_puntuacion >= 90) {
    $nivel_rendimiento = ['texto' => 'Excelente', 'clase' => 'success', 'icono' => 'fa-star'];
} elseif ($porcentaje_puntuacion >= 80) {
    $nivel_rendimiento = ['texto' => 'Muy Bueno', 'clase' => 'success', 'icono' => 'fa-thumbs-up'];
} elseif ($porcentaje_puntuacion >= 70) {
    $nivel_rendimiento = ['texto' => 'Bueno', 'clase' => 'warning', 'icono' => 'fa-check'];
} elseif ($porcentaje_puntuacion >= 60) {
    $nivel_rendimiento = ['texto' => 'Regular', 'clase' => 'warning', 'icono' => 'fa-minus'];
} else {
    $nivel_rendimiento = ['texto' => 'Necesita Mejorar', 'clase' => 'danger', 'icono' => 'fa-exclamation-triangle'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados - <?php echo htmlspecialchars($modulo['nombre']); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .results-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            padding: 3rem 0;
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .results-header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .results-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            box-shadow: var(--shadow);
            border-top: 4px solid var(--primary-color);
        }
        
        .stat-card.success {
            border-top-color: var(--success-color);
        }
        
        .stat-card.warning {
            border-top-color: var(--warning-color);
        }
        
        .stat-card.danger {
            border-top-color: var(--danger-color);
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: var(--medium-gray);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .performance-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem 2rem;
            border-radius: 50px;
            font-size: 1.25rem;
            font-weight: bold;
            margin: 1rem 0;
        }
        
        .performance-badge.success {
            background: var(--success-color);
            color: white;
        }
        
        .performance-badge.warning {
            background: var(--warning-color);
            color: white;
        }
        
        .performance-badge.danger {
            background: var(--danger-color);
            color: white;
        }
        
        .question-review {
            background: white;
            border-radius: 8px;
            margin-bottom: 1rem;
            overflow: hidden;
            box-shadow: var(--shadow);
        }
        
        .question-review-header {
            padding: 1rem 1.5rem;
            background: var(--light-gray);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .question-review-header.correct {
            border-left: 4px solid var(--success-color);
        }
        
        .question-review-header.incorrect {
            border-left: 4px solid var(--danger-color);
        }
        
        .question-review-body {
            padding: 1.5rem;
        }
        
        .question-text {
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--dark-gray);
        }
        
        .answer-comparison {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .answer-section {
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }
        
        .answer-section.user-answer {
            background: rgba(239, 68, 68, 0.05);
            border-color: var(--danger-color);
        }
        
        .answer-section.correct-answer {
            background: rgba(16, 185, 129, 0.05);
            border-color: var(--success-color);
        }
        
        .answer-section.correct-user {
            background: rgba(16, 185, 129, 0.05);
            border-color: var(--success-color);
        }
        
        .answer-label {
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            margin: 2rem 0;
        }
        
        @media (max-width: 768px) {
            .answer-comparison {
                grid-template-columns: 1fr;
            }
            
            .results-header h1 {
                font-size: 2rem;
            }
            
            .stat-value {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body class="main-layout">
    <!-- Header de resultados -->
    <div class="results-header">
        <div class="container">
            <?php if ($timeout): ?>
                <i class="fas fa-clock" style="font-size: 3rem; margin-bottom: 1rem; color: var(--warning-color);"></i>
                <h1>Tiempo Agotado</h1>
                <p>Se completó la evaluación de <?php echo htmlspecialchars($modulo['nombre']); ?> por tiempo límite</p>
            <?php else: ?>
                <i class="fas fa-check-circle" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                <h1>¡Evaluación Completada!</h1>
                <p>Resultados de <?php echo htmlspecialchars($modulo['nombre']); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="container">
        <!-- Resumen de resultados -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-chart-bar"></i> Resumen de Resultados</h3>
            </div>
            <div class="card-body">
                <div class="text-center">
                    <div class="performance-badge <?php echo $nivel_rendimiento['clase']; ?>">
                        <i class="fas <?php echo $nivel_rendimiento['icono']; ?>"></i>
                        <?php echo $nivel_rendimiento['texto']; ?>
                    </div>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value text-primary"><?php echo number_format($puntuacion_obtenida, 1); ?></div>
                        <div class="stat-label">Puntuación Obtenida</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-value text-secondary"><?php echo $puntuacion_maxima; ?></div>
                        <div class="stat-label">Puntuación Máxima</div>
                    </div>
                    
                    <div class="stat-card <?php echo $porcentaje_puntuacion >= 70 ? 'success' : ($porcentaje_puntuacion >= 60 ? 'warning' : 'danger'); ?>">
                        <div class="stat-value"><?php echo number_format($porcentaje_puntuacion, 1); ?>%</div>
                        <div class="stat-label">Porcentaje de Puntuación</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-value text-success"><?php echo $num_correctas; ?></div>
                        <div class="stat-label">Respuestas Correctas</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-value text-secondary"><?php echo $total_preguntas; ?></div>
                        <div class="stat-label">Total de Preguntas</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-value text-info"><?php echo gmdate('H:i:s', $evaluacion_usuario['tiempo_usado_segundos']); ?></div>
                        <div class="stat-label">Tiempo Utilizado</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico de rendimiento -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-chart-pie"></i> Análisis de Rendimiento</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="chart-container">
                            <canvas id="scoreChart"></canvas>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="chart-container">
                            <canvas id="answersChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revisión detallada de preguntas -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-list-check"></i> Revisión Detallada de Preguntas</h3>
            </div>
            <div class="card-body">
                <?php foreach ($respuestas as $index => $respuesta): ?>
                <div class="question-review">
                    <div class="question-review-header <?php echo $respuesta['es_correcta'] ? 'correct' : 'incorrect'; ?>">
                        <div>
                            <strong>Pregunta <?php echo $index + 1; ?></strong>
                            <span class="badge <?php echo $respuesta['es_correcta'] ? 'badge-success' : 'badge-danger'; ?>">
                                <i class="fas <?php echo $respuesta['es_correcta'] ? 'fa-check' : 'fa-times'; ?>"></i>
                                <?php echo $respuesta['es_correcta'] ? 'Correcta' : 'Incorrecta'; ?>
                            </span>
                        </div>
                        <div>
                            <span class="badge badge-info">
                                <?php echo $respuesta['puntos_obtenidos']; ?>/<?php echo $respuesta['puntos']; ?> puntos
                            </span>
                        </div>
                    </div>
                    
                    <div class="question-review-body">
                        <div class="question-text">
                            <?php echo nl2br(htmlspecialchars($respuesta['pregunta'])); ?>
                        </div>
                        
                        <?php if ($respuesta['tipo_pregunta'] !== 'texto'): ?>
                        <div class="answer-comparison">
                            <div class="answer-section <?php echo $respuesta['es_correcta'] ? 'correct-user' : 'user-answer'; ?>">
                                <div class="answer-label <?php echo $respuesta['es_correcta'] ? 'text-success' : 'text-danger'; ?>">
                                    <i class="fas <?php echo $respuesta['es_correcta'] ? 'fa-check' : 'fa-times'; ?>"></i>
                                    Tu Respuesta
                                </div>
                                <div><?php echo htmlspecialchars($respuesta['respuesta_usuario'] ?? 'Sin respuesta'); ?></div>
                            </div>
                            
                            <?php if (!$respuesta['es_correcta']): ?>
                            <div class="answer-section correct-answer">
                                <div class="answer-label text-success">
                                    <i class="fas fa-check"></i>
                                    Respuesta Correcta
                                </div>
                                <div><?php echo htmlspecialchars($respuesta['respuesta_correcta']); ?></div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>
                        <!-- Para preguntas de texto -->
                        <div class="answer-section <?php echo $respuesta['es_correcta'] ? 'correct-user' : 'user-answer'; ?>">
                            <div class="answer-label">
                                <i class="fas fa-comment"></i>
                                Tu Respuesta
                            </div>
                            <div><?php echo nl2br(htmlspecialchars($respuesta['respuesta_usuario'] ?? 'Sin respuesta')); ?></div>
                        </div>
                        
                        <?php if (!empty($respuesta['respuesta_correcta']) && $respuesta['respuesta_correcta'] !== 'Respuesta abierta evaluada manualmente'): ?>
                        <div class="alert alert-info mt-2">
                            <strong>Nota:</strong> <?php echo htmlspecialchars($respuesta['respuesta_correcta']); ?>
                        </div>
                        <?php endif; ?>
                        <?php endif; ?>
                        
                        <div class="mt-2 text-secondary">
                            <small>
                                <i class="fas fa-clock"></i>
                                Tiempo de respuesta: <?php echo $respuesta['tiempo_respuesta_segundos']; ?> segundos
                            </small>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Acciones -->
        <div class="card">
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <a href="dashboard.php" class="btn btn-primary">
                        <i class="fas fa-home"></i> Volver al Dashboard
                    </a>
                    
                    <div class="gap-2">
                        <button onclick="imprimirResultados()" class="btn btn-outline">
                            <i class="fas fa-print"></i> Imprimir Resultados
                        </button>
                        
                        <button onclick="descargarPDF()" class="btn btn-secondary">
                            <i class="fas fa-download"></i> Descargar PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Crear gráfico de puntuación
        const scoreCtx = document.getElementById('scoreChart').getContext('2d');
        new Chart(scoreCtx, {
            type: 'doughnut',
            data: {
                labels: ['Puntos Obtenidos', 'Puntos Perdidos'],
                datasets: [{
                    data: [<?php echo $puntuacion_obtenida; ?>, <?php echo $puntuacion_maxima - $puntuacion_obtenida; ?>],
                    backgroundColor: ['#10b981', '#e5e7eb'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    title: {
                        display: true,
                        text: 'Distribución de Puntuación'
                    }
                }
            }
        });

        // Crear gráfico de respuestas
        const answersCtx = document.getElementById('answersChart').getContext('2d');
        new Chart(answersCtx, {
            type: 'doughnut',
            data: {
                labels: ['Correctas', 'Incorrectas'],
                datasets: [{
                    data: [<?php echo $num_correctas; ?>, <?php echo $total_preguntas - $num_correctas; ?>],
                    backgroundColor: ['#10b981', '#ef4444'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    title: {
                        display: true,
                        text: 'Respuestas Correctas vs Incorrectas'
                    }
                }
            }
        });

        function imprimirResultados() {
            window.print();
        }

        function descargarPDF() {
            // Implementar descarga de PDF
            alert('Función de descarga PDF en desarrollo');
        }

        // Mensaje de felicitaciones o ánimo
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($porcentaje_puntuacion >= 80): ?>
            setTimeout(() => {
                if (confirm('¡Excelente resultado! ¿Te gustaría compartir tu logro?')) {
                    // Implementar función de compartir
                }
            }, 2000);
            <?php endif; ?>
        });
    </script>
</body>
</html>