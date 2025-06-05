<?php
session_start();

// Verificar si el usuario está autenticado y es administrador
if (!isset($_SESSION['doctor_id']) || $_SESSION['user_rol'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Configuración de la base de datos
require_once '../../backend/db/conection.php';

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
            <div class="w-full md:w-3/4 lg:w-5/6 content-container">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold"><i class="bi bi-people"></i> Gestionar Doctores</h2>
                    <a href="nuevo_doctor.php" class="px-4 py-2 bg-approved text-white rounded hover:bg-opacity-90">
                        <i class="bi bi-person-plus"></i> Nuevo Doctor
                    </a>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="bg-users text-white p-4 rounded-t-lg">
                        <h5 class="m-0 font-medium"><i class="bi bi-list-ul"></i> Lista de Doctores</h5>
                    </div>
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="w-full table-auto">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-4 py-2 text-left">ID</th>
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
                                        <td class="px-4 py-3"><?php echo $row['id']; ?></td>
                                        <td class="px-4 py-3"><?php echo htmlspecialchars($row['nombre'] . ' ' . $row['apellido']); ?></td>
                                        <td class="px-4 py-3"><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td class="px-4 py-3">
                                            <?php if ($row['rol'] === 'admin'): ?>
                                                <span class="inline-block px-2 py-1 text-xs text-white bg-admin rounded">Administrador</span>
                                            <?php else: ?>
                                                <span class="inline-block px-2 py-1 text-xs text-white bg-primary rounded">Doctor</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-3">
                                            <?php if ($row['activo']): ?>
                                                <span class="inline-block px-2 py-1 text-xs text-white bg-approved rounded">Activo</span>
                                            <?php else: ?>
                                                <span class="inline-block px-2 py-1 text-xs text-white bg-rejected rounded">Inactivo</span>
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
                                                <a href="gestionar_doctores.php?toggle_id=<?php echo $row['id']; ?>&estado=<?php echo $row['activo']; ?>" 
                                                class="inline-block px-2 py-1 text-xs text-white rounded mr-1 <?php echo $row['activo'] ? 'bg-rejected' : 'bg-approved'; ?>">
                                                    <?php if ($row['activo']): ?>
                                                        <i class="bi bi-person-x"></i> Desactivar
                                                    <?php else: ?>
                                                        <i class="bi bi-person-check"></i> Activar
                                                    <?php endif; ?>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <a href="editar_doctor.php?id=<?php echo $row['id']; ?>" class="inline-block px-2 py-1 text-xs text-white bg-primary rounded">
                                                <i class="bi bi-pencil"></i> Editar
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if ($result->num_rows === 0): ?>
                        <div class="p-4 mt-4 bg-blue-100 border-l-4 border-blue-500 text-blue-700">
                            <i class="bi bi-info-circle"></i> No hay doctores registrados.
                        </div>
                        <?php endif; ?>
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