<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['doctor_id'])) {
    header('Location: login.php');
    exit();
}

// Configuración de la base de datos
require_once '../../backend/db/conection.php';

// Variables para mensajes
$success_perfil = '';
$error_perfil = '';
$success_password = '';
$error_password = '';

// Obtener información actual del usuario
$id = $_SESSION['doctor_id'];
$sql = "SELECT nombre, apellido, email FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

// Procesar el formulario de edición de perfil
if (isset($_POST['editar_perfil'])) {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $email = trim($_POST['email']);
    
    // Validaciones básicas
    if (empty($nombre) || empty($apellido) || empty($email)) {
        $error_perfil = "Todos los campos son obligatorios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_perfil = "El formato del correo electrónico no es válido.";
    } else {
        // Verificar si el email ya existe (excepto el del usuario actual)
        $check_sql = "SELECT id FROM usuarios WHERE email = ? AND id != ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("si", $email, $id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error_perfil = "El correo electrónico ya está registrado por otro usuario.";
        } else {
            // Actualizar datos del perfil
            $update_sql = "UPDATE usuarios SET nombre = ?, apellido = ?, email = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("sssi", $nombre, $apellido, $email, $id);
            
            if ($update_stmt->execute()) {
                // Actualizar datos de sesión
                $_SESSION['doctor_nombre'] = $nombre;
                $_SESSION['doctor_apellido'] = $apellido;
                
                $success_perfil = "Perfil actualizado correctamente.";
                $usuario['nombre'] = $nombre;
                $usuario['apellido'] = $apellido;
                $usuario['email'] = $email;
            } else {
                $error_perfil = "Error al actualizar el perfil: " . $conn->error;
            }
        }
    }
}

