<?php
require_once 'config/config.php';
requireAuth();

$database = new Database();
$db = $database->getConnection();

// Información del usuario
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$nombre_completo = $_SESSION['nombre_completo'] ?? $username;

// Obtener información adicional del usuario desde la base de datos
try {
    $stmt = $db->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_info = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user_info) {
        $user_info = [
            'id' => $user_id,
            'username' => $username,
            'nombre_completo' => $nombre_completo,
            'email' => $_SESSION['email'] ?? '',
            'tipo_usuario' => $_SESSION['tipo_usuario'] ?? 'postulante'
        ];
    }
} catch (Exception $e) {
    $user_info = [
        'id' => $user_id,
        'username' => $username,
        'nombre_completo' => $nombre_completo,
        'email' => $_SESSION['email'] ?? '',
        'tipo_usuario' => $_SESSION['tipo_usuario'] ?? 'postulante'
    ];
}

// Verificar comprobantes (simulado por ahora)
$has_approved_comprobante = true; // Temporal para evitar bloqueos
$user_comprobantes = []; // Array vacío por ahora

// Verificar módulos disponibles
try {
    // Por ahora usamos datos estáticos, luego se puede conectar a base de datos real
    $modulos_disponibles = 3;
    $evaluaciones_completadas = 0;
    $progreso_total = 0;
    $puntuacion_promedio = '--';
    // Definir módulos por defecto para evitar warnings y mostrar contenido
    $modulos = [
        [
            'id' => 1,
            'nombre' => 'Programación Básica',
            'descripcion' => 'Conceptos fundamentales de programación',
            'duracion_minutos' => 25
        ],
        [
            'id' => 2,
            'nombre' => 'Bases de Datos',
            'descripcion' => 'SQL y gestión de bases de datos',
            'duracion_minutos' => 45
        ],
        [
            'id' => 3,
            'nombre' => 'Lógica y Razonamiento',
            'descripcion' => 'Pruebas de aptitud y pensamiento lógico',
            'duracion_minutos' => 30
        ]
    ];
    // Progreso por módulo (vacío por defecto)
    $progreso_por_modulo = [];
} catch (Exception $e) {
    $modulos_disponibles = 3;
    $evaluaciones_completadas = 0;
    $progreso_total = 0;
    $puntuacion_promedio = '--';
    $modulos = [];
    $progreso_por_modulo = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Evaluación</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dashboard-page">
    <!-- Header del Dashboard -->
    <div class="dashboard-header">
        <div class="container">
            <div class="dashboard-title">
                <h1><i class="fas fa-clipboard-check"></i> Sistema de Evaluación</h1>
                <p>Bienvenido, <?php echo htmlspecialchars($user_info['nombre_completo']); ?></p>
            </div>
            <div class="user-menu">
                <div class="user-dropdown">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo htmlspecialchars($user_info['username']); ?></span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="dropdown-menu">
                    <!-- <a href="register.php"><i class="fas fa-user"></i> Perfil</a> -->
                    <a href="resultados.php"><i class="fas fa-chart-line"></i> Mis Resultados</a>
                    <?php if (isAdmin()): ?>
                    <a href="admin/dashboard.php"><i class="fas fa-cog"></i> Panel Admin</a>
                    <?php endif; ?>
                    <hr>
                    <a href="api/auth/logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido Principal -->
    <div class="dashboard-container">
        <!-- Mensaje de bienvenida si es necesario -->
        <?php if (!$has_approved_comprobante): ?>
        <div class="message-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
                <strong>Acción requerida:</strong> Debes subir y que sea aprobado tu comprobante de pago para poder acceder a las evaluaciones.
                <br>
                <a href="comprobantes.php" class="btn btn-primary" style="margin-top: 8px;">
                    <i class="fas fa-upload"></i> Subir Comprobante
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-tasks"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $modulos_disponibles; ?></h3>
                    <p>Módulos Disponibles</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $evaluaciones_completadas; ?></h3>
                    <p>Evaluaciones Completadas</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $progreso_total; ?>%</h3>
                    <p>Progreso Total</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon purple">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $puntuacion_promedio; ?></h3>
                    <p>Puntuación Promedio</p>
                </div>
            </div>
        </div>

        <!-- Módulos de Evaluación (se muestran más abajo) -->

        <!-- Información adicional -->
        <div style="margin-top: 40px; background: white; border-radius: 8px; padding: 24px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
            <h3><i class="fas fa-info-circle"></i> Información Importante</h3>
            <div style="margin-top: 16px;">
                <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 16px; margin-bottom: 16px;">
                    <h4 style="color: #856404; margin-bottom: 8px;">
                        <i class="fas fa-video"></i> Verificación por Cámara
                    </h4>
                    <p style="color: #856404; margin: 0;">
                        Todas las evaluaciones requieren verificación por cámara para garantizar la integridad del proceso. 
                        Asegúrate de tener una cámara web funcionando y una conexión estable a internet.
                    </p>
                </div>
                
                <div style="background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 8px; padding: 16px;">
                    <h4 style="color: #0c5460; margin-bottom: 8px;">
                        <i class="fas fa-clock"></i> Tiempo Limitado
                    </h4>
                    <p style="color: #0c5460; margin: 0;">
                        Cada módulo tiene un tiempo límite específico. Una vez iniciada la evaluación, 
                        el tiempo comenzará a correr y no se puede pausar.
                    </p>
                </div>
            </div>
            
            <div style="margin-top: 24px; text-align: center;">
                <a href="comprobantes.php" class="btn btn-success">
                    <i class="fas fa-file-upload"></i>
                    Subir Comprobante de Pago
                </a>
                <a href="resultados.php" class="btn btn-info" style="margin-left: 12px;">
                    <i class="fas fa-chart-line"></i>
                    Ver Mis Resultados
                </a>
            </div>
        </div>
    </div>

    <style>
        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 8px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            padding: 8px 0;
            min-width: 200px;
            display: none;
            z-index: 1000;
        }
        
        .user-menu:hover .dropdown-menu {
            display: block;
        }
        
        .dropdown-menu a {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            color: #64748b;
            text-decoration: none;
            transition: background 0.2s;
        }
        
        .dropdown-menu a:hover {
            background: #f8fafc;
            color: #1e293b;
        }
        
        .dropdown-menu hr {
            margin: 8px 0;
            border: none;
            border-top: 1px solid #e2e8f0;
        }
        
        .section-header {
            margin: 40px 0 24px 0;
        }
        
        .section-header h2 {
            color: #1e293b;
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .module-actions {
            display: flex;
            gap: 8px;
        }
    </style>

    <script>
        // Efectos de hover para las cards
        document.querySelectorAll('.stat-card, .module-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-4px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });

        // Mostrar información de usuario
        console.log('Usuario logueado:', '<?php echo $username; ?>');
        console.log('ID de usuario:', '<?php echo $user_id; ?>');
    </script>
</body>
</html>
                    
                </li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <!-- Bienvenida -->
        

        <!-- Estado de comprobantes -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-file-invoice-dollar"></i> Estado de Comprobantes</h3>
            </div>
            <div class="card-body">
                <?php if (empty($user_comprobantes)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        No has subido ningún comprobante de pago. 
                        <a href="comprobantes.php">Subir ahora</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Archivo</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                    <th>Comentarios</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($user_comprobantes as $comp): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($comp['nombre_archivo']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($comp['fecha_subida'])); ?></td>
                                    <td>
                                        <?php 
                                        $badge_class = $comp['estado'] === 'aprobado' ? 'badge-success' : 
                                                      ($comp['estado'] === 'rechazado' ? 'badge-danger' : 'badge-warning');
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?>">
                                            <?php echo ucfirst($comp['estado']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($comp['comentarios'] ?? '-'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Módulos de evaluación -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-tasks"></i> Módulos de Evaluación</h3>
            </div>
            <div class="card-body">
                <?php if (!$has_approved_comprobante): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-lock"></i>
                        Los módulos de evaluación estarán disponibles una vez que tu comprobante de pago sea aprobado.
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($modulos as $modulo): ?>
                            <?php 
                            $progreso = $progreso_por_modulo[$modulo['id']] ?? null;
                            $puede_acceder = true;
                            
                            if ($progreso) {
                                $estado = $progreso['estado'];
                                $completado = in_array($estado, ['completado', 'tiempo_agotado']);
                            } else {
                                $estado = 'no_iniciado';
                                $completado = false;
                            }
                            ?>
                            <div class="col-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h4><?php echo htmlspecialchars($modulo['nombre']); ?></h4>
                                    </div>
                                    <div class="card-body">
                                        <p><?php echo htmlspecialchars($modulo['descripcion']); ?></p>
                                        <p><strong>Duración:</strong> <?php echo $modulo['duracion_minutos']; ?> minutos</p>
                                        
                                        <?php if ($progreso): ?>
                                            <p><strong>Estado:</strong> 
                                                <?php 
                                                $badge_class = $estado === 'completado' ? 'badge-success' : 
                                                              ($estado === 'tiempo_agotado' ? 'badge-danger' : 
                                                              ($estado === 'en_progreso' ? 'badge-warning' : 'badge-secondary'));
                                                ?>
                                                <span class="badge <?php echo $badge_class; ?>">
                                                    <?php 
                                                    echo $estado === 'completado' ? 'Completado' :
                                                         ($estado === 'tiempo_agotado' ? 'Tiempo Agotado' :
                                                         ($estado === 'en_progreso' ? 'En Progreso' : 'No Iniciado'));
                                                    ?>
                                                </span>
                                            </p>
                                            
                                            <?php if ($completado): ?>
                                                <p><strong>Puntuación:</strong> <?php echo $progreso['puntuacion']; ?> puntos</p>
                                                <p><strong>Tiempo usado:</strong> <?php echo gmdate('H:i:s', $progreso['tiempo_usado_segundos']); ?></p>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-footer">
                                        <?php if ($completado): ?>
                                            <button class="btn btn-secondary" disabled>
                                                <i class="fas fa-check"></i> Completado
                                            </button>
                                            <a href="resultados.php?modulo=<?php echo $modulo['id']; ?>" class="btn btn-outline btn-small">
                                                <i class="fas fa-chart-bar"></i> Ver Resultados
                                            </a>
                                        <?php elseif ($estado === 'en_progreso'): ?>
                                            <a href="evaluacion.php?modulo=<?php echo $modulo['id']; ?>" class="btn btn-warning">
                                                <i class="fas fa-play"></i> Continuar
                                            </a>
                                        <?php else: ?>
                                            <button onclick="iniciarEvaluacion(<?php echo $modulo['id']; ?>)" class="btn btn-primary">
                                                <i class="fas fa-play"></i> Iniciar Evaluación
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal para verificación de cámara -->
    <div id="cameraModal" class="modal-overlay" style="display: none;">
        <div class="modal">
            <div class="modal-header">
                <h3>Verificación de Cámara</h3>
            </div>
            <div class="modal-body">
                <p>Para realizar la evaluación, necesitas activar tu cámara web. Esto es requerido para la integridad del proceso de evaluación.</p>
                <div class="text-center">
                    <video id="cameraPreview" width="400" height="300" autoplay style="border-radius: 8px; background: #000;"></video>
                </div>
                <div id="cameraStatus" class="text-center mt-2"></div>
            </div>
            <div class="modal-footer">
                <button onclick="closeCameraModal()" class="btn btn-secondary">Cancelar</button>
                <button id="confirmCameraBtn" onclick="confirmCamera()" class="btn btn-primary" disabled>
                    <i class="fas fa-check"></i> Confirmar y Continuar
                </button>
            </div>
        </div>
    </div>

    <script src="assets/js/dashboard.js"></script>
    <script>
        function toggleUserMenu() {
            const dropdown = document.getElementById('userMenuDropdown');
            dropdown.classList.toggle('show');
        }

        // Cerrar menú si se hace clic fuera
        document.addEventListener('click', function(event) {
            const userMenu = document.querySelector('.user-menu');
            if (!userMenu.contains(event.target)) {
                document.getElementById('userMenuDropdown').classList.remove('show');
            }
        });

        function logout() {
            if (confirm('¿Estás seguro de que quieres cerrar sesión?')) {
                fetch('api/auth/logout.php', {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    window.location.href = 'login.php';
                })
                .catch(error => {
                    console.error('Error:', error);
                    window.location.href = 'login.php';
                });
            }
        }

        let currentModuloId = null;
        let cameraStream = null;

        function iniciarEvaluacion(moduloId) {
            currentModuloId = moduloId;
            showCameraModal();
        }

        function showCameraModal() {
            document.getElementById('cameraModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
            
            // Solicitar acceso a la cámara
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(function(stream) {
                    cameraStream = stream;
                    const video = document.getElementById('cameraPreview');
                    video.srcObject = stream;
                    
                    document.getElementById('cameraStatus').innerHTML = 
                        '<i class="fas fa-check-circle text-success"></i> Cámara activada correctamente';
                    document.getElementById('confirmCameraBtn').disabled = false;
                })
                .catch(function(err) {
                    console.error('Error accediendo a la cámara:', err);
                    document.getElementById('cameraStatus').innerHTML = 
                        '<i class="fas fa-exclamation-triangle text-danger"></i> Error: No se pudo acceder a la cámara. Es requerida para continuar.';
                });
        }

        function closeCameraModal() {
            if (cameraStream) {
                cameraStream.getTracks().forEach(track => track.stop());
                cameraStream = null;
            }
            
            document.getElementById('cameraModal').style.display = 'none';
            document.body.style.overflow = 'auto';
            currentModuloId = null;
        }

        function confirmCamera() {
            if (cameraStream && currentModuloId) {
                // Detener la cámara del modal
                cameraStream.getTracks().forEach(track => track.stop());
                
                // Redirigir a la evaluación
                window.location.href = `evaluacion.php?modulo=${currentModuloId}&camera_verified=1`;
            }
        }
    </script>
</body>
</html>