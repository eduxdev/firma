<?php
session_start();

// Configuración de la base de datos
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'firma';

// Conectar a la base de datos
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Obtener datos del formulario
$email = $_POST['email'];
$password = $_POST['password'];

// Preparar consulta
$sql = "SELECT id, nombre, apellido, password, activo FROM usuarios WHERE email = ? AND password = ? AND rol = 'doctor'";
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
    
    // Redirigir al panel
    header("Location: revisar_formularios.php");
    exit();
}

// Si llegamos aquí, las credenciales son inválidas
header("Location: login.php?error=invalid");
exit();
?> 