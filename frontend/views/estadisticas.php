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

// Configuración para el header
$titulo = "Estadísticas";
$subtitulo = "Análisis y métricas del sistema";


// Scripts adicionales para el header
$scripts_adicionales = '
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    <title>Estadísticas - Panel de Administración</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        blue: {
                            400: '#60a5fa',
                            600: '#2563eb',
                            700: '#1d4ed8'
                        },
                        green: {
                            400: '#4ade80',
                            600: '#16a34a'
                        },
                        red: {
                            400: '#f87171',
                            600: '#dc2626'
                        },
                        gray: {
                            50: '#f9fafb',
                            100: '#f3f4f6',
                            200: '#e5e7eb',
                            300: '#d1d5db',
                            400: '#9ca3af',
                            500: '#6b7280',
                            600: '#4b5563',
                            700: '#374151',
                            800: '#1f2937',
                            900: '#111827'
                        }
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php include 'shared_styles.php'; ?>
</head>
<body class="h-full bg-[#f8f9fa]">
    <?php include 'menu_lateral.php'; ?>
    
    <div class="main-content">
        <?php include 'header.php'; ?>

        <main class="p-4 sm:p-6 lg:p-8">
            <!-- Encabezado de la página -->
            <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
                <h1 class="text-2xl font-bold text-gray-800">Estadísticas del Sistema</h1>
                <div class="flex gap-3">
                    <a href="exportar_estadisticas.php" 
                       class="inline-flex items-center justify-center h-9 rounded-md px-4 text-sm font-medium border border-gray-200 bg-white text-gray-900 shadow-sm hover:bg-gray-100 hover:text-gray-900 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-gray-950 disabled:pointer-events-none disabled:opacity-50">
                        <i class="bi bi-download me-2"></i> Exportar Datos
                    </a>
                </div>
            </div>
            
            <!-- Resumen de Estadísticas -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mb-8">
                <!-- Total Formularios -->
                <div class="bg-white rounded-lg border border-gray-100 shadow-sm hover:shadow-md transition-all duration-200">
                    <div class="p-4 sm:p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="p-3 bg-blue-100 text-blue-700 rounded-lg">
                                    <i class="bi bi-file-text text-2xl"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-800">Total Formularios</h3>
                            </div>
                            <span class="text-3xl font-bold text-gray-800">
                                <?php echo $formularios['pendientes'] + $formularios['aprobados'] + $formularios['rechazados']; ?>
                            </span>
                        </div>
                        <div class="flex flex-wrap gap-3 mt-4 text-sm">
                            <span class="inline-flex items-center gap-1 text-blue-600">
                                <i class="bi bi-hourglass-split"></i> <?php echo $formularios['pendientes']; ?> pendientes
                            </span>
                            <span class="inline-flex items-center gap-1 text-green-600">
                                <i class="bi bi-check-circle"></i> <?php echo $formularios['aprobados']; ?> aprobados
                            </span>
                            <span class="inline-flex items-center gap-1 text-red-600">
                                <i class="bi bi-x-circle"></i> <?php echo $formularios['rechazados']; ?> rechazados
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Total Usuarios -->
                <div class="bg-white rounded-lg border border-gray-100 shadow-sm hover:shadow-md transition-all duration-200">
                    <div class="p-4 sm:p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="p-3 bg-green-100 text-green-700 rounded-lg">
                                    <i class="bi bi-people text-2xl"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-800">Total Usuarios</h3>
                            </div>
                            <span class="text-3xl font-bold text-gray-800"><?php echo $usuarios['total_doctores']; ?></span>
                        </div>
                        <div class="flex flex-wrap gap-3 mt-4 text-sm">
                            <span class="inline-flex items-center gap-1 text-blue-600">
                                <i class="bi bi-person-gear"></i> <?php echo $usuarios['total_admins']; ?> administradores
                            </span>
                            <span class="inline-flex items-center gap-1 text-blue-600">
                                <i class="bi bi-person-badge"></i> <?php echo $usuarios['total_docs']; ?> doctores
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Tasa de Aprobación -->
                <div class="bg-white rounded-lg border border-gray-100 shadow-sm hover:shadow-md transition-all duration-200">
                    <div class="p-4 sm:p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="p-3 bg-amber-100 text-amber-700 rounded-lg">
                                    <i class="bi bi-activity text-2xl"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-800">Tasa de Aprobación</h3>
                            </div>
                            <span class="text-3xl font-bold text-gray-800">
                                <?php 
                                    $total = $formularios['pendientes'] + $formularios['aprobados'] + $formularios['rechazados'];
                                    echo $total > 0 ? round(($formularios['aprobados'] / $total) * 100) : 0; 
                                ?>%
                            </span>
                        </div>
                        <div class="mt-4">
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-green-600 h-2 rounded-full" style="width: <?php echo $total > 0 ? round(($formularios['aprobados'] / $total) * 100) : 0; ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Gráficos principales -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-8">
                <!-- Gráfico de estado de formularios -->
                <div class="bg-white rounded-lg border border-gray-100 shadow-sm p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
                        <h3 class="text-lg font-semibold text-gray-800">Estado de Formularios</h3>
                        <div class="flex gap-2">
                            <button class="inline-flex items-center justify-center h-8 rounded-md px-3 text-xs font-medium border border-gray-200 bg-white text-gray-700 shadow-sm hover:bg-gray-50">
                                <i class="bi bi-calendar3 me-1"></i> Este mes
                            </button>
                        </div>
                    </div>
                    <div class="h-64 sm:h-80">
                        <canvas id="chartFormularios"></canvas>
                    </div>
                </div>
                
                <!-- Gráfico de distribución de usuarios -->
                <div class="bg-white rounded-lg border border-gray-100 shadow-sm p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
                        <h3 class="text-lg font-semibold text-gray-800">Distribución de Usuarios</h3>
                        <div class="flex gap-2">
                            <button class="inline-flex items-center justify-center h-8 rounded-md px-3 text-xs font-medium border border-gray-200 bg-white text-gray-700 shadow-sm hover:bg-gray-50">
                                <i class="bi bi-calendar3 me-1"></i> Este mes
                            </button>
                        </div>
                    </div>
                    <div class="h-64 sm:h-80">
                        <canvas id="chartUsuarios"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Gráfico de tendencia por mes -->
            <div class="bg-white rounded-lg border border-gray-100 shadow-sm p-4 sm:p-6 mb-8">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
                    <h3 class="text-lg font-semibold text-gray-800">Tendencia de Formularios</h3>
                    <div class="flex gap-2">
                        <button class="inline-flex items-center justify-center h-8 rounded-md px-3 text-xs font-medium border border-gray-200 bg-white text-gray-700 shadow-sm hover:bg-gray-50">
                            <i class="bi bi-calendar3 me-1"></i> Últimos 6 meses
                        </button>
                    </div>
                </div>
                <div class="h-80 sm:h-96">
                    <canvas id="chartTendencia"></canvas>
                </div>
            </div>
            
            <!-- Fila adicional: Estado de usuarios y Top doctores -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-8">
                <!-- Gráfico de estado de usuarios -->
                <div class="bg-white rounded-lg border border-gray-100 shadow-sm p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
                        <h3 class="text-lg font-semibold text-gray-800">Estado de Usuarios</h3>
                        <div class="flex gap-2">
                            <button class="inline-flex items-center justify-center h-8 rounded-md px-3 text-xs font-medium border border-gray-200 bg-white text-gray-700 shadow-sm hover:bg-gray-50">
                                <i class="bi bi-calendar3 me-1"></i> Este mes
                            </button>
                        </div>
                    </div>
                    <div class="h-64 sm:h-80">
                        <canvas id="chartEstadoUsuarios"></canvas>
                    </div>
                </div>
                
                <!-- Top doctores -->
                <div class="bg-white rounded-lg border border-gray-100 shadow-sm p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
                        <h3 class="text-lg font-semibold text-gray-800">Doctores Recientes</h3>
                        <div class="flex gap-2">
                            <button class="inline-flex items-center justify-center h-8 rounded-md px-3 text-xs font-medium border border-gray-200 bg-white text-gray-700 shadow-sm hover:bg-gray-50">
                                <i class="bi bi-calendar3 me-1"></i> Este mes
                            </button>
                        </div>
                    </div>
                    <?php if ($result_doctores->num_rows > 0): ?>
                        <div class="space-y-4">
                            <?php while ($doctor = $result_doctores->fetch_assoc()): ?>
                            <div class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                                <div class="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center flex-shrink-0">
                                    <i class="bi bi-person"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-medium text-gray-900 truncate"><?php echo htmlspecialchars($doctor['nombre'] . ' ' . $doctor['apellido']); ?></h4>
                                    <div class="flex items-center text-sm text-gray-500">
                                        <i class="bi bi-calendar3 me-1"></i>
                                        <span class="truncate">Desde: <?php echo date('d/m/Y', strtotime($doctor['fecha_creacion'])); ?></span>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 italic text-center">No hay datos suficientes</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts para gráficos -->
    <script>
        // Configuración común de Chart.js
        Chart.defaults.font.family = 'system-ui, sans-serif';
        Chart.defaults.font.size = 12;
        Chart.defaults.color = '#374151';
        Chart.defaults.responsive = true;
        Chart.defaults.maintainAspectRatio = false;

        // Función para ajustar el tamaño de los gráficos
        function resizeCharts() {
            const charts = [chartFormularios, chartUsuarios, chartTendencia, chartEstadoUsuarios];
            charts.forEach(chart => {
                if (chart) chart.resize();
            });
        }

        // Escuchar cambios de tamaño de ventana
        window.addEventListener('resize', resizeCharts);
        
        // Datos de los gráficos
        const datosFormularios = <?php echo $datos_formularios; ?>;
        const datosUsuarios = <?php echo $datos_usuarios; ?>;
        const datosEstadoUsuarios = <?php echo $datos_estado_usuarios; ?>;
        const datosPorMes = <?php echo $datos_por_mes; ?>;
        
        // Colores
        const colores = {
            pendientes: '#60a5fa', // blue-400
            aprobados: '#4ade80', // green-400
            rechazados: '#f87171', // red-400
            primary: '#2563eb', // blue-600
            admin: '#2563eb', // blue-600
            users: '#60a5fa', // blue-400
            active: '#4ade80' // green-400
        };
        
        // 1. Gráfico de Formularios (Donut)
        const ctxFormularios = document.getElementById('chartFormularios').getContext('2d');
        const chartFormularios = new Chart(ctxFormularios, {
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
                plugins: {
                    legend: {
                        position: window.innerWidth < 640 ? 'bottom' : 'right',
                        align: 'center'
                    }
                },
                cutout: '65%'
            }
        });
        
        // 2. Gráfico de Usuarios (Pie)
        const ctxUsuarios = document.getElementById('chartUsuarios').getContext('2d');
        const chartUsuarios = new Chart(ctxUsuarios, {
            type: 'pie',
            data: {
                labels: datosUsuarios.labels,
                datasets: [{
                    data: datosUsuarios.values,
                    backgroundColor: [colores.admin, colores.users],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                plugins: {
                    legend: {
                        position: window.innerWidth < 640 ? 'bottom' : 'right',
                        align: 'center'
                    }
                }
            }
        });
        
        // 3. Gráfico de Tendencia (Line)
        const ctxTendencia = document.getElementById('chartTendencia').getContext('2d');
        const chartTendencia = new Chart(ctxTendencia, {
            type: 'line',
            data: {
                labels: datosPorMes.labels,
                datasets: [
                    {
                        label: 'Total',
                        data: datosPorMes.totales,
                        borderColor: colores.primary,
                        backgroundColor: 'rgba(37, 99, 235, 0.1)',
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
                        position: window.innerWidth < 640 ? 'bottom' : 'top',
                        align: 'center'
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
        const chartEstadoUsuarios = new Chart(ctxEstadoUsuarios, {
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
                plugins: {
                    legend: {
                        position: window.innerWidth < 640 ? 'bottom' : 'right',
                        align: 'center'
                    }
                },
                cutout: '65%'
            }
        });

        // Ajustar posición de las leyendas al cambiar el tamaño de la ventana
        window.addEventListener('resize', () => {
            const isMobile = window.innerWidth < 640;
            const charts = [chartFormularios, chartUsuarios, chartEstadoUsuarios];
            charts.forEach(chart => {
                if (chart) {
                    chart.options.plugins.legend.position = isMobile ? 'bottom' : 'right';
                    chart.update();
                }
            });
            if (chartTendencia) {
                chartTendencia.options.plugins.legend.position = isMobile ? 'bottom' : 'top';
                chartTendencia.update();
            }
        });
    </script>
</body>
</html>
<?php
$conn->close();
?> 