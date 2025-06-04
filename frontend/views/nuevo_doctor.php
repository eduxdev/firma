<?php
session_start();

// Verificar si el usuario está autenticado y es administrador
if (!isset($_SESSION['doctor_id']) || $_SESSION['user_rol'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Configuración de la base de datos
require_once '../../backend/db/conection.php';

// Variables para mensajes
$error = '';
$success = '';

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener los datos del formulario
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $rol = $_POST['rol'];
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    // Validaciones básicas
    if (empty($nombre) || empty($apellido) || empty($email) || empty($password)) {
        $error = "Todos los campos son obligatorios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "El formato del correo electrónico no es válido.";
    } else {
        // Verificar si el email ya existe
        $check_sql = "SELECT id FROM usuarios WHERE email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error = "El correo electrónico ya está registrado.";
        } else {
            // Insertar el nuevo doctor
            $sql = "INSERT INTO usuarios (nombre, apellido, email, password, rol, activo) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssi", $nombre, $apellido, $email, $password, $rol, $activo);
            
            if ($stmt->execute()) {
                $success = "Doctor añadido correctamente.";
                
                // Limpiar el formulario
                $nombre = $apellido = $email = $password = '';
                $rol = 'doctor';
                $activo = 1;
            } else {
                $error = "Error al registrar el doctor: " . $stmt->error;
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
    <title>Nuevo Doctor - Panel de Administración</title>
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
                    <i class="bi bi-person-badge-fill"></i> 
                    Administrador: <?php echo htmlspecialchars($_SESSION['doctor_nombre'] . ' ' . $_SESSION['doctor_apellido']); ?>
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
                <a href="admin_panel.php" class="btn btn-primary mb-3">
                    <i class="bi bi-arrow-left"></i> Volver al Dashboard
                </a>
                <div class="card">
                    <div class="card-header-primary">
                        <h5 class="mb-0"><i class="bi bi-speedometer2"></i> Panel Admin</h5>
                    </div>
                    <div class="card-body p-0">
                        <nav class="nav flex-column admin-menu p-2">
                            <a class="nav-link" href="admin_panel.php">
                                <i class="bi bi-house-door"></i> Dashboard
                            </a>
                            <a class="nav-link active" href="gestionar_doctores.php">
                                <i class="bi bi-people"></i> Gestionar Doctores
                            </a>
                            <a class="nav-link" href="formularios_pendientes.php">
                                <i class="bi bi-file-earmark-text"></i> Formularios
                            </a>
                            <a class="nav-link" href="configuracion.php">
                                <i class="bi bi-gear"></i> Configuración
                            </a>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Contenido principal -->
            <div class="col-md-9 col-lg-10">
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="admin_panel.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="gestionar_doctores.php">Gestionar Doctores</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Nuevo Doctor</li>
                    </ol>
                </nav>

                <div class="card">
                    <div class="card-header-users">
                        <h5 class="mb-0"><i class="bi bi-person-plus"></i> Añadir Nuevo Doctor</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle"></i> <?php echo $success; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo isset($nombre) ? htmlspecialchars($nombre) : ''; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="apellido" class="form-label">Apellido <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="apellido" name="apellido" value="<?php echo isset($apellido) ? htmlspecialchars($apellido) : ''; ?>" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="password" class="form-label">Contraseña <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="rol" class="form-label">Rol <span class="text-danger">*</span></label>
                                    <select class="form-select" id="rol" name="rol" required>
                                        <option value="doctor" <?php echo (isset($rol) && $rol === 'doctor') ? 'selected' : ''; ?>>Doctor</option>
                                        <option value="admin" <?php echo (isset($rol) && $rol === 'admin') ? 'selected' : ''; ?>>Administrador</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" id="activo" name="activo" <?php echo (!isset($activo) || $activo) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="activo">
                                            Cuenta activa
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="gestionar_doctores.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Guardar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$conn->close();
?> 