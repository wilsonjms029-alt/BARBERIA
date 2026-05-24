<?php
session_start();
require_once dirname(__DIR__) . '/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

function normTel(string $tel): string
{
    return preg_replace('/[^0-9]/', '', $tel);
}

function tienePassword(?string $hash): bool
{
    return is_string($hash)
        && strlen($hash) >= 60
        && str_starts_with($hash, '$2y$');
}

function sqlTelExpr(string $col): string
{
    return "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE($col, ' ', ''), '-', ''), '+', ''), '.', ''), '(', '')";
}

function buscarUsuarioPorTelefono(PDO $pdo, string $norm): ?array
{
    if (strlen($norm) < 7) {
        return null;
    }
    $expr = sqlTelExpr('telefono');
    $exprU = sqlTelExpr('usuario');
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE $expr = ? OR $exprU = ? LIMIT 1");
    $stmt->execute([$norm, $norm]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function buscarClientePorTelefono(PDO $pdo, string $norm): ?array
{
    $expr = sqlTelExpr('telefono');
    $stmt = $pdo->prepare("SELECT nombre, telefono FROM clientes WHERE $expr = ? LIMIT 1");
    $stmt->execute([$norm]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function iniciarSesion(array $usuario): void
{
    $_SESSION['usuario_id'] = (int) $usuario['id'];
    $_SESSION['usuario_nombre'] = $usuario['nombre'] ?? '';
    $_SESSION['usuario_rol'] = $usuario['rol'] ?? 'cliente';
    $_SESSION['usuario_telefono'] = normTel($usuario['telefono'] ?? $usuario['usuario'] ?? '');
}

function jsonOut(array $data, int $code = 200): void
{
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$input = $_POST;
if (empty($input) && ($raw = file_get_contents('php://input'))) {
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        $input = $decoded;
    }
}

try {
    if ($action === 'check') {
        $tel = normTel($input['telefono'] ?? '');
        if (strlen($tel) < 7) {
            jsonOut(['ok' => false, 'error' => 'Ingresa un número válido (mín. 7 dígitos).']);
        }

        $usuario = buscarUsuarioPorTelefono($pdo, $tel);
        $cliente = buscarClientePorTelefono($pdo, $tel);

        if ($usuario) {
            $nombre = $usuario['nombre'] ?: null;
            if (tienePassword($usuario['password'])) {
                jsonOut([
                    'ok' => true,
                    'status' => 'login',
                    'nombre' => $nombre,
                    'telefono' => $tel,
                ]);
            }
            jsonOut([
                'ok' => true,
                'status' => 'create_password',
                'nombre' => $nombre,
                'telefono' => $tel,
            ]);
        }

        jsonOut([
            'ok' => true,
            'status' => 'register',
            'nombre' => $cliente['nombre'] ?? null,
            'telefono' => $tel,
        ]);
    }

    if ($action === 'login') {
        $tel = normTel($input['telefono'] ?? '');
        $pass = $input['password'] ?? '';

        if (strlen($tel) < 7 || $pass === '') {
            jsonOut(['ok' => false, 'error' => 'Teléfono y contraseña son obligatorios.'], 400);
        }

        $usuario = buscarUsuarioPorTelefono($pdo, $tel);
        if (!$usuario || !tienePassword($usuario['password']) || !password_verify($pass, $usuario['password'])) {
            jsonOut(['ok' => false, 'error' => 'Teléfono o contraseña incorrectos.'], 401);
        }

        iniciarSesion($usuario);
        $redirect = ($usuario['rol'] === 'admin' || $usuario['usuario'] === 'admin')
            ? 'admin.php'
            : 'index.php';

        jsonOut(['ok' => true, 'redirect' => $redirect]);
    }

    if ($action === 'set_password') {
        $tel = normTel($input['telefono'] ?? '');
        $pass = $input['password'] ?? '';
        $pass2 = $input['password_confirm'] ?? $pass;
        $nombre = trim($input['nombre'] ?? '');

        if (strlen($tel) < 7) {
            jsonOut(['ok' => false, 'error' => 'Número de teléfono inválido.'], 400);
        }
        if (strlen($pass) < 4) {
            jsonOut(['ok' => false, 'error' => 'La contraseña debe tener al menos 4 caracteres.'], 400);
        }
        if ($pass !== $pass2) {
            jsonOut(['ok' => false, 'error' => 'Las contraseñas no coinciden.'], 400);
        }

        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $usuario = buscarUsuarioPorTelefono($pdo, $tel);

        if ($usuario) {
            if (tienePassword($usuario['password'])) {
                jsonOut(['ok' => false, 'error' => 'Este teléfono ya tiene contraseña. Inicia sesión.'], 409);
            }
            if ($nombre === '') {
                $nombre = $usuario['nombre'] ?: 'Cliente';
            }
            $stmt = $pdo->prepare('UPDATE usuarios SET password = ?, nombre = ? WHERE id = ?');
            $stmt->execute([$hash, $nombre, $usuario['id']]);
            $usuario['password'] = $hash;
            $usuario['nombre'] = $nombre;
        } else {
            if ($nombre === '') {
                $cliente = buscarClientePorTelefono($pdo, $tel);
                $nombre = $cliente['nombre'] ?? 'Cliente';
            }
            $stmt = $pdo->prepare(
                'INSERT INTO usuarios (usuario, nombre, password, telefono, rol) VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([$tel, $nombre, $hash, $tel, 'cliente']);
            $usuario = [
                'id' => (int) $pdo->lastInsertId(),
                'usuario' => $tel,
                'nombre' => $nombre,
                'telefono' => $tel,
                'rol' => 'cliente',
                'password' => $hash,
            ];
        }

        iniciarSesion($usuario);
        jsonOut(['ok' => true, 'redirect' => 'index.php']);
    }

    jsonOut(['ok' => false, 'error' => 'Acción no válida.'], 400);
} catch (Exception $e) {
    jsonOut(['ok' => false, 'error' => 'Error del servidor. Intenta de nuevo.'], 500);
}
