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
        
        .btn-new {
            background-color: var(--color-approved);
            border-color: var(--color-approved);
            color: white;
        }
        
        .btn-new:hover {
            background-color: #3b7160;
            border-color: #3b7160;
        }
        
        .badge-admin {
            background-color: var(--color-admin);
        }
        
        .badge-doctor {
            background-color: var(--color-primary);
        }
        
        .badge-active {
            background-color: var(--color-approved);
        }
        
        .badge-inactive {
            background-color: var(--color-rejected);
        }
        
        .btn-activate {
            background-color: var(--color-approved);
            border-color: var(--color-approved);
            color: white;
        }
        
        .btn-activate:hover {
            background-color: #3b7160;
            border-color: #3b7160;
        }
        
        .btn-deactivate {
            background-color: var(--color-rejected);
            border-color: var(--color-rejected);
            color: white;
        }
        
        .btn-deactivate:hover {
            background-color: #855e5e;
            border-color: #855e5e;
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
                <a href="admin_panel.php" class="btn btn-primary mb-3">
                    <i class="bi bi-arrow-left"></i> Volver al Dashboard
                </a>
                <div class="card">
                    <div class="card-header-primary">
                        <h5 class="mb-0"><i class="bi bi-speedometer2"></i> Panel Admin</h5>
                    </div>
                    <div class="card-body p-0">
                        <nav class="nav flex-column admin-menu p-2">
                            <a class="nav-link" href="admin_panel.php">
                                <i class="bi bi-house-door"></i> Dashboard
                            </a>
                            <a class="nav-link active" href="gestionar_doctores.php">
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="bi bi-people"></i> Gestionar Doctores</h2>
                    <a href="nuevo_doctor.php" class="btn btn-new">
                        <i class="bi bi-person-plus"></i> Nuevo Doctor
                    </a>
                </div>
                
                <div class="card">
                    <div class="card-header-users">
                        <h5 class="mb-0"><i class="bi bi-list-ul"></i> Lista de Doctores</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Email</th>
                                        <th>Rol</th>
                                        <th>Estado</th>
                                        <th>Fecha Creación</th>
                                        <th>Última Sesión</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['nombre'] . ' ' . $row['apellido']); ?></td>
                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td>
                                            <?php if ($row['rol'] === 'admin'): ?>
                                                <span class="badge badge-admin">Administrador</span>
                                            <?php else: ?>
                                                <span class="badge badge-doctor">Doctor</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($row['activo']): ?>
                                                <span class="badge badge-active">Activo</span>
                                            <?php else: ?>
                                                <span class="badge badge-inactive">Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($row['fecha_creacion'])); ?></td>
                                        <td>
                                            <?php 
                                            if ($row['ultima_sesion']) {
                                                echo date('d/m/Y H:i', strtotime($row['ultima_sesion']));
                                            } else {
                                                echo '<span class="text-muted">Nunca</span>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php if ($row['id'] !== $_SESSION['doctor_id']): // No permitir desactivar tu propia cuenta ?>
                                                <a href="gestionar_doctores.php?toggle_id=<?php echo $row['id']; ?>&estado=<?php echo $row['activo']; ?>" 
                                                class="btn btn-sm <?php echo $row['activo'] ? 'btn-deactivate' : 'btn-activate'; ?>">
                                                    <?php if ($row['activo']): ?>
                                                        <i class="bi bi-person-x"></i> Desactivar
                                                    <?php else: ?>
                                                        <i class="bi bi-person-check"></i> Activar
                                                    <?php endif; ?>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <a href="editar_doctor.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="bi bi-pencil"></i> Editar
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if ($result->num_rows === 0): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> No hay doctores registrados.
                        </div>
                        <?php endif; ?>
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