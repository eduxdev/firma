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

// Configuración para el header
$titulo = "Nuevo Doctor";
$subtitulo = "Registro de nuevo usuario del sistema";


// Scripts adicionales para el header
$scripts_adicionales = '
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<style>
    [x-cloak] { 
        display: none !important; 
    }
</style>';
?>
<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Doctor - Panel de Administración</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="//unpkg.com/alpinejs" defer></script>
    <?php include 'shared_styles.php'; ?>
</head>
<body class="h-full bg-[#f8f9fa]">
    <?php include 'menu_lateral.php'; ?>
    
    <div class="main-content">
        <?php include 'header.php'; ?>

        <main class="p-4 sm:p-6 lg:p-8">
            <!-- Breadcrumb -->
            <nav class="flex mb-6 overflow-x-auto whitespace-nowrap" aria-label="breadcrumb">
                <ol class="flex items-center space-x-2">
                    <li>
                        <a href="admin_panel.php" class="text-blue-600 hover:text-blue-700">Dashboard</a>
                    </li>
                    <li class="text-gray-500">/</li>
                    <li>
                        <a href="gestionar_doctores.php" class="text-blue-600 hover:text-blue-700">Gestionar Doctores</a>
                    </li>
                    <li class="text-gray-500">/</li>
                    <li class="text-gray-600">Nuevo Doctor</li>
                </ol>
            </nav>

            <!-- Formulario -->
            <div class="bg-white rounded-lg border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-4 sm:p-6 border-b border-gray-100">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-800">Nuevo Doctor</h2>
                            <p class="text-sm text-gray-500 mt-1">Añadir un nuevo usuario al sistema</p>
                        </div>
                    </div>
                </div>

                <div class="p-4 sm:p-6">
                    <?php if (!empty($error)): ?>
                        <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700">
                            <i class="bi bi-exclamation-circle me-2"></i><?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($success)): ?>
                        <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700">
                            <i class="bi bi-check-circle me-2"></i><?php echo $success; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                            <div class="space-y-4">
                                <div>
                                    <label for="nombre" class="block text-sm font-medium text-gray-700 mb-2">
                                        Nombre <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="bi bi-person text-gray-400"></i>
                                        </div>
                                        <input type="text" 
                                               id="nombre" 
                                               name="nombre" 
                                               value="<?php echo isset($nombre) ? htmlspecialchars($nombre) : ''; ?>" 
                                               class="w-full pl-10 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                               required>
                                    </div>
                                </div>

                                <div>
                                    <label for="apellido" class="block text-sm font-medium text-gray-700 mb-2">
                                        Apellido <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="bi bi-person text-gray-400"></i>
                                        </div>
                                        <input type="text" 
                                               id="apellido" 
                                               name="apellido" 
                                               value="<?php echo isset($apellido) ? htmlspecialchars($apellido) : ''; ?>" 
                                               class="w-full pl-10 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                               required>
                                    </div>
                                </div>

                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                        Correo Electrónico <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="bi bi-envelope text-gray-400"></i>
                                        </div>
                                        <input type="email" 
                                               id="email" 
                                               name="email" 
                                               value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" 
                                               class="w-full pl-10 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                               required>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                        Contraseña <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="bi bi-lock text-gray-400"></i>
                                        </div>
                                        <input type="password" 
                                               id="password" 
                                               name="password" 
                                               class="w-full pl-10 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                               required>
                                    </div>
                                </div>

                                <div>
                                    <label for="rol" class="block text-sm font-medium text-gray-700 mb-2">
                                        Rol <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="bi bi-shield-lock text-gray-400"></i>
                                        </div>
                                        <select id="rol" 
                                                name="rol" 
                                                class="w-full pl-10 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 appearance-none bg-white" 
                                                required>
                                            <option value="doctor" <?php echo (isset($rol) && $rol === 'doctor') ? 'selected' : ''; ?>>Doctor</option>
                                            <option value="admin" <?php echo (isset($rol) && $rol === 'admin') ? 'selected' : ''; ?>>Administrador</option>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <i class="bi bi-chevron-down text-gray-400"></i>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center pt-4">
                                    <div class="relative">
                                        <input type="checkbox" 
                                               id="activo" 
                                               name="activo" 
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" 
                                               <?php echo (!isset($activo) || $activo) ? 'checked' : ''; ?>>
                                        <label for="activo" class="ml-2 block text-sm text-gray-700">
                                            Cuenta activa
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 space-x-0 sm:space-x-3 pt-6 border-t border-gray-100">
                            <a href="gestionar_doctores.php" 
                               class="inline-flex items-center justify-center h-9 rounded-md px-4 py-2 text-sm font-medium border border-gray-200 bg-white text-gray-900 shadow-sm hover:bg-gray-100 hover:text-gray-900 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-gray-950 disabled:pointer-events-none disabled:opacity-50">
                                <i class="bi bi-x-circle me-2"></i> Cancelar
                            </a>
                            <button type="submit" 
                                    class="inline-flex items-center justify-center h-9 rounded-md px-4 py-2 text-sm font-medium bg-blue-600 text-white shadow-sm hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-blue-950 disabled:pointer-events-none disabled:opacity-50">
                                <i class="bi bi-save me-2"></i> Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
<?php
$conn->close();
?> 