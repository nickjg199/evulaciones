<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>\ud83d\udd0d Test de Conexi\u00f3n Detallado</h1>";

// Test 1: Verificar PHP y extensiones
echo "<h2>1. Verificando PHP...</h2>";
echo "Versi\u00f3n PHP: " . phpversion() . "<br>";
echo "PDO disponible: " . (extension_loaded('pdo') ? "\u2705 S\u00ed" : "\u274c No") . "<br>";
echo "PDO MySQL disponible: " . (extension_loaded('pdo_mysql') ? "\u2705 S\u00ed" : "\u274c No") . "<br>";

// Test 2: Conexi\u00f3n b\u00e1sica
echo "<h2>2. Test de conexi\u00f3n b\u00e1sica...</h2>";
try {
	$pdo = new PDO("mysql:host=localhost", 'root', '');
	echo "\u2705 Conexi\u00f3n a MySQL b\u00e1sica: OK<br>";
    
	// Test 3: Verificar si la base de datos existe
	echo "<h2>3. Verificando base de datos...</h2>";
	$stmt = $pdo->query("SHOW DATABASES LIKE 'evaluacion_postulantes'");
	if ($stmt->rowCount() > 0) {
		echo "\u2705 Base de datos 'evaluacion_postulantes' existe<br>";
        
		// Test 4: Conectar a la base de datos espec\u00edfica
		echo "<h2>4. Conectando a la base de datos...</h2>";
		$pdo = new PDO("mysql:host=localhost;dbname=evaluacion_postulantes;charset=utf8", 'root', '');
		echo "\u2705 Conexi\u00f3n a evaluacion_postulantes: OK<br>";
        
		// Test 5: Verificar tabla usuarios
		echo "<h2>5. Verificando tabla usuarios...</h2>";
		$stmt = $pdo->query("SHOW TABLES LIKE 'usuarios'");
		if ($stmt->rowCount() > 0) {
			echo "\u2705 Tabla 'usuarios' existe<br>";
            
			// Test 6: Verificar usuario admin
			echo "<h2>6. Verificando usuario admin...</h2>";
			$stmt = $pdo->prepare("SELECT id, username, password, tipo_usuario FROM usuarios WHERE username = 'admin'");
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$admin = $stmt->fetch(PDO::FETCH_ASSOC);
				echo "\u2705 Usuario admin encontrado<br>";
				echo "ID: " . $admin['id'] . "<br>";
				echo "Username: " . $admin['username'] . "<br>";
				echo "Tipo: " . $admin['tipo_usuario'] . "<br>";
				echo "Hash password: " . substr($admin['password'], 0, 20) . "...<br>";
                
				// Test 7: Verificar password
				echo "<h2>7. Test de password...</h2>";
				if (password_verify('password', $admin['password'])) {
					echo "\u2705 Password 'password' es v\u00e1lido<br>";
				} else {
					echo "\u274c Password 'password' NO es v\u00e1lido<br>";
					echo "Regenerando password...<br>";
					$new_hash = password_hash('password', PASSWORD_DEFAULT);
					$update = $pdo->prepare("UPDATE usuarios SET password = ? WHERE username = 'admin'");
					$update->execute([$new_hash]);
					echo "\u2705 Password regenerado<br>";
				}
			} else {
				echo "\u274c Usuario admin NO encontrado<br>";
				echo "Creando usuario admin...<br>";
				$hash = password_hash('password', PASSWORD_DEFAULT);
				$stmt = $pdo->prepare("INSERT INTO usuarios (username, email, password, nombre_completo, tipo_usuario) VALUES (?, ?, ?, ?, ?)");
				$stmt->execute(['admin', 'admin@evaluacion.com', $hash, 'Administrador', 'admin']);
				echo "\u2705 Usuario admin creado<br>";
			}
		} else {
			echo "\u274c Tabla 'usuarios' NO existe<br>";
			echo "Creando tabla usuarios...<br>";
			$sql = "CREATE TABLE usuarios (
				id INT PRIMARY KEY AUTO_INCREMENT,
				username VARCHAR(50) UNIQUE NOT NULL,
				email VARCHAR(100) UNIQUE NOT NULL,
				password VARCHAR(255) NOT NULL,
				nombre_completo VARCHAR(100) NOT NULL,
				telefono VARCHAR(20),
				fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				estado ENUM('activo', 'inactivo') DEFAULT 'activo',
				tipo_usuario ENUM('postulante', 'admin') DEFAULT 'postulante'
			)";
			$pdo->exec($sql);
			echo "\u2705 Tabla usuarios creada<br>";
		}
	} else {
		echo "\u274c Base de datos 'evaluacion_postulantes' NO existe<br>";
		echo "Creando base de datos...<br>";
		$pdo->exec("CREATE DATABASE evaluacion_postulantes");
		echo "\u2705 Base de datos creada<br>";
	}
    
} catch (Exception $e) {
	echo "\u274c Error: " . $e->getMessage() . "<br>";
	echo "<h3>Posibles soluciones:</h3>";
	echo "<ul>";
	echo "<li>Verifica que XAMPP est\u00e9 ejecut\u00e1ndose</li>";
	echo "<li>Verifica que MySQL est\u00e9 activo en el panel de control de XAMPP</li>";
	echo "<li>Verifica que no haya otro proceso usando el puerto 3306</li>";
	echo "</ul>";
}

// Test 8: Simular el login completo
echo "<h2>8. Simulando proceso de login...</h2>";
try {
	require_once 'config/config.php';
	echo "\u2705 Archivo config.php cargado<br>";
    
	$database = new Database();
	echo "\u2705 Clase Database instanciada<br>";
    
	$db = $database->getConnection();
	echo "\u2705 Conexi\u00f3n obtenida<br>";
    
	require_once 'classes/User.php';
	echo "\u2705 Clase User cargada<br>";
    
	$user = new User($db);
	echo "\u2705 Objeto User creado<br>";
    
	$result = $user->login('admin', 'password');
	echo "Resultado del login: " . json_encode($result) . "<br>";
    
	if ($result['success']) {
		echo "\u2705 Login simulado exitoso<br>";
	} else {
		echo "\u274c Login simulado fall\u00f3: " . $result['message'] . "<br>";
	}
    
} catch (Exception $e) {
	echo "\u274c Error en simulaci\u00f3n: " . $e->getMessage() . "<br>";
	echo "Trace: " . $e->getTraceAsString() . "<br>";
}

echo "<h2>\ud83c\udfaf Resumen</h2>";
echo "<p>Si todos los tests anteriores pasaron, el problema puede estar en:</p>";
echo "<ul>";
echo "<li>Cache del navegador</li>";
echo "<li>Errores JavaScript en el frontend</li>";
echo "<li>Problemas con las rutas de archivos</li>";
echo "</ul>";

echo "<h3>Acciones recomendadas:</h3>";
echo "<ol>";
echo "<li><a href='login.php'>Probar login nuevamente</a></li>";
echo "<li><a href='status.php'>Verificar estado del sistema</a></li>";
echo "<li>Limpiar cache del navegador (Ctrl+F5)</li>";
echo "</ol>";

echo "<style>\nbody { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }\nh1, h2, h3 { color: #333; }\nul, ol { margin-left: 20px; }\na { color: #007bff; }\n</style>";

?>

