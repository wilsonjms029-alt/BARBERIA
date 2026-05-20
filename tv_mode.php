<?php
date_default_timezone_set('America/Caracas');
require 'db.php';
$hoy = date('Y-m-d');
$sql = "SELECT c.*, b.nombre as barbero, COALESCE(s.duracion, 30) as duracion 
        FROM citas c 
        LEFT JOIN barberos b ON c.barbero_id = b.id 
        LEFT JOIN servicios s ON c.servicio = s.nombre 
        WHERE c.fecha = ? AND c.estado_pago != 'rechazado'
        ORDER BY c.hora ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$hoy]);
$citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
$ahora = time();
$en_silla = [];
$en_espera = [];
foreach($citas as $c) {
    $hora_cita = strtotime($hoy . ' ' . $c['hora']);
    $hora_fin = $hora_cita + ($c['duracion'] * 60);
    if ($ahora >= $hora_cita && $ahora <= ($hora_fin + 600)) {
        $c['progress'] = min(100, round(($ahora - $hora_cita) / ($c['duracion'] * 60) * 100));
        $en_silla[] = $c;
    } elseif ($ahora < $hora_cita) {
        $en_espera[] = $c;
    }
}
$en_espera = array_slice($en_espera, 0, 6);
$total_hoy = count($citas);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>ALCORTE — TV Mode</title>
    <meta http-equiv="refresh" content="30">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Orbitron:wght@500;700;900&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body{overflow:hidden;height:100vh;display:flex;flex-direction:column}
        .tv-header{padding:32px 48px;display:flex;justify-content:space-between;align-items:flex-end;border-bottom:1px solid rgba(255,255,255,.06)}
        .clock{font-family:'Orbitron',sans-serif;font-size:4rem;font-weight:900;line-height:1;text-shadow:0 0 30px rgba(212,168,83,.3)}
        .tv-main{flex:1;display:grid;grid-template-columns:7fr 5fr;gap:0;overflow:hidden}
        .panel-left{padding:36px 48px;display:flex;flex-direction:column;gap:24px;overflow:hidden}
        .panel-right{background:rgba(11,18,32,.5);border-left:1px solid rgba(255,255,255,.05);padding:36px;display:flex;flex-direction:column;overflow:hidden}
        .chair-card{
            background:linear-gradient(135deg,rgba(17,26,46,.8),rgba(11,18,32,.9));
            border:1px solid rgba(255,255,255,.06);border-radius:var(--radius-xl);
            padding:32px;display:flex;justify-content:space-between;align-items:center;
            position:relative;overflow:hidden;animation:fadeUp .6s ease both;
        }
        .chair-card::before{content:'';position:absolute;left:0;top:0;bottom:0;width:4px;background:var(--gold);border-radius:0 4px 4px 0}
        .chair-card .glow{position:absolute;right:-40px;top:-40px;width:150px;height:150px;background:radial-gradient(circle,rgba(212,168,83,.08),transparent 70%);pointer-events:none}
        .progress-bar-tv{height:4px;background:rgba(255,255,255,.06);border-radius:var(--radius-full);overflow:hidden;margin-top:12px}
        .progress-bar-tv .fill{height:100%;background:linear-gradient(90deg,var(--gold),var(--gold-light));border-radius:var(--radius-full);transition:width 1s ease}
        .queue-item{
            display:flex;align-items:center;justify-content:space-between;padding:16px 20px;
            background:rgba(0,0,0,.3);border-radius:var(--radius-lg);
            border-left:3px solid var(--gray700);animation:fadeUp .5s ease both;
        }
        .queue-item:first-child{border-left-color:var(--gold);background:rgba(212,168,83,.04)}
        .stat-card{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.05);border-radius:var(--radius-lg);padding:20px;text-align:center}
        .blink{animation:breathe 2s infinite}
        .aurora{position:fixed;inset:0;pointer-events:none;z-index:0;opacity:.3}
        .aurora div{position:absolute;border-radius:50%;filter:blur(120px)}
        .aurora .a1{width:500px;height:500px;background:rgba(212,168,83,.08);top:-200px;right:10%;animation:float 8s ease-in-out infinite}
        .aurora .a2{width:400px;height:400px;background:rgba(46,111,64,.06);bottom:-100px;left:5%;animation:float 10s ease-in-out infinite reverse}
    </style>
