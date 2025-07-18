<?php
session_start();

// Verificar si el usuario está autenticado y es administrador
if (!isset($_SESSION['doctor_id']) || $_SESSION['user_rol'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Verificar que se haya proporcionado un ID
if (!isset($_GET['id'])) {
    header('Location: gestionar_doctores.php');
    exit();
}

// Configuración de la base de datos
require_once '../../backend/db/conection.php';

$id = (int)$_GET['id'];

// Variables para mensajes
$error = '';
$success = '';

// Obtener los datos del doctor
$sql = "SELECT * FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: gestionar_doctores.php');
    exit();
}

$doctor = $result->fetch_assoc();

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
    if (empty($nombre) || empty($apellido) || empty($email)) {
        $error = "Los campos de nombre, apellido y correo son obligatorios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "El formato del correo electrónico no es válido.";
    } else {
        // Verificar si el email ya existe (solo si cambió)
        if ($email !== $doctor['email']) {
            $check_sql = "SELECT id FROM usuarios WHERE email = ? AND id != ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("si", $email, $id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $error = "El correo electrónico ya está registrado por otro usuario.";
            }
        }
        
        if (empty($error)) {
            // Actualizar con o sin contraseña
            if (empty($password)) {
                $sql = "UPDATE usuarios SET nombre = ?, apellido = ?, email = ?, rol = ?, activo = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssiii", $nombre, $apellido, $email, $rol, $activo, $id);
            } else {
                $sql = "UPDATE usuarios SET nombre = ?, apellido = ?, email = ?, password = ?, rol = ?, activo = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssiii", $nombre, $apellido, $email, $password, $rol, $activo, $id);
            }
            
            if ($stmt->execute()) {
                $success = "Información del doctor actualizada correctamente.";
                
                // Actualizar los datos mostrados
                $doctor['nombre'] = $nombre;
                $doctor['apellido'] = $apellido;
                $doctor['email'] = $email;
                $doctor['rol'] = $rol;
                $doctor['activo'] = $activo;
            } else {
                $error = "Error al actualizar la información: " . $stmt->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Doctor - Panel de Administración</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="//unpkg.com/alpinejs" defer></script>
    <style>
        .main-content {
            margin-left: 16rem;
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        [x-cloak] { 
            display: none !important; 
        }
    </style>
</head>
<body class="h-full bg-[#f8f9fa]">
    <?php include 'menu_lateral.php'; ?>
    
    <div class="main-content">
        <?php include 'header.php'; ?>

        <main class="p-6">
            <!-- Breadcrumb -->
            <nav class="flex mb-6" aria-label="breadcrumb">
                <ol class="flex items-center space-x-2">
                    <li>
                        <a href="admin_panel.php" class="text-blue-600 hover:text-blue-700">Dashboard</a>
                    </li>
                    <li class="text-gray-500">/</li>
                    <li>
                        <a href="gestionar_doctores.php" class="text-blue-600 hover:text-blue-700">Gestionar Doctores</a>
                    </li>
                    <li class="text-gray-500">/</li>
                    <li class="text-gray-600">Editar Doctor</li>
                </ol>
            </nav>

            <!-- Formulario -->
            <div class="bg-white rounded-lg border border-gray-100 shadow-sm">
                <div class="p-6 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-800">Editar Doctor</h2>
                            <p class="text-sm text-gray-500 mt-1">Modificar información del usuario</p>
                        </div>
                    </div>
                </div>

                <div class="p-6">
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
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="nombre" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nombre <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       id="nombre" 
                                       name="nombre" 
                                       value="<?php echo htmlspecialchars($doctor['nombre']); ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                       required>
                            </div>

                            <div>
                                <label for="apellido" class="block text-sm font-medium text-gray-700 mb-2">
                                    Apellido <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       id="apellido" 
                                       name="apellido" 
                                       value="<?php echo htmlspecialchars($doctor['apellido']); ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                       required>
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                    Correo Electrónico <span class="text-red-500">*</span>
                                </label>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       value="<?php echo htmlspecialchars($doctor['email']); ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                       required>
                            </div>

                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                    Contraseña <small class="text-gray-500">(Dejar vacío para mantener la actual)</small>
                                </label>
                                <input type="password" 
                                       id="password" 
                                       name="password" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label for="rol" class="block text-sm font-medium text-gray-700 mb-2">
                                    Rol <span class="text-red-500">*</span>
                                </label>
                                <select id="rol" 
                                        name="rol" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                        required>
                                    <option value="doctor" <?php echo ($doctor['rol'] === 'doctor') ? 'selected' : ''; ?>>Doctor</option>
                                    <option value="admin" <?php echo ($doctor['rol'] === 'admin') ? 'selected' : ''; ?>>Administrador</option>
                                </select>
                            </div>

                            <div class="flex items-center">
                                <div class="flex h-full items-center">
                                    <input type="checkbox" 
                                           id="activo" 
                                           name="activo" 
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" 
                                           <?php echo ($doctor['activo']) ? 'checked' : ''; ?>>
                                    <label for="activo" class="ml-2 block text-sm text-gray-700">
                                        Cuenta activa
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3 pt-6 border-t border-gray-100">
                            <a href="gestionar_doctores.php" 
                               class="inline-flex items-center justify-center h-9 rounded-md px-4 text-sm font-medium border border-gray-200 bg-white text-gray-900 shadow-sm hover:bg-gray-100 hover:text-gray-900 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-gray-950 disabled:pointer-events-none disabled:opacity-50">
                                <i class="bi bi-x-circle me-2"></i> Cancelar
                            </a>
                            <button type="submit" 
                                    class="inline-flex items-center justify-center h-9 rounded-md px-4 text-sm font-medium bg-blue-600 text-white shadow-sm hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-blue-950 disabled:pointer-events-none disabled:opacity-50">
                                <i class="bi bi-save me-2"></i> Guardar Cambios
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