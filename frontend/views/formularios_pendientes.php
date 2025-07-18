<?php
session_start();

// Verificar si el doctor está autenticado
if (!isset($_SESSION['doctor_id'])) {
    header('Location: login.php');
    exit();
}

// Configuración de la base de datos
require_once '../../backend/db/conection.php';

// Procesar eliminación de formulario si se recibe la solicitud
if (isset($_POST['eliminar_formulario']) && isset($_POST['form_id'])) {
    $form_id = (int)$_POST['form_id'];
    
    // Verificar que el formulario existe y está pendiente
    $check_sql = "SELECT id FROM formularios_consentimiento WHERE id = ? AND estado_revision = 'pendiente'";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $form_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        // Eliminar el formulario
        $delete_sql = "DELETE FROM formularios_consentimiento WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $form_id);
        
        if ($delete_stmt->execute()) {
            $_SESSION['success'] = "El formulario ha sido eliminado correctamente.";
        } else {
            $_SESSION['error'] = "Error al eliminar el formulario.";
        }
    } else {
        $_SESSION['error'] = "El formulario no existe o no está pendiente.";
    }
    
    // Redirigir para evitar reenvío del formulario
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Obtener término de búsqueda
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';

// Configuración de paginación
$registros_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Obtener total de registros para la paginación con búsqueda
$sql_total = "SELECT COUNT(*) as total FROM formularios_consentimiento 
              WHERE estado_revision = 'pendiente'";
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

// Obtener formularios pendientes con búsqueda
$sql = "SELECT id, nombre, apellido, fecha_creacion FROM formularios_consentimiento 
        WHERE estado_revision = 'pendiente'";
if (!empty($busqueda)) {
    $sql .= " AND (CONCAT(nombre, ' ', apellido) LIKE ?)";
}
$sql .= " ORDER BY fecha_creacion DESC LIMIT ? OFFSET ?";

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

// Configuración para el header
$titulo = "Formularios Pendientes";
$subtitulo = "Revisión de consentimientos médicos";
$url_volver = "admin_panel.php";


