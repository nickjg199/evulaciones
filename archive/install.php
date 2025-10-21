<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Instalaci\u00f3n - Sistema de Evaluaci\u00f3n</title>
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
	</style>
</head>
<body class="login-page">
	<div class="install-container">
		<div class="text-center mb-4">
			<h1><i class="fas fa-cog"></i> Instalaci\u00f3n del Sistema de Evaluaci\u00f3n</h1>
			<p>Sigue estos pasos para configurar correctamente el sistema</p>
		</div>

		<!-- Paso 1: Verificar requisitos -->
		<div class="step">
			<div class="step-header">
				<div class="step-number">1</div>
				<div>
					<h3>Verificar Requisitos del Sistema</h3>
					<p>Comprobando la configuraci\u00f3n del servidor</p>
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
				<p><strong>Opci\u00f3n 1:</strong> Importar archivo SQL directamente</p>
				<div class="alert alert-info">
					<i class="fas fa-info-circle"></i>
					Importa el archivo <strong>database/database.sql</strong> en phpMyAdmin o tu cliente MySQL preferido.
				</div>
			</div>
		</div>

		<!-- Paso 3: Finalizar -->
		<div class="step">
			<div class="step-header">
				<div class="step-number">3</div>
				<div>
					<h3>Finalizar Instalaci\u00f3n</h3>
					<p>\u00daltimos pasos para completar la configuraci\u00f3n</p>
				</div>
			</div>
			<div class="step-body">
				<div class="alert alert-warning">
					<i class="fas fa-exclamation-triangle"></i>
					<strong>Importante:</strong> Por seguridad, elimina o renombra este archivo (install.php) despu\u00e9s de completar la instalaci\u00f3n.
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
			// Esto es est\u00e1tico en el paquete, el script real est\u00e1 en test-connection.php
			fetch('test-connection.php')
				.then(response => response.text())
				.then(text => console.log(text))
				.catch(err => console.error(err));
		}
	</script>
</body>
</html>

