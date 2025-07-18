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
                session_start();
                $_SESSION['password_changed'] = true;
                session_destroy();
                header('Location: login.php?password_changed=true');
                exit();
            } else {
                $error_password = "Error al actualizar la contraseña: " . $conn->error;
            }
        }
    }
}

// Configuración para el header
$titulo = "Configuración";
$subtitulo = "Ajustes de cuenta y preferencias";

// Scripts adicionales para el header
$scripts_adicionales = '
<script src="//unpkg.com/alpinejs" defer></script>
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
    <title>Configuración - Panel de Administración</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <?php include 'shared_styles.php'; ?>
</head>
<body class="h-full bg-[#f8f9fa]" x-data="{ showModal: false }">
    <?php include 'menu_lateral.php'; ?>
    
    <div class="main-content">
        <?php include 'header.php'; ?>

        <main class="p-4 sm:p-6 lg:p-8">
            <?php if ($_SESSION['user_rol'] === 'admin'): ?>
            <nav class="flex mb-6" aria-label="breadcrumb">
                <ol class="flex items-center space-x-2">
                    <li>
                        <a href="admin_panel.php" class="text-blue-600 hover:text-blue-700">Dashboard</a>
                    </li>
                    <li class="text-gray-500">/</li>
                    <li class="text-gray-600">Configuración</li>
                </ol>
            </nav>
            <?php endif; ?>

            <!-- Encabezado de la página -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Configuración</h2>
                    <p class="text-sm text-gray-500 mt-1">Ajustes de cuenta y preferencias</p>
                </div>
            </div>

            <!-- Mensajes de éxito o error para actualización de perfil -->
            <?php if (!empty($success_perfil)): ?>
                <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded flex items-center">
                    <i class="bi bi-check-circle me-2"></i><?php echo $success_perfil; ?>
                    <button type="button" class="ml-auto" onclick="this.parentElement.remove();">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_perfil)): ?>
                <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded flex items-center">
                    <i class="bi bi-exclamation-circle me-2"></i><?php echo $error_perfil; ?>
                    <button type="button" class="ml-auto" onclick="this.parentElement.remove();">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Mensajes de éxito o error para cambio de contraseña -->
            <?php if (!empty($success_password)): ?>
                <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded flex items-center">
                    <i class="bi bi-check-circle me-2"></i><?php echo $success_password; ?>
                    <button type="button" class="ml-auto" onclick="this.parentElement.remove();">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error_password)): ?>
                <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded flex items-center">
                    <i class="bi bi-exclamation-circle me-2"></i><?php echo $error_password; ?>
                    <button type="button" class="ml-auto" onclick="this.parentElement.remove();">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Contenido principal -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Información del Usuario -->
                <div class="bg-white rounded-lg border border-gray-100 shadow-sm">
                    <div class="p-6 border-b border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">Información del Usuario</h3>
                                <p class="text-sm text-gray-500 mt-1">Datos personales de la cuenta</p>
                            </div>
                            <button class="inline-flex items-center justify-center h-9 rounded-md px-4 text-sm font-medium bg-blue-600 text-white shadow-sm hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-blue-950 disabled:pointer-events-none disabled:opacity-50"
                                    onclick="document.getElementById('editarPerfilModal').classList.toggle('hidden')">
                                <i class="bi bi-pencil-square me-2"></i> Editar Perfil
                            </button>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div>
                                <h4 class="text-sm font-medium text-gray-500">Nombre completo</h4>
                                <p class="mt-1 text-base text-gray-900"><?php echo htmlspecialchars($_SESSION['doctor_nombre'] . ' ' . $_SESSION['doctor_apellido']); ?></p>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-500">Correo electrónico</h4>
                                <p class="mt-1 text-base text-gray-900"><?php echo htmlspecialchars($usuario['email']); ?></p>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-500">Rol</h4>
                                <p class="mt-1 text-base text-gray-900"><?php echo $_SESSION['user_rol'] === 'admin' ? 'Administrador' : 'Doctor'; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Seguridad -->
                <div class="bg-white rounded-lg border border-gray-100 shadow-sm">
                    <div class="p-6 border-b border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">Seguridad</h3>
                                <p class="text-sm text-gray-500 mt-1">Gestión de contraseña y seguridad</p>
                            </div>
                            <button class="inline-flex items-center justify-center h-9 rounded-md px-4 text-sm font-medium bg-blue-600 text-white shadow-sm hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-blue-950 disabled:pointer-events-none disabled:opacity-50"
                                    onclick="document.getElementById('cambiarPasswordModal').classList.toggle('hidden')">
                                <i class="bi bi-key me-2"></i> Cambiar Contraseña
                            </button>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div>
                                <h4 class="text-sm font-medium text-gray-500">Última actualización de contraseña</h4>
                                <p class="mt-1 text-base text-gray-900">No disponible</p>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-500">Estado de la cuenta</h4>
                                <p class="mt-1 inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-sm font-medium text-green-800">
                                    <i class="bi bi-shield-check me-1"></i> Activa
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Editar Perfil -->
    <div id="editarPerfilModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800">Editar Perfil</h3>
                    <button type="button" class="text-gray-400 hover:text-gray-500" onclick="document.getElementById('editarPerfilModal').classList.add('hidden')">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
            <form method="POST" action="">
                <div class="p-6 space-y-4">
                    <div>
                        <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="bi bi-person text-gray-400"></i>
                            </span>
                            <input type="text" 
                                   class="block w-full pl-10 pr-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all duration-200 text-sm" 
                                   id="nombre" 
                                   name="nombre" 
                                   value="<?php echo htmlspecialchars($usuario['nombre']); ?>" 
                                   required>
                        </div>
                    </div>
                    <div>
                        <label for="apellido" class="block text-sm font-medium text-gray-700 mb-1">Apellido</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="bi bi-person text-gray-400"></i>
                            </span>
                            <input type="text" 
                                   class="block w-full pl-10 pr-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all duration-200 text-sm" 
                                   id="apellido" 
                                   name="apellido" 
                                   value="<?php echo htmlspecialchars($usuario['apellido']); ?>" 
                                   required>
                        </div>
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="bi bi-envelope text-gray-400"></i>
                            </span>
                            <input type="email" 
                                   class="block w-full pl-10 pr-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all duration-200 text-sm" 
                                   id="email" 
                                   name="email" 
                                   value="<?php echo htmlspecialchars($usuario['email']); ?>" 
                                   required>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                    <button type="button" 
                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200"
                            onclick="document.getElementById('editarPerfilModal').classList.add('hidden')">
                        <i class="bi bi-x-circle mr-2"></i>
                        Cancelar
                    </button>
                    <button type="submit" 
                            name="editar_perfil" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                        <i class="bi bi-check-circle mr-2"></i>
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Cambiar Contraseña -->
    <div id="cambiarPasswordModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800">Cambiar Contraseña</h3>
                    <button type="button" class="text-gray-400 hover:text-gray-500" onclick="document.getElementById('cambiarPasswordModal').classList.add('hidden')">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
            <form method="POST" action="">
                <div class="p-6 space-y-4">
                    <div>
                        <label for="password_actual" class="block text-sm font-medium text-gray-700 mb-1">Contraseña Actual</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="bi bi-lock text-gray-400"></i>
                            </span>
                            <input type="password" 
                                   class="block w-full pl-10 pr-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all duration-200 text-sm" 
                                   id="password_actual" 
                                   name="password_actual" 
                                   required>
                        </div>
                    </div>
                    <div>
                        <label for="password_nueva" class="block text-sm font-medium text-gray-700 mb-1">Nueva Contraseña</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="bi bi-key text-gray-400"></i>
                            </span>
                            <input type="password" 
                                   class="block w-full pl-10 pr-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all duration-200 text-sm" 
                                   id="password_nueva" 
                                   name="password_nueva" 
                                   required>
                        </div>
                    </div>
                    <div>
                        <label for="password_confirmacion" class="block text-sm font-medium text-gray-700 mb-1">Confirmar Nueva Contraseña</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="bi bi-key-fill text-gray-400"></i>
                            </span>
                            <input type="password" 
                                   class="block w-full pl-10 pr-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all duration-200 text-sm" 
                                   id="password_confirmacion" 
                                   name="password_confirmacion" 
                                   required>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                    <button type="button" 
                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200"
                            onclick="document.getElementById('cambiarPasswordModal').classList.add('hidden')">
                        <i class="bi bi-x-circle mr-2"></i>
                        Cancelar
                    </button>
                    <button type="submit" 
                            name="cambiar_password" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                        <i class="bi bi-check-circle mr-2"></i>
                        Actualizar Contraseña
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
?> 