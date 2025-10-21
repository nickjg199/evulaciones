<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Evaluación - Inicio de Sesión</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card fade-in">
            <div class="login-header">
                <i class="fas fa-clipboard-check"></i>
                <h1>Sistema de Evaluación</h1>
                <p>Accede a tu cuenta para continuar</p>
            </div>

            <div id="message-container"></div>

            <form id="loginForm" class="login-form">
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user"></i>
                        Usuario o Email
                    </label>
                    <input type="text" id="username" name="username" class="form-control" required autocomplete="username" placeholder="Ingresa tu usuario o email">
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        Contraseña
                    </label>
                    <div class="password-input">
                        <input type="password" id="password" name="password" class="form-control" required autocomplete="current-password" placeholder="Ingresa tu contraseña">
                        <button type="button" class="toggle-password" onclick="togglePassword()">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-full" id="loginBtn">
                    <i class="fas fa-sign-in-alt"></i>
                    Iniciar Sesión
                </button>
            </form>

            <div class="login-footer">
                <p>¿No tienes cuenta? <a href="register.php">Regístrate aquí</a></p>
                <p><a href="index.php">← Volver al inicio</a></p>
            </div>
        </div>
    </div>

    <script src="assets/js/login.js"></script>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.toggle-password i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Mostrar credenciales de prueba
        setTimeout(() => {
            const messageContainer = document.getElementById('message-container');
            messageContainer.innerHTML = `
                <div class="message-info">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <strong>Credenciales de prueba:</strong><br>
                        Usuario: <strong>admin</strong><br>
                        Contraseña: <strong>password</strong>
                    </div>
                </div>
            `;
        }, 1000);
    </script>
</body>
</html>
            </div>
        </div>
    </div>

    <script src="assets/js/login.js"></script>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.toggle-password i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>