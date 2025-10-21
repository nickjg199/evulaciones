<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Evaluación de Postulantes</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .welcome-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            text-align: center;
        }
        .welcome-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin: 20px 0;
        }
        .welcome-header {
            margin-bottom: 30px;
        }
        .welcome-header i {
            font-size: 4rem;
            color: #007bff;
            margin-bottom: 20px;
        }
        .welcome-header h1 {
            color: #333;
            margin-bottom: 10px;
        }
        .welcome-header p {
            color: #666;
            font-size: 1.1rem;
        }
        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 30px 0;
        }
        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 15px 25px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background: #007bff;
            color: white;
        }
        .btn-primary:hover {
            background: #0056b3;
            transform: translateY(-2px);
        }
        .btn-success {
            background: #28a745;
            color: white;
        }
        .btn-success:hover {
            background: #1e7e34;
            transform: translateY(-2px);
        }
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        .btn-warning:hover {
            background: #e0a800;
            transform: translateY(-2px);
        }
        .btn-info {
            background: #17a2b8;
            color: white;
        }
        .btn-info:hover {
            background: #138496;
            transform: translateY(-2px);
        }
        .system-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 30px 0;
            text-align: left;
        }
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .status-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            border: 1px solid #dee2e6;
        }
        .status-ok {
            border-color: #28a745;
            color: #28a745;
        }
        .status-error {
            border-color: #dc3545;
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="welcome-container">
        <div class="welcome-card">
            <div class="welcome-header">
                <i class="fas fa-clipboard-check"></i>
                <h1>Sistema de Evaluación de Postulantes</h1>
                <p>Plataforma completa para la evaluación de candidatos a ofertas laborales</p>
            </div>

            <div class="action-buttons">
                <a href="login.php" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i>
                    Iniciar Sesión
                </a>
                <a href="register.php" class="btn btn-success">
                    <i class="fas fa-user-plus"></i>
                    Registrarse
                </a>
                <a href="status.php" class="btn btn-info">
                    <i class="fas fa-chart-line"></i>
                    Estado del Sistema
                </a>
                <a href="admin/dashboard.php" class="btn btn-warning">
                    <i class="fas fa-cog"></i>
                    Panel Admin
                </a>
            </div>

            <div class="system-info">
                <h3><i class="fas fa-info-circle"></i> Información del Sistema</h3>
                <div class="status-grid">
                    <div class="status-item status-ok">
                        <i class="fas fa-server"></i>
                        <br><strong>Apache</strong>
                        <br>Activo
                    </div>
                    <div class="status-item status-ok">
                        <i class="fas fa-database"></i>
                        <br><strong>MySQL</strong>
                        <br>Conectado
                    </div>
                    <div class="status-item status-ok">
                        <i class="fas fa-code"></i>
                        <br><strong>PHP</strong>
                        <br>v<?php echo phpversion(); ?>
                    </div>
                    <div class="status-item status-ok">
                        <i class="fas fa-folder"></i>
                        <br><strong>Archivos</strong>
                        <br>Listos
                    </div>
                </div>
                
                <div style="margin-top: 20px; padding: 15px; background: #e7f3ff; border-radius: 8px;">
                    <h4><i class="fas fa-key"></i> Credenciales de Prueba</h4>
                    <p><strong>Usuario:</strong> admin<br>
                    <strong>Contraseña:</strong> password</p>
                </div>
            </div>

            <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 10px;">
                <h3><i class="fas fa-tools"></i> Herramientas de Diagnóstico</h3>
                <div class="action-buttons" style="margin-top: 15px;">
                    <a href="test-connection.php" class="btn btn-info">
                        <i class="fas fa-stethoscope"></i>
                        Test Conexión
                    </a>
                    <a href="fix.php" class="btn btn-warning">
                        <i class="fas fa-wrench"></i>
                        Reparar Sistema
                    </a>
                    <a href="reset.php" class="btn btn-success">
                        <i class="fas fa-redo"></i>
                        Reset Completo
                    </a>
                    <a href="install.php" class="btn btn-primary">
                        <i class="fas fa-download"></i>
                        Instalación
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>