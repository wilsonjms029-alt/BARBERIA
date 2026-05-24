<?php
if (!defined('BARBERIA_ROOT')) {
    define('BARBERIA_ROOT', dirname(__DIR__));
}
require_once BARBERIA_ROOT . '/backend/bootstrap.php';

/** Abreviatura del día en español (LUN, MAR, …) */
function barberia_dia_corto(DateTimeInterface $fecha): string
{
    static $dias = ['DOM', 'LUN', 'MAR', 'MIÉ', 'JUE', 'VIE', 'SÁB'];
    return $dias[(int) $fecha->format('w')];
}

/** Fecha larga: "lunes, 24 de mayo" */
function barberia_fecha_larga(?DateTimeInterface $fecha = null): string
{
    static $dias = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
    static $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    $f = $fecha ?? new DateTime();
    return ucfirst($dias[(int) $f->format('w')]) . ', ' . $f->format('j') . ' de ' . $meses[(int) $f->format('n') - 1];
}

/** Mes abreviado en español */
function barberia_mes_corto(int $numeroMes): string
{
    static $meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
    return $meses[$numeroMes - 1] ?? '';
}
