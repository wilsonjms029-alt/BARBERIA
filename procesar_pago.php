<!-- <?php
require 'db.php';

// Recibimos los datos del formulario
$barbero_id = $_POST['barbero_id'];
$fecha = $_POST['fecha'];
$hora = $_POST['hora'];
$cliente = $_POST['nombre'];
$email = $_POST['email'];

// --- AQUÍ IRÍA LA LÓGICA DE LA PASARELA DE PAGO ---
// Ejemplo: $pago = Stripe::charge($tarjeta, $monto);
// Si la pasarela dice "OK", entonces procedemos a guardar.

$pago_exitoso = true; // Simulamos que el pago pasó correctamente

if ($pago_exitoso) {
    // Verificamos una última vez que no ganaron el lugar hace 1 segundo
    $check = $pdo->prepare("SELECT id FROM citas WHERE barbero_id=? AND fecha=? AND hora=?");
    $check->execute([$barbero_id, $fecha, $hora]);
    
    if ($check->rowCount() == 0) {
        // Guardamos la cita
        $stmt = $pdo->prepare("INSERT INTO citas (barbero_id, cliente_nombre, cliente_email, fecha, hora, estado_pago) VALUES (?, ?, ?, ?, ?, 'pagado')");
        $stmt->execute([$barbero_id, $cliente, $email, $fecha, $hora]);
        
        echo "¡Éxito! Tu cita ha sido pagada y reservada para el $fecha a las $hora.";
    } else {
        echo "Error: Lo sentimos, alguien acaba de reservar esa hora mientras pagabas.";
    }
} else {
    echo "El pago ha sido rechazado.";
}
?> -->