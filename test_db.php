<?php
// test_db.php - SOLO PARA PRUEBAS
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Diagnóstico de Conexión</h1>";

try {
    require 'db.php';
    echo "<p style='color:green'>✅ 1. Conexión a Base de Datos exitosa.</p>";
    
    // Probar si existe la tabla citas
    $test = $pdo->query("SELECT count(*) FROM citas");
    echo "<p style='color:green'>✅ 2. Tabla 'citas' encontrada.</p>";

    // Probar api_horarios logic
    echo "<p>3. Intentando obtener horarios...</p>";
    $fecha = date('Y-m-d');
    $barbero_id = 1;
    
    // Simular lógica de api_horarios
    $stmt = $pdo->prepare("SELECT time_format(hora, '%H:%i') as hora FROM citas WHERE barbero_id = ? AND fecha = ?");
    $stmt->execute([$barbero_id, $fecha]);
    $ocupadas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<p style='color:green'>✅ 4. Consulta SQL exitosa. Citas hoy: " . count($ocupadas) . "</p>";

} catch (Exception $e) {
    echo "<h2 style='color:red'>❌ ERROR DETECTADO:</h2>";
    echo "<pre>" . $e->getMessage() . "</pre>";
    echo "<p>Revisa tu archivo <b>db.php</b> (usuario/contraseña) o si creaste la base de datos en phpMyAdmin.</p>";
}
?>