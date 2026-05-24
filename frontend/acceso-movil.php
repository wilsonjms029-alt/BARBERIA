<?php
/**
 * URLs y QR para abrir AlCorte en Android / iPhone (misma Wi‑Fi que el PC).
 */
function obtener_ips_red(): array {
    $ips = [];
    if (function_exists('socket_create')) {
        $sock = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if ($sock) {
            @socket_connect($sock, '8.8.8.8', 80);
            @socket_getsockname($sock, $ip);
            @socket_close($sock);
            if (!empty($ip) && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $ips[] = $ip;
            }
        }
    }
    if (!empty($_SERVER['SERVER_ADDR']) && $_SERVER['SERVER_ADDR'] !== '127.0.0.1') {
        $ips[] = $_SERVER['SERVER_ADDR'];
    }
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' && function_exists('shell_exec')) {
        $out = @shell_exec('powershell -NoProfile -Command "(Get-NetIPAddress -AddressFamily IPv4 | Where-Object { $_.IPAddress -notlike \'127.*\' -and $_.PrefixOrigin -ne \'WellKnown\' }).IPAddress -join \',\'"');
        if ($out) {
            foreach (explode(',', trim($out)) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                    $ips[] = $ip;
                }
            }
        }
    }
    $ips = array_values(array_unique($ips));
    usort($ips, function ($a, $b) {
        $score = function ($ip) {
            if (preg_match('/^192\.168\./', $ip)) return 0;
            if (preg_match('/^10\./', $ip)) return 1;
            if (preg_match('/^172\.(1[6-9]|2[0-9]|3[01])\./', $ip)) return 2;
            if (preg_match('/^172\./', $ip)) return 9;
            return 5;
        };
        return $score($a) <=> $score($b);
    });
    return $ips;
}

$ips = obtener_ips_red();
$ip_wifi = $ips[0] ?? 'TU_IP_WIFI';
$baseDot = '/.barberia';
$baseSimple = '/alcorte';
$host = $_SERVER['HTTP_HOST'] ?? $ip_wifi;
$es_localhost = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true);

function url_para_ip(string $ip, string $path): string {
    return 'http://' . $ip . $path . '/';
}

$url_recomendada = url_para_ip($ip_wifi, $baseSimple);
$url_alterna_dot = url_para_ip($ip_wifi, $baseDot);
$url_admin = $url_recomendada . 'admin.php';
$qr_data = rawurlencode($url_recomendada);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Android — AlCorte</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="manifest" href="assets/manifest.json">
    <meta name="theme-color" content="#cfa87b">
