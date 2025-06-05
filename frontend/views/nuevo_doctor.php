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
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#2c6e8f',
                        secondary: '#48a5c5',
                        accent: '#e9f5fb',
                        pending: '#7c97ab',
                        approved: '#4a8573',
                        rejected: '#a17a7a',
                        light: '#f8f9fa',
                        dark: '#345464',
                        users: '#5a7d9a',
                        admin: '#3d4a54',
                        active: '#4a8573',
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
            color: #345464;
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
<body class="bg-light">
    <nav class="bg-white shadow-md py-4">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <img src="/public/assets/img/logo.jpg" alt="Logo" class="h-12">
            <div class="flex items-center">
                <span class="mr-4 text-dark">
                    <i class="bi bi-person-badge-fill"></i> 
                    Administrador: <?php echo htmlspecialchars($_SESSION['doctor_nombre'] . ' ' . $_SESSION['doctor_apellido']); ?>
                </span>
                <a href="cerrar_sesion.php" class="px-4 py-2 border border-primary text-primary hover:bg-primary hover:text-white rounded">
                    <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 mt-6">
        <div class="flex flex-wrap">
            <!-- Menú lateral -->
            <div class="w-full md:w-1/4 lg:w-1/6 pr-4 sidebar-container">
                <a href="admin_panel.php" class="inline-block px-4 py-2 mb-4 bg-primary text-white rounded hover:bg-opacity-90">
                    <i class="bi bi-arrow-left"></i> Volver al Dashboard
                </a>
                <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow mb-6">
                    <div class="bg-primary text-white p-4 rounded-t-lg">
                        <h5 class="m-0 font-medium"><i class="bi bi-speedometer2"></i> Panel Admin</h5>
                    </div>
                    <div class="p-0">
                        <nav class="flex flex-col p-2">
                            <a class="py-2 px-3 rounded mb-1 flex items-center text-dark hover:bg-primary hover:text-white" href="admin_panel.php">
                                <i class="bi bi-house-door w-6 text-center"></i> Dashboard
                            </a>
                            <a class="py-2 px-3 rounded mb-1 flex items-center text-dark hover:bg-primary hover:text-white" href="formularios_pendientes.php">
                                <i class="bi bi-file-earmark-text w-6 text-center"></i> Formularios
                            </a>
                            <a class="py-2 px-3 rounded mb-1 flex items-center text-dark hover:bg-primary hover:text-white bg-primary text-white" href="gestionar_doctores.php">
                                <i class="bi bi-people w-6 text-center"></i> Gestionar Doctores
                            </a>
                            <a class="py-2 px-3 rounded mb-1 flex items-center text-dark hover:bg-primary hover:text-white" href="estadisticas.php">
                                <i class="bi bi-bar-chart w-6 text-center"></i> Estadísticas
                            </a>
                            <a class="py-2 px-3 rounded mb-1 flex items-center text-dark hover:bg-primary hover:text-white" href="configuracion.php">
                                <i class="bi bi-gear w-6 text-center"></i> Configuración
                            </a>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Contenido principal -->
            <div class="w-full md:w-3/4 lg:w-5/6">
                <nav class="flex mb-6" aria-label="breadcrumb">
                    <ol class="flex">
                        <li class="mr-2">
                            <a href="admin_panel.php" class="text-primary hover:underline">Dashboard</a>
                            <span class="mx-1 text-gray-500">/</span>
                        </li>
                        <li class="mr-2">
                            <a href="gestionar_doctores.php" class="text-primary hover:underline">Gestionar Doctores</a>
                            <span class="mx-1 text-gray-500">/</span>
                        </li>
                        <li class="text-gray-600">Nuevo Doctor</li>
                    </ol>
                </nav>

                <div class="bg-white rounded-lg shadow-sm">
                    <div class="bg-users text-white p-4 rounded-t-lg">
                        <h5 class="m-0 font-medium"><i class="bi bi-person-plus"></i> Añadir Nuevo Doctor</h5>
                    </div>
                    <div class="p-6">
                        <?php if (!empty($error)): ?>
                            <div class="mb-4 p-4 bg-red-100 border-l-4 border-rejected text-rejected rounded">
                                <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($success)): ?>
                            <div class="mb-4 p-4 bg-green-100 border-l-4 border-approved text-approved rounded">
                                <i class="bi bi-check-circle"></i> <?php echo $success; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="flex flex-wrap -mx-3 mb-4">
                                <div class="w-full md:w-1/2 px-3 mb-4 md:mb-0">
                                    <label for="nombre" class="block text-gray-700 mb-2">Nombre <span class="text-red-500">*</span></label>
                                    <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-primary" 
                                           id="nombre" name="nombre" value="<?php echo isset($nombre) ? htmlspecialchars($nombre) : ''; ?>" required>
                                </div>
                                <div class="w-full md:w-1/2 px-3">
                                    <label for="apellido" class="block text-gray-700 mb-2">Apellido <span class="text-red-500">*</span></label>
                                    <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-primary" 
                                           id="apellido" name="apellido" value="<?php echo isset($apellido) ? htmlspecialchars($apellido) : ''; ?>" required>
                                </div>
                            </div>

                            <div class="flex flex-wrap -mx-3 mb-4">
                                <div class="w-full md:w-1/2 px-3 mb-4 md:mb-0">
                                    <label for="email" class="block text-gray-700 mb-2">Correo Electrónico <span class="text-red-500">*</span></label>
                                    <input type="email" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-primary" 
                                           id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                                </div>
                                <div class="w-full md:w-1/2 px-3">
                                    <label for="password" class="block text-gray-700 mb-2">Contraseña <span class="text-red-500">*</span></label>
                                    <input type="password" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-primary" 
                                           id="password" name="password" required>
                                </div>
                            </div>

                            <div class="flex flex-wrap -mx-3 mb-4">
                                <div class="w-full md:w-1/2 px-3 mb-4 md:mb-0">
                                    <label for="rol" class="block text-gray-700 mb-2">Rol <span class="text-red-500">*</span></label>
                                    <select class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-primary" 
                                            id="rol" name="rol" required>
                                        <option value="doctor" <?php echo (isset($rol) && $rol === 'doctor') ? 'selected' : ''; ?>>Doctor</option>
                                        <option value="admin" <?php echo (isset($rol) && $rol === 'admin') ? 'selected' : ''; ?>>Administrador</option>
                                    </select>
                                </div>
                                <div class="w-full md:w-1/2 px-3">
                                    <div class="flex items-center mt-8">
                                        <input class="mr-2 h-5 w-5" type="checkbox" id="activo" name="activo" 
                                               <?php echo (!isset($activo) || $activo) ? 'checked' : ''; ?>>
                                        <label class="text-gray-700" for="activo">
                                            Cuenta activa
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <a href="gestionar_doctores.php" class="px-4 py-2 bg-gray-500 text-white rounded mr-2 hover:bg-gray-600">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </a>
                                <button type="submit" class="px-4 py-2 bg-primary text-white rounded hover:bg-opacity-90">
                                    <i class="bi bi-save"></i> Guardar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alpine.js para interactividad, alternativa ligera a jQuery/Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>
</html>
<?php
$conn->close();
?> 