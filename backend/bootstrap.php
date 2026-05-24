<?php
if (!defined('BARBERIA_ROOT')) {
    define('BARBERIA_ROOT', dirname(__DIR__));
}
require_once BARBERIA_ROOT . '/backend/config/db.php';
require_once BARBERIA_ROOT . '/backend/branding.php';

/** Ruta web de la app (ej. /.barberia) para redirecciones desde /api/ */
function barberia_web_base(): string
{
    $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    if (str_ends_with($dir, '/api')) {
        $dir = dirname($dir);
    }
    return rtrim($dir, '/') ?: '';
}
