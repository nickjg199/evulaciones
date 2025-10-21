<?php
echo "<h1>\u2705 PHP Funciona Correctamente</h1>";
echo "<p>Fecha y hora: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>Versi\u00f3n PHP: " . phpversion() . "</p>";
echo "<p>Directorio actual: " . __DIR__ . "</p>";
echo "<h2>URLs del Sistema:</h2>";
echo "<ul>";
echo "<li><a href='index.php'>P\u00e1gina Principal</a></li>";
echo "<li><a href='login.php'>Login</a></li>";
echo "<li><a href='status.php'>Estado del Sistema</a></li>";
echo "<li><a href='admin/dashboard.php'>Panel Admin</a></li>";
echo "</ul>";

?>

