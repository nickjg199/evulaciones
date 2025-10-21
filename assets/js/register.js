document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('registerForm');
    const registerBtn = document.getElementById('registerBtn');
    const messageContainer = document.getElementById('message-container');
    
    // Elementos del formulario
    const usernameInput = document.getElementById('username');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const nombreCompletoInput = document.getElementById('nombre_completo');

    registerForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validar formulario
        if (!validateForm()) {
            return;
        }
        
        const formData = new FormData(registerForm);
        
        // Deshabilitar botón y mostrar loading
        registerBtn.disabled = true;
        registerBtn.innerHTML = '<i class="loading"></i> Creando cuenta...';
        
        // Limpiar mensajes previos
        messageContainer.innerHTML = '';
        
        fetch('api/auth/register.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage('¡Cuenta creada exitosamente! Redirigiendo al login...', 'success');
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 2000);
            } else {
                showMessage(data.message || 'Error al crear la cuenta', 'error');
                // Rehabilitar botón
                registerBtn.disabled = false;
                registerBtn.innerHTML = '<i class="fas fa-user-plus"></i> Crear Cuenta';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Error de conexión. Por favor, intenta nuevamente.', 'error');
            // Rehabilitar botón
            registerBtn.disabled = false;
            registerBtn.innerHTML = '<i class="fas fa-user-plus"></i> Crear Cuenta';
        });
    });

    function validateForm() {
        let isValid = true;
        const errors = [];

        // Validar username
        if (usernameInput.value.trim().length < 3) {
            errors.push('El nombre de usuario debe tener al menos 3 caracteres');
            usernameInput.style.borderColor = '#ef4444';
            isValid = false;
        } else {
            usernameInput.style.borderColor = '#10b981';
        }

        // Validar email
        if (!validateEmail(emailInput.value)) {
            errors.push('El email no es válido');
            emailInput.style.borderColor = '#ef4444';
            isValid = false;
        } else {
            emailInput.style.borderColor = '#10b981';
        }

        // Validar nombre completo
        if (nombreCompletoInput.value.trim().length < 2) {
            errors.push('El nombre completo es requerido');
            nombreCompletoInput.style.borderColor = '#ef4444';
            isValid = false;
        } else {
            nombreCompletoInput.style.borderColor = '#10b981';
        }

        // Validar contraseña
        if (passwordInput.value.length < 6) {
            errors.push('La contraseña debe tener al menos 6 caracteres');
            passwordInput.style.borderColor = '#ef4444';
            isValid = false;
        } else {
            passwordInput.style.borderColor = '#10b981';
        }

        // Validar confirmación de contraseña
        if (passwordInput.value !== confirmPasswordInput.value) {
            errors.push('Las contraseñas no coinciden');
            confirmPasswordInput.style.borderColor = '#ef4444';
            isValid = false;
        } else if (confirmPasswordInput.value.length >= 6) {
            confirmPasswordInput.style.borderColor = '#10b981';
        }

        if (!isValid) {
            showMessage(errors.join('<br>'), 'error');
        }

        return isValid;
    }

    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    function showMessage(message, type) {
        const alertClass = `message-${type}`;
        const iconClass = type === 'success' ? 'fa-check-circle' : 
                         type === 'error' ? 'fa-exclamation-circle' : 
                         type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle';
        
        messageContainer.innerHTML = `
            <div class="message ${alertClass}">
                <i class="fas ${iconClass}"></i>
                ${message}
            </div>
        `;
    }

    // Validación en tiempo real
    usernameInput.addEventListener('input', function() {
        if (this.value.trim().length < 3) {
            this.style.borderColor = '#ef4444';
        } else {
            this.style.borderColor = '#10b981';
        }
    });

    emailInput.addEventListener('input', function() {
        if (!validateEmail(this.value)) {
            this.style.borderColor = '#ef4444';
        } else {
            this.style.borderColor = '#10b981';
        }
    });

    nombreCompletoInput.addEventListener('input', function() {
        if (this.value.trim().length < 2) {
            this.style.borderColor = '#ef4444';
        } else {
            this.style.borderColor = '#10b981';
        }
    });

    passwordInput.addEventListener('input', function() {
        if (this.value.length < 6) {
            this.style.borderColor = '#ef4444';
        } else {
            this.style.borderColor = '#10b981';
        }
        
        // Revalidar confirmación si ya tiene contenido
        if (confirmPasswordInput.value.length > 0) {
            if (this.value !== confirmPasswordInput.value) {
                confirmPasswordInput.style.borderColor = '#ef4444';
            } else {
                confirmPasswordInput.style.borderColor = '#10b981';
            }
        }
    });

    confirmPasswordInput.addEventListener('input', function() {
        if (this.value !== passwordInput.value) {
            this.style.borderColor = '#ef4444';
        } else if (this.value.length >= 6) {
            this.style.borderColor = '#10b981';
        }
    });

    // Verificar disponibilidad de username (debounced)
    let usernameTimeout;
    usernameInput.addEventListener('input', function() {
        clearTimeout(usernameTimeout);
        const username = this.value.trim();
        
        if (username.length >= 3) {
            usernameTimeout = setTimeout(() => {
                checkUsernameAvailability(username);
            }, 500);
        }
    });

    function checkUsernameAvailability(username) {
        fetch('api/auth/check-username.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ username: username })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.available) {
                usernameInput.style.borderColor = '#ef4444';
                showMessage('El nombre de usuario ya está en uso', 'warning');
            } else {
                usernameInput.style.borderColor = '#10b981';
            }
        })
        .catch(error => {
            console.error('Error checking username:', error);
        });
    }
});