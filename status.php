<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

function checkSystemStatus() {
    $status = [
        'php_version' => phpversion(),
        'extensions' => [],
        'database' => false,
        'config_exists' => false,
        'directories' => [],
        'admin_user' => false
    ];
    
    // Verificar extensiones PHP
    $required_extensions = ['pdo', 'pdo_mysql', 'mysqli', 'json', 'session'];
    foreach ($required_extensions as $ext) {
        $status['extensions'][$ext] = extension_loaded($ext);
    }
    
    // Verificar archivo de configuración
    $status['config_exists'] = file_exists('config/config.php');
    
    // Verificar directorios
    $required_dirs = ['uploads', 'uploads/comprobantes', 'uploads/temp', 'config', 'assets', 'classes', 'api'];
    foreach ($required_dirs as $dir) {
        $status['directories'][$dir] = is_dir($dir);
    }
    
    // Verificar conexión a base de datos
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=evaluacion_postulantes;charset=utf8", 'root', '');
        $status['database'] = true;
        
        // Verificar usuario admin
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE username = 'admin'");
        $stmt->execute();
        $status['admin_user'] = $stmt->fetchColumn() > 0;
    } catch (Exception $e) {
        $status['database'] = false;
        $status['database_error'] = $e->getMessage();
    }
    
    return $status;
}

$status = checkSystemStatus();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado del Sistema - Evaluación de Postulantes</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .status-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        .status-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .status-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .status-item:last-child {
            border-bottom: none;
        }
        .status-ok {
            color: #28a745;
            font-weight: bold;
        }
        .status-error {
            color: #dc3545;
            font-weight: bold;
        }
        .quick-actions {
            display: flex;
            gap: 10px;
            margin: 20px 0;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        .btn-primary {
            background: #007bff;
            color: white;
        }
        .btn-success {
            background: #28a745;
            color: white;
        }
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
    </style>
</head>
<body>
    <div class="status-container">
        <h1>🔍 Estado del Sistema de Evaluación</h1>
        
        <div class="quick-actions">
            <a href="login.php" class="btn btn-primary">🔐 Ir al Login</a>
            <a href="fix.php" class="btn btn-warning">🔧 Reparar Sistema</a>
            <a href="install.php" class="btn btn-success">⚙️ Instalación</a>
            <a href="admin/dashboard.php" class="btn btn-primary">👨‍💼 Panel Admin</a>
        </div>
        
        <!-- Estado PHP -->
        <div class="status-card">
            <h2>🐘 Estado de PHP</h2>
            <div class="status-item">
                <span>Versión de PHP</span>
                <span class="status-ok"><?php echo $status['php_version']; ?></span>
            </div>
            <?php foreach ($status['extensions'] as $ext => $loaded): ?>
            <div class="status-item">
                <span>Extensión <?php echo $ext; ?></span>
                <span class="<?php echo $loaded ? 'status-ok' : 'status-error'; ?>">
                    <?php echo $loaded ? '✅ Disponible' : '❌ No disponible'; ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Estado Base de Datos -->
        <div class="status-card">
            <h2>🗄️ Estado de Base de Datos</h2>
            <div class="status-item">
                <span>Conexión MySQL</span>
                <span class="<?php echo $status['database'] ? 'status-ok' : 'status-error'; ?>">
                    <?php echo $status['database'] ? '✅ Conectado' : '❌ Error de conexión'; ?>
                </span>
            </div>
            <?php if (!$status['database'] && isset($status['database_error'])): ?>
            <div class="status-item">
                <span>Error</span>
                <span class="status-error"><?php echo $status['database_error']; ?></span>
            </div>
            <?php endif; ?>
            <div class="status-item">
                <span>Usuario Administrador</span>
                <span class="<?php echo $status['admin_user'] ? 'status-ok' : 'status-error'; ?>">
                    <?php echo $status['admin_user'] ? '✅ Existe' : '❌ No encontrado'; ?>
                </span>
            </div>
        </div>
        
        <!-- Estado Archivos -->
        <div class="status-card">
            <h2>📁 Estado de Archivos y Directorios</h2>
            <div class="status-item">
                <span>Archivo de configuración</span>
                <span class="<?php echo $status['config_exists'] ? 'status-ok' : 'status-error'; ?>">
                    <?php echo $status['config_exists'] ? '✅ Existe' : '❌ No encontrado'; ?>
                </span>
            </div>
            <?php foreach ($status['directories'] as $dir => $exists): ?>
            <div class="status-item">
                <span>Directorio <?php echo $dir; ?></span>
                <span class="<?php echo $exists ? 'status-ok' : 'status-error'; ?>">
                    <?php echo $exists ? '✅ Existe' : '❌ No encontrado'; ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Credenciales -->
        <div class="status-card">
            <h2>🔐 Credenciales del Sistema</h2>
            <div class="status-item">
                <span>Usuario Administrador</span>
                <span><strong>admin</strong></span>
            </div>
            <div class="status-item">
                <span>Contraseña Administrador</span>
                <span><strong>password</strong></span>
            </div>
            <div class="status-item">
                <span>Base de Datos</span>
                <span><strong>evaluacion_postulantes</strong></span>
            </div>
        </div>
        
        <!-- Enlaces Útiles -->
        <div class="status-card">
            <h2>🔗 Enlaces Útiles</h2>
            <div class="status-item">
                <span>Login del Sistema</span>
                <a href="login.php" class="status-ok">Acceder</a>
            </div>
            <div class="status-item">
                <span>Panel de Administración</span>
                <a href="admin/dashboard.php" class="status-ok">Acceder</a>
            </div>
            <div class="status-item">
                <span>phpMyAdmin</span>
                <a href="http://localhost/phpmyadmin" target="_blank" class="status-ok">Abrir</a>
            </div>
            <div class="status-item">
                <span>XAMPP Control Panel</span>
                <span>C:\xampp\xampp-control.exe</span>
            </div>
        </div>
        
        <div class="status-card">
            <h2>📋 Resumen del Estado</h2>
            <p>
                <?php
                $all_good = $status['database'] && $status['config_exists'] && $status['admin_user'] && 
                           array_sum($status['extensions']) == count($status['extensions']);
                
                if ($all_good) {
                    echo "🎉 <strong style='color: #28a745;'>Sistema funcionando correctamente</strong>";
                    echo "<br>El sistema está listo para usar. Puedes hacer login con las credenciales de administrador.";
                } else {
                    echo "⚠️ <strong style='color: #dc3545;'>Se encontraron problemas</strong>";
                    echo "<br>Ejecuta el script de reparación automática para solucionar los problemas.";
                }
                ?>
            </p>
        </div>
    </div>
</body>
</html>