// Procesar el formulario de cambio de contraseña
if (isset($_POST['cambiar_password'])) {
    $password_actual = trim($_POST['password_actual']);
    $password_nueva = trim($_POST['password_nueva']);
    $password_confirmacion = trim($_POST['password_confirmacion']);
    
    // Validaciones básicas
    if (empty($password_actual) || empty($password_nueva) || empty($password_confirmacion)) {
        $error_password = "Todos los campos son obligatorios.";
    } elseif ($password_nueva !== $password_confirmacion) {
        $error_password = "Las contraseñas nuevas no coinciden.";
    } else {
        // Verificar la contraseña actual
        $check_password_sql = "SELECT password FROM usuarios WHERE id = ?";
        $check_password_stmt = $conn->prepare($check_password_sql);
        $check_password_stmt->bind_param("i", $id);
        $check_password_stmt->execute();
        $check_password_result = $check_password_stmt->get_result();
        $user_data = $check_password_result->fetch_assoc();
        
        if ($password_actual != $user_data['password']) {
            $error_password = "La contraseña actual es incorrecta.";
        } else {
            // Actualizar la contraseña
            $update_password_sql = "UPDATE usuarios SET password = ? WHERE id = ?";
            $update_password_stmt = $conn->prepare($update_password_sql);
            $update_password_stmt->bind_param("si", $password_nueva, $id);
            
            if ($update_password_stmt->execute()) {
                $success_password = "Contraseña actualizada correctamente.";
            } else {
                $error_password = "Error al actualizar la contraseña: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        :root {
            --color-primary: #2c6e8f;
            --color-secondary: #48a5c5;
            --color-accent: #e9f5fb;
            --color-pending: #7c97ab;
            --color-approved: #4a8573;
            --color-rejected: #a17a7a;
            --color-light: #f8f9fa;
            --color-dark: #345464;
            --color-users: #5a7d9a;
            --color-admin: #3d4a54;
            --color-active: #4a8573;
        }
        
        body {
            background-color: var(--color-light);
            color: var(--color-dark);
        }
        
        .custom-navbar {
            background-color: #ffffff;
            color: var(--color-dark);
            padding: 15px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .custom-navbar img {
            height: 50px;
        }
        
        .card {
            border-radius: 8px;
            box-shadow: 0 3px 5px rgba(0,0,0,0.08);
            transition: transform 0.2s;
            border: none;
        }
        
        .card:hover {
            transform: translateY(-3px);
        }
        
        .nav-pills .nav-link.active {
            background-color: var(--color-primary);
            color: white;
        }
        
        .admin-menu .nav-link {
            color: var(--color-dark);
            border-radius: 5px;
            margin-bottom: 5px;
            padding: 10px;
        }
        
        .admin-menu .nav-link:hover, 
        .admin-menu .nav-link.active {
            background-color: var(--color-primary);
            color: white;
        }
        
        .admin-menu .nav-link i {
            width: 25px;
            text-align: center;
        }
        
        .card-header-primary {
            background-color: var(--color-primary);
            color: white;
            padding: 15px;
            border-radius: 8px 8px 0 0;
        }
        
        .card-header-users {
            background-color: var(--color-users);
            color: white;
            padding: 15px;
            border-radius: 8px 8px 0 0;
        }
        
        .card-header-security {
            background-color: var(--color-admin);
            color: white;
            padding: 15px;
            border-radius: 8px 8px 0 0;
        }
        
        .btn-primary {
            background-color: var(--color-primary);
            border-color: var(--color-primary);
        }
        
        .btn-primary:hover {
            background-color: #1d5977;
            border-color: #1d5977;
        }
        
        .btn-outline-primary {
            color: var(--color-primary);
            border-color: var(--color-primary);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--color-primary);
            border-color: var(--color-primary);
            color: white;
        }
        
        .modal-header {
            background-color: var(--color-primary);
            color: white;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }
        
        .breadcrumb-item a {
            color: var(--color-primary);
            text-decoration: none;
        }
        
        .breadcrumb-item a:hover {
            text-decoration: underline;
        }
        
        .alert-success {
            background-color: rgba(74, 133, 115, 0.2);
            border-color: var(--color-approved);
            color: var(--color-approved);
        }
        
        .alert-danger {
            background-color: rgba(161, 122, 122, 0.2);
            border-color: var(--color-rejected);
            color: var(--color-rejected);
        }
    </style>
</head>
<body>
    <nav class="custom-navbar">
        <div class="container d-flex justify-content-between align-items-center">
            <img src="/public/assets/img/logo.jpg" alt="Logo">
            <div class="d-flex align-items-center">
                <span class="me-3 text-dark">
                    <?php if ($_SESSION['user_rol'] === 'admin'): ?>
                        <i class="bi bi-person-badge-fill"></i> 
                        Administrador: 
                    <?php else: ?>
                        <i class="bi bi-person-vcard"></i> 
                        Doctor: 
                    <?php endif; ?>
                    <?php echo htmlspecialchars($_SESSION['doctor_nombre'] . ' ' . $_SESSION['doctor_apellido']); ?>
                </span>
                <a href="cerrar_sesion.php" class="btn btn-outline-primary">
                    <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Menú lateral -->
            <div class="col-md-3 col-lg-2">
                <?php if ($_SESSION['user_rol'] === 'admin'): ?>
                <a href="admin_panel.php" class="btn btn-primary mb-3">
                    <i class="bi bi-arrow-left"></i> Volver al Dashboard
                </a>
                <?php else: ?>
                <a href="formularios_pendientes.php" class="btn btn-primary mb-3">
                    <i class="bi bi-arrow-left"></i> Volver a Formularios
                </a>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header-primary">
                        <h5 class="mb-0">
                            <?php if ($_SESSION['user_rol'] === 'admin'): ?>
                                <i class="bi bi-speedometer2"></i> Panel Admin
                            <?php else: ?>
                                <i class="bi bi-menu-button-wide"></i> Menú Doctor
                            <?php endif; ?>
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <nav class="nav flex-column admin-menu p-2">
                            <?php if ($_SESSION['user_rol'] === 'admin'): ?>
                                <a class="nav-link" href="admin_panel.php">
                                    <i class="bi bi-house-door"></i> Dashboard
                                </a>
                                <a class="nav-link" href="gestionar_doctores.php">
                                    <i class="bi bi-people"></i> Gestionar Doctores
                                </a>
                            <?php endif; ?>
                            <a class="nav-link" href="formularios_pendientes.php">
                                <i class="bi bi-file-earmark-text"></i> Formularios
                            </a>
                            <a class="nav-link active" href="configuracion.php">
                                <i class="bi bi-gear"></i> Configuración
                            </a>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Contenido principal -->
            <div class="col-md-9 col-lg-10">
                <?php if ($_SESSION['user_rol'] === 'admin'): ?>
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="admin_panel.php">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Configuración</li>
                    </ol>
                </nav>
                <?php endif; ?>
                
                <h2 class="mb-4"><i class="bi bi-gear"></i> Configuración</h2>

                <!-- Mensajes de éxito o error para actualización de perfil -->
                <?php if (!empty($success_perfil)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill"></i> <?php echo $success_perfil; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($error_perfil)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $error_perfil; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Mensajes de éxito o error para cambio de contraseña -->
                <?php if (!empty($success_password)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill"></i> <?php echo $success_password; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($error_password)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $error_password; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6">
                        <!-- Información del Usuario -->
                        <div class="card mb-4">
                            <div class="card-header-users">
                                <h5 class="mb-0"><i class="bi bi-person-circle"></i> Información del Usuario</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>Nombre:</strong> <?php echo htmlspecialchars($_SESSION['doctor_nombre'] . ' ' . $_SESSION['doctor_apellido']); ?></p>
                                <p><strong>Rol:</strong> <?php echo $_SESSION['user_rol'] === 'admin' ? 'Administrador' : 'Doctor'; ?></p>
                                <p><strong>ID:</strong> <?php echo $_SESSION['doctor_id']; ?></p>
                                <div class="d-grid">
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editarPerfilModal">
                                        <i class="bi bi-pencil-square"></i> Editar Perfil
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <!-- Seguridad -->
                        <div class="card mb-4">
                            <div class="card-header-security">
                                <h5 class="mb-0"><i class="bi bi-shield-lock"></i> Seguridad</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <h5 class="mb-1">Cambiar Contraseña</h5>
                                    <p class="text-muted">Actualiza tu contraseña para mayor seguridad</p>
                                    <div class="d-grid">
                                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#cambiarPasswordModal">
                                            <i class="bi bi-key"></i> Cambiar Contraseña
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar Perfil -->
    <div class="modal fade" id="editarPerfilModal" tabindex="-1" aria-labelledby="editarPerfilModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editarPerfilModalLabel"><i class="bi bi-person-circle"></i> Editar Perfil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="apellido" class="form-label">Apellido</label>
                            <input type="text" class="form-control" id="apellido" name="apellido" value="<?php echo htmlspecialchars($usuario['apellido']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="editar_perfil" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Cambiar Contraseña -->
    <div class="modal fade" id="cambiarPasswordModal" tabindex="-1" aria-labelledby="cambiarPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cambiarPasswordModalLabel"><i class="bi bi-key"></i> Cambiar Contraseña</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="password_actual" class="form-label">Contraseña Actual</label>
                            <input type="password" class="form-control" id="password_actual" name="password_actual" required>
                        </div>
                        <div class="mb-3">
                            <label for="password_nueva" class="form-label">Nueva Contraseña</label>
                            <input type="password" class="form-control" id="password_nueva" name="password_nueva" required>
                        </div>
                        <div class="mb-3">
                            <label for="password_confirmacion" class="form-label">Confirmar Nueva Contraseña</label>
                            <input type="password" class="form-control" id="password_confirmacion" name="password_confirmacion" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="cambiar_password" class="btn btn-primary">Actualizar Contraseña</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$conn->close();
?> 