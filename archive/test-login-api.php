<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>\ud83d\udd0d Test Directo del API de Login</h1>";

// Simular llamada POST al API
echo "<h2>Simulando llamada POST...</h2>";

// Configurar variables POST
$_POST['username'] = 'admin';
$_POST['password'] = 'password';
$_SERVER['REQUEST_METHOD'] = 'POST';

echo "Username: " . $_POST['username'] . "<br>";
echo "Password: " . $_POST['password'] . "<br>";

// Capturar output del API
ob_start();

try {
	// Incluir el archivo del API
	include 'api/auth/login.php';
    
} catch (Exception $e) {
	echo "Error al incluir API: " . $e->getMessage() . "<br>";
}

$api_output = ob_get_clean();

echo "<h2>Salida del API:</h2>";
echo "<pre style='background: #f4f4f4; padding: 10px; border-radius: 5px;'>";
echo htmlspecialchars($api_output);
echo "</pre>";

// Intentar decodificar como JSON
echo "<h2>An\u00e1lisis de la respuesta:</h2>";
$decoded = json_decode($api_output, true);

if ($decoded === null) {
	echo "\u274c La respuesta NO es JSON v\u00e1lido<br>";
	echo "Error JSON: " . json_last_error_msg() . "<br>";
} else {
	echo "\u2705 Respuesta JSON v\u00e1lida<br>";
	echo "Contenido decodificado:<br>";
	echo "<pre>" . print_r($decoded, true) . "</pre>";
}

// Test directo de clases
echo "<h2>Test directo de las clases:</h2>";

try {
	require_once 'config/config.php';
	echo "\u2705 Config cargado<br>";
    
	$database = new Database();
	$db = $database->getConnection();
	echo "\u2705 Conexi\u00f3n establecida<br>";
    
	require_once 'classes/User.php';
	$user = new User($db);
	echo "\u2705 Objeto User creado<br>";
    
	$result = $user->login('admin', 'password');
	echo "\u2705 M\u00e9todo login ejecutado<br>";
	echo "Resultado: <pre>" . print_r($result, true) . "</pre>";
    
} catch (Exception $e) {
	echo "\u274c Error en test directo: " . $e->getMessage() . "<br>";
	echo "Stack trace:<br><pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h2>\ud83c\udfaf Diagn\u00f3stico</h2>";
echo "<p>Si ves errores arriba, ese es el problema. Si todo parece bien, el problema est\u00e1 en la comunicaci\u00f3n JavaScript-PHP.</p>";

echo "<h3>Pr\u00f3ximos pasos:</h3>";
echo "<ol>";
echo "<li><a href='login.php' target='_blank'>Probar login real</a> (abre consola del navegador F12)</li>";
echo "<li><a href='api/auth/login.php' target='_blank'>Acceder al API directamente</a></li>";
echo "<li>Revisar logs de error de PHP</li>";
echo "</ol>";

echo "<style>\nbody { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }\nh1, h2, h3 { color: #333; }\npre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }\n</style>";

?>

