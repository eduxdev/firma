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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        :root {
            --color-primary: #2c6e8f;
            --color-secondary: #48a5c5;
            --color-accent: #e9f5fb;
            --color-pending: #7c97ab;
            --color-approved: #4a8573;
            --color-rejected: #a17a7a;
            --color-light: #f8f9fa;
            --color-dark: #345464;
            --color-users: #5a7d9a;
            --color-admin: #3d4a54;
            --color-active: #4a8573;
        }
        
        body {
            background-color: var(--color-light);
            color: var(--color-dark);
        }
        
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
<body>
    <nav class="custom-navbar">
        <div class="container d-flex justify-content-between align-items-center">
            <img src="/public/assets/img/logo.jpg" alt="Logo">
            <div class="d-flex align-items-center">
                <span class="me-3 text-dark">
                    <i class="bi bi-person-badge-fill"></i> 
                    Administrador: <?php echo htmlspecialchars($_SESSION['doctor_nombre'] . ' ' . $_SESSION['doctor_apellido']); ?>
                </span>
                <a href="cerrar_sesion.php" class="btn btn-outline-primary">
                    <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Menú lateral -->
            <div class="col-md-3 col-lg-2">
                <div class="card">
                    <div class="card-header-primary">
                        <h5 class="mb-0"><i class="bi bi-speedometer2"></i> Panel Admin</h5>
                    </div>
                    <div class="card-body p-0">
                        <nav class="nav flex-column admin-menu p-2">
                            <a class="nav-link active" href="admin_panel.php">
                                <i class="bi bi-house-door"></i> Dashboard
                            </a>
                            <a class="nav-link" href="gestionar_doctores.php">
                                <i class="bi bi-people"></i> Gestionar Doctores
                            </a>
                            <a class="nav-link" href="formularios_pendientes.php">
                                <i class="bi bi-file-earmark-text"></i> Formularios
                            </a>
                            <a class="nav-link" href="configuracion.php">
                                <i class="bi bi-gear"></i> Configuración
                            </a>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Contenido principal -->
            <div class="col-md-9 col-lg-10">
                <h2 class="mb-4"><i class="bi bi-speedometer2"></i> Dashboard de Administración</h2>
                
                <!-- Resumen -->
                <div class="row mb-4">
                    <!-- Estadísticas de formularios -->
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header-primary">
                                <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Formularios</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 text-center">
                                        <div class="icon-box icon-pending">
                                            <i class="bi bi-hourglass-split fs-3"></i>
                                        </div>
                                        <h3><?php echo $conteo['pendientes']; ?></h3>
                                        <p>Pendientes</p>
                                    </div>
                                    <div class="col-md-4 text-center">
                                        <div class="icon-box icon-approved">
                                            <i class="bi bi-check-circle fs-3"></i>
                                        </div>
                                        <h3><?php echo $conteo['aprobados']; ?></h3>
                                        <p>Aprobados</p>
                                    </div>
                                    <div class="col-md-4 text-center">
                                        <div class="icon-box icon-rejected">
                                            <i class="bi bi-x-circle fs-3"></i>
                                        </div>
                                        <h3><?php echo $conteo['rechazados']; ?></h3>
                                        <p>Rechazados</p>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <a href="formularios_pendientes.php" class="btn btn-primary">
                                        <i class="bi bi-arrow-right"></i> Ver formularios
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Estadísticas de doctores -->
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header-users">
                                <h5 class="mb-0"><i class="bi bi-people"></i> Usuarios</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 text-center">
                                        <div class="icon-box icon-users">
                                            <i class="bi bi-person-check fs-3"></i>
                                        </div>
                                        <h3><?php echo $doctores['total_doctores']; ?></h3>
                                        <p>Total Usuarios</p>
                                    </div>
                                    <div class="col-md-4 text-center">
                                        <div class="icon-box icon-admin">
                                            <i class="bi bi-person-lock fs-3"></i>
                                        </div>
                                        <h3><?php echo $doctores['total_admins']; ?></h3>
                                        <p>Administradores</p>
                                    </div>
                                    <div class="col-md-4 text-center">
                                        <div class="icon-box icon-active">
                                            <i class="bi bi-person-check fs-3"></i>
                                        </div>
                                        <h3><?php echo $doctores['activos']; ?></h3>
                                        <p>Activos</p>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <a href="gestionar_doctores.php" class="btn btn-users">
                                        <i class="bi bi-person-plus"></i> Gestionar doctores
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Acciones rápidas -->
                <div class="card mb-4">
                    <div class="card-header-primary">
                        <h5 class="mb-0"><i class="bi bi-lightning"></i> Acciones Rápidas</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <a href="nuevo_doctor.php" class="btn action-btn action-btn-new w-100 h-100 d-flex flex-column justify-content-center align-items-center p-4">
                                    <i class="bi bi-person-plus fs-1"></i>
                                    <span class="mt-2">Nuevo Doctor</span>
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="formularios_pendientes.php" class="btn action-btn action-btn-pending w-100 h-100 d-flex flex-column justify-content-center align-items-center p-4">
                                    <i class="bi bi-hourglass-split fs-1"></i>
                                    <span class="mt-2">Pendientes</span>
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="estadisticas.php" class="btn action-btn action-btn-stats w-100 h-100 d-flex flex-column justify-content-center align-items-center p-4">
                                    <i class="bi bi-bar-chart fs-1"></i>
                                    <span class="mt-2">Estadísticas</span>
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="configuracion.php" class="btn action-btn action-btn-config w-100 h-100 d-flex flex-column justify-content-center align-items-center p-4">
                                    <i class="bi bi-gear fs-1"></i>
                                    <span class="mt-2">Configuración</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$conn->close();
?> 