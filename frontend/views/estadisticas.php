<?php
session_start();

// Verificar si el usuario está autenticado y es administrador
if (!isset($_SESSION['doctor_id']) || $_SESSION['user_rol'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Configuración de la base de datos
require_once '../../backend/db/conection.php';

// 1. Obtener datos para estadísticas de formularios por estado
$sql_formularios = "SELECT 
    SUM(CASE WHEN estado_revision = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
    SUM(CASE WHEN estado_revision = 'aprobado' THEN 1 ELSE 0 END) as aprobados,
    SUM(CASE WHEN estado_revision = 'rechazado' THEN 1 ELSE 0 END) as rechazados
FROM formularios_consentimiento";
$result_formularios = $conn->query($sql_formularios);
$formularios = $result_formularios->fetch_assoc();

// 2. Obtener datos para estadísticas de usuarios
$sql_usuarios = "SELECT 
    COUNT(*) as total_doctores,
    SUM(CASE WHEN rol = 'admin' THEN 1 ELSE 0 END) as total_admins,
    SUM(CASE WHEN rol = 'doctor' THEN 1 ELSE 0 END) as total_docs,
    SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) as activos,
    SUM(CASE WHEN activo = 0 THEN 1 ELSE 0 END) as inactivos
FROM usuarios";
$result_usuarios = $conn->query($sql_usuarios);
$usuarios = $result_usuarios->fetch_assoc();

// 3. Obtener datos para estadísticas de formularios por mes (últimos 6 meses)
$sql_por_mes = "SELECT 
    DATE_FORMAT(fecha_creacion, '%Y-%m') as mes,
    COUNT(*) as total,
    SUM(CASE WHEN estado_revision = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
    SUM(CASE WHEN estado_revision = 'aprobado' THEN 1 ELSE 0 END) as aprobados,
    SUM(CASE WHEN estado_revision = 'rechazado' THEN 1 ELSE 0 END) as rechazados
FROM formularios_consentimiento
WHERE fecha_creacion >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
GROUP BY DATE_FORMAT(fecha_creacion, '%Y-%m')
ORDER BY mes ASC";
$result_por_mes = $conn->query($sql_por_mes);

// Preparar datos para el gráfico de líneas
$meses = [];
$totales = [];
$pendientes_por_mes = [];
$aprobados_por_mes = [];
$rechazados_por_mes = [];

while ($row = $result_por_mes->fetch_assoc()) {
    // Convertir formato YYYY-MM a nombre de mes
    $timestamp = strtotime($row['mes'] . '-01');
    $nombre_mes = date('M Y', $timestamp);
    
    $meses[] = $nombre_mes;
    $totales[] = $row['total'];
    $pendientes_por_mes[] = $row['pendientes'];
    $aprobados_por_mes[] = $row['aprobados'];
    $rechazados_por_mes[] = $row['rechazados'];
}

// 4. Obtener doctores más activos (con más formularios)
$sql_doctores_activos = "SELECT 
    nombre, 
    apellido,
    fecha_creacion
FROM usuarios
WHERE rol = 'doctor' AND activo = 1
ORDER BY fecha_creacion DESC
LIMIT 5";
$result_doctores = $conn->query($sql_doctores_activos);

// Convertir datos a formato JSON para usar en JavaScript
$datos_formularios = json_encode([
    'labels' => ['Pendientes', 'Aprobados', 'Rechazados'],
    'values' => [$formularios['pendientes'], $formularios['aprobados'], $formularios['rechazados']]
]);

$datos_usuarios = json_encode([
    'labels' => ['Administradores', 'Doctores'],
    'values' => [$usuarios['total_admins'], $usuarios['total_docs']]
]);

$datos_estado_usuarios = json_encode([
    'labels' => ['Activos', 'Inactivos'],
    'values' => [$usuarios['activos'], $usuarios['inactivos']]
]);

$datos_por_mes = json_encode([
    'labels' => $meses,
    'totales' => $totales,
    'pendientes' => $pendientes_por_mes,
    'aprobados' => $aprobados_por_mes,
    'rechazados' => $rechazados_por_mes
]);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas - Panel de Administración</title>
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
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                            <a class="py-2 px-3 rounded mb-1 flex items-center text-dark hover:bg-primary hover:text-white" href="gestionar_doctores.php">
                                <i class="bi bi-people w-6 text-center"></i> Gestionar Doctores
                            </a>
                            <a class="py-2 px-3 rounded mb-1 flex items-center text-dark hover:bg-primary hover:text-white bg-primary text-white" href="estadisticas.php">
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
                <h2 class="text-2xl font-bold mb-6"><i class="bi bi-bar-chart"></i> Estadísticas del Sistema</h2>
                
                <!-- Resumen de Estadísticas -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold mb-2 text-primary flex items-center">
                            <i class="bi bi-file-text mr-2"></i> Total Formularios
                        </h3>
                        <p class="text-4xl font-bold">
                            <?php echo $formularios['pendientes'] + $formularios['aprobados'] + $formularios['rechazados']; ?>
                        </p>
                        <div class="flex justify-between mt-4 text-sm">
                            <span class="text-pending"><i class="bi bi-hourglass-split"></i> <?php echo $formularios['pendientes']; ?> pendientes</span>
                            <span class="text-approved"><i class="bi bi-check-circle"></i> <?php echo $formularios['aprobados']; ?> aprobados</span>
                            <span class="text-rejected"><i class="bi bi-x-circle"></i> <?php echo $formularios['rechazados']; ?> rechazados</span>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold mb-2 text-users flex items-center">
                            <i class="bi bi-people mr-2"></i> Total Usuarios
                        </h3>
                        <p class="text-4xl font-bold">
                            <?php echo $usuarios['total_doctores']; ?>
                        </p>
                        <div class="flex justify-between mt-4 text-sm">
                            <span class="text-admin"><i class="bi bi-person-gear"></i> <?php echo $usuarios['total_admins']; ?> administradores</span>
                            <span class="text-primary"><i class="bi bi-person-badge"></i> <?php echo $usuarios['total_docs']; ?> doctores</span>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold mb-2 text-active flex items-center">
                            <i class="bi bi-activity mr-2"></i> Tasa de Aprobación
                        </h3>
                        <p class="text-4xl font-bold">
                            <?php 
                                $total = $formularios['pendientes'] + $formularios['aprobados'] + $formularios['rechazados'];
                                echo $total > 0 ? round(($formularios['aprobados'] / $total) * 100) : 0; 
                            ?>%
                        </p>
                        <div class="w-full bg-gray-200 rounded-full h-2.5 mt-4">
                            <div class="bg-approved h-2.5 rounded-full" style="width: <?php echo $total > 0 ? round(($formularios['aprobados'] / $total) * 100) : 0; ?>%"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Gráficos principales -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <!-- Gráfico de estado de formularios -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold mb-4">Estado de Formularios</h3>
                        <div class="h-64">
                            <canvas id="chartFormularios"></canvas>
                        </div>
                    </div>
                    
                    <!-- Gráfico de distribución de usuarios -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold mb-4">Distribución de Usuarios</h3>
                        <div class="h-64">
                            <canvas id="chartUsuarios"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Gráfico de tendencia por mes -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
                    <h3 class="text-lg font-semibold mb-4">Tendencia de Formularios (Últimos 6 meses)</h3>
                    <div class="h-80">
                        <canvas id="chartTendencia"></canvas>
                    </div>
                </div>
                
                <!-- Fila adicional: Estado de usuarios y Top doctores -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <!-- Gráfico de estado de usuarios -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold mb-4">Estado de Usuarios</h3>
                        <div class="h-64">
                            <canvas id="chartEstadoUsuarios"></canvas>
                        </div>
                    </div>
                    
                    <!-- Top doctores -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold mb-4">Doctores Recientes</h3>
                        <?php if ($result_doctores->num_rows > 0): ?>
                            <ul class="space-y-4">
                                <?php while ($doctor = $result_doctores->fetch_assoc()): ?>
                                <li class="flex items-center">
                                    <div class="w-10 h-10 bg-primary text-white rounded-full flex items-center justify-center mr-3">
                                        <i class="bi bi-person"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-medium"><?php echo htmlspecialchars($doctor['nombre'] . ' ' . $doctor['apellido']); ?></h4>
                                        <div class="flex items-center">
                                            <span class="text-sm text-gray-600">Desde: <?php echo date('d/m/Y', strtotime($doctor['fecha_creacion'])); ?></span>
                                        </div>
                                    </div>
                                </li>
                                <?php endwhile; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-gray-500 italic">No hay datos suficientes</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Botones de exportación (simulado) -->
                <div class="flex justify-end space-x-3 mb-8">
                    <button class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 flex items-center">
                        <i class="bi bi-file-pdf mr-2"></i> Exportar PDF
                    </button>
                    <button class="px-4 py-2 bg-primary text-white rounded hover:bg-opacity-90 flex items-center">
                        <i class="bi bi-file-excel mr-2"></i> Exportar Excel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts para gráficos -->
    <script>
        // Datos de los gráficos
        const datosFormularios = <?php echo $datos_formularios; ?>;
        const datosUsuarios = <?php echo $datos_usuarios; ?>;
        const datosEstadoUsuarios = <?php echo $datos_estado_usuarios; ?>;
        const datosPorMes = <?php echo $datos_por_mes; ?>;
        
        // Colores
        const colores = {
            pendientes: '#7c97ab',
            aprobados: '#4a8573',
            rechazados: '#a17a7a',
            primary: '#2c6e8f',
            admin: '#3d4a54',
            users: '#5a7d9a',
            active: '#4a8573'
        };
        
        // Configuración común de Chart.js
        Chart.defaults.font.family = 'system-ui, sans-serif';
        Chart.defaults.font.size = 12;
        Chart.defaults.color = '#345464';
        
        // 1. Gráfico de Formularios (Donut)
        const ctxFormularios = document.getElementById('chartFormularios').getContext('2d');
        new Chart(ctxFormularios, {
            type: 'doughnut',
            data: {
                labels: datosFormularios.labels,
                datasets: [{
                    data: datosFormularios.values,
                    backgroundColor: [colores.pendientes, colores.aprobados, colores.rechazados],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                cutout: '65%'
            }
        });
        
        // 2. Gráfico de Usuarios (Pie)
        const ctxUsuarios = document.getElementById('chartUsuarios').getContext('2d');
        new Chart(ctxUsuarios, {
            type: 'pie',
            data: {
                labels: datosUsuarios.labels,
                datasets: [{
                    data: datosUsuarios.values,
                    backgroundColor: [colores.admin, colores.primary],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        
        // 3. Gráfico de Tendencia (Line)
        const ctxTendencia = document.getElementById('chartTendencia').getContext('2d');
        new Chart(ctxTendencia, {
            type: 'line',
            data: {
                labels: datosPorMes.labels,
                datasets: [
                    {
                        label: 'Total',
                        data: datosPorMes.totales,
                        borderColor: colores.primary,
                        backgroundColor: 'rgba(44, 110, 143, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3
                    },
                    {
                        label: 'Pendientes',
                        data: datosPorMes.pendientes,
                        borderColor: colores.pendientes,
                        borderWidth: 2,
                        borderDash: [5, 5],
                        fill: false,
                        tension: 0.3
                    },
                    {
                        label: 'Aprobados',
                        data: datosPorMes.aprobados,
                        borderColor: colores.aprobados,
                        borderWidth: 2,
                        fill: false,
                        tension: 0.3
                    },
                    {
                        label: 'Rechazados',
                        data: datosPorMes.rechazados,
                        borderColor: colores.rechazados,
                        borderWidth: 2,
                        fill: false,
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                }
            }
        });
        
        // 4. Gráfico de Estado de Usuarios (Donut)
        const ctxEstadoUsuarios = document.getElementById('chartEstadoUsuarios').getContext('2d');
        new Chart(ctxEstadoUsuarios, {
            type: 'doughnut',
            data: {
                labels: datosEstadoUsuarios.labels,
                datasets: [{
                    data: datosEstadoUsuarios.values,
                    backgroundColor: [colores.active, colores.rechazados],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                cutout: '65%'
            }
        });
    </script>

    <!-- Alpine.js para interactividad, alternativa ligera a jQuery/Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>
</html>
<?php
$conn->close();
?> 