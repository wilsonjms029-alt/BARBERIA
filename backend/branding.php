<?php

/** Valores por defecto de configuración (SaaS: cada instancia personaliza su marca). */
function barberia_config_defaults(): array
{
    return [
        'banco_nombre' => '',
        'banco_ci' => '',
        'banco_telefono' => '',
        'zelle_email' => '',
        'estado_movil' => '1',
        'estado_zelle' => '1',
        'estado_efectivo' => '1',
        'nombre_negocio' => '',
        'logo_url' => '',
    ];
}

function barberia_merge_config(array $raw): array
{
    return array_merge(barberia_config_defaults(), $raw);
}

function barberia_fetch_config(PDO $pdo): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    try {
        $raw = $pdo->query('SELECT clave, valor FROM configuracion')->fetchAll(PDO::FETCH_KEY_PAIR);
        $cache = barberia_merge_config($raw ?: []);
    } catch (Exception $e) {
        $cache = barberia_config_defaults();
    }
    return $cache;
}

/** URL pública del logo (ruta relativa o absoluta). */
function barberia_logo_src(?string $path): ?string
{
    if ($path === null || trim($path) === '') {
        return null;
    }
    $path = trim($path);
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }
    return ltrim(str_replace('\\', '/', $path), '/');
}

function barberia_branding_from_config(array $config): array
{
    $nombre = trim($config['nombre_negocio'] ?? '');
    $logo = barberia_logo_src($config['logo_url'] ?? null);

    return [
        'nombre_negocio' => $nombre,
        'logo_url' => $logo,
        'has_logo' => $logo !== null,
        'has_nombre' => $nombre !== '',
    ];
}

/** Guarda clave/valor en configuracion. */
function barberia_config_set(PDO $pdo, string $clave, string $valor): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO configuracion (clave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = ?'
    );
    $stmt->execute([$clave, $valor, $valor]);
}

/**
 * Sube logo de la barbería. Devuelve ruta web relativa (uploads/...) o null.
 */
function barberia_upload_logo(array $file, string $uploadDir): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return null;
    }
    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $mime = mime_content_type($file['tmp_name']) ?: ($file['type'] ?? '');
    if (!in_array($mime, $allowed, true)) {
        return null;
    }
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $ext = match ($mime) {
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
        default => 'jpg',
    };
    $name = 'logo_' . time() . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $name)) {
        return null;
    }
    return 'uploads/' . $name;
}
