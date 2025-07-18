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

// Configuración para el header
$titulo = "Panel de Administración";
$subtitulo = "Gestión y monitoreo del sistema";
$menu_activo = 'dashboard';
?>
<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="h-full bg-[#f8f9fa]">
    <?php include 'menu_lateral.php'; ?>
    
    <div class="ml-64">
        <?php include 'header.php'; ?>

        <main class="p-6">
            <!-- Estadísticas Principales -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <!-- Formularios Pendientes -->
                <div class="bg-white rounded-lg border border-gray-100 shadow-sm hover:shadow-md transition-all duration-200">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="p-3 bg-amber-100 text-amber-700 rounded-lg">
                                    <i class="bi bi-hourglass-split text-2xl"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-800">Pendientes</h3>
                            </div>
                            <span class="text-3xl font-bold text-gray-800"><?php echo $conteo['pendientes']; ?></span>
                        </div>
                        <a href="formularios_pendientes.php" class="inline-flex items-center text-sm text-amber-600 hover:text-amber-700">
                            Ver formularios pendientes
                            <i class="bi bi-arrow-right ml-2"></i>
                        </a>
                    </div>
                </div>

                <!-- Formularios Aprobados -->
                <div class="bg-white rounded-lg border border-gray-100 shadow-sm hover:shadow-md transition-all duration-200">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="p-3 bg-green-100 text-green-700 rounded-lg">
                                    <i class="bi bi-check-circle text-2xl"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-800">Aprobados</h3>
                            </div>
                            <span class="text-3xl font-bold text-gray-800"><?php echo $conteo['aprobados']; ?></span>
                        </div>
                        <a href="formularios_aprobados.php" class="inline-flex items-center text-sm text-green-600 hover:text-green-700">
                            Ver formularios aprobados
                            <i class="bi bi-arrow-right ml-2"></i>
                        </a>
                    </div>
                </div>

                <!-- Formularios Rechazados -->
                <div class="bg-white rounded-lg border border-gray-100 shadow-sm hover:shadow-md transition-all duration-200">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="p-3 bg-red-100 text-red-700 rounded-lg">
                                    <i class="bi bi-x-circle text-2xl"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-800">Rechazados</h3>
                            </div>
                            <span class="text-3xl font-bold text-gray-800"><?php echo $conteo['rechazados']; ?></span>
                        </div>
                        <a href="formularios_rechazados.php" class="inline-flex items-center text-sm text-red-600 hover:text-red-700">
                            Ver formularios rechazados
                            <i class="bi bi-arrow-right ml-2"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Estadísticas de Usuarios -->
            <div class="bg-white rounded-lg border border-gray-100 shadow-sm p-6 mb-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-semibold text-gray-800">Gestión de Usuarios</h2>
                    <a href="nuevo_doctor.php" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="bi bi-person-plus"></i>
                        Nuevo Doctor
                    </a>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Total Usuarios -->
                    <div class="rounded-lg border border-gray-100 p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="p-2 bg-blue-100 text-blue-700 rounded-lg">
                                <i class="bi bi-people text-xl"></i>
                            </div>
                            <h3 class="font-semibold text-gray-800">Total Usuarios</h3>
                        </div>
                        <p class="text-2xl font-bold text-gray-800"><?php echo $doctores['total_doctores']; ?></p>
                    </div>

                    <!-- Administradores -->
                    <div class="rounded-lg border border-gray-100 p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="p-2 bg-purple-100 text-purple-700 rounded-lg">
                                <i class="bi bi-shield-lock text-xl"></i>
                            </div>
                            <h3 class="font-semibold text-gray-800">Administradores</h3>
                        </div>
                        <p class="text-2xl font-bold text-gray-800"><?php echo $doctores['total_admins']; ?></p>
                    </div>

                    <!-- Usuarios Activos -->
                    <div class="rounded-lg border border-gray-100 p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="p-2 bg-green-100 text-green-700 rounded-lg">
                                <i class="bi bi-person-check text-xl"></i>
                            </div>
                            <h3 class="font-semibold text-gray-800">Usuarios Activos</h3>
                        </div>
                        <p class="text-2xl font-bold text-gray-800"><?php echo $doctores['activos']; ?></p>
                    </div>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <a href="gestionar_doctores.php" class="group bg-white rounded-lg border border-gray-100 shadow-sm p-6 hover:shadow-md transition-all duration-200">
                    <div class="flex flex-col items-center text-center gap-4">
                        <div class="p-3 bg-blue-100 text-blue-700 rounded-lg group-hover:scale-110 transition-transform">
                            <i class="bi bi-people-fill text-3xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800 mb-1">Gestionar Doctores</h3>
                            <p class="text-sm text-gray-500">Administrar usuarios del sistema</p>
                        </div>
                    </div>
                </a>

                <a href="formularios_pendientes.php" class="group bg-white rounded-lg border border-gray-100 shadow-sm p-6 hover:shadow-md transition-all duration-200">
                    <div class="flex flex-col items-center text-center gap-4">
                        <div class="p-3 bg-amber-100 text-amber-700 rounded-lg group-hover:scale-110 transition-transform">
                            <i class="bi bi-file-earmark-text text-3xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800 mb-1">Revisar Formularios</h3>
                            <p class="text-sm text-gray-500">Gestionar formularios pendientes</p>
                        </div>
                    </div>
                </a>

                <a href="estadisticas.php" class="group bg-white rounded-lg border border-gray-100 shadow-sm p-6 hover:shadow-md transition-all duration-200">
                    <div class="flex flex-col items-center text-center gap-4">
                        <div class="p-3 bg-green-100 text-green-700 rounded-lg group-hover:scale-110 transition-transform">
                            <i class="bi bi-bar-chart text-3xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800 mb-1">Estadísticas</h3>
                            <p class="text-sm text-gray-500">Ver reportes y análisis</p>
                        </div>
                    </div>
                </a>

                <a href="configuracion.php" class="group bg-white rounded-lg border border-gray-100 shadow-sm p-6 hover:shadow-md transition-all duration-200">
                    <div class="flex flex-col items-center text-center gap-4">
                        <div class="p-3 bg-purple-100 text-purple-700 rounded-lg group-hover:scale-110 transition-transform">
                            <i class="bi bi-gear text-3xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800 mb-1">Configuración</h3>
                            <p class="text-sm text-gray-500">Ajustes del sistema</p>
                        </div>
                    </div>
                </a>
            </div>
        </main>
    </div>
</body>
</html>
<?php
$conn->close();
?> 