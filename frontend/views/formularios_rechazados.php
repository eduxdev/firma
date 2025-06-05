@<?php
session_start();

// Verificar si el doctor está autenticado
if (!isset($_SESSION['doctor_id'])) {
    header('Location: login.php');
    exit();
}

// Configuración de la base de datos
require_once '../../backend/db/conection.php';

// Obtener formularios rechazados
$sql = "SELECT id, nombre, apellido, fecha_creacion, fecha_revision, comentarios_doctor FROM formularios_consentimiento WHERE estado_revision = 'rechazado' ORDER BY fecha_revision DESC";
$result = $conn->query($sql);

// Contar formularios por estado
$sql_conteo = "SELECT 
    SUM(CASE WHEN estado_revision = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
    SUM(CASE WHEN estado_revision = 'aprobado' THEN 1 ELSE 0 END) as aprobados,
    SUM(CASE WHEN estado_revision = 'rechazado' THEN 1 ELSE 0 END) as rechazados
FROM formularios_consentimiento";
$result_conteo = $conn->query($sql_conteo);
$conteo = $result_conteo->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Doctor - Formularios Rechazados</title>
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
        
        .card-pending {
            background-color: var(--color-pending);
            color: white;
            border-left: 5px solid #5c7a91;
        }
        
        .card-approved {
            background-color: var(--color-approved);
            color: white;
            border-left: 5px solid #346755;
        }
        
        .card-rejected {
            background-color: var(--color-rejected);
            color: white;
            border-left: 5px solid #855e5e;
        }
        
        .nav-pills .nav-link {
            color: var(--color-dark);
            padding: 8px 16px;
            border-radius: 4px;
        }
        
        .nav-pills .nav-link.active {
            background-color: var(--color-primary);
            color: white;
        }
        
        .nav-pills .nav-link:hover:not(.active) {
            background-color: var(--color-accent);
        }
        
        .table {
            border-radius: 8px;
            overflow: hidden;
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
        }
        
        .card-header-custom {
            background-color: var(--color-rejected);
            color: white;
            padding: 15px;
            border-radius: 8px 8px 0 0;
        }
        
        .btn-outline-secondary {
            color: var(--color-rejected);
            border-color: var(--color-rejected);
        }
        
        .btn-outline-secondary:hover {
            background-color: var(--color-rejected);
            border-color: var(--color-rejected);
            color: white;
        }
    </style>
</head>
<body>
    <nav class="custom-navbar">
        <div class="container d-flex justify-content-between align-items-center">
            <img src="/public/assets/img/logo.jpg" alt="Logo">
            <div class="d-flex align-items-center">
                <span class="me-3 text-dark">Dr. <?php echo htmlspecialchars($_SESSION['doctor_nombre'] . ' ' . $_SESSION['doctor_apellido']); ?></span>
                <a href="cerrar_sesion.php" class="btn btn-outline-primary">
                    <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <?php if (isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'admin'): ?>
        <div class="mb-4">
            <a href="admin_panel.php" class="btn btn-primary">
                <i class="bi bi-arrow-left"></i> Volver al Dashboard
            </a>
        </div>
        <?php endif; ?>
        
        <!-- Resumen de formularios -->
        <div class="row mb-4">
            <div class="col-md-4">
                <a href="formularios_pendientes.php" class="text-decoration-none">
                    <div class="card card-pending">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-hourglass-split me-2"></i>Pendientes</h5>
                            <p class="card-text display-6"><?php echo $conteo['pendientes']; ?></p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="formularios_aprobados.php" class="text-decoration-none">
                    <div class="card card-approved">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-check-circle me-2"></i>Aprobados</h5>
                            <p class="card-text display-6"><?php echo $conteo['aprobados']; ?></p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="formularios_rechazados.php" class="text-decoration-none">
                    <div class="card card-rejected">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-x-circle me-2"></i>Rechazados</h5>
                            <p class="card-text display-6"><?php echo $conteo['rechazados']; ?></p>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Navegación de pestañas -->
        <ul class="nav nav-pills mb-4">
            <li class="nav-item">
                <a class="nav-link" href="formularios_pendientes.php">
                    <i class="bi bi-hourglass-split me-2"></i>Pendientes
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="formularios_aprobados.php">
                    <i class="bi bi-check-circle me-2"></i>Aprobados
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="formularios_rechazados.php">
                    <i class="bi bi-x-circle me-2"></i>Rechazados
                </a>
            </li>
            <li class="nav-item ms-auto">
                <a class="nav-link" href="configuracion.php">
                    <i class="bi bi-gear me-2"></i>Configuración
                </a>
            </li>
            <?php if (isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'admin'): ?>
            <li class="nav-item">
                <a class="nav-link" href="admin_panel.php">
                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                </a>
            </li>
            <?php endif; ?>
        </ul>

        <div class="card">
            <div class="card-header-custom">
                <h4><i class="bi bi-x-circle me-2"></i>Formularios Rechazados</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Fecha de Creación</th>
                                <th>Fecha de Rechazo</th>
                                <th>Motivo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['nombre'] . ' ' . $row['apellido']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($row['fecha_creacion'])); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($row['fecha_revision'])); ?></td>
                                <td>
                                    <?php if (!empty($row['comentarios_doctor'])): ?>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo htmlspecialchars($row['comentarios_doctor']); ?>">
                                            <i class="bi bi-info-circle"></i> Ver motivo
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted">Sin comentarios</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="ver_formulario.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">
                                        <i class="bi bi-eye"></i> Ver Detalles
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($result->num_rows == 0): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> No hay formularios rechazados.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Inicializar tooltips de Bootstrap
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    </script>
</body>
</html>
<?php
$conn->close();
?> 