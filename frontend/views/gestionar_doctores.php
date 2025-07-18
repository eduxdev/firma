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

// Configuración para el header
$titulo = "Gestionar Doctores";
$subtitulo = "Administración de usuarios del sistema";


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
    <title>Gestionar Doctores - Panel de Administración</title>
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
<body class="h-full bg-[#f8f9fa]" x-data="{ showModal: false, doctorId: null, doctorNombre: '' }">
    <?php include 'menu_lateral.php'; ?>
    
    <div class="main-content">
        <?php include 'header.php'; ?>

        <main class="p-6">
            <?php if (isset($_SESSION['error'])): ?>
            <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700">
                <i class="bi bi-exclamation-circle me-2"></i><?php echo $_SESSION['error']; ?>
            </div>
            <?php unset($_SESSION['error']); endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
            <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700">
                <i class="bi bi-check-circle me-2"></i><?php echo $_SESSION['success']; ?>
            </div>
            <?php unset($_SESSION['success']); endif; ?>

            <!-- Estadísticas de Usuarios -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Total Doctores -->
                <div class="bg-white rounded-lg border border-gray-100 shadow-sm hover:shadow-md transition-all duration-200">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="p-3 bg-blue-100 text-blue-700 rounded-lg">
                                    <i class="bi bi-people text-2xl"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-800">Total Doctores</h3>
                            </div>
                            <span class="text-3xl font-bold text-gray-800"><?php echo $result->num_rows; ?></span>
                        </div>
                        
                    </div>
                </div>

                <!-- Doctores Activos -->
                <div class="bg-white rounded-lg border border-gray-100 shadow-sm hover:shadow-md transition-all duration-200">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="p-3 bg-green-100 text-green-700 rounded-lg">
                                    <i class="bi bi-person-check text-2xl"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-800">Doctores Activos</h3>
                            </div>
                            <span class="text-3xl font-bold text-gray-800">
                                <?php 
                                $activos = 0;
                                mysqli_data_seek($result, 0);
                                while ($row = $result->fetch_assoc()) {
                                    if ($row['activo']) $activos++;
                                }
                                echo $activos;
                                mysqli_data_seek($result, 0);
                                ?>
                            </span>
                        </div>
                        
                    </div>
                </div>

                <!-- Doctores Inactivos -->
                <div class="bg-white rounded-lg border border-gray-100 shadow-sm hover:shadow-md transition-all duration-200">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="p-3 bg-red-100 text-red-700 rounded-lg">
                                    <i class="bi bi-person-x text-2xl"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-800">Doctores Inactivos</h3>
                            </div>
                            <span class="text-3xl font-bold text-gray-800"><?php echo $result->num_rows - $activos; ?></span>
                        </div>
                        
                    </div>
                </div>
            </div>

            <!-- Lista de Doctores -->
            <div class="bg-white rounded-lg border border-gray-100 shadow-sm">
                <div class="p-6 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-800">Lista de Doctores</h2>
                            <p class="text-sm text-gray-500 mt-1">Gestión de usuarios del sistema</p>
                        </div>
                        <a href="nuevo_doctor.php" class="inline-flex items-center justify-center h-9 rounded-md px-4 text-sm font-medium bg-green-600 text-white shadow-sm hover:bg-green-700 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-green-950 disabled:pointer-events-none disabled:opacity-50">
                            <i class="bi bi-person-plus me-2"></i> Nuevo Doctor
                        </a>
                    </div>
                </div>
                
                <div class="relative">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50/75 border-b border-gray-200">
                            <tr>
                                <th class="h-12 px-6 text-left align-middle font-medium text-gray-500">
                                    <div class="flex items-center space-x-2">
                                        <span>Nombre</span>
                                    </div>
                                </th>
                                <th class="h-12 px-6 text-left align-middle font-medium text-gray-500">
                                    <div class="flex items-center space-x-2">
                                        <span>Email</span>
                                    </div>
                                </th>
                                <th class="h-12 px-6 text-left align-middle font-medium text-gray-500">
                                    <div class="flex items-center space-x-2">
                                        <span>Rol</span>
                                    </div>
                                </th>
                                <th class="h-12 px-6 text-left align-middle font-medium text-gray-500">
                                    <div class="flex items-center space-x-2">
                                        <span>Estado</span>
                                    </div>
                                </th>
                                <th class="h-12 px-6 text-left align-middle font-medium text-gray-500">
                                    <div class="flex items-center space-x-2">
                                        <span>Fecha Creación</span>
                                    </div>
                                </th>
                                <th class="h-12 px-6 text-left align-middle font-medium text-gray-500">
                                    <div class="flex items-center space-x-2">
                                        <span>Última Sesión</span>
                                    </div>
                                </th>
                                <th class="h-12 px-6 text-right align-middle font-medium text-gray-500">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 border-b border-gray-200">
                            <?php if ($result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50/50">
                                    <td class="p-4 px-6 align-middle">
                                        <div class="flex items-center gap-3">
                                            <div class="h-9 w-9 rounded-full bg-gray-100/75 flex items-center justify-center">
                                                <i class="bi bi-person text-gray-600"></i>
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-medium"><?php echo htmlspecialchars($row['nombre'] . ' ' . $row['apellido']); ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4 px-6 align-middle text-gray-700">
                                        <?php echo htmlspecialchars($row['email']); ?>
                                    </td>
                                    <td class="p-4 px-6 align-middle">
                                        <?php if ($row['rol'] === 'admin'): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                Administrador
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                Doctor
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 px-6 align-middle">
                                        <?php if ($row['activo']): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Activo
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Inactivo
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 px-6 align-middle text-gray-700">
                                        <div class="flex flex-col">
                                            <span><?php echo date('d/m/Y', strtotime($row['fecha_creacion'])); ?></span>
                                            <span class="text-xs text-gray-500"><?php echo date('H:i', strtotime($row['fecha_creacion'])); ?></span>
                                        </div>
                                    </td>
                                    <td class="p-4 px-6 align-middle text-gray-700">
                                        <?php if ($row['ultima_sesion']): ?>
                                            <div class="flex flex-col">
                                                <span><?php echo date('d/m/Y', strtotime($row['ultima_sesion'])); ?></span>
                                                <span class="text-xs text-gray-500"><?php echo date('H:i', strtotime($row['ultima_sesion'])); ?></span>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-gray-400">Nunca</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 px-6 align-middle">
                                        <?php if ($row['id'] !== $_SESSION['doctor_id']): ?>
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="gestionar_doctores.php?toggle_id=<?php echo $row['id']; ?>&estado=<?php echo $row['activo']; ?>" 
                                                   class="inline-flex items-center justify-center h-9 rounded-md px-3 text-sm font-medium <?php echo $row['activo'] ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700'; ?> text-white shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-gray-950 disabled:pointer-events-none disabled:opacity-50">
                                                    <?php if ($row['activo']): ?>
                                                        <i class="bi bi-person-x me-2"></i> Desactivar
                                                    <?php else: ?>
                                                        <i class="bi bi-person-check me-2"></i> Activar
                                                    <?php endif; ?>
                                                </a>
                                                
                                                <a href="editar_doctor.php?id=<?php echo $row['id']; ?>" 
                                                   class="inline-flex items-center justify-center h-9 rounded-md px-3 text-sm font-medium border border-gray-200 bg-white text-gray-900 shadow-sm hover:bg-gray-100 hover:text-gray-900 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-gray-950 disabled:pointer-events-none disabled:opacity-50">
                                                    <i class="bi bi-pencil me-2"></i> Editar
                                                </a>

                                                <button type="button" 
                                                        @click="showModal = true; doctorId = <?php echo $row['id']; ?>; doctorNombre = '<?php echo htmlspecialchars($row['nombre'] . ' ' . $row['apellido']); ?>'"
                                                        class="inline-flex items-center justify-center h-9 rounded-md px-3 text-sm font-medium bg-red-600 text-white shadow-sm hover:bg-red-700 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-red-950 disabled:pointer-events-none disabled:opacity-50">
                                                    <i class="bi bi-trash me-2"></i> Eliminar
                                                </button>
                                            </div>
                                        <?php else: ?>
                                            <div class="flex items-center justify-end">
                                                <a href="editar_doctor.php?id=<?php echo $row['id']; ?>" 
                                                   class="inline-flex items-center justify-center h-9 rounded-md px-3 text-sm font-medium border border-gray-200 bg-white text-gray-900 shadow-sm hover:bg-gray-100 hover:text-gray-900 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-gray-950 disabled:pointer-events-none disabled:opacity-50">
                                                    <i class="bi bi-pencil me-2"></i> Editar
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="py-12">
                                        <div class="text-center">
                                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 text-gray-500 mb-3">
                                                <i class="bi bi-inbox text-2xl"></i>
                                            </div>
                                            <h3 class="text-lg font-medium text-gray-900 mb-1">No hay doctores</h3>
                                            <p class="text-sm text-gray-500">No hay doctores registrados en el sistema.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
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
                        <i class="bi bi-exclamation-triangle text-red-600 text-2xl"></i>
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
                                class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
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