<?php
session_start();

require_once '../../backend/db/conection.php';

// Obtener datos del formulario
$email = $_POST['email'];
$password = $_POST['password'];

// Preparar consulta - Ahora sin filtrar por rol
$sql = "SELECT id, nombre, apellido, password, activo, rol FROM usuarios WHERE email = ? AND password = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $email, $password);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $usuario = $result->fetch_assoc();
    
    // Verificar si la cuenta está activa
    if (!$usuario['activo']) {
        header("Location: login.php?error=inactive");
        exit();
    }
    
    // Actualizar última sesión
    $sql = "UPDATE usuarios SET ultima_sesion = CURRENT_TIMESTAMP WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario['id']);
    $stmt->execute();
    
    // Establecer variables de sesión
    $_SESSION['doctor_id'] = $usuario['id'];
    $_SESSION['doctor_nombre'] = $usuario['nombre'];
    $_SESSION['doctor_apellido'] = $usuario['apellido'];
    $_SESSION['user_rol'] = $usuario['rol'];
    
    // Redirigir según el rol
    if ($usuario['rol'] === 'admin') {
        header("Location: admin_panel.php"); // Creamos esta página después
    } else {
        header("Location: revisar_formularios.php");
    }
    exit();
}

// Si llegamos aquí, las credenciales son inválidas
header("Location: login.php?error=invalid");
exit();
?> 