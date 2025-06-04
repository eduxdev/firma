<?php
session_start();

// Verificar si el doctor está autenticado
if (!isset($_SESSION['doctor_id'])) {
    header('Location: login.php');
    exit();
}

// Redirigir a la página de formularios pendientes
header('Location: formularios_pendientes.php');
exit();
?> 