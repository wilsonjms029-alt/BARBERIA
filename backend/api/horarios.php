<?php
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/bootstrap.php';

$barbero_id = $_GET['barbero_id'] ?? null;
$fecha = $_GET['fecha'] ?? null;
$servicio_id = $_GET['servicio_id'] ?? null;

if (!$barbero_id || !$fecha || !$servicio_id) { echo json_encode([]); exit; }

// Horario Barbero
$stmt = $pdo->prepare("SELECT hora_inicio, hora_fin, hora_descanso_inicio, hora_descanso_fin FROM barberos WHERE id = ?");
$stmt->execute([$barbero_id]);
$horario = $stmt->fetch(PDO::FETCH_ASSOC);

// Duración Servicio
$stmt_serv = $pdo->prepare("SELECT duracion FROM servicios WHERE id = ?");
$stmt_serv->execute([$servicio_id]);
$duracion_servicio = $stmt_serv->fetchColumn() ?: 30;

if (!$horario) { echo json_encode([]); exit; }

function toMin($time) { return strtotime("1970-01-01 $time") / 60; }
$inicio_dia = toMin($horario['hora_inicio']);
$fin_dia = toMin($horario['hora_fin']);
$inicio_alm = $horario['hora_descanso_inicio'] ? toMin($horario['hora_descanso_inicio']) : null;
$fin_alm = $horario['hora_descanso_fin'] ? toMin($horario['hora_descanso_fin']) : null;

// Citas existentes (bloqueos)
$stmt = $pdo->prepare("SELECT hora, servicio FROM citas WHERE barbero_id = ? AND fecha = ? AND estado_pago != 'rechazado'");
$stmt->execute([$barbero_id, $fecha]);
$citas_ocupadas = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // Intentamos obtener la duración de esa cita ya guardada
    $stmt_d = $pdo->prepare("SELECT duracion FROM servicios WHERE nombre = ? LIMIT 1");
    $stmt_d->execute([$row['servicio']]);
    $dur = $stmt_d->fetchColumn() ?: 30; 
    $inicio_cita = toMin($row['hora']);
    $citas_ocupadas[] = ['inicio' => $inicio_cita, 'fin' => $inicio_cita + $dur];
}

$intervalo = 30;
$horas_disponibles = [];

for ($t = $inicio_dia; $t <= ($fin_dia - $duracion_servicio); $t += $intervalo) {
    $t_fin = $t + $duracion_servicio;
    
    // Validar Almuerzo
    if ($inicio_alm && $fin_alm) {
        if ($t < $fin_alm && $t_fin > $inicio_alm) continue;
    }
    
    // Validar Choque Citas
    $choca = false;
    foreach ($citas_ocupadas as $c) {
        if ($t < $c['fin'] && $t_fin > $c['inicio']) { $choca = true; break; }
    }
    if (!$choca) $horas_disponibles[] = date('H:i', mktime(0, $t));
}
echo json_encode($horas_disponibles);
?>