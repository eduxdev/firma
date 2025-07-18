<?php
// Verificar si las variables necesarias están definidas
$menu_activo = $menu_activo ?? '';

// Obtener conteos de formularios
require_once '../../backend/db/conection.php';
$sql_conteo = "SELECT 
    SUM(CASE WHEN estado_revision = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
    SUM(CASE WHEN estado_revision = 'aprobado' THEN 1 ELSE 0 END) as aprobados,
    SUM(CASE WHEN estado_revision = 'rechazado' THEN 1 ELSE 0 END) as rechazados
FROM formularios_consentimiento";
$result_conteo = $conn->query($sql_conteo);
$conteo = $result_conteo->fetch_assoc();

// Detectar la página actual basada en el nombre del archivo
$current_page = basename($_SERVER['PHP_SELF']);
switch ($current_page) {
    case 'admin_panel.php':
        $menu_activo = 'dashboard';
        break;
    case 'formularios_pendientes.php':
        $menu_activo = 'pendientes';
        break;
    case 'formularios_aprobados.php':
        $menu_activo = 'aprobados';
        break;
    case 'formularios_rechazados.php':
        $menu_activo = 'rechazados';
        break;
    case 'gestionar_doctores.php':
        $menu_activo = 'doctores';
        break;
    case 'estadisticas.php':
        $menu_activo = 'estadisticas';
        break;
    case 'configuracion.php':
        $menu_activo = 'configuracion';
        break;
}

// Función para crear el submenú de formularios con badge solo en pendientes
function crearSubmenuFormularios($conteo) {
    return [
        [
            'titulo' => 'Pendientes',
            'url' => 'formularios_pendientes.php',
            'icono' => 'bi-clock',
            'id' => 'pendientes',
            'badge' => $conteo['pendientes'] > 0 ? [
                'numero' => $conteo['pendientes'],
                'clase' => 'bg-amber-100 text-amber-700'
            ] : null
        ],
        [
            'titulo' => 'Aprobados',
            'url' => 'formularios_aprobados.php',
            'icono' => 'bi-check-circle',
            'id' => 'aprobados'
        ],
        [
            'titulo' => 'Rechazados',
            'url' => 'formularios_rechazados.php',
            'icono' => 'bi-x-circle',
            'id' => 'rechazados'
        ]
    ];
}

// Definir los elementos del menú según el rol del usuario
$menu_items = [];

if ($_SESSION['user_rol'] === 'admin') {
    // Menú para administradores
    $menu_items = [
        [
            'titulo' => 'Dashboard',
            'url' => 'admin_panel.php',
            'icono' => 'bi-grid-1x2',
            'id' => 'dashboard'
        ],
        [
            'titulo' => 'Formularios',
            'url' => '#',
            'icono' => 'bi-file-text',
            'id' => 'formularios',
            'submenu' => crearSubmenuFormularios($conteo)
        ],
        [
            'titulo' => 'Doctores',
            'url' => 'gestionar_doctores.php',
            'icono' => 'bi-people',
            'id' => 'doctores'
        ],
        [
            'titulo' => 'Estadísticas',
            'url' => 'estadisticas.php',
            'icono' => 'bi-bar-chart',
            'id' => 'estadisticas'
        ],
        [
            'titulo' => 'Configuración',
            'url' => 'configuracion.php',
            'icono' => 'bi-gear',
            'id' => 'configuracion'
        ]
    ];
} else {
    // Menú para doctores
    $menu_items = [
        [
            'titulo' => 'Formularios',
            'url' => '#',
            'icono' => 'bi-file-text',
            'id' => 'formularios',
            'submenu' => crearSubmenuFormularios($conteo)
        ],
        [
            'titulo' => 'Configuración',
            'url' => 'configuracion.php',
            'icono' => 'bi-gear',
            'id' => 'configuracion'
        ]
    ];
}

// Definir las clases para los estados del menú
$menu_item_base = "group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200";
$menu_item_active = "bg-blue-50 text-blue-700";
$menu_item_inactive = "text-gray-600 hover:bg-gray-50 hover:text-gray-900";

// Definir las clases para los subelementos del menú
$submenu_item_base = "flex items-center justify-between rounded-lg px-3 py-2 text-sm font-medium transition-colors w-full";
$submenu_item_active = "bg-blue-50 text-blue-700";
$submenu_item_inactive = "text-gray-600 hover:bg-gray-50 hover:text-gray-900";
?>

<!-- Menú Lateral -->
<div x-data="{ menuAbierto: false }" @keydown.escape="menuAbierto = false">
    <!-- Botón de menú móvil -->
    <button @click="menuAbierto = !menuAbierto" 
            type="button"
            class="menu-toggle lg:hidden fixed top-4 left-4 p-2 rounded-lg bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 focus:outline-none">
        <i class="bi" :class="menuAbierto ? 'bi-x-lg' : 'bi-list'"></i>
    </button>

    <!-- Overlay para móvil -->
    <div x-show="menuAbierto" 
         class="menu-overlay lg:hidden fixed inset-0 bg-gray-800/40 backdrop-blur-sm"
         @click="menuAbierto = false">
    </div>

    <!-- Menú lateral -->
    <aside class="fixed left-0 top-0 h-screen w-64 border-r border-gray-200 bg-white lg:block"
           :class="menuAbierto ? 'block' : 'hidden'">
        <div class="flex h-full flex-col">
            <!-- Logo y Título -->
            <div class="flex h-16 items-center px-6">
                <a href="admin_panel.php" 
                   @click="menuAbierto = false" 
                   class="flex items-center gap-3">
                    <div class="hidden lg:flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600 text-white">
                        <i class="bi bi-heart-pulse text-xl"></i>
                    </div>
                    <span class="font-semibold text-gray-800 lg:ml-0 ml-10">Panel Médico</span>
                </a>
            </div>

            <!-- Navegación -->
            <nav class="flex-1 overflow-auto px-3 py-4">
                <ul class="flex flex-col gap-1">
                    <?php foreach ($menu_items as $item): ?>
                        <li>
                            <?php if (isset($item['submenu'])): ?>
                                <!-- Elemento con Submenú -->
                                <div x-data="{ open: true }">
                                    <button @click="open = !open" 
                                            class="<?php echo $menu_item_base . ' w-full justify-between ' . (in_array($menu_activo, array_column($item['submenu'], 'id')) ? $menu_item_active : $menu_item_inactive); ?>">
                                        <div class="flex items-center gap-3">
                                            <i class="bi <?php echo $item['icono']; ?> text-lg transition-transform group-hover:scale-110"></i>
                                            <span><?php echo $item['titulo']; ?></span>
                                        </div>
                                        <i class="bi bi-chevron-down text-sm transition-transform" :class="{ 'rotate-180': !open }"></i>
                                    </button>
                                    
                                    <div x-show="open" 
                                         class="mt-1 space-y-1 px-3">
                                        <?php foreach ($item['submenu'] as $subitem): ?>
                                            <a href="<?php echo $subitem['url']; ?>" 
                                               @click="menuAbierto = false"
                                               class="<?php echo $submenu_item_base . ' ' . ($menu_activo === $subitem['id'] ? $submenu_item_active : $submenu_item_inactive); ?>">
                                                <div class="flex items-center gap-2">
                                                    <i class="bi <?php echo $subitem['icono']; ?>"></i>
                                                    <?php echo $subitem['titulo']; ?>
                                                </div>
                                                <?php if (isset($subitem['badge']) && $subitem['badge']): ?>
                                                    <span class="inline-flex items-center justify-center rounded-full px-2.5 py-0.5 text-xs font-medium <?php echo $subitem['badge']['clase']; ?>">
                                                        <?php echo $subitem['badge']['numero']; ?>
                                                    </span>
                                                <?php endif; ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <!-- Elemento Simple -->
                                <a href="<?php echo $item['url']; ?>" 
                                   @click="menuAbierto = false"
                                   class="<?php echo $menu_item_base . ' ' . ($menu_activo === $item['id'] ? $menu_item_active : $menu_item_inactive); ?>">
                                    <i class="bi <?php echo $item['icono']; ?> text-lg transition-transform group-hover:scale-110"></i>
                                    <span><?php echo $item['titulo']; ?></span>
                                </a>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>

            <!-- Footer del Menú -->
            <div class="border-t border-gray-200 p-4">
                <a href="cerrar_sesion.php" 
                   @click="menuAbierto = false"
                   class="flex w-full items-center justify-center gap-2 rounded-lg bg-red-50 px-3 py-2 text-sm font-medium text-red-600 transition-all duration-200 hover:bg-red-100 hover:text-red-700">
                    <i class="bi bi-box-arrow-right text-lg"></i>
                    Cerrar Sesión
                </a>
            </div>
        </div>
    </aside>
</div>

<!-- Alpine.js para el menú desplegable -->
<script src="//unpkg.com/alpinejs" defer></script> 