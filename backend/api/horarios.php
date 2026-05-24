<?php
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/bootstrap.php';

date_default_timezone_set('America/Caracas');

$barbero_id = $_GET['barbero_id'] ?? null;
$fecha = $_GET['fecha'] ?? null;
$servicio_id = $_GET['servicio_id'] ?? null;

if (!$barbero_id || !$fecha || !$servicio_id) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare('SELECT hora_inicio, hora_fin, hora_descanso_inicio, hora_descanso_fin FROM barberos WHERE id = ?');
$stmt->execute([$barbero_id]);
$horario = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt_serv = $pdo->prepare('SELECT duracion FROM servicios WHERE id = ?');
$stmt_serv->execute([$servicio_id]);
$duracion_servicio = (int) ($stmt_serv->fetchColumn() ?: 30);

if (!$horario) {
    echo json_encode([]);
    exit;
}

function minutosDesdeMedianoche(string $time): int
{
    return (int) (strtotime('1970-01-01 ' . $time) / 60);
}

function minutosAHora(int $min): string
{
    return sprintf('%02d:%02d', intdiv($min, 60), $min % 60);
}

/** Formato 12 h para mostrar en pantalla (ej. 2:30 p. m.) */
function minutosAHora12(int $min): string
{
    $h24 = intdiv($min, 60);
    $m = $min % 60;
    $h12 = $h24 % 12;
    if ($h12 === 0) {
        $h12 = 12;
    }
    $periodo = $h24 >= 12 ? 'p. m.' : 'a. m.';

    return sprintf('%d:%02d %s', $h12, $m, $periodo);
}

$inicio_dia = minutosDesdeMedianoche($horario['hora_inicio']);
$fin_dia = minutosDesdeMedianoche($horario['hora_fin']);
$inicio_alm = $horario['hora_descanso_inicio'] ? minutosDesdeMedianoche($horario['hora_descanso_inicio']) : null;
$fin_alm = $horario['hora_descanso_fin'] ? minutosDesdeMedianoche($horario['hora_descanso_fin']) : null;

$stmt = $pdo->prepare(
    "SELECT hora, servicio FROM citas WHERE barbero_id = ? AND fecha = ? AND estado_pago != 'rechazado'"
);
$stmt->execute([$barbero_id, $fecha]);
$citas_ocupadas = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $stmt_d = $pdo->prepare('SELECT duracion FROM servicios WHERE nombre = ? LIMIT 1');
    $stmt_d->execute([$row['servicio']]);
    $dur = (int) ($stmt_d->fetchColumn() ?: 30);
    $inicio_cita = minutosDesdeMedianoche($row['hora']);
    $citas_ocupadas[] = ['inicio' => $inicio_cita, 'fin' => $inicio_cita + $dur];
}

$hoy = date('Y-m-d');
$ahora_min = (int) date('G') * 60 + (int) date('i');
$fecha_pasada = $fecha < $hoy;
$es_hoy = $fecha === $hoy;

$intervalo = 30;
$horas = [];

for ($t = $inicio_dia; $t <= ($fin_dia - $duracion_servicio); $t += $intervalo) {
    $t_fin = $t + $duracion_servicio;
    $disponible = true;
    $motivo = '';

    if ($fecha_pasada || ($es_hoy && $t < $ahora_min)) {
        $disponible = false;
        $motivo = 'pasado';
    } elseif ($inicio_alm !== null && $fin_alm !== null && $t < $fin_alm && $t_fin > $inicio_alm) {
        $disponible = false;
        $motivo = 'descanso';
    } else {
        foreach ($citas_ocupadas as $c) {
            if ($t < $c['fin'] && $t_fin > $c['inicio']) {
                $disponible = false;
                $motivo = 'ocupado';
                break;
            }
        }
    }

    $horas[] = [
        'hora' => minutosAHora($t),
        'hora_12' => minutosAHora12($t),
        'disponible' => $disponible,
        'motivo' => $motivo,
    ];
}

echo json_encode($horas, JSON_UNESCAPED_UNICODE);