// Scripts adicionales para el header
$scripts_adicionales = '
<script src="//unpkg.com/alpinejs" defer></script>
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
    <title>Formularios Pendientes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="//unpkg.com/alpinejs" defer></script>
    <style>
        .main-content {
            margin-left: 16rem;
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        [x-cloak] { 
            display: none !important; 
        }
    </style>
</head>
<body class="h-full bg-[#f8f9fa]" x-data="{ showModal: false, formId: null, formNombre: '' }">
    <?php include 'menu_lateral.php'; ?>
    
    <div class="main-content">
        <?php include 'header.php'; ?>

        <main class="p-6">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700">
                    <i class="bi bi-check-circle me-2"></i><?php echo $_SESSION['success']; ?>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700">
                    <i class="bi bi-exclamation-circle me-2"></i><?php echo $_SESSION['error']; ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <!-- Estadísticas de Formularios -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Formularios Pendientes - Activo -->
                <div class="bg-amber-50 rounded-lg border-2 border-amber-200 shadow-md hover:shadow-lg transition-all duration-200 ring-2 ring-amber-100 ring-offset-2">
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
                        <a href="formularios_pendientes.php" class="inline-flex items-center text-sm text-amber-700 hover:text-amber-800 font-medium">
                            Ver formularios pendientes
                            <i class="bi bi-arrow-right ml-2"></i>
                        </a>
                    </div>
                </div>

                <!-- Formularios Aprobados - Inactivo -->
                <a href="formularios_aprobados.php" class="block">
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
                            <span class="inline-flex items-center text-sm text-green-600 hover:text-green-700">
                                Ver formularios aprobados
                                <i class="bi bi-arrow-right ml-2"></i>
                            </span>
                        </div>
                    </div>
                </a>

                <!-- Formularios Rechazados - Inactivo -->
                <a href="formularios_rechazados.php" class="block">
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
                            <span class="inline-flex items-center text-sm text-red-600 hover:text-red-700">
                                Ver formularios rechazados
                                <i class="bi bi-arrow-right ml-2"></i>
                            </span>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Lista de Formularios -->
            <div class="bg-white rounded-lg border border-gray-100 shadow-sm">
                <div class="p-6 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-800">Formularios Pendientes</h2>
                            <p class="text-sm text-gray-500 mt-1">Lista de formularios pendientes de revisión</p>
                        </div>
                        <!-- Buscador -->
                        <div class="flex items-center space-x-2">
                            <form action="" method="GET" class="flex items-center">
                                <div class="relative">
                                    <input type="text" 
                                           name="busqueda" 
                                           value="<?php echo htmlspecialchars($busqueda); ?>" 
                                           placeholder="Buscar por nombre..." 
                                           class="h-9 w-64 px-3 py-1 text-sm border border-gray-200 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-200 focus:border-gray-400 placeholder:text-gray-500">
                                    <button type="submit" class="absolute right-0 top-0 h-full px-3 text-gray-400 hover:text-gray-600">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="relative">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50/75 border-b border-gray-200">
                            <tr>
                                <th class="h-12 px-6 text-left align-middle font-medium text-gray-500">
                                    <div class="flex items-center space-x-2">
                                        <span>Nombre</span>
                                    </div>
                                </th>
                                <th class="h-12 px-6 text-left align-middle font-medium text-gray-500">
                                    <div class="flex items-center space-x-2">
                                        <span>Fecha</span>
                                    </div>
                                </th>
                                <th class="h-12 px-6 text-right align-middle font-medium text-gray-500">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 border-b border-gray-200">
                            <?php if ($result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50/50">
                                    <td class="p-4 px-6 align-middle">
                                        <div class="flex items-center gap-3">
                                            <div class="h-9 w-9 rounded-full bg-amber-100/75 flex items-center justify-center">
                                                <i class="bi bi-person text-amber-600"></i>
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-medium"><?php echo htmlspecialchars($row['nombre'] . ' ' . $row['apellido']); ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4 px-6 align-middle text-gray-700">
                                        <div class="flex flex-col">
                                            <span><?php echo date('d/m/Y', strtotime($row['fecha_creacion'])); ?></span>
                                            <span class="text-xs text-gray-500"><?php echo date('H:i', strtotime($row['fecha_creacion'])); ?></span>
                                        </div>
                                    </td>
                                    <td class="p-4 px-6 align-middle">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="ver_formulario.php?id=<?php echo $row['id']; ?>" 
                                               class="inline-flex items-center justify-center h-9 rounded-md px-3 text-sm font-medium border border-gray-200 bg-white text-gray-900 shadow-sm hover:bg-gray-100 hover:text-gray-900 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-gray-950 disabled:pointer-events-none disabled:opacity-50">
                                                <i class="bi bi-eye me-2"></i> Ver
                                            </a>
                                            <button type="button" 
                                                    @click="showModal = true; formId = <?php echo $row['id']; ?>; formNombre = '<?php echo htmlspecialchars($row['nombre'] . ' ' . $row['apellido']); ?>'"
                                                    class="inline-flex items-center justify-center h-9 rounded-md px-3 text-sm font-medium bg-red-600 text-white shadow-sm hover:bg-red-700 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-red-950 disabled:pointer-events-none disabled:opacity-50">
                                                <i class="bi bi-trash me-2"></i> Eliminar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="py-12">
                                        <div class="text-center">
                                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-amber-100 text-amber-600 mb-3">
                                                <i class="bi bi-inbox text-2xl"></i>
                                            </div>
                                            <h3 class="text-lg font-medium text-gray-900 mb-1">No hay formularios</h3>
                                            <p class="text-sm text-gray-500">No hay formularios pendientes de revisión en este momento.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_paginas > 1): ?>
                <div class="flex items-center justify-between px-6 py-4 border-t border-gray-200">
                    <div class="text-sm text-gray-500">
                        Página <span class="font-medium"><?php echo $pagina_actual; ?></span> de <span class="font-medium"><?php echo $total_paginas; ?></span>
                    </div>
                    <nav class="flex items-center space-x-2" aria-label="Navegación">
                        <a href="?pagina=<?php echo $pagina_actual - 1; ?><?php echo !empty($busqueda) ? '&busqueda=' . urlencode($busqueda) : ''; ?>" 
                           class="<?php echo ($pagina_actual <= 1) ? 'pointer-events-none opacity-50' : ''; ?> inline-flex items-center justify-center h-9 rounded-md px-3 text-sm font-medium border border-gray-200 bg-white text-gray-900 shadow-sm hover:bg-gray-100 hover:text-gray-900 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-gray-950 disabled:pointer-events-none disabled:opacity-50">
                            <i class="bi bi-chevron-left me-2"></i>
                            Anterior
                        </a>
                        <div class="flex items-center space-x-1">
                            <?php for($i = 1; $i <= $total_paginas; $i++): ?>
                            <a href="?pagina=<?php echo $i; ?><?php echo !empty($busqueda) ? '&busqueda=' . urlencode($busqueda) : ''; ?>" 
                               class="<?php echo ($pagina_actual == $i) ? 'bg-gray-900 text-white hover:bg-gray-800' : 'bg-white text-gray-900 hover:bg-gray-100'; ?> inline-flex items-center justify-center h-9 w-9 rounded-md text-sm font-medium border border-gray-200 shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-gray-950 disabled:pointer-events-none disabled:opacity-50">
                                <?php echo $i; ?>
                            </a>
                            <?php endfor; ?>
                        </div>
                        <a href="?pagina=<?php echo $pagina_actual + 1; ?><?php echo !empty($busqueda) ? '&busqueda=' . urlencode($busqueda) : ''; ?>" 
                           class="<?php echo ($pagina_actual >= $total_paginas) ? 'pointer-events-none opacity-50' : ''; ?> inline-flex items-center justify-center h-9 rounded-md px-3 text-sm font-medium border border-gray-200 bg-white text-gray-900 shadow-sm hover:bg-gray-100 hover:text-gray-900 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-gray-950 disabled:pointer-events-none disabled:opacity-50">
                            Siguiente
                            <i class="bi bi-chevron-right ms-2"></i>
                        </a>
                    </nav>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Modal de confirmación de eliminación -->
    <div x-cloak x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Overlay de fondo oscuro -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showModal = false"></div>

        <!-- Contenedor del modal -->
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
                     @click.away="showModal = false">
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="bi bi-exclamation-triangle text-red-600 text-xl"></i>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                <h3 class="text-base font-semibold leading-6 text-gray-900" id="modal-title">
                                    Confirmar Eliminación
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        ¿Estás seguro de que deseas eliminar el formulario de <span x-text="formNombre" class="font-medium"></span>? Esta acción no se puede deshacer.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <form action="" method="POST" class="inline-block">
                            <input type="hidden" name="form_id" :value="formId">
                            <input type="hidden" name="eliminar_formulario" value="1">
                            <button type="submit" 
                                    class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto">
                                Eliminar
                            </button>
                        </form>
                        <button type="button" 
                                @click="showModal = false"
                                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
?> 