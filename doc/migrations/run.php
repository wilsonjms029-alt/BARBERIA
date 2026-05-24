<?php
/**
 * Ejecuta migraciones SQL en orden (001_, 002_, …).
 *
 * Uso:
 *   php doc/migrations/run.php           → todas
 *   php doc/migrations/run.php 001       → solo la que coincida
 */
require_once dirname(__DIR__, 2) . '/backend/bootstrap.php';

$dir = __DIR__;
$filter = $argv[1] ?? null;
$files = glob($dir . '/*.sql') ?: [];
sort($files);

if ($filter) {
    $files = array_values(array_filter(
        $files,
        static fn(string $f): bool => str_contains(basename($f), $filter)
    ));
}

if ($files === []) {
    fwrite(STDERR, "No hay migraciones que ejecutar.\n");
    exit(1);
}

foreach ($files as $file) {
    $name = basename($file);
    echo "→ {$name}\n";
    $sql = preg_replace('/--.*$/m', '', file_get_contents($file));
    $sql = trim($sql);
    if ($sql === '') {
        continue;
    }
    $pdo->exec($sql);
    echo "  OK\n";
}

echo "\nMigraciones completadas.\n";
