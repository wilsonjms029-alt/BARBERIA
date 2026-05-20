<?php
// api_horarios.php
// IMPORTANTE: Esto evita que advertencias de PHP rompan el JSON
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

require 'db.php';

$barbero_id = $_GET['barbero_id'] ?? 1;
$fecha = $_GET['fecha'] ?? '';

// Si no hay fecha, devolvemos array vacío
if(empty($fecha)) {
    echo json_encode([]);
    exit;
}

// Horario Laboral: Puedes editarlo aquí
$horas_posibles = ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00'];

try {
    // Buscamos citas que NO estén rechazadas (incluye pendientes y verificadas)
    $stmt = $pdo->prepare("SELECT time_format(hora, '%H:%i') as hora FROM citas WHERE barbero_id = ? AND fecha = ? AND estado_pago != 'rechazado'");
    $stmt->execute([$barbero_id, $fecha]);
    $ocupadas = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Calculamos libres
    $disponibles = array_values(array_diff($horas_posibles, $ocupadas));
    
    echo json_encode($disponibles);

} catch (Exception $e) {
    // En caso de error, devolvemos array vacío para que no salga error en pantalla
    echo json_encode([]);
}
?>