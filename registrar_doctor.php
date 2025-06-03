<?php
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

// Datos del doctor
$nombre = "Doctor";
$apellido = "Ejemplo";
$email = "doctor@ejemplo.com";
$password = "doctor123"; // Contraseña sin hash
$rol = "doctor";

// Preparar consulta
$sql = "INSERT INTO usuarios (nombre, apellido, email, password, rol) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssss", $nombre, $apellido, $email, $password, $rol);

// Ejecutar consulta
if ($stmt->execute()) {
    echo "Doctor registrado exitosamente.<br>";
    echo "Email: " . $email . "<br>";
    echo "Contraseña: " . $password . "<br>";
    echo "<strong>Por favor, cambie la contraseña después del primer inicio de sesión.</strong>";
} else {
    echo "Error al registrar el doctor: " . $stmt->error;
}

$stmt->close();
$conn->close();
?> 