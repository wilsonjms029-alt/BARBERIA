<?php
// db.php
$host = 'localhost';
$db   = 'barberia_db'; // Asegúrate que este nombre sea correcto en tu phpMyAdmin
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    // Si falla, no mostramos el error en pantalla para no romper JSON, solo detenemos.
    die("Error critico de conexion.");
}
?>