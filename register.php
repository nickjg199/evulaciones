<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Evaluación - Registro</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card register-card">
            <div class="login-header">
                <i class="fas fa-user-plus"></i>
                <h1>Crear Cuenta</h1>
                <p>Regístrate para acceder al sistema de evaluación</p>
            </div>

            <div id="message-container"></div>

            <form id="registerForm" class="login-form">
                <div class="form-row">
                    <div class="form-group half">
                        <label for="username">
                            <i class="fas fa-user"></i>
                            Usuario
                        </label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group half">
                        <label for="email">
                            <i class="fas fa-envelope"></i>
                            Email
                        </label>
                        <input type="email" id="email" name="email" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="nombre_completo">
                        <i class="fas fa-id-card"></i>
                        Nombre Completo
                    </label>
                    <input type="text" id="nombre_completo" name="nombre_completo" required>
                </div>

                <div class="form-group">
                    <label for="telefono">
                        <i class="fas fa-phone"></i>
                        Teléfono (Opcional)
                    </label>
                    <input type="tel" id="telefono" name="telefono">
                </div>

                <div class="form-row">
                    <div class="form-group half">
                        <label for="password">
                            <i class="fas fa-lock"></i>
                            Contraseña
                        </label>
                        <div class="password-input">
                            <input type="password" id="password" name="password" required minlength="6">
                            <button type="button" class="toggle-password" onclick="togglePassword('password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-group half">
                        <label for="confirm_password">
                            <i class="fas fa-lock"></i>
                            Confirmar Contraseña
                        </label>
                        <div class="password-input">
                            <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                            <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="password-requirements">
                    <p><i class="fas fa-info-circle"></i> La contraseña debe tener al menos 6 caracteres</p>
                </div>

                <button type="submit" class="btn btn-primary btn-full" id="registerBtn">
                    <i class="fas fa-user-plus"></i>
                    Crear Cuenta
                </button>
            </form>

            <div class="login-footer">
                <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a></p>
            </div>
        </div>
    </div>

    <script src="assets/js/register.js"></script>
    <script>
        function togglePassword(inputId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = passwordInput.nextElementSibling.querySelector('i');
            
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