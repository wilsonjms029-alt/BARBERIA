<?php
require_once dirname(__DIR__) . '/bootstrap.php';

$base = barberia_web_base();

// Validar que vengan datos
if (!isset($_POST['barbero_id'])) {
    header('Location: ' . $base . '/index.php');
    exit;
}

$barbero_id = $_POST['barbero_id'];
$servicio_id = $_POST['servicio'];
$fecha = $_POST['fecha'];
$hora = $_POST['hora'];
$nombre = strtoupper($_POST['nombre']);
$telefono = preg_replace('/[^0-9]/', '', $_POST['telefono']);
$metodo = $_POST['metodo_pago'];
$referencia = $_POST['referencia'];

// Obtener nombre del servicio
$stmt = $pdo->prepare("SELECT nombre FROM servicios WHERE id = ?");
$stmt->execute([$servicio_id]);
$nom_servicio = $stmt->fetchColumn() ?: 'Servicio General';

// Estado por defecto
$estado = 'pendiente';

try {
    $sql = "INSERT INTO citas (barbero_id, cliente_nombre, cliente_telefono, fecha, hora, estado_pago, referencia_pago, metodo_pago, servicio) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$barbero_id, $nombre, $telefono, $fecha, $hora, $estado, $referencia, $metodo, $nom_servicio]);
    
    $id_cita = $pdo->lastInsertId();
    
    // REDIRIGIR AL TICKET VIP
    header('Location: ' . $base . '/ver_ticket.php?id=' . $id_cita);
    exit;

} catch (Exception $e) {
    die("Error al reservar: " . $e->getMessage());
}
?>