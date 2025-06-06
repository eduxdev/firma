<?php
session_start();

// Verificar si el doctor está autenticado
if (!isset($_SESSION['doctor_id'])) {
    header('Location: login.php');
    exit();
}

// Configuración de la base de datos
require_once '../../backend/db/conection.php';

// Obtener término de búsqueda
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';

// Configuración de paginación
$registros_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Obtener total de registros para la paginación con búsqueda
$sql_total = "SELECT COUNT(*) as total FROM formularios_consentimiento 
              WHERE estado_revision = 'aprobado'";
if (!empty($busqueda)) {
    $busqueda_param = "%$busqueda%";
    $sql_total .= " AND (CONCAT(nombre, ' ', apellido) LIKE ?)";
}
$stmt_total = $conn->prepare($sql_total);
if (!empty($busqueda)) {
    $stmt_total->bind_param("s", $busqueda_param);
}
$stmt_total->execute();
$result_total = $stmt_total->get_result();
$total_registros = $result_total->fetch_assoc()['total'];
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Obtener formularios aprobados con búsqueda
$sql = "SELECT id, nombre, apellido, fecha_creacion, fecha_revision 
        FROM formularios_consentimiento 
        WHERE estado_revision = 'aprobado'";
if (!empty($busqueda)) {
    $sql .= " AND (CONCAT(nombre, ' ', apellido) LIKE ?)";
}
$sql .= " ORDER BY fecha_revision DESC LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
if (!empty($busqueda)) {
    $stmt->bind_param("sii", $busqueda_param, $registros_por_pagina, $offset);
} else {
    $stmt->bind_param("ii", $registros_por_pagina, $offset);
}
$stmt->execute();
$result = $stmt->get_result();

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
    <title>Panel del Doctor - Formularios Aprobados</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="//unpkg.com/alpinejs" defer></script>
    <style>
        [x-cloak] { 
            display: none !important; 
        }
    </style>
