<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación - Sistema de Evaluación</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .install-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .step {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            overflow: hidden;
        }
        
        .step-header {
            background: var(--primary-color);
            color: white;
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .step-number {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .step-body {
            padding: 1.5rem;
        }
        
        .requirement {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }
        
        .requirement.success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
        }
        
        .requirement.error {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
        }
        
        .sql-box {
            background: #1e293b;
            color: #e2e8f0;
            padding: 1rem;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 0.875rem;
            overflow-x: auto;
            margin: 1rem 0;
        }
        
        .copy-button {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }
        
        .status-indicator {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
        }
        
        .status-success {
            background: var(--success-color);
            color: white;
        }
        
        .status-error {
            background: var(--danger-color);
            color: white;
        }
    </style>
</head>
<body class="login-page">
    <div class="install-container">
        <div class="text-center mb-4">
            <h1><i class="fas fa-cog"></i> Instalación del Sistema de Evaluación</h1>
            <p>Sigue estos pasos para configurar correctamente el sistema</p>
        </div>

        <!-- Paso 1: Verificar requisitos -->
        <div class="step">
            <div class="step-header">
                <div class="step-number">1</div>
                <div>
                    <h3>Verificar Requisitos del Sistema</h3>
                    <p>Comprobando la configuración del servidor</p>
                </div>
            </div>
            <div class="step-body">
                <div id="requirements">
                    <div class="requirement" id="req-php">
                        <div class="status-indicator" id="php-status">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <strong>PHP 7.4 o superior</strong>
                            <div id="php-version"></div>
                        </div>
                    </div>
                    
                    <div class="requirement" id="req-pdo">
                        <div class="status-indicator" id="pdo-status">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <strong>PDO MySQL</strong>
                            <div>Extensión para conexión a base de datos</div>
                        </div>
                    </div>
                    
                    <div class="requirement" id="req-gd">
                        <div class="status-indicator" id="gd-status">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <strong>GD Library</strong>
                            <div>Para procesamiento de imágenes</div>
                        </div>
                    </div>
                    
                    <div class="requirement" id="req-fileinfo">
                        <div class="status-indicator" id="fileinfo-status">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <strong>Fileinfo</strong>
                            <div>Para validación de archivos</div>
                        </div>
                    </div>
                    
                    <div class="requirement" id="req-uploads">
                        <div class="status-indicator" id="uploads-status">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <strong>Directorio uploads</strong>
                            <div>Permisos de escritura requeridos</div>
                        </div>
                    </div>
                </div>
                
                <button onclick="checkRequirements()" class="btn btn-primary">
                    <i class="fas fa-refresh"></i> Verificar Requisitos
                </button>
            </div>
        </div>

        <!-- Paso 2: Configurar base de datos -->
        <div class="step">
            <div class="step-header">
                <div class="step-number">2</div>
                <div>
                    <h3>Configurar Base de Datos</h3>
                    <p>Ejecuta el siguiente script SQL en tu servidor MySQL</p>
                </div>
            </div>
            <div class="step-body">
                <p><strong>Opción 1:</strong> Importar archivo SQL directamente</p>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Importa el archivo <strong>database/database.sql</strong> en phpMyAdmin o tu cliente MySQL preferido.
                </div>
                
                <p><strong>Opción 2:</strong> Ejecutar script automático</p>
                <form id="dbForm">
                    <div class="form-row">
                        <div class="form-group half">
                            <label for="db_host">Host de Base de Datos:</label>
                            <input type="text" id="db_host" name="db_host" value="localhost" required>
                        </div>
                        <div class="form-group half">
                            <label for="db_port">Puerto:</label>
                            <input type="text" id="db_port" name="db_port" value="3306" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group half">
                            <label for="db_user">Usuario:</label>
                            <input type="text" id="db_user" name="db_user" value="root" required>
                        </div>
                        <div class="form-group half">
                            <label for="db_pass">Contraseña:</label>
                            <input type="password" id="db_pass" name="db_pass">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="db_name">Nombre de Base de Datos:</label>
                        <input type="text" id="db_name" name="db_name" value="evaluacion_postulantes" required>
                    </div>
                    
                    <div id="db-message"></div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-database"></i> Crear Base de Datos
                    </button>
                </form>
            </div>
        </div>

        <!-- Paso 3: Configurar administrador -->
        <div class="step">
            <div class="step-header">
                <div class="step-number">3</div>
                <div>
                    <h3>Configurar Cuenta de Administrador</h3>
                    <p>Crea la cuenta de administrador principal</p>
                </div>
            </div>
            <div class="step-body">
                <form id="adminForm">
                    <div class="form-group">
                        <label for="admin_username">Nombre de Usuario:</label>
                        <input type="text" id="admin_username" name="admin_username" value="admin" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_email">Email:</label>
                        <input type="email" id="admin_email" name="admin_email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_name">Nombre Completo:</label>
                        <input type="text" id="admin_name" name="admin_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_password">Contraseña:</label>
                        <input type="password" id="admin_password" name="admin_password" required minlength="6">
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_password_confirm">Confirmar Contraseña:</label>
                        <input type="password" id="admin_password_confirm" name="admin_password_confirm" required minlength="6">
                    </div>
                    
                    <div id="admin-message"></div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-shield"></i> Crear Administrador
                    </button>
                </form>
            </div>
        </div>

        <!-- Paso 4: Finalizar -->
        <div class="step">
            <div class="step-header">
                <div class="step-number">4</div>
                <div>
                    <h3>Finalizar Instalación</h3>
                    <p>Últimos pasos para completar la configuración</p>
                </div>
            </div>
            <div class="step-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Importante:</strong> Por seguridad, elimina o renombra este archivo (setup.php) después de completar la instalación.
                </div>
                
                <div class="alert alert-success" id="success-message" style="display: none;">
                    <i class="fas fa-check-circle"></i>
                    <strong>¡Instalación completada!</strong> El sistema está listo para usar.
                </div>
                
                <div class="text-center">
                    <a href="login.php" class="btn btn-success">
                        <i class="fas fa-sign-in-alt"></i> Ir al Sistema
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function checkRequirements() {
            fetch('api/setup/check-requirements.php')
            .then(response => response.json())
            .then(data => {
                updateRequirement('php', data.php);
                updateRequirement('pdo', data.pdo);
                updateRequirement('gd', data.gd);
                updateRequirement('fileinfo', data.fileinfo);
                updateRequirement('uploads', data.uploads);
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        function updateRequirement(req, result) {
            const element = document.getElementById(`req-${req}`);
            const status = document.getElementById(`${req}-status`);
            
            if (result.status) {
                element.className = 'requirement success';
                status.className = 'status-indicator status-success';
                status.innerHTML = '<i class="fas fa-check"></i>';
            } else {
                element.className = 'requirement error';
                status.className = 'status-indicator status-error';
                status.innerHTML = '<i class="fas fa-times"></i>';
            }
            
            if (req === 'php' && result.version) {
                document.getElementById('php-version').textContent = `Versión: ${result.version}`;
            }
        }

        document.getElementById('dbForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const messageDiv = document.getElementById('db-message');
            
            fetch('api/setup/create-database.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    messageDiv.innerHTML = `
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            ${data.message}
                        </div>
                    `;
                } else {
                    messageDiv.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i>
                            ${data.message}
                        </div>
                    `;
                }
            })
            .catch(error => {
                messageDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        Error de conexión
                    </div>
                `;
            });
        });

        document.getElementById('adminForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const password = document.getElementById('admin_password').value;
            const confirmPassword = document.getElementById('admin_password_confirm').value;
            const messageDiv = document.getElementById('admin-message');
            
            if (password !== confirmPassword) {
                messageDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        Las contraseñas no coinciden
                    </div>
                `;
                return;
            }
            
            const formData = new FormData(this);
            
            fetch('api/setup/create-admin.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    messageDiv.innerHTML = `
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            ${data.message}
                        </div>
                    `;
                    document.getElementById('success-message').style.display = 'block';
                } else {
                    messageDiv.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i>
                            ${data.message}
                        </div>
                    `;
                }
            })
            .catch(error => {
                messageDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        Error de conexión
                    </div>
                `;
            });
        });

        // Verificar requisitos al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            checkRequirements();
        });
    </script>
</body>
</html>