</head>
<body>
    <div class="aurora"><div class="a1"></div><div class="a2"></div></div>

    <header class="tv-header" style="position:relative;z-index:2">
        <div>
            <h1 style="font-size:2.5rem;font-weight:900;letter-spacing:.1em;text-transform:uppercase;font-style:italic">AL<span class="text-gold" style="font-style:normal">CORTE</span></h1>
            <p style="font-size:1rem;color:var(--gray500);letter-spacing:.2em;text-transform:uppercase;margin-top:4px">Turnos en Vivo</p>
        </div>
        <div style="text-align:right">
            <div class="clock text-gold" id="liveClock"><?= date('H:i') ?></div>
            <p style="font-size:.85rem;color:var(--gold);font-weight:700;letter-spacing:.15em;text-transform:uppercase"><?= strftime("%A, %d de %B") ?></p>
        </div>
    </header>

    <main class="tv-main" style="position:relative;z-index:2">
        <div class="panel-left">
            <h2 class="flex items-center gap-3" style="font-size:1.4rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em">
                <i class="fas fa-cut text-gold animate-float"></i>
                <span>Atendiendo Ahora</span>
            </h2>

            <?php if(empty($en_silla)): ?>
                <div style="flex:1;display:flex;align-items:center;justify-content:center;border:2px dashed rgba(255,255,255,.06);border-radius:var(--radius-xl)">
                    <p style="font-size:1.3rem;color:var(--gray700);text-transform:uppercase;letter-spacing:.1em">Sala operativa libre</p>
                </div>
            <?php else: ?>
                <?php foreach($en_silla as $i => $c): ?>
                <div class="chair-card" style="animation-delay:<?= $i*0.15 ?>s">
                    <div class="glow"></div>
                    <div style="position:relative;z-index:2">
                        <p style="font-size:.85rem;color:var(--gray500);text-transform:uppercase;letter-spacing:.15em">Cliente</p>
                        <h3 style="font-size:2.2rem;font-weight:900;margin:4px 0 12px"><?= htmlspecialchars(explode(' ', $c['cliente_nombre'])[0]) ?></h3>
                        <div class="flex items-center gap-3" style="background:rgba(0,0,0,.3);padding:8px 14px;border-radius:var(--radius);display:inline-flex">
                            <div style="width:36px;height:36px;border-radius:50%;background:var(--gold);display:flex;align-items:center;justify-content:center;font-weight:900;color:var(--bg);font-size:.9rem"><?= mb_substr($c['barbero'] ?? 'B',0,1) ?></div>
                            <span style="font-size:1rem;color:var(--gray300)"><?= htmlspecialchars($c['barbero'] ?? 'Barbero') ?></span>
                        </div>
                        <div class="progress-bar-tv" style="width:200px">
                            <div class="fill" style="width:<?= $c['progress'] ?>%"></div>
                        </div>
                    </div>
                    <div style="text-align:right;position:relative;z-index:2">
                        <div class="flex items-center gap-3" style="font-size:2.8rem;font-weight:900;font-family:'Orbitron',sans-serif">
                            <i class="fas fa-clock text-gold" style="font-size:1.5rem"></i>
                            <?= substr($c['hora'],0,5) ?>
                        </div>
                        <span class="badge badge-gold blink" style="margin-top:8px;font-size:.7rem;padding:6px 14px">En Progreso</span>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Stats -->
            <div class="grid grid-3 gap-4" style="margin-top:auto">
                <div class="stat-card">
                    <p style="font-size:.65rem;color:var(--gray500);text-transform:uppercase;letter-spacing:.1em;font-weight:700">Total Hoy</p>
                    <p class="font-mono font-black text-3xl" style="margin-top:4px"><?= $total_hoy ?></p>
                </div>
                <div class="stat-card">
                    <p style="font-size:.65rem;color:var(--gray500);text-transform:uppercase;letter-spacing:.1em;font-weight:700">En Silla</p>
                    <p class="font-mono font-black text-3xl text-gold" style="margin-top:4px"><?= count($en_silla) ?></p>
                </div>
                <div class="stat-card">
                    <p style="font-size:.65rem;color:var(--gray500);text-transform:uppercase;letter-spacing:.1em;font-weight:700">En Cola</p>
                    <p class="font-mono font-black text-3xl" style="margin-top:4px"><?= count($en_espera) ?></p>
                </div>
            </div>
        </div>

        <div class="panel-right">
            <h2 class="flex items-center gap-3" style="font-size:1.1rem;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:var(--gray300);margin-bottom:24px">
                <i class="fas fa-hourglass-half"></i> Próximos Turnos
            </h2>
            <div class="flex flex-col gap-3" style="flex:1;overflow:hidden">
                <?php if(empty($en_espera)): ?>
                    <div style="flex:1;display:flex;align-items:center;justify-content:center">
                        <p style="font-size:1rem;color:var(--gray700)">No hay más citas en cola.</p>
                    </div>
                <?php else: ?>
                    <?php foreach($en_espera as $i => $e): ?>
                    <div class="queue-item" style="animation-delay:<?= $i*0.1 ?>s">
                        <div>
                            <p style="font-size:1.1rem;font-weight:800"><?= htmlspecialchars($e['cliente_nombre']) ?></p>
                            <p style="font-size:.75rem;color:var(--gray500)">con <?= htmlspecialchars($e['barbero'] ?? 'Barbero') ?></p>
                        </div>
                        <div style="text-align:right">
                            <p class="font-mono font-black" style="font-size:1.4rem"><?= substr($e['hora'],0,5) ?></p>
                            <p style="font-size:.6rem;color:var(--gray500);text-transform:uppercase;letter-spacing:.1em">Hora Cita</p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div style="margin-top:auto;padding-top:24px;border-top:1px solid rgba(255,255,255,.05);text-align:center">
                <p style="color:var(--gray500);font-size:.8rem;margin-bottom:8px">¿Quieres agendar?</p>
                <div class="btn btn-primary btn-sm" style="display:inline-flex">Escanea el QR en Recepción</div>
            </div>
        </div>
    </main>

    <script>
        // Live clock
        function updateClock() {
            const now = new Date();
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            document.getElementById('liveClock').textContent = h + ':' + m;
        }
        setInterval(updateClock, 1000);
    </script>
</body>
</html>