</head>
<body class="bg-gray-50" x-data="{ showModal: false, formId: null }">
    <!-- Modal de confirmación -->
    <div x-cloak x-show="showModal" 
         class="fixed inset-0 z-50">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-black bg-opacity-50" @click="showModal = false"></div>

        <!-- Modal -->
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg shadow-xl p-6 max-w-sm mx-auto relative"
                 @click.away="showModal = false">
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                        <i class="bi bi-exclamation-triangle text-red-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Confirmar Eliminación</h3>
                    <p class="text-sm text-gray-500 mb-6">¿Estás seguro de que deseas eliminar este formulario? Esta acción no se puede deshacer.</p>
                </div>
                <div class="flex justify-center space-x-3">
                    <form action="borrar_formulario.php" method="POST">
                        <input type="hidden" name="id" :value="formId">
                        <input type="hidden" name="confirmar" value="1">
                        <button type="submit" 
                                class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-red-400 rounded-md hover:bg-red-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-red-500">
                            Eliminar
                        </button>
                    </form>
                    <button @click="showModal = false" 
                            class="inline-flex justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-gray-500">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Navbar -->
    <nav class="bg-white shadow">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <img src="/public/assets/img/logo.jpg" alt="Logo" class="h-12">
            <div class="flex items-center">
                <span class="mr-4 text-gray-700">Dr. <?php echo htmlspecialchars($_SESSION['doctor_nombre'] . ' ' . $_SESSION['doctor_apellido']); ?></span>
                <a href="cerrar_sesion.php" class="inline-flex items-center px-4 py-2 border border-blue-600 text-blue-600 rounded-md hover:bg-blue-600 hover:text-white transition-colors">
                    <i class="bi bi-box-arrow-right mr-2"></i> Cerrar Sesión
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 mt-8">
        <?php if (isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'admin'): ?>
        <div class="mb-6">
            <a href="admin_panel.php" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                <i class="bi bi-arrow-left mr-2"></i> Volver al Dashboard
            </a>
        </div>
        <?php endif; ?>
        
        <!-- Resumen de formularios -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <a href="formularios_pendientes.php" class="block">
                <div class="bg-blue-400 text-white rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-1 transition-all duration-200">
                    <div class="p-6 border-l-4 border-blue-600">
                        <h5 class="text-lg font-semibold mb-2"><i class="bi bi-hourglass-split mr-2"></i>Pendientes</h5>
                        <p class="text-4xl font-bold"><?php echo $conteo['pendientes']; ?></p>
                    </div>
                </div>
            </a>
            <a href="formularios_aprobados.php" class="block">
                <div class="bg-green-400 text-white rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-1 transition-all duration-200">
                    <div class="p-6 border-l-4 border-green-600">
                        <h5 class="text-lg font-semibold mb-2"><i class="bi bi-check-circle mr-2"></i>Aprobados</h5>
                        <p class="text-4xl font-bold"><?php echo $conteo['aprobados']; ?></p>
                    </div>
                </div>
            </a>
            <a href="formularios_rechazados.php" class="block">
                <div class="bg-red-400 text-white rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-1 transition-all duration-200">
                    <div class="p-6 border-l-4 border-red-600">
                        <h5 class="text-lg font-semibold mb-2"><i class="bi bi-x-circle mr-2"></i>Rechazados</h5>
                        <p class="text-4xl font-bold"><?php echo $conteo['rechazados']; ?></p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Navegación de pestañas -->
        <div class="flex flex-wrap items-center mb-6 border-b border-gray-200">
            <nav class="flex space-x-4">
                <a href="formularios_pendientes.php<?php echo !empty($busqueda) ? '?busqueda=' . urlencode($busqueda) : ''; ?>" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-t-md">
                    <i class="bi bi-hourglass-split mr-2"></i>Pendientes
                </a>
                <a href="formularios_aprobados.php<?php echo !empty($busqueda) ? '?busqueda=' . urlencode($busqueda) : ''; ?>" class="px-4 py-2 text-sm font-medium text-white bg-green-400 rounded-t-md">
                    <i class="bi bi-check-circle mr-2"></i>Aprobados
                </a>
                <a href="formularios_rechazados.php<?php echo !empty($busqueda) ? '?busqueda=' . urlencode($busqueda) : ''; ?>" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-t-md">
                    <i class="bi bi-x-circle mr-2"></i>Rechazados
                </a>
            </nav>
            <div class="ml-auto flex items-center space-x-4">
                <!-- Campo de búsqueda -->
                <form action="" method="GET" class="flex items-center">
                    <div class="relative">
                        <input type="text" 
                               name="busqueda" 
                               value="<?php echo htmlspecialchars($busqueda); ?>" 
                               placeholder="Buscar por nombre..." 
                               class="w-64 px-4 py-2 pr-10 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <button type="submit" class="absolute right-0 top-0 mt-2 mr-3 text-gray-400 hover:text-gray-600">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>
                <a href="configuracion.php" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-md">
                    <i class="bi bi-gear mr-2"></i>Configuración
                </a>
                <?php if (isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'admin'): ?>
                <a href="admin_panel.php" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-md">
                    <i class="bi bi-speedometer2 mr-2"></i>Dashboard
                </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md">
            <div class="bg-green-400 text-white px-6 py-4 rounded-t-lg">
                <h4 class="text-xl font-semibold"><i class="bi bi-check-circle mr-2"></i>Formularios Aprobados</h4>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha de Creación</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha de Aprobación</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo htmlspecialchars($row['nombre'] . ' ' . $row['apellido']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('d/m/Y H:i', strtotime($row['fecha_creacion'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('d/m/Y H:i', strtotime($row['fecha_revision'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="flex items-center space-x-2">
                                        <a href="ver_formulario.php?id=<?php echo $row['id']; ?>" 
                                           class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                            <i class="bi bi-eye me-1"></i> Ver
                                        </a>
                                        <a href="generar_pdf.php?id=<?php echo $row['id']; ?>" 
                                           target="_blank"
                                           class="inline-flex items-center px-3 py-1.5 bg-green-400 text-white text-sm font-medium rounded-md hover:bg-green-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                                            <i class="bi bi-file-pdf me-1"></i> PDF
                                        </a>
                                        <button type="button" 
                                                @click="showModal = true; formId = <?php echo $row['id']; ?>"
                                                class="inline-flex items-center px-3 py-1.5 bg-red-400 text-white text-sm font-medium rounded-md hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-400 transition-colors">
                                            <i class="bi bi-trash me-1"></i> Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($result->num_rows == 0): ?>
                <div class="bg-blue-50 text-blue-700 p-4 rounded-md mt-4">
                    <i class="bi bi-info-circle mr-2"></i> No hay formularios aprobados.
                </div>
                <?php endif; ?>

                <?php if ($total_paginas > 1): ?>
                <nav class="flex justify-center mt-6" aria-label="Navegación de páginas">
                    <ul class="flex space-x-2">
                        <li>
                            <a href="?pagina=<?php echo $pagina_actual - 1; ?><?php echo !empty($busqueda) ? '&busqueda=' . urlencode($busqueda) : ''; ?>" 
                               class="<?php echo ($pagina_actual <= 1) ? 'opacity-50 cursor-not-allowed' : ''; ?> px-3 py-2 text-sm font-medium text-blue-600 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                Anterior
                            </a>
                        </li>
                        <?php for($i = 1; $i <= $total_paginas; $i++): ?>
                        <li>
                            <a href="?pagina=<?php echo $i; ?><?php echo !empty($busqueda) ? '&busqueda=' . urlencode($busqueda) : ''; ?>" 
                               class="<?php echo ($pagina_actual == $i) ? 'bg-blue-600 text-white' : 'text-blue-600 hover:bg-gray-50'; ?> px-3 py-2 text-sm font-medium border border-gray-300 rounded-md">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        <li>
                            <a href="?pagina=<?php echo $pagina_actual + 1; ?><?php echo !empty($busqueda) ? '&busqueda=' . urlencode($busqueda) : ''; ?>" 
                               class="<?php echo ($pagina_actual >= $total_paginas) ? 'opacity-50 cursor-not-allowed' : ''; ?> px-3 py-2 text-sm font-medium text-blue-600 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                Siguiente
                            </a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
?> 