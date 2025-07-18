<?php
// Verificar si las variables necesarias están definidas
$menu_activo = $menu_activo ?? '';

// Definir los elementos del menú
$menu_items = [
    [
        'titulo' => 'Dashboard',
        'url' => 'admin_panel.php',
        'icono' => 'bi-grid-1x2',
        'id' => 'dashboard'
    ],
    [
        'titulo' => 'Formularios',
        'id' => 'formularios',
        'submenu' => [
            [
                'titulo' => 'Pendientes',
                'url' => 'formularios_pendientes.php',
                'icono' => 'bi-clock',
                'id' => 'pendientes'
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
        ]
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

// Definir las clases para los estados del menú
$menu_item_base = "group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200";
$menu_item_active = "bg-blue-50 text-blue-700 shadow-sm";
$menu_item_inactive = "text-gray-600 hover:bg-gray-50 hover:text-gray-900";
?>

<!-- Menú Lateral -->
<aside class="fixed left-0 top-0 z-20 h-screen w-64 border-r border-gray-200 bg-white">
    <div class="flex h-full flex-col">
        <!-- Logo y Título -->
        <div class="flex h-16 items-center px-6">
            <a href="admin_panel.php" class="flex items-center gap-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600 text-white">
                    <i class="bi bi-heart-pulse text-xl"></i>
                </div>
                <span class="text-lg font-semibold text-gray-800">Panel Médico</span>
            </a>
        </div>

        <!-- Navegación -->
        <nav class="flex-1 overflow-auto px-3 py-4">
            <ul class="flex flex-col gap-1">
                <?php foreach ($menu_items as $item): ?>
                    <?php if (isset($item['submenu'])): ?>
                        <!-- Grupo de Menú con Submenú -->
                        <li class="mt-2">
                            <div class="flex flex-col">
                                <span class="mb-2 px-3 text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    <?php echo $item['titulo']; ?>
                                </span>
                                <ul class="flex flex-col gap-1">
                                    <?php foreach ($item['submenu'] as $subitem): ?>
                                        <li>
                                            <a href="<?php echo $subitem['url']; ?>" 
                                               class="<?php echo $menu_item_base . ' ' . ($menu_activo === $subitem['id'] ? $menu_item_active : $menu_item_inactive); ?>">
                                                <i class="bi <?php echo $subitem['icono']; ?> text-lg transition-transform group-hover:scale-110"></i>
                                                <span class="relative">
                                                    <?php echo $subitem['titulo']; ?>
                                                    <?php if ($menu_activo === $subitem['id']): ?>
                                                        <span class="absolute -bottom-1 left-0 h-0.5 w-full rounded-full bg-blue-600/40"></span>
                                                    <?php endif; ?>
                                                </span>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </li>
                    <?php else: ?>
                        <!-- Elemento de Menú Simple -->
                        <li>
                            <a href="<?php echo $item['url']; ?>" 
                               class="<?php echo $menu_item_base . ' ' . ($menu_activo === $item['id'] ? $menu_item_active : $menu_item_inactive); ?>">
                                <i class="bi <?php echo $item['icono']; ?> text-lg transition-transform group-hover:scale-110"></i>
                                <span class="relative">
                                    <?php echo $item['titulo']; ?>
                                    <?php if ($menu_activo === $item['id']): ?>
                                        <span class="absolute -bottom-1 left-0 h-0.5 w-full rounded-full bg-blue-600/40"></span>
                                    <?php endif; ?>
                                </span>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </nav>

        <!-- Footer del Menú -->
        <div class="border-t border-gray-200 p-4">
            <a href="cerrar_sesion.php" 
               class="flex w-full items-center justify-center gap-2 rounded-lg bg-red-50 px-3 py-2 text-sm font-medium text-red-600 transition-all duration-200 hover:bg-red-100 hover:text-red-700">
                <i class="bi bi-box-arrow-right text-lg"></i>
                Cerrar Sesión
            </a>
        </div>
    </div>
</aside> 