</head>
<body class="bg-[#070d19] text-white min-h-screen p-5 font-sans pb-24">
    <div class="max-w-md mx-auto space-y-5">
        <div>
            <h1 class="text-lg font-black uppercase tracking-wider">Al<span class="text-[#cfa87b]">Corte</span></h1>
            <p class="text-[10px] text-gray-500 uppercase tracking-widest mt-1">Conectar Android a la web del PC</p>
        </div>

        <?php if ($es_localhost): ?>
        <div class="bg-amber-950/40 border border-amber-500/30 rounded-xl p-4 text-xs text-amber-200 leading-relaxed">
            <strong>Paso 1 (PC):</strong> Laragon → <strong>Start All</strong>.<br>
            <strong>Paso 2 (móvil):</strong> Misma Wi‑Fi que el PC. Escanea el QR o copia la URL verde (usa <code class="text-amber-100">http://</code>, no https).
        </div>
        <?php endif; ?>

        <!-- URL recomendada sin punto en la carpeta -->
        <div class="bg-[#0b1220] border-2 border-[#cfa87b]/50 rounded-2xl p-5 space-y-4">
            <p class="text-[10px] uppercase text-[#cfa87b] font-bold tracking-wider">✓ URL para Android (recomendada)</p>
            <p id="url-principal" class="font-mono text-sm text-white break-all select-all"><?= htmlspecialchars($url_recomendada) ?></p>
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=<?= $qr_data ?>&bgcolor=070d19&color=cfa87b"
                 alt="QR" class="mx-auto rounded-lg border border-white/10" width="220" height="220">
            <p class="text-[10px] text-gray-500 text-center">Escanea con la cámara o Chrome en Android</p>
            <button type="button" onclick="copiar('<?= htmlspecialchars($url_recomendada, ENT_QUOTES) ?>')"
                class="w-full py-3 bg-[#cfa87b] text-black font-bold rounded-xl text-xs uppercase tracking-widest">
                Copiar URL
            </button>
            <a href="<?= htmlspecialchars($url_recomendada) ?>" class="block w-full py-3 border border-white/20 rounded-xl text-center text-xs text-gray-300">
                Probar en este dispositivo
            </a>
        </div>

        <div class="bg-[#0b1220] border border-white/10 rounded-xl p-4 space-y-2 text-xs">
            <p class="text-gray-500 uppercase text-[10px] font-bold tracking-wider">Otras URLs</p>
            <p><span class="text-gray-500">Admin:</span><br>
                <a href="<?= htmlspecialchars($url_admin) ?>" class="text-[#cfa87b] font-mono break-all"><?= htmlspecialchars($url_admin) ?></a></p>
            <p><span class="text-gray-500">Ruta antigua (con punto):</span><br>
                <span class="font-mono text-gray-400 break-all"><?= htmlspecialchars($url_alterna_dot) ?></span></p>
        </div>

        <?php if (count($ips) > 1): ?>
        <div class="bg-[#0b1220] border border-white/10 rounded-xl p-4 text-xs">
            <p class="text-gray-500 uppercase text-[10px] font-bold mb-2">IPs detectadas en este PC</p>
            <ul class="space-y-2">
                <?php foreach ($ips as $i => $ip): ?>
                <li class="<?= $i === 0 ? 'text-[#cfa87b]' : 'text-gray-400' ?>">
                    <?= htmlspecialchars($ip) ?>
                    <?= $i === 0 ? ' ← usa esta en Android' : '' ?>
                    <?php if (preg_match('/^172\.25\./', $ip)): ?>
                    <span class="text-red-400 block text-[10px]">No usar (red virtual)</span>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <div class="bg-red-950/30 border border-red-500/20 rounded-xl p-4 text-xs text-red-200/90 space-y-2">
            <p class="font-bold text-red-300 uppercase text-[10px] tracking-wider">Si Android no abre</p>
            <ul class="list-disc pl-4 space-y-1 text-gray-300">
                <li>Móvil en <strong>Wi‑Fi</strong>, no en datos 4G/5G.</li>
                <li>Escribe <strong>http://</strong> (sin la <strong>s</strong> de https).</li>
                <li>Usa la URL <strong>/alcorte/</strong>, no solo la IP.</li>
                <li>Desactiva VPN en el celular.</li>
                <li>En el PC: Windows puede pedir permitir <strong>Apache</strong> → Red privada.</li>
                <li>Router con “aislamiento de clientes”: desactívalo o usa otra red Wi‑Fi.</li>
            </ul>
        </div>

        <div class="flex flex-col gap-2 pt-2">
            <a href="red-test.php" class="block text-center text-[10px] text-gray-500 underline">Probar conexión (red-test.php)</a>
            <a href="doc/README.md" class="block text-center text-[10px] text-gray-500 underline" download>Documentación (carpeta doc)</a>
            <a href="doc/DOCUMENTACION-MOVIL-WEB.md" class="block text-center text-[10px] text-[#cfa87b] underline" download>Guía paso a paso (.md)</a>
        </div>
    </div>
    <script>
    function copiar(t) {
        navigator.clipboard.writeText(t).then(() => alert('URL copiada. Pégala en Chrome del Android.'))
            .catch(() => { prompt('Copia esta URL:', t); });
    }
    </script>
</body>
</html>
