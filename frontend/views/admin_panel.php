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
        /* Eliminar estas líneas porque ya están en admin-style.css */
        
        .custom-navbar {
            background-color: #ffffff;
            color: var(--color-dark);
            padding: 15px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .custom-navbar img {
            height: 50px;
        }
        
        .card {
            border-radius: 8px;
            box-shadow: 0 3px 5px rgba(0,0,0,0.08);
            transition: transform 0.2s;
            border: none;
        }
        
        .card:hover {
            transform: translateY(-3px);
        }
        
        .nav-pills .nav-link.active {
            background-color: var(--color-primary);
            color: white;
        }
        
        .table {
            border-radius: 8px;
            overflow: hidden;
        }
        
        .admin-menu .nav-link {
            color: var(--color-dark);
            border-radius: 5px;
            margin-bottom: 5px;
            padding: 10px;
        }
        
        .admin-menu .nav-link:hover, 
        .admin-menu .nav-link.active {
            background-color: var(--color-primary);
            color: white;
        }
        
        .admin-menu .nav-link i {
            width: 25px;
            text-align: center;
        }
        
        .card-header-primary {
            background-color: var(--color-primary);
            color: white;
            padding: 15px;
            border-radius: 8px 8px 0 0;
        }
        
        .card-header-users {
            background-color: var(--color-users);
            color: white;
            padding: 15px;
            border-radius: 8px 8px 0 0;
        }
        
        .btn-primary {
            background-color: var(--color-primary);
            border-color: var(--color-primary);
        }
        
        .btn-primary:hover {
            background-color: #1d5977;
            border-color: #1d5977;
        }
        
        .btn-outline-primary {
            color: var(--color-primary);
            border-color: var(--color-primary);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--color-primary);
            border-color: var(--color-primary);
            color: white;
        }
        
        .btn-users {
            background-color: var(--color-users);
            border-color: var(--color-users);
            color: white;
        }
        
        .btn-users:hover {
            background-color: #486c89;
            border-color: #486c89;
            color: white;
        }
        
        .icon-box {
            padding: 15px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }
        
        .icon-pending {
            background-color: var(--color-pending);
            color: white;
        }
        
        .icon-approved {
            background-color: var(--color-approved);
            color: white;
        }
        
        .icon-rejected {
            background-color: var(--color-rejected);
            color: white;
        }
        
        .icon-users {
            background-color: var(--color-users);
            color: white;
        }
        
        .icon-admin {
            background-color: var(--color-admin);
            color: white;
        }
        
        .icon-active {
            background-color: var(--color-active);
            color: white;
        }
        
        .action-btn {
            height: 100%;
            transition: all 0.2s;
            border-radius: 8px;
            border-left: 5px solid rgba(0,0,0,0.2);
        }
        
        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .action-btn-new {
            background-color: var(--color-approved);
            border-color: var(--color-approved);
        }
        
        .action-btn-new:hover {
            background-color: #3b7160;
            border-color: #3b7160;
        }
        
        .action-btn-pending {
            background-color: var(--color-pending);
            border-color: var(--color-pending);
        }
        
        .action-btn-pending:hover {
            background-color: #617d90;
            border-color: #617d90;
        }
        
        .action-btn-stats {
            background-color: var(--color-users);
            border-color: var(--color-users);
        }
        
        .action-btn-stats:hover {
            background-color: #486c89;
            border-color: #486c89;
        }
        
        .action-btn-config {
            background-color: var(--color-admin);
            border-color: var(--color-admin);
        }
        
        .action-btn-config:hover {
            background-color: #2e3841;
            border-color: #2e3841;
        }
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
                <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow mb-6">
                    <div class="bg-primary text-white p-4 rounded-t-lg">
                        <h5 class="m-0 font-medium"><i class="bi bi-speedometer2"></i> Panel Admin</h5>
                    </div>
                    <div class="p-0">
                        <nav class="flex flex-col p-2">
                            <a class="py-2 px-3 rounded mb-1 flex items-center text-dark hover:bg-primary hover:text-white bg-primary text-white" href="admin_panel.php">
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
                            <a class="py-2 px-3 rounded mb-1 flex items-center text-dark hover:bg-primary hover:text-white" href="configuracion.php">
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
                            <div class="bg-primary text-white p-4 rounded-t-lg">
                                <h5 class="m-0 font-medium"><i class="bi bi-file-earmark-text"></i> Formularios</h5>
                            </div>
                            <div class="p-4">
                                <div class="flex flex-wrap -mx-2">
                                    <div class="w-1/3 px-2 text-center">
                                        <div class="flex items-center justify-center bg-pending text-white p-4 rounded-lg mb-4">
                                            <i class="bi bi-hourglass-split text-2xl"></i>
                                        </div>
                                        <h3 class="text-xl font-bold"><?php echo $conteo['pendientes']; ?></h3>
                                        <p>Pendientes</p>
                                    </div>
                                    <div class="w-1/3 px-2 text-center">
                                        <div class="flex items-center justify-center bg-approved text-white p-4 rounded-lg mb-4">
                                            <i class="bi bi-check-circle text-2xl"></i>
                                        </div>
                                        <h3 class="text-xl font-bold"><?php echo $conteo['aprobados']; ?></h3>
                                        <p>Aprobados</p>
                                    </div>
                                    <div class="w-1/3 px-2 text-center">
                                        <div class="flex items-center justify-center bg-rejected text-white p-4 rounded-lg mb-4">
                                            <i class="bi bi-x-circle text-2xl"></i>
                                        </div>
                                        <h3 class="text-xl font-bold"><?php echo $conteo['rechazados']; ?></h3>
                                        <p>Rechazados</p>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <a href="formularios_pendientes.php" class="inline-block px-4 py-2 bg-primary text-white rounded hover:bg-opacity-90">
                                        <i class="bi bi-arrow-right"></i> Ver formularios
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Estadísticas de doctores -->
                    <div class="w-full md:w-1/2 px-2 mb-4">
                        <div class="bg-white rounded-lg shadow-sm h-full">
                            <div class="bg-users text-white p-4 rounded-t-lg">
                                <h5 class="m-0 font-medium"><i class="bi bi-people"></i> Usuarios</h5>
                            </div>
                            <div class="p-4">
                                <div class="flex flex-wrap -mx-2">
                                    <div class="w-1/3 px-2 text-center">
                                        <div class="flex items-center justify-center bg-users text-white p-4 rounded-lg mb-4">
                                            <i class="bi bi-person-check text-2xl"></i>
                                        </div>
                                        <h3 class="text-xl font-bold"><?php echo $doctores['total_doctores']; ?></h3>
                                        <p>Total Usuarios</p>
                                    </div>
                                    <div class="w-1/3 px-2 text-center">
                                        <div class="flex items-center justify-center bg-admin text-white p-4 rounded-lg mb-4">
                                            <i class="bi bi-person-lock text-2xl"></i>
                                        </div>
                                        <h3 class="text-xl font-bold"><?php echo $doctores['total_admins']; ?></h3>
                                        <p>Administradores</p>
                                    </div>
                                    <div class="w-1/3 px-2 text-center">
                                        <div class="flex items-center justify-center bg-active text-white p-4 rounded-lg mb-4">
                                            <i class="bi bi-person-check text-2xl"></i>
                                        </div>
                                        <h3 class="text-xl font-bold"><?php echo $doctores['activos']; ?></h3>
                                        <p>Activos</p>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <a href="gestionar_doctores.php" class="inline-block px-4 py-2 bg-users text-white rounded hover:bg-opacity-90">
                                        <i class="bi bi-person-plus"></i> Gestionar doctores
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Acciones rápidas -->
                <div class="bg-white rounded-lg shadow-sm mb-6">
                    <div class="bg-primary text-white p-4 rounded-t-lg">
                        <h5 class="m-0 font-medium"><i class="bi bi-lightning"></i> Acciones Rápidas</h5>
                    </div>
                    <div class="p-4">
                        <div class="flex flex-wrap -mx-2">
                            <div class="w-full md:w-1/4 px-2 mb-4">
                                <a href="nuevo_doctor.php" class="flex flex-col items-center justify-center p-6 bg-approved text-white rounded-lg border-l-4 border-approved-dark h-full hover:shadow-lg transition-shadow">
                                    <i class="bi bi-person-plus text-4xl"></i>
                                    <span class="mt-2">Nuevo Doctor</span>
                                </a>
                            </div>
                            <div class="w-full md:w-1/4 px-2 mb-4">
                                <a href="formularios_pendientes.php" class="flex flex-col items-center justify-center p-6 bg-pending text-white rounded-lg border-l-4 border-pending-dark h-full hover:shadow-lg transition-shadow">
                                    <i class="bi bi-hourglass-split text-4xl"></i>
                                    <span class="mt-2">Pendientes</span>
                                </a>
                            </div>
                            <div class="w-full md:w-1/4 px-2 mb-4">
                                <a href="estadisticas.php" class="flex flex-col items-center justify-center p-6 bg-users text-white rounded-lg border-l-4 border-users-dark h-full hover:shadow-lg transition-shadow">
                                    <i class="bi bi-bar-chart text-4xl"></i>
                                    <span class="mt-2">Estadísticas</span>
                                </a>
                            </div>
                            <div class="w-full md:w-1/4 px-2 mb-4">
                                <a href="configuracion.php" class="flex flex-col items-center justify-center p-6 bg-admin text-white rounded-lg border-l-4 border-admin-dark h-full hover:shadow-lg transition-shadow">
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

    <!-- Alpine.js para interactividad, alternativa ligera a jQuery/Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>
</html>
<?php
$conn->close();
?> 