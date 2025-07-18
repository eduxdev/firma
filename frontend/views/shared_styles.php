<link rel="stylesheet" href="/public/assets/css/styles.css">
<style>
    /* Layout principal */
    .main-content {
        margin-left: 16rem;
        min-height: 100vh;
        background-color: #f8f9fa;
        position: relative;
        z-index: 1;
    }

    /* Menú lateral */
    aside {
        z-index: 40 !important;
    }

    /* Header */
    header {
        position: sticky !important;
        top: 0;
        z-index: 30 !important;
        background-color: white;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }

    /* Botón de menú móvil */
    .menu-toggle {
        z-index: 50 !important;
    }

    /* Overlay del menú */
    .menu-overlay {
        z-index: 35 !important;
    }

    /* Media queries */
    @media (max-width: 1024px) {
        .main-content {
            margin-left: 0;
            width: 100%;
        }
    }

    /* Estilos específicos para Alpine.js */
    [x-cloak] { 
        display: none !important; 
    }

    /* Estilos para gráficos responsivos */
    .chart-container {
        position: relative;
        width: 100%;
        height: 100%;
    }

    /* Estilos para tarjetas de estadísticas */
    .stat-card {
        transition: all 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    /* Estilos para listas responsivas */
    .responsive-list {
        display: grid;
        gap: 1rem;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    }

    /* Estilos para texto truncado */
    .truncate-2-lines {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Estilos para botones y badges */
    .badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
        line-height: 1;
    }

    /* Estilos para tooltips */
    .tooltip {
        position: relative;
    }
    .tooltip:hover::before {
        content: attr(data-tooltip);
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        padding: 0.5rem;
        background: rgba(0, 0, 0, 0.8);
        color: white;
        border-radius: 0.375rem;
        font-size: 0.75rem;
        white-space: nowrap;
        z-index: 50;
    }

    /* Estilos para scrollbars personalizados */
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 3px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* Estilos para animaciones */
    .animate-fade-in {
        animation: fadeIn 0.3s ease-in-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .animate-slide-in {
        animation: slideIn 0.3s ease-in-out;
    }
    @keyframes slideIn {
        from { transform: translateY(10px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    /* Media queries para responsividad */
    @media (max-width: 640px) {
        .responsive-grid {
            grid-template-columns: 1fr;
        }
        .hide-on-mobile {
            display: none;
        }
    }

    @media (min-width: 641px) and (max-width: 1024px) {
        .responsive-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (min-width: 1025px) {
        .responsive-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }
</style> 