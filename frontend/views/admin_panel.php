<?php
session_start();

// Verificar si el usuario está autenticado y es administrador
if (!isset($_SESSION['doctor_id']) || $_SESSION['user_rol'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Configuración de la base de datos
require_once '../../backend/db/conection.php';

// Contar formularios por estado
$sql_conteo = "SELECT 
    SUM(CASE WHEN estado_revision = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
    SUM(CASE WHEN estado_revision = 'aprobado' THEN 1 ELSE 0 END) as aprobados,
    SUM(CASE WHEN estado_revision = 'rechazado' THEN 1 ELSE 0 END) as rechazados
FROM formularios_consentimiento";
$result_conteo = $conn->query($sql_conteo);
$conteo = $result_conteo->fetch_assoc();

// Contar doctores
$sql_doctores = "SELECT 
    COUNT(*) as total_doctores,
    SUM(CASE WHEN rol = 'admin' THEN 1 ELSE 0 END) as total_admins,
    SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) as activos 
FROM usuarios";
$result_doctores = $conn->query($sql_doctores);
$doctores = $result_doctores->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
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
    <style>
        [x-cloak] { 
            display: none !important; 
        }
    </style>
</head>
<body class="bg-gray-50">
    <nav class="bg-white shadow">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <img src="/public/assets/img/logo.jpg" alt="Logo" class="h-12">
            <div class="flex items-center">
                <span class="mr-4 text-gray-600">
                    <i class="bi bi-person-badge-fill"></i> 
                    Administrador: <?php echo htmlspecialchars($_SESSION['doctor_nombre'] . ' ' . $_SESSION['doctor_apellido']); ?>
                </span>
                <a href="cerrar_sesion.php" class="inline-flex items-center px-4 py-2 border border-blue-400 text-blue-500 rounded-md hover:bg-blue-400 hover:text-white transition-colors">
                    <i class="bi bi-box-arrow-right mr-2"></i> Cerrar Sesión
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 mt-6">
        <div class="flex flex-wrap">
            <!-- Menú lateral -->
            <div class="w-full md:w-1/4 lg:w-1/6 pr-4 sidebar-container">
                <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow mb-6">
                    <div class="bg-blue-400 text-white p-4 rounded-t-lg">
                        <h5 class="m-0 font-medium"><i class="bi bi-speedometer2"></i> Panel Admin</h5>
                    </div>
                    <div class="p-0">
                        <nav class="flex flex-col p-2">
                            <a class="py-2 px-3 rounded mb-1 flex items-center text-gray-600 hover:bg-blue-300 hover:text-white bg-blue-300 text-white" href="admin_panel.php">
                                <i class="bi bi-house-door w-6 text-center"></i> Dashboard
                            </a>
                            <a class="py-2 px-3 rounded mb-1 flex items-center text-gray-600 hover:bg-blue-300 hover:text-white" href="formularios_pendientes.php">
                                <i class="bi bi-file-earmark-text w-6 text-center"></i> Formularios
                            </a>
                            <a class="py-2 px-3 rounded mb-1 flex items-center text-gray-600 hover:bg-blue-300 hover:text-white" href="gestionar_doctores.php">
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
                <h2 class="mb-4 text-xl font-bold"><i class="bi bi-speedometer2"></i> Dashboard de Administración</h2>
                
                <!-- Resumen -->
                <div class="flex flex-wrap -mx-2 mb-6">
                    <!-- Estadísticas de formularios -->
                    <div class="w-full md:w-1/2 px-2 mb-4">
                        <div class="bg-white rounded-lg shadow-sm h-full">
                            <div class="bg-blue-400 text-white p-4 rounded-t-lg">
                                <h5 class="m-0 font-medium"><i class="bi bi-file-earmark-text"></i> Formularios</h5>
                            </div>
                            <div class="p-4">
                                <div class="flex flex-wrap -mx-2">
                                    <div class="w-1/3 px-2 text-center">
                                        <div class="flex items-center justify-center bg-blue-300 text-white p-4 rounded-lg mb-4">
                                            <i class="bi bi-hourglass-split text-2xl"></i>
                                        </div>
                                        <h3 class="text-xl font-bold"><?php echo $conteo['pendientes']; ?></h3>
                                        <p>Pendientes</p>
                                    </div>
                                    <div class="w-1/3 px-2 text-center">
                                        <div class="flex items-center justify-center bg-green-300 text-white p-4 rounded-lg mb-4">
                                            <i class="bi bi-check-circle text-2xl"></i>
                                        </div>
                                        <h3 class="text-xl font-bold"><?php echo $conteo['aprobados']; ?></h3>
                                        <p>Aprobados</p>
                                    </div>
                                    <div class="w-1/3 px-2 text-center">
                                        <div class="flex items-center justify-center bg-red-300 text-white p-4 rounded-lg mb-4">
                                            <i class="bi bi-x-circle text-2xl"></i>
                                        </div>
                                        <h3 class="text-xl font-bold"><?php echo $conteo['rechazados']; ?></h3>
                                        <p>Rechazados</p>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <a href="formularios_pendientes.php" class="inline-block px-4 py-2 bg-blue-400 text-white rounded hover:bg-blue-500 transition-colors">
                                        <i class="bi bi-arrow-right"></i> Ver formularios
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Estadísticas de doctores -->
                    <div class="w-full md:w-1/2 px-2 mb-4">
                        <div class="bg-white rounded-lg shadow-sm h-full">
                            <div class="bg-blue-400 text-white p-4 rounded-t-lg">
                                <h5 class="m-0 font-medium"><i class="bi bi-people"></i> Usuarios</h5>
                            </div>
                            <div class="p-4">
                                <div class="flex flex-wrap -mx-2">
                                    <div class="w-1/3 px-2 text-center">
                                        <div class="flex items-center justify-center bg-blue-300 text-white p-4 rounded-lg mb-4">
                                            <i class="bi bi-person-check text-2xl"></i>
                                        </div>
                                        <h3 class="text-xl font-bold"><?php echo $doctores['total_doctores']; ?></h3>
                                        <p>Total Usuarios</p>
                                    </div>
                                    <div class="w-1/3 px-2 text-center">
                                        <div class="flex items-center justify-center bg-blue-500 text-white p-4 rounded-lg mb-4">
                                            <i class="bi bi-person-lock text-2xl"></i>
                                        </div>
                                        <h3 class="text-xl font-bold"><?php echo $doctores['total_admins']; ?></h3>
                                        <p>Administradores</p>
                                    </div>
                                    <div class="w-1/3 px-2 text-center">
                                        <div class="flex items-center justify-center bg-green-300 text-white p-4 rounded-lg mb-4">
                                            <i class="bi bi-person-check text-2xl"></i>
                                        </div>
                                        <h3 class="text-xl font-bold"><?php echo $doctores['activos']; ?></h3>
                                        <p>Activos</p>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <a href="gestionar_doctores.php" class="inline-block px-4 py-2 bg-blue-400 text-white rounded hover:bg-blue-500 transition-colors">
                                        <i class="bi bi-person-plus"></i> Gestionar doctores
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Acciones rápidas -->
                <div class="bg-white rounded-lg shadow-sm mb-6">
                    <div class="bg-blue-400 text-white p-4 rounded-t-lg">
                        <h5 class="m-0 font-medium"><i class="bi bi-lightning"></i> Acciones Rápidas</h5>
                    </div>
                    <div class="p-4">
                        <div class="flex flex-wrap -mx-2">
                            <div class="w-full md:w-1/4 px-2 mb-4">
                                <a href="nuevo_doctor.php" class="flex flex-col items-center justify-center p-6 bg-green-300 text-white rounded-lg border-l-4 border-green-400 h-full hover:shadow-lg transition-shadow">
                                    <i class="bi bi-person-plus text-4xl"></i>
                                    <span class="mt-2">Nuevo Doctor</span>
                                </a>
                            </div>
                            <div class="w-full md:w-1/4 px-2 mb-4">
                                <a href="formularios_pendientes.php" class="flex flex-col items-center justify-center p-6 bg-blue-300 text-white rounded-lg border-l-4 border-blue-400 h-full hover:shadow-lg transition-shadow">
                                    <i class="bi bi-hourglass-split text-4xl"></i>
                                    <span class="mt-2">Pendientes</span>
                                </a>
                            </div>
                            <div class="w-full md:w-1/4 px-2 mb-4">
                                <a href="estadisticas.php" class="flex flex-col items-center justify-center p-6 bg-blue-300 text-white rounded-lg border-l-4 border-blue-400 h-full hover:shadow-lg transition-shadow">
                                    <i class="bi bi-bar-chart text-4xl"></i>
                                    <span class="mt-2">Estadísticas</span>
                                </a>
                            </div>
                            <div class="w-full md:w-1/4 px-2 mb-4">
                                <a href="configuracion.php" class="flex flex-col items-center justify-center p-6 bg-blue-400 text-white rounded-lg border-l-4 border-blue-500 h-full hover:shadow-lg transition-shadow">
                                    <i class="bi bi-gear text-4xl"></i>
                                    <span class="mt-2">Configuración</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alpine.js para interactividad -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>
</html>
<?php
$conn->close();
?> 