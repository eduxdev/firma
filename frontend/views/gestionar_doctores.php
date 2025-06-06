<?php
session_start();

// Verificar si el usuario está autenticado y es administrador
if (!isset($_SESSION['doctor_id']) || $_SESSION['user_rol'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Configuración de la base de datos
require_once '../../backend/db/conection.php';

// Procesar eliminación de doctor
if (isset($_POST['eliminar_doctor']) && isset($_POST['doctor_id'])) {
    $doctor_id = (int)$_POST['doctor_id'];
    
    // No permitir eliminar al doctor actual
    if ($doctor_id !== $_SESSION['doctor_id']) {
        $sql_delete = "DELETE FROM usuarios WHERE id = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("i", $doctor_id);
        
        if ($stmt_delete->execute()) {
            $_SESSION['success'] = "Doctor eliminado correctamente.";
        } else {
            $_SESSION['error'] = "Error al eliminar el doctor.";
        }
    } else {
        $_SESSION['error'] = "No puedes eliminar tu propia cuenta.";
    }
    
    header("Location: gestionar_doctores.php");
    exit();
}

// Procesar activación/desactivación si se proporciona un ID
if (isset($_GET['toggle_id']) && isset($_GET['estado'])) {
    $id = (int)$_GET['toggle_id'];
    $estado = $_GET['estado'] === '1' ? 0 : 1; // Invertir el estado
    
    $sql = "UPDATE usuarios SET activo = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $estado, $id);
    $stmt->execute();
    
    // Redirigir para evitar reenvíos del formulario
    header("Location: gestionar_doctores.php");
    exit();
}

// Obtener la lista de doctores
$sql = "SELECT id, nombre, apellido, email, rol, activo, fecha_creacion, ultima_sesion FROM usuarios ORDER BY fecha_creacion DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Doctores - Panel de Administración</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        blue: {
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb'
                        },
                        green: {
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            300: '#86efac',
                            400: '#4ade80'
                        },
                        red: {
                            100: '#fee2e2',
                            200: '#fecaca',
                            300: '#fca5a5',
                            400: '#f87171'
                        },
                        gray: {
                            50: '#f9fafb',
                            100: '#f3f4f6',
                            200: '#e5e7eb',
                            300: '#d1d5db',
                            400: '#9ca3af',
                            500: '#6b7280',
                            600: '#4b5563',
                            700: '#374151'
                        }
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="//unpkg.com/alpinejs" defer></script>
    <style>
        /* Los estilos específicos de esta vista pueden permanecer aquí */
        [x-cloak] { 
            display: none !important; 
        }
    </style>
</head>
<body class="bg-gray-50" x-data="{ showModal: false, doctorId: null, doctorNombre: '' }">
    <nav class="bg-white shadow">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <img src="/public/assets/img/logo.jpg" alt="Logo" class="h-12">
            <div class="flex items-center">
                <span class="mr-4 text-gray-600">
                    <i class="bi bi-person-badge-fill"></i> 
                    Administrador: <?php echo htmlspecialchars($_SESSION['doctor_nombre'] . ' ' . $_SESSION['doctor_apellido']); ?>
                </span>
                <a href="cerrar_sesion.php" class="inline-flex items-center px-4 py-2 border border-blue-400 text-blue-500 hover:bg-blue-400 hover:text-white rounded-md transition-colors">
                    <i class="bi bi-box-arrow-right mr-2"></i> Cerrar Sesión
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 mt-6">
        <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border-l-4 border-red-300 text-red-700 p-4 mb-4">
            <i class="bi bi-exclamation-circle"></i> <?php echo $_SESSION['error']; ?>
        </div>
        <?php unset($_SESSION['error']); endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border-l-4 border-green-300 text-green-700 p-4 mb-4">
            <i class="bi bi-check-circle"></i> <?php echo $_SESSION['success']; ?>
        </div>
        <?php unset($_SESSION['success']); endif; ?>

        <div class="flex flex-wrap">
            <!-- Menú lateral -->
            <div class="w-full md:w-1/4 lg:w-1/6 pr-4 sidebar-container">
                <a href="admin_panel.php" class="inline-block px-4 py-2 mb-4 bg-blue-400 text-white rounded hover:bg-blue-500 transition-colors">
                    <i class="bi bi-arrow-left"></i> Volver al Dashboard
                </a>
                <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow mb-6">
                    <div class="bg-blue-400 text-white p-4 rounded-t-lg">
                        <h5 class="m-0 font-medium"><i class="bi bi-speedometer2"></i> Panel Admin</h5>
                    </div>
                    <div class="p-0">
                        <nav class="flex flex-col p-2">
                            <a class="py-2 px-3 rounded mb-1 flex items-center text-gray-600 hover:bg-blue-300 hover:text-white" href="admin_panel.php">
                                <i class="bi bi-house-door w-6 text-center"></i> Dashboard
                            </a>
                            <a class="py-2 px-3 rounded mb-1 flex items-center text-gray-600 hover:bg-blue-300 hover:text-white" href="formularios_pendientes.php">
                                <i class="bi bi-file-earmark-text w-6 text-center"></i> Formularios
                            </a>
                            <a class="py-2 px-3 rounded mb-1 flex items-center text-gray-600 hover:bg-blue-300 hover:text-white bg-blue-300 text-white" href="gestionar_doctores.php">
                                <i class="bi bi-people w-6 text-center"></i> Gestionar Doctores
                            </a>
                            <a class="py-2 px-3 rounded mb-1 flex items-center text-gray-600 hover:bg-blue-300 hover:text-white" href="estadisticas.php">
                                <i class="bi bi-bar-chart w-6 text-center"></i> Estadísticas
                            </a>
                            <a class="py-2 px-3 rounded mb-1 flex items-center text-gray-600 hover:bg-blue-300 hover:text-white" href="configuracion.php">
                                <i class="bi bi-gear w-6 text-center"></i> Configuración
                            </a>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Contenido principal -->
            <div class="w-full md:w-3/4 lg:w-5/6 content-container">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold"><i class="bi bi-people"></i> Gestionar Doctores</h2>
                    <a href="nuevo_doctor.php" class="px-4 py-2 bg-green-300 text-white rounded-md hover:bg-green-400 transition-colors">
                        <i class="bi bi-person-plus"></i> Nuevo Doctor
                    </a>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="bg-blue-400 text-white p-4 rounded-t-lg">
                        <h5 class="m-0 font-medium"><i class="bi bi-list-ul"></i> Lista de Doctores</h5>
                    </div>
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="w-full table-auto">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-4 py-2 text-left">Nombre</th>
                                        <th class="px-4 py-2 text-left">Email</th>
                                        <th class="px-4 py-2 text-left">Rol</th>
                                        <th class="px-4 py-2 text-left">Estado</th>
                                        <th class="px-4 py-2 text-left">Fecha Creación</th>
                                        <th class="px-4 py-2 text-left">Última Sesión</th>
                                        <th class="px-4 py-2 text-left">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="px-4 py-3"><?php echo htmlspecialchars($row['nombre'] . ' ' . $row['apellido']); ?></td>
                                        <td class="px-4 py-3"><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td class="px-4 py-3">
                                            <?php if ($row['rol'] === 'admin'): ?>
                                                <span class="inline-block px-2 py-1 text-xs text-white bg-blue-400 rounded">Administrador</span>
                                            <?php else: ?>
                                                <span class="inline-block px-2 py-1 text-xs text-white bg-blue-300 rounded">Doctor</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-3">
                                            <?php if ($row['activo']): ?>
                                                <span class="inline-block px-2 py-1 text-xs text-white bg-green-300 rounded">Activo</span>
                                            <?php else: ?>
                                                <span class="inline-block px-2 py-1 text-xs text-white bg-red-300 rounded">Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-3"><?php echo date('d/m/Y H:i', strtotime($row['fecha_creacion'])); ?></td>
                                        <td class="px-4 py-3">
                                            <?php 
                                            if ($row['ultima_sesion']) {
                                                echo date('d/m/Y H:i', strtotime($row['ultima_sesion']));
                                            } else {
                                                echo '<span class="text-gray-500">Nunca</span>';
                                            }
                                            ?>
                                        </td>
                                        <td class="px-4 py-3">
                                            <?php if ($row['id'] !== $_SESSION['doctor_id']): // No permitir desactivar tu propia cuenta ?>
                                                <div class="flex items-center space-x-2">
                                                    <a href="gestionar_doctores.php?toggle_id=<?php echo $row['id']; ?>&estado=<?php echo $row['activo']; ?>" 
                                                    class="inline-block px-2 py-1 text-xs text-white rounded transition-colors <?php echo $row['activo'] ? 'bg-red-300 hover:bg-red-400' : 'bg-green-300 hover:bg-green-400'; ?>">
                                                        <?php if ($row['activo']): ?>
                                                            <i class="bi bi-person-x"></i> Desactivar
                                                        <?php else: ?>
                                                            <i class="bi bi-person-check"></i> Activar
                                                        <?php endif; ?>
                                                    </a>
                                                    
                                                    <a href="editar_doctor.php?id=<?php echo $row['id']; ?>" 
                                                       class="inline-block px-2 py-1 text-xs text-white bg-blue-400 rounded hover:bg-blue-500 transition-colors">
                                                        <i class="bi bi-pencil"></i> Editar
                                                    </a>

                                                    <button type="button" 
                                                            @click="showModal = true; doctorId = <?php echo $row['id']; ?>; doctorNombre = '<?php echo htmlspecialchars($row['nombre'] . ' ' . $row['apellido']); ?>'"
                                                            class="inline-block px-2 py-1 text-xs text-white bg-red-300 rounded hover:bg-red-400 transition-colors">
                                                        <i class="bi bi-trash"></i> Eliminar
                                                    </button>
                                                </div>
                                            <?php else: ?>
                                                <a href="editar_doctor.php?id=<?php echo $row['id']; ?>" 
                                                   class="inline-block px-2 py-1 text-xs text-white bg-blue-400 rounded hover:bg-blue-500 transition-colors">
                                                    <i class="bi bi-pencil"></i> Editar
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if ($result->num_rows === 0): ?>
                        <div class="p-4 mt-4 bg-blue-100 border-l-4 border-blue-400 text-blue-700">
                            <i class="bi bi-info-circle"></i> No hay doctores registrados.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación de eliminación -->
    <div x-cloak x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-black bg-opacity-50" @click="showModal = false"></div>

        <!-- Modal -->
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl p-6 max-w-sm mx-auto relative">
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                        <i class="bi bi-exclamation-triangle text-red-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Confirmar Eliminación</h3>
                    <p class="text-sm text-gray-500 mb-6">
                        ¿Estás seguro de que deseas eliminar al doctor <span x-text="doctorNombre" class="font-semibold"></span>? 
                        Esta acción no se puede deshacer y solo es posible si el doctor no tiene formularios asociados.
                    </p>
                </div>
                <div class="flex justify-center space-x-3">
                    <form action="" method="POST">
                        <input type="hidden" name="doctor_id" :value="doctorId">
                        <input type="hidden" name="eliminar_doctor" value="1">
                        <button type="submit" 
                                class="px-4 py-2 bg-red-300 text-white text-sm font-medium rounded-md hover:bg-red-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-300 transition-colors">
                            Eliminar
                        </button>
                    </form>
                    <button @click="showModal = false" 
                            class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
?> 