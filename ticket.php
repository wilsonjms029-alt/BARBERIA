<?php
// ticket.php
require 'db.php';

// Recibir datos
$servicio = $_POST['servicio'];
$nombre = $_POST['nombre'];
$email = $_POST['email'];
$fecha = $_POST['fecha'];
$hora = $_POST['hora'];
$referencia = $_POST['referencia'];
$monto = 10.00; // Esto podrías sacarlo dinámicamente del select de servicio

// Guardar en BD
try {
    $stmt = $pdo->prepare("INSERT INTO citas (barbero_id, cliente_nombre, cliente_email, fecha, hora, estado_pago, referencia_pago, servicio, monto) VALUES (1, ?, ?, ?, ?, 'pagado', ?, ?, ?)");
    $stmt->execute([$nombre, $email, $fecha, $hora, $referencia, $servicio, $monto]);
    $id_cita = $pdo->lastInsertId();
} catch (Exception $e) {
    die("Error al guardar: " . $e->getMessage());
}

// FORMATO DE FECHA AMIGABLE
setlocale(LC_TIME, 'es_ES');
$fecha_humana = date("d/m/Y", strtotime($fecha));

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Ticket #<?php echo $id_cita; ?></title>
</head>
<body class="bg-gray-900 min-h-screen flex items-center justify-center p-4">

    <div class="bg-white max-w-sm w-full p-8 rounded shadow-2xl relative">
        <div class="absolute top-0 left-0 w-full h-4 bg-gray-900" style="clip-path: polygon(0 0, 5% 100%, 10% 0, 15% 100%, 20% 0, 25% 100%, 30% 0, 35% 100%, 40% 0, 45% 100%, 50% 0, 55% 100%, 60% 0, 65% 100%, 70% 0, 75% 100%, 80% 0, 85% 100%, 90% 0, 95% 100%, 100% 0);"></div>

        <div class="text-center mb-6">
            <h2 class="text-2xl font-black uppercase tracking-widest border-b-2 border-black pb-2 inline-block">Barber King</h2>
            <p class="text-xs text-gray-500 mt-2">COMPROBANTE DE CITA</p>
        </div>

        <div class="space-y-4 text-sm font-mono text-gray-700">
            <div class="flex justify-between">
                <span>Cliente:</span>
                <span class="font-bold"><?php echo strtoupper($nombre); ?></span>
            </div>
            <div class="flex justify-between">
                <span>Fecha:</span>
                <span class="font-bold"><?php echo $fecha_humana; ?></span>
            </div>
            <div class="flex justify-between">
                <span>Hora:</span>
                <span class="font-bold bg-black text-white px-1"><?php echo substr($hora, 0, 5); ?></span>
            </div>
            <div class="flex justify-between border-t border-dashed border-gray-400 pt-2">
                <span>Servicio:</span>
                <span><?php echo $servicio; ?></span>
            </div>
            <div class="flex justify-between text-xs text-gray-500">
                <span>Pago Móvil Ref:</span>
                <span><?php echo $referencia; ?></span>
            </div>
        </div>

        <div class="mt-8 border-t-2 border-black pt-4 text-center">
            <h3 class="text-xl font-bold">ESTADO: PAGADO</h3>
            <p class="text-xs text-gray-400 mt-1">Por favor llegue 5 min antes.</p>
            <p class="text-xs text-gray-400">Ticket #<?php echo str_pad($id_cita, 6, "0", STR_PAD_LEFT); ?></p>
        </div>

        <div class="absolute bottom-0 left-0 w-full h-4 bg-gray-900" style="clip-path: polygon(0 100%, 5% 0%, 10% 100%, 15% 0%, 20% 100%, 25% 0%, 30% 100%, 35% 0%, 40% 100%, 45% 0%, 50% 100%, 55% 0%, 60% 100%, 65% 0%, 70% 100%, 75% 0%, 80% 100%, 85% 0%, 90% 100%, 95% 0%, 100% 100%);"></div>
    </div>

</body>
</html>