<?php
/**
 * Datos de prueba: barbería con marca configurada.
 * Uso: php doc/migrations/seeds/barberia-demo.php
 */
require_once dirname(__DIR__, 3) . '/backend/bootstrap.php';

$demo = [
    'nombre_negocio' => 'Barbería El Estilo',
    'logo_url' => 'https://ui-avatars.com/api/?name=El+Estilo&background=ffffff&color=1a2744&size=256&bold=true&format=png',
    'banco_nombre' => 'Banesco',
    'banco_ci' => 'V-12345678',
    'banco_telefono' => '04241234567',
    'zelle_email' => 'pagos@elestilo.com',
    'estado_movil' => '1',
    'estado_zelle' => '1',
    'estado_efectivo' => '1',
];

foreach ($demo as $clave => $valor) {
    barberia_config_set($pdo, $clave, $valor);
}

echo "Barbería de prueba registrada:\n";
echo "  Nombre: {$demo['nombre_negocio']}\n";
echo "  Logo:   {$demo['logo_url']}\n";
echo "\nAbre http://localhost/.barberia/index.php\n";
