<?php
session_start();

// Verificar si el usuario está autenticado y es admin o doctor
if (!isset($_SESSION['doctor_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../../backend/db/conection.php';

if (isset($_POST['id']) && isset($_POST['confirmar'])) {
    $id = intval($_POST['id']);
    
    // Borrar el formulario
    $sql = "DELETE FROM formularios_consentimiento WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

// Redirigir de vuelta a la página anterior
$pagina_anterior = $_SERVER['HTTP_REFERER'] ?? 'formularios_pendientes.php';
header("Location: " . $pagina_anterior);
exit();
?> 