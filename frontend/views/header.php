<?php
// Verificar si las variables necesarias están definidas
$titulo = $titulo ?? 'Panel del Doctor';
$subtitulo = $subtitulo ?? '';
$scripts_adicionales = $scripts_adicionales ?? '';
?>

<!-- Scripts adicionales -->
<?php echo $scripts_adicionales; ?>

<!-- Header -->
<header class="sticky top-0 z-30 w-full border-b border-gray-800/10 bg-white/95 backdrop-blur supports-[backdrop-filter]:bg-white/60">
    <div class="flex h-16 items-center gap-4 px-4 sm:px-6 lg:px-8">
        <!-- Espacio para el botón de menú en móvil -->
        <div class="w-8 lg:hidden"></div>

        <!-- Título y Subtítulo -->
        <div class="flex-1 min-w-0">
            <h1 class="text-lg font-semibold text-gray-800 truncate"><?php echo $titulo; ?></h1>
            <?php if ($subtitulo): ?>
                <p class="text-sm text-gray-500 truncate"><?php echo $subtitulo; ?></p>
            <?php endif; ?>
        </div>

        <!-- Botones Adicionales (si existen) -->
        <?php if (!empty($botones_adicionales)): ?>
            <div class="flex items-center gap-2 overflow-x-auto">
                <?php foreach ($botones_adicionales as $boton): ?>
                    <?php if ($boton['tipo'] === 'link'): ?>
                        <a href="<?php echo $boton['url']; ?>" class="<?php echo $boton['clase']; ?> whitespace-nowrap">
                            <?php if (isset($boton['icono'])): ?>
                                <i class="bi bi-<?php echo $boton['icono']; ?> me-2"></i>
                            <?php endif; ?>
                            <?php echo $boton['texto']; ?>
                        </a>
                    <?php elseif ($boton['tipo'] === 'button'): ?>
                        <button onclick="<?php echo $boton['onclick']; ?>" class="<?php echo $boton['clase']; ?> whitespace-nowrap">
                            <?php if (isset($boton['icono'])): ?>
                                <i class="bi bi-<?php echo $boton['icono']; ?> me-2"></i>
                            <?php endif; ?>
                            <?php echo $boton['texto']; ?>
                        </button>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</header> 