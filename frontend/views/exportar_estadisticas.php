<?php
session_start();

// Verificar si el usuario está autenticado y es administrador
if (!isset($_SESSION['doctor_id']) || $_SESSION['user_rol'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Configuración de la base de datos
require_once '../../backend/db/conection.php';

// Configurar headers para descarga de CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=estadisticas_' . date('Y-m-d') . '.csv');

// Crear el archivo CSV
$output = fopen('php://output', 'w');

// Establecer el separador de columnas para Excel
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM para UTF-8

// Encabezados del CSV
fputcsv($output, [
    'Categoría',
    'Métrica',
    'Valor',
    'Fecha'
]);

// 1. Estadísticas de Formularios
$sql_formularios = "SELECT 
    SUM(CASE WHEN estado_revision = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
    SUM(CASE WHEN estado_revision = 'aprobado' THEN 1 ELSE 0 END) as aprobados,
    SUM(CASE WHEN estado_revision = 'rechazado' THEN 1 ELSE 0 END) as rechazados
FROM formularios_consentimiento";
$result_formularios = $conn->query($sql_formularios);
$formularios = $result_formularios->fetch_assoc();

// Escribir estadísticas de formularios
fputcsv($output, ['Formularios', 'Pendientes', $formularios['pendientes'], date('Y-m-d')]);
fputcsv($output, ['Formularios', 'Aprobados', $formularios['aprobados'], date('Y-m-d')]);
fputcsv($output, ['Formularios', 'Rechazados', $formularios['rechazados'], date('Y-m-d')]);

// 2. Estadísticas de Usuarios
$sql_usuarios = "SELECT 
    COUNT(*) as total_doctores,
    SUM(CASE WHEN rol = 'admin' THEN 1 ELSE 0 END) as total_admins,
    SUM(CASE WHEN rol = 'doctor' THEN 1 ELSE 0 END) as total_docs,
    SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) as activos,
    SUM(CASE WHEN activo = 0 THEN 1 ELSE 0 END) as inactivos
FROM usuarios";
$result_usuarios = $conn->query($sql_usuarios);
$usuarios = $result_usuarios->fetch_assoc();

// Escribir estadísticas de usuarios
fputcsv($output, ['Usuarios', 'Total Doctores', $usuarios['total_doctores'], date('Y-m-d')]);
fputcsv($output, ['Usuarios', 'Administradores', $usuarios['total_admins'], date('Y-m-d')]);
fputcsv($output, ['Usuarios', 'Doctores', $usuarios['total_docs'], date('Y-m-d')]);
fputcsv($output, ['Usuarios', 'Usuarios Activos', $usuarios['activos'], date('Y-m-d')]);
fputcsv($output, ['Usuarios', 'Usuarios Inactivos', $usuarios['inactivos'], date('Y-m-d')]);

// 3. Estadísticas por mes (últimos 6 meses)
$sql_por_mes = "SELECT 
    DATE_FORMAT(fecha_creacion, '%Y-%m') as mes,
    COUNT(*) as total,
    SUM(CASE WHEN estado_revision = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
    SUM(CASE WHEN estado_revision = 'aprobado' THEN 1 ELSE 0 END) as aprobados,
    SUM(CASE WHEN estado_revision = 'rechazado' THEN 1 ELSE 0 END) as rechazados
FROM formularios_consentimiento
WHERE fecha_creacion >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
GROUP BY DATE_FORMAT(fecha_creacion, '%Y-%m')
ORDER BY mes ASC";
$result_por_mes = $conn->query($sql_por_mes);

// Escribir estadísticas mensuales
fputcsv($output, ['', '', '', '']); // Línea en blanco para separar secciones
fputcsv($output, ['Estadísticas Mensuales', '', '', '']);
fputcsv($output, ['Mes', 'Total', 'Pendientes', 'Aprobados', 'Rechazados']);

while ($row = $result_por_mes->fetch_assoc()) {
    fputcsv($output, [
        $row['mes'],
        $row['total'],
        $row['pendientes'],
        $row['aprobados'],
        $row['rechazados']
    ]);
}

// Cerrar la conexión y el archivo
$conn->close();
fclose($output);
?> 