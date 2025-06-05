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
    <link rel="stylesheet" href="/public/assets/css/admin-style.css">
    <style>
        /* Los estilos específicos de esta vista pueden permanecer aquí */
    </style>
</head>
<body class="bg-light">
    <nav class="bg-white shadow-md py-4">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <img src="/public/assets/img/logo.jpg" alt="Logo" class="h-12">
            <div class="flex items-center">
                <span class="mr-4 text-dark">
                    <?php if ($_SESSION['user_rol'] === 'admin'): ?>
                        <i class="bi bi-person-badge-fill"></i> 
                        Administrador: 
                    <?php else: ?>
                        <i class="bi bi-person-vcard"></i> 
                        Doctor: 
                    <?php endif; ?>
                    <?php echo htmlspecialchars($_SESSION['doctor_nombre'] . ' ' . $_SESSION['doctor_apellido']); ?>
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
                <?php if ($_SESSION['user_rol'] === 'admin'): ?>
                <a href="admin_panel.php" class="inline-block px-4 py-2 mb-4 bg-primary text-white rounded hover:bg-opacity-90">
                    <i class="bi bi-arrow-left"></i> Volver al Dashboard
                </a>
                <?php else: ?>
                <a href="formularios_pendientes.php" class="inline-block px-4 py-2 mb-4 bg-primary text-white rounded hover:bg-opacity-90">
                    <i class="bi bi-arrow-left"></i> Volver a Formularios
                </a>
                <?php endif; ?>
                
                <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow mb-6">
                    <div class="bg-primary text-white p-4 rounded-t-lg">
                        <h5 class="m-0 font-medium">
                            <?php if ($_SESSION['user_rol'] === 'admin'): ?>
                                <i class="bi bi-speedometer2"></i> Panel Admin
                            <?php else: ?>
                                <i class="bi bi-menu-button-wide"></i> Menú Doctor
                            <?php endif; ?>
                        </h5>
                    </div>
                    <div class="p-0">
                        <nav class="flex flex-col p-2">
                            <?php if ($_SESSION['user_rol'] === 'admin'): ?>
                                <a class="py-2 px-3 rounded mb-1 flex items-center text-dark hover:bg-primary hover:text-white" href="admin_panel.php">
                                    <i class="bi bi-house-door w-6 text-center"></i> Dashboard
                                </a>
                                <a class="py-2 px-3 rounded mb-1 flex items-center text-dark hover:bg-primary hover:text-white" href="formularios_pendientes.php">
                                    <i class="bi bi-file-earmark-text w-6 text-center"></i> Formularios
                                </a>
                                <a class="py-2 px-3 rounded mb-1 flex items-center text-dark hover:bg-primary hover:text-white" href="gestionar_doctores.php">
                                    <i class="bi bi-people w-6 text-center"></i> Gestionar Doctores
                                </a>
                                <a class="py-2 px-3 rounded mb-1 flex items-center text-dark hover:bg-primary hover:text-white" href="estadisticas.php">
                                    <i class="bi bi-bar-chart w-6 text-center"></i> Estadísticas
                                </a>
                            <?php endif; ?>
                            <a class="py-2 px-3 rounded mb-1 flex items-center text-dark hover:bg-primary hover:text-white bg-primary text-white" href="configuracion.php">
                                <i class="bi bi-gear w-6 text-center"></i> Configuración
                            </a>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Contenido principal -->
            <div class="w-full md:w-3/4 lg:w-5/6 content-container">
                <?php if ($_SESSION['user_rol'] === 'admin'): ?>
                <nav class="flex mb-6" aria-label="breadcrumb">
                    <ol class="flex">
                        <li class="mr-2">
                            <a href="admin_panel.php" class="text-primary hover:underline">Dashboard</a>
                            <span class="mx-1 text-gray-500">/</span>
                        </li>
                        <li class="text-gray-600">Configuración</li>
                    </ol>
                </nav>
                <?php endif; ?>
                
                <h2 class="mb-6 text-xl font-bold"><i class="bi bi-gear"></i> Configuración</h2>

                <!-- Mensajes de éxito o error para actualización de perfil -->
                <?php if (!empty($success_perfil)): ?>
                    <div class="mb-4 p-4 bg-green-100 border-l-4 border-approved text-approved rounded flex items-center">
                        <i class="bi bi-check-circle-fill mr-2"></i> <?php echo $success_perfil; ?>
                        <button type="button" class="ml-auto" onclick="this.parentElement.remove();">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($error_perfil)): ?>
                    <div class="mb-4 p-4 bg-red-100 border-l-4 border-rejected text-rejected rounded flex items-center">
                        <i class="bi bi-exclamation-triangle-fill mr-2"></i> <?php echo $error_perfil; ?>
                        <button type="button" class="ml-auto" onclick="this.parentElement.remove();">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                <?php endif; ?>
                
                <!-- Mensajes de éxito o error para cambio de contraseña -->
                <?php if (!empty($success_password)): ?>
                    <div class="mb-4 p-4 bg-green-100 border-l-4 border-approved text-approved rounded flex items-center">
                        <i class="bi bi-check-circle-fill mr-2"></i> <?php echo $success_password; ?>
                        <button type="button" class="ml-auto" onclick="this.parentElement.remove();">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($error_password)): ?>
                    <div class="mb-4 p-4 bg-red-100 border-l-4 border-rejected text-rejected rounded flex items-center">
                        <i class="bi bi-exclamation-triangle-fill mr-2"></i> <?php echo $error_password; ?>
                        <button type="button" class="ml-auto" onclick="this.parentElement.remove();">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                <?php endif; ?>

                <div class="flex flex-wrap -mx-3">
                    <div class="w-full md:w-1/2 px-3 mb-6">
                        <!-- Información del Usuario -->
                        <div class="bg-white rounded-lg shadow-sm mb-6">
                            <div class="bg-users text-white p-4 rounded-t-lg">
                                <h5 class="m-0 font-medium"><i class="bi bi-person-circle"></i> Información del Usuario</h5>
                            </div>
                            <div class="p-6">
                                <p class="mb-2"><strong>Nombre:</strong> <?php echo htmlspecialchars($_SESSION['doctor_nombre'] . ' ' . $_SESSION['doctor_apellido']); ?></p>
                                <p class="mb-2"><strong>Rol:</strong> <?php echo $_SESSION['user_rol'] === 'admin' ? 'Administrador' : 'Doctor'; ?></p>
                                <p class="mb-4"><strong>ID:</strong> <?php echo $_SESSION['doctor_id']; ?></p>
                                <div class="w-full">
                                    <button class="w-full px-4 py-2 bg-primary text-white rounded hover:bg-opacity-90" 
                                            onclick="document.getElementById('editarPerfilModal').classList.toggle('hidden')">
                                        <i class="bi bi-pencil-square"></i> Editar Perfil
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="w-full md:w-1/2 px-3 mb-6">
                        <!-- Seguridad -->
                        <div class="bg-white rounded-lg shadow-sm mb-6">
                            <div class="bg-admin text-white p-4 rounded-t-lg">
                                <h5 class="m-0 font-medium"><i class="bi bi-shield-lock"></i> Seguridad</h5>
                            </div>
                            <div class="p-6">
                                <div class="mb-3">
                                    <h5 class="text-lg font-medium mb-1">Cambiar Contraseña</h5>
                                    <p class="text-gray-600 mb-4">Actualiza tu contraseña para mayor seguridad</p>
                                    <div class="w-full">
                                        <button class="w-full px-4 py-2 bg-primary text-white rounded hover:bg-opacity-90"
                                                onclick="document.getElementById('cambiarPasswordModal').classList.toggle('hidden')">
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
    <div id="editarPerfilModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md">
            <div class="bg-primary text-white p-4 rounded-t-lg flex justify-between items-center">
                <h5 class="m-0 font-medium"><i class="bi bi-person-circle"></i> Editar Perfil</h5>
                <button type="button" class="text-white" onclick="document.getElementById('editarPerfilModal').classList.add('hidden')">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <form method="POST" action="">
                <div class="p-6">
                    <div class="mb-4">
                        <label for="nombre" class="block text-gray-700 mb-2">Nombre</label>
                        <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-primary" 
                               id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                    </div>
                    <div class="mb-4">
                        <label for="apellido" class="block text-gray-700 mb-2">Apellido</label>
                        <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-primary" 
                               id="apellido" name="apellido" value="<?php echo htmlspecialchars($usuario['apellido']); ?>" required>
                    </div>
                    <div class="mb-4">
                        <label for="email" class="block text-gray-700 mb-2">Correo Electrónico</label>
                        <input type="email" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-primary" 
                               id="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                    </div>
                </div>
                <div class="bg-gray-100 p-4 rounded-b-lg flex justify-end">
                    <button type="button" class="px-4 py-2 bg-gray-500 text-white rounded mr-2 hover:bg-gray-600" 
                            onclick="document.getElementById('editarPerfilModal').classList.add('hidden')">Cancelar</button>
                    <button type="submit" name="editar_perfil" class="px-4 py-2 bg-primary text-white rounded hover:bg-opacity-90">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Cambiar Contraseña -->
    <div id="cambiarPasswordModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md">
            <div class="bg-primary text-white p-4 rounded-t-lg flex justify-between items-center">
                <h5 class="m-0 font-medium"><i class="bi bi-key"></i> Cambiar Contraseña</h5>
                <button type="button" class="text-white" onclick="document.getElementById('cambiarPasswordModal').classList.add('hidden')">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <form method="POST" action="">
                <div class="p-6">
                    <div class="mb-4">
                        <label for="password_actual" class="block text-gray-700 mb-2">Contraseña Actual</label>
                        <input type="password" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-primary" 
                               id="password_actual" name="password_actual" required>
                    </div>
                    <div class="mb-4">
                        <label for="password_nueva" class="block text-gray-700 mb-2">Nueva Contraseña</label>
                        <input type="password" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-primary" 
                               id="password_nueva" name="password_nueva" required>
                    </div>
                    <div class="mb-4">
                        <label for="password_confirmacion" class="block text-gray-700 mb-2">Confirmar Nueva Contraseña</label>
                        <input type="password" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-primary" 
                               id="password_confirmacion" name="password_confirmacion" required>
                    </div>
                </div>
                <div class="bg-gray-100 p-4 rounded-b-lg flex justify-end">
                    <button type="button" class="px-4 py-2 bg-gray-500 text-white rounded mr-2 hover:bg-gray-600"
                            onclick="document.getElementById('cambiarPasswordModal').classList.add('hidden')">Cancelar</button>
                    <button type="submit" name="cambiar_password" class="px-4 py-2 bg-primary text-white rounded hover:bg-opacity-90">Actualizar Contraseña</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Alpine.js para interactividad, alternativa ligera a jQuery/Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>
</html>
<?php
$conn->close();
?> 