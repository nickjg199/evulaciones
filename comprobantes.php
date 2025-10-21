<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config/config.php';
require_once 'classes/User.php';
require_once 'classes/ComprobantePago.php';

requireAuth();

$database = new Database();
$db = $database->getConnection();

$user = new User($db);
$comprobante = new ComprobantePago($db);

$user_info = $user->getUserById($_SESSION['user_id']);
$user_comprobantes = $comprobante->getUserComprobantes($_SESSION['user_id']);
$has_approved_comprobante = $comprobante->hasApprovedComprobante($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobantes de Pago - Sistema de Evaluación</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="main-layout">
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="dashboard.php" class="navbar-brand">
                <i class="fas fa-clipboard-check"></i>
                Sistema de Evaluación
            </a>
            <ul class="navbar-nav">
                <li><a href="dashboard.php"><i class="fas fa-home"></i> Inicio</a></li>
                <li><a href="comprobantes.php" class="active"><i class="fas fa-file-upload"></i> Comprobantes</a></li>
                <!-- <li><a href="perfil.php"><i class="fas fa-user"></i> Perfil</a></li> -->
                <li class="user-menu">
                    <!-- <button class="user-menu-toggle" onclick="toggleUserMenu()">
                        <i class="fas fa-user-circle"></i>
                        <?php echo htmlspecialchars($user_info['nombre_completo']); ?>
                        <i class="fas fa-chevron-down"></i>
                    </button> -->
                    <div class="user-menu-dropdown" id="userMenuDropdown">
                        <a href="perfil.php"><i class="fas fa-user"></i> Mi Perfil</a>
                        <a href="configuracion.php"><i class="fas fa-cog"></i> Configuración</a>
                        <a href="#" onclick="logout()"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
                    </div>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <!-- Título -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-file-invoice-dollar"></i> Comprobantes de Pago</h3>
            </div>
            <div class="card-body">
                <p>Aquí puedes subir y gestionar tus comprobantes de pago. Es necesario que al menos uno sea aprobado para acceder a las evaluaciones.</p>
                
                <?php if ($has_approved_comprobante): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <strong>¡Excelente!</strong> Tienes un comprobante aprobado. Ya puedes acceder a las evaluaciones.
                </div>
                <?php endif; ?>
                


            </div>
        </div>

        <div class="row">
            <!-- Subir nuevo comprobante -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-upload"></i> Subir Comprobante</h4>
                    </div>
                    <div class="card-body">
                        <div id="message-container"></div>
                        
                        <form id="uploadForm" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="comprobante">Seleccionar archivo:</label>
                                <div class="file-upload" id="fileUploadArea">
                                    <i class="fas fa-cloud-upload-alt" style="font-size: 3rem; color: var(--medium-gray); margin-bottom: 1rem;"></i>
                                    <p>Arrastra tu archivo aquí o <strong>haz clic para seleccionar</strong></p>
                                    <p class="text-secondary">Formatos permitidos: PDF, JPG, JPEG, PNG, GIF (máximo 5MB)</p>
                                    <input type="file" id="comprobante" name="comprobante" accept=".pdf,.jpg,.jpeg,.png,.gif" style="display: none;">
                                </div>
                                <div id="selectedFile" style="display: none;">
                                    <div class="alert alert-info">
                                        <i class="fas fa-file"></i>
                                        <span id="fileName"></span>
                                        <button type="button" onclick="clearFile()" style="float: right; background: none; border: none; color: var(--danger-color);">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary" id="uploadBtn">
                                <i class="fas fa-upload"></i> Subir Comprobante
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Información sobre comprobantes -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-info-circle"></i> Información Importante</h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h5><i class="fas fa-exclamation-circle"></i> Requisitos del comprobante:</h5>
                            <ul>
                                <li>El archivo debe ser claro y legible</li>
                                <li>Debe mostrar el monto pagado y la fecha</li>
                                <li>Formatos aceptados: PDF, JPG, JPEG, PNG, GIF</li>
                                <li>Tamaño máximo: 5MB</li>
                            </ul>
                        </div>

                        <div class="alert alert-warning">
                            <h5><i class="fas fa-clock"></i> Proceso de revisión:</h5>
                            <ul>
                                <li>Los comprobantes son revisados manualmente</li>
                                <li>El tiempo de revisión es de 24-48 horas</li>
                                <li>Recibirás notificación del resultado</li>
                                <li>Si es rechazado, podrás subir uno nuevo</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>

        <!-- Lista de comprobantes -->
        <div class="card">
            <div class="card-header">
                <h4><i class="fas fa-list"></i> Mis Comprobantes</h4>
            </div>
            <div class="card-body">
                <?php if (empty($user_comprobantes)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        No has subido ningún comprobante de pago aún.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Archivo</th>
                                    <th>Fecha de Subida</th>
                                    <th>Estado</th>
                                    <th>Comentarios</th>
                                    <th>Fecha de Revisión</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($user_comprobantes as $comp): ?>
                                <tr>
                                    <td>
                                        <i class="fas fa-file"></i>
                                        <?php echo htmlspecialchars($comp['nombre_archivo']); ?>
                                        <?php if (!empty($comp['ruta_archivo'])): 
                                            $file_url = (strpos($comp['ruta_archivo'], 'http') === 0 || strpos($comp['ruta_archivo'], '/') === 0)
                                                         ? $comp['ruta_archivo']
                                                         : BASE_URL . ltrim($comp['ruta_archivo'], '/\\');
                                        ?>
                                            <div class="file-actions" style="margin-top:6px;">
                                                <a href="<?php echo htmlspecialchars($file_url); ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye"></i> Ver / Descargar
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($comp['fecha_subida'])); ?></td>
                                    <td>
                                        <?php 
                                        $badge_class = $comp['estado'] === 'aprobado' ? 'badge-success' : 
                                                      ($comp['estado'] === 'rechazado' ? 'badge-danger' : 'badge-warning');
                                        $icon_class = $comp['estado'] === 'aprobado' ? 'fa-check' : 
                                                     ($comp['estado'] === 'rechazado' ? 'fa-times' : 'fa-clock');
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?>">
                                            <i class="fas <?php echo $icon_class; ?>"></i>
                                            <?php echo ucfirst($comp['estado']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($comp['comentarios'])): ?>
                                            <button class="btn btn-outline btn-small" onclick="showComments('<?php echo htmlspecialchars($comp['comentarios']); ?>')">
                                                <i class="fas fa-comment"></i> Ver
                                            </button>
                                        <?php else: ?>
                                            <span class="text-secondary">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo $comp['fecha_revision'] ? date('d/m/Y H:i', strtotime($comp['fecha_revision'])) : '-'; ?>
                                    </td>
                                    <td>
                                        <?php if ($comp['estado'] === 'pendiente'): ?>
                                            <button onclick="deleteComprobante(<?php echo $comp['id']; ?>)" class="btn btn-danger btn-small">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </button>
                                        <?php elseif ($comp['estado'] === 'rechazado'): ?>
                                            <button onclick="deleteComprobante(<?php echo $comp['id']; ?>)" class="btn btn-danger btn-small">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </button>
                                        <?php else: ?>
                                            <span class="badge badge-success">
                                                <i class="fas fa-lock"></i> Aprobado
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal para comentarios -->
    <div id="commentsModal" class="modal-overlay" style="display: none;">
        <div class="modal">
            <div class="modal-header">
                <h3>Comentarios del Administrador</h3>
                <button class="modal-close" onclick="closeCommentsModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div id="commentsContent"></div>
            </div>
            <div class="modal-footer">
                <button onclick="closeCommentsModal()" class="btn btn-secondary">Cerrar</button>
            </div>
        </div>
    </div>

    <script>
        function toggleUserMenu(event) {
            event.stopPropagation();
            const dropdown = document.getElementById('userMenuDropdown');
            dropdown.classList.toggle('show');
        }

        // Cerrar el dropdown si se hace clic fuera
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('userMenuDropdown');
            if (!dropdown) return;
            const userMenu = document.querySelector('.user-menu');
            if (!userMenu.contains(event.target)) {
                dropdown.classList.remove('show');
            }
        });

        function logout() {
            if (confirm('¿Estás seguro de que quieres cerrar sesión?')) {
                fetch('api/auth/logout.php', {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    window.location.href = 'login.php';
                })
                .catch(error => {
                    console.error('Error:', error);
                    window.location.href = 'login.php';
                });
            }
        }

        // Manejo de archivos
        const fileUploadArea = document.getElementById('fileUploadArea');
        const fileInput = document.getElementById('comprobante');
        const selectedFileDiv = document.getElementById('selectedFile');
        const fileNameSpan = document.getElementById('fileName');
        const uploadForm = document.getElementById('uploadForm');
        const uploadBtn = document.getElementById('uploadBtn');
        const messageContainer = document.getElementById('message-container');

        fileUploadArea.addEventListener('click', () => {
            fileInput.click();
        });

        fileUploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            fileUploadArea.classList.add('dragover');
        });

        fileUploadArea.addEventListener('dragleave', () => {
            fileUploadArea.classList.remove('dragover');
        });

        fileUploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            fileUploadArea.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                showSelectedFile(files[0]);
            }
        });

        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                showSelectedFile(e.target.files[0]);
            }
        });

        function showSelectedFile(file) {
            fileNameSpan.textContent = file.name;
            fileUploadArea.style.display = 'none';
            selectedFileDiv.style.display = 'block';
        }

        function clearFile() {
            fileInput.value = '';
            fileUploadArea.style.display = 'block';
            selectedFileDiv.style.display = 'none';
        }

        uploadForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!fileInput.files.length) {
                showMessage('Por favor selecciona un archivo', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('comprobante', fileInput.files[0]);

            uploadBtn.disabled = true;
            uploadBtn.innerHTML = '<i class="loading"></i> Subiendo...';

            fetch('api/comprobantes/upload.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    showMessage(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('Error al subir el archivo', 'error');
            })
            .finally(() => {
                uploadBtn.disabled = false;
                uploadBtn.innerHTML = '<i class="fas fa-upload"></i> Subir Comprobante';
            });
        });

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

        function showComments(comments) {
            document.getElementById('commentsContent').innerHTML = `<p>${comments}</p>`;
            document.getElementById('commentsModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeCommentsModal() {
            document.getElementById('commentsModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function deleteComprobante(comprobanteId) {
            if (confirm('¿Estás seguro de que quieres eliminar este comprobante?')) {
                fetch('api/comprobantes/delete.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ comprobante_id: comprobanteId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage(data.message, 'success');
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        showMessage(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('Error al eliminar comprobante', 'error');
                });
            }
        }
    </script>
</body>
</html>