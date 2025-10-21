<?php
// Script simple para configurar la base de datos
header('Content-Type: text/html; charset=utf-8');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$host = $_POST['host'] ?? 'localhost';
	$username = $_POST['username'] ?? 'root';
	$password = $_POST['password'] ?? '';
    
	try {
		// Conectar a MySQL
		$pdo = new PDO("mysql:host=$host;charset=utf8", $username, $password);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
		// Crear base de datos
		$pdo->exec("CREATE DATABASE IF NOT EXISTS evaluacion_postulantes");
		$pdo->exec("USE evaluacion_postulantes");
        
		// Crear tabla usuarios
		$pdo->exec("CREATE TABLE IF NOT EXISTS usuarios (
			id INT PRIMARY KEY AUTO_INCREMENT,
			username VARCHAR(50) UNIQUE NOT NULL,
			email VARCHAR(100) UNIQUE NOT NULL,
			password VARCHAR(255) NOT NULL,
			nombre_completo VARCHAR(100) NOT NULL,
			telefono VARCHAR(20),
			fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			estado ENUM('activo', 'inactivo') DEFAULT 'activo',
			tipo_usuario ENUM('postulante', 'admin') DEFAULT 'postulante'
		)");
        
		// Create other tables and initial data (omitted here in archive copy)
        
		// Update or create config file
		$config_content = "<?php
// Configuraci\u00f3n de la base de datos
class Database {
	private \$host = '$host';
	private \$db_name = 'evaluacion_postulantes';
	private \$username = '$username';
	private \$password = '$password';
	private \$conn;

	public function getConnection() {
		\$this->conn = null;
        
		try {
			\$this->conn = new PDO(
				\"mysql:host=\" . \$this->host . \";dbname=\" . \$this->db_name . \";charset=utf8\",
				\$this->username,
				\$this->password
			);
			\$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			\$this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		} catch(PDOException \$exception) {
			echo \"Error de conexi\u00f3n: \" . \$exception->getMessage();
			exit();
		}
        
		return \$this->conn;
	}
}

// Configuraci\u00f3n general del sistema
define('BASE_URL', 'http://localhost/evaluacion/');
define('UPLOAD_DIR', 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['pdf', 'jpg', 'jpeg', 'png', 'gif']);

// Configuraci\u00f3n de sesi\u00f3n
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Cambiar a 1 en producci\u00f3n con HTTPS

// Zona horaria
date_default_timezone_set('America/Lima');
";
        
		if (!file_exists('config')) {
			mkdir('config', 0755, true);
		}
		file_put_contents('config/config.php', $config_content);
        
		$success = "\u00a1Base de datos configurada correctamente! Ya puedes usar el sistema.";
        
	} catch (Exception $e) {
		$error = "Error: " . $e->getMessage();
	}
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Configuraci\u00f3n R\u00e1pida - Sistema de Evaluaci\u00f3n</title>
	<style>
		body {
			font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			margin: 0;
			padding: 20px;
			min-height: 100vh;
		}
		.container {
			max-width: 500px;
			margin: 0 auto;
			background: white;
			border-radius: 12px;
			padding: 30px;
			box-shadow: 0 10px 25px rgba(0,0,0,0.1);
		}
		h1 {
			text-align: center;
			color: #333;
			margin-bottom: 10px;
		}
		.subtitle {
			text-align: center;
			color: #666;
			margin-bottom: 30px;
		}
		.form-group {
			margin-bottom: 20px;
		}
	</style>
</head>
<body>
	<div class="container">
		<h1>\ud83d\ude80 Configuraci\u00f3n R\u00e1pida</h1>
		<p class="subtitle">Sistema de Evaluaci\u00f3n de Postulantes</p>

		<?php if ($error): ?>
		<div class="alert alert-error">
			<strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
		</div>
		<?php endif; ?>

		<?php if ($success): ?>
		<div class="alert alert-success">
			<strong>\u00a1\u00c9xito!</strong> <?php echo htmlspecialchars($success); ?>
		</div>
        
		<div class="credentials">
			<h3>Credenciales de Administrador:</h3>
			<ul>
				<li><strong>Usuario:</strong> admin</li>
				<li><strong>Contrase\u00f1a:</strong> password</li>
				<li><strong>Email:</strong> admin@evaluacion.com</li>
			</ul>
			<p><strong>\u26a0\ufe0f Importante:</strong> Cambia la contrase\u00f1a despu\u00e9s del primer login.</p>
		</div>
        
		<div class="links">
			<a href="login.php">\ud83d\udd11 Acceder al Sistema</a>
			<a href="register.php">\ud83d\udc64 Registrar Usuario</a>
		</div>
        
		<?php else: ?>
        
		<div class="info">
			<strong>\u2139\ufe0f Informaci\u00f3n:</strong> Esta herramienta configurar\u00e1 autom\u00e1ticamente la base de datos y crear\u00e1 las tablas necesarias para el sistema.
		</div>
        
		<form method="POST">
			<div class="form-group">
				<label for="host">Servidor MySQL:</label>
				<input type="text" id="host" name="host" value="localhost" required>
			</div>

			<div class="form-group">
				<label for="username">Usuario MySQL:</label>
				<input type="text" id="username" name="username" value="root" required>
			</div>

			<div class="form-group">
				<label for="password">Contrase\u00f1a MySQL:</label>
				<input type="password" id="password" name="password" placeholder="Dejar vac\u00edo si no hay contrase\u00f1a">
			</div>

			<button type="submit">Configurar Sistema</button>
		</form>
        
		<?php endif; ?>
	</div>
</body>
</html>

