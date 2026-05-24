<?php
require_once __DIR__ . '/bootstrap.php';
$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT c.*, b.nombre as barbero FROM citas c LEFT JOIN barberos b ON c.barbero_id = b.id WHERE c.id = ?");
$stmt->execute([$id]);
$cita = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$cita) die("Ticket inválido");

$config = barberia_fetch_config($pdo);
$branding = barberia_branding_from_config($config);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AlCorte — Comprobante de reserva</title>
    <link rel="stylesheet" href="assets/styles.css">
    <?php include __DIR__ . '/includes/brand-styles.php'; ?>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Orbitron:wght@500;700;900&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body{display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:100vh;overflow:hidden}
        .card-wrap{perspective:1000px}
        .holo-card{
            width:340px;max-width:90vw;height:560px;border-radius:var(--radius-xl);position:relative;
            background:rgba(15,15,25,.75);border:1px solid rgba(255,255,255,.08);
            backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);
            box-shadow:0 0 60px rgba(0,0,0,.6),0 0 30px rgba(212,168,83,.05);
            display:flex;flex-direction:column;justify-content:space-between;padding:28px 24px;
            transform-style:preserve-3d;transition:transform .4s ease;overflow:hidden;
        }
        .holo-foil{position:absolute;inset:0;border-radius:var(--radius-xl);opacity:.4;
            background:linear-gradient(135deg,transparent 30%,rgba(212,168,83,.08) 42%,rgba(255,255,255,.06) 48%,transparent 58%);
            pointer-events:none;mix-blend-mode:overlay;animation:foilShift 6s ease-in-out infinite alternate}
        @keyframes foilShift{0%{background-position:0% 0%}100%{background-position:100% 100%}}
        .scan-line{position:absolute;width:100%;height:2px;background:var(--gold);opacity:.3;
            box-shadow:0 0 15px var(--gold);animation:scanMove 4s infinite linear;pointer-events:none;z-index:5}
        @keyframes scanMove{0%{top:-2px;opacity:0}10%{opacity:.4}90%{opacity:.4}100%{top:100%;opacity:0}}
        .notch{position:absolute;top:-1px;left:50%;transform:translateX(-50%);width:80px;height:16px;
            background:var(--bg);border-bottom-left-radius:10px;border-bottom-right-radius:10px;
            border:1px solid rgba(255,255,255,.06);border-top:0}
        .neon-text{font-family:'Orbitron',sans-serif;text-shadow:0 0 12px rgba(212,168,83,.5)}
        .data-row{display:flex;justify-content:space-between;align-items:center;padding:10px 16px;
            background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:var(--radius)}
        .barcode{display:flex;gap:1.5px;align-items:end;height:40px;justify-content:center;opacity:.4}
        .barcode span{display:block;background:var(--white);border-radius:1px}
        .share-btn{position:fixed;bottom:20px;right:20px;z-index:10}
    </style>
</head>
<body>
    <canvas id="particles"></canvas>
    <div class="card-wrap page-content animate-scaleIn">
        <div class="holo-card" id="pass">
            <div class="scan-line"></div>
            <div class="holo-foil"></div>
            <div class="notch"></div>

            <!-- Top: Brand -->
            <div class="brand-block--ticket" style="margin-top:12px;position:relative;z-index:2">
                <?php
                $brand_variant = 'center';
                $brand_subline = 'Cita confirmada';
                $brand_subline_muted = true;
                include __DIR__ . '/includes/brand-header.php';
                ?>
            </div>

            <!-- Middle: Data -->
            <div style="position:relative;z-index:2" class="flex flex-col gap-4">
                <div class="text-center">
                    <div style="width:72px;height:72px;margin:0 auto;border-radius:50%;padding:3px;border:2px solid var(--gold);box-shadow:0 0 20px rgba(212,168,83,.2)">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($cita['cliente_nombre']) ?>&background=0b1220&color=fff&size=128" style="width:100%;height:100%;border-radius:50%">
                    </div>
                    <h2 style="font-size:1.15rem;font-weight:900;text-transform:uppercase;margin-top:10px;letter-spacing:.04em"><?= htmlspecialchars(explode(' ', $cita['cliente_nombre'])[0]) ?></h2>
                    <p style="font-size:.6rem;color:var(--gray500)">Cliente</p>
                </div>

                <div class="data-row">
                    <div class="text-center" style="flex:1">
                        <p style="font-size:.5rem;color:var(--gray500);text-transform:uppercase;letter-spacing:.1em">Fecha</p>
                        <p class="text-gold font-bold" style="font-size:1.1rem"><?= date('d', strtotime($cita['fecha'])) . '/' . barberia_mes_corto((int) date('n', strtotime($cita['fecha']))) ?></p>
                    </div>
                    <div style="width:1px;height:28px;background:rgba(255,255,255,.08)"></div>
                    <div class="text-center" style="flex:1">
                        <p style="font-size:.5rem;color:var(--gray500);text-transform:uppercase;letter-spacing:.1em">Hora</p>
                        <p class="font-mono font-black" style="font-size:1.5rem"><?= substr($cita['hora'],0,5) ?></p>
                    </div>
                </div>

                <div class="text-center">
                    <p style="font-size:.55rem;color:var(--gray500);text-transform:uppercase;letter-spacing:.15em">Detalle del servicio</p>
                    <p style="font-size:.8rem;font-weight:700;margin-top:3px"><?= htmlspecialchars($cita['barbero']) ?> · <?= htmlspecialchars($cita['servicio']) ?></p>
                </div>
            </div>

            <!-- Bottom: Status + Barcode -->
            <div class="text-center" style="position:relative;z-index:2">
                <span class="badge badge-green" style="margin-bottom:8px">● Reserva confirmada</span>
                <div class="barcode" id="barcode"></div>
                <p class="font-mono" style="font-size:.5rem;color:var(--gray700);margin-top:6px">N.º <?= str_pad($id, 8, '0', STR_PAD_LEFT) ?> · Ref. <?= htmlspecialchars($cita['referencia_pago']) ?></p>
            </div>
        </div>
    </div>

    <p style="color:var(--gray500);font-size:.7rem;margin-top:24px;animation:breathe 2s infinite;position:relative;z-index:2">Haz captura de pantalla para ingresar</p>
    <a href="index.php" class="text-gold font-bold page-content" style="font-size:.7rem;margin-top:12px;letter-spacing:.15em;text-transform:uppercase">Volver al inicio</a>

    <script src="assets/app.js"></script>
    <script>
        new ParticleSystem('particles', 25);
        // Generate CSS barcode
        const bc = document.getElementById('barcode');
        const ticketId = "<?= str_pad($id, 8, '0', STR_PAD_LEFT) ?>";
        for(let i = 0; i < 50; i++) {
            const bar = document.createElement('span');
            const w = (ticketId.charCodeAt(i % ticketId.length) % 3) + 1;
            const h = 20 + Math.random() * 20;
            bar.style.width = w + 'px';
            bar.style.height = h + 'px';
            bc.appendChild(bar);
        }
        // Tilt effect
        const card = document.getElementById('pass');
        card.addEventListener('mousemove', e => {
            const rect = card.getBoundingClientRect();
            const x = (e.clientX - rect.left) / rect.width - 0.5;
            const y = (e.clientY - rect.top) / rect.height - 0.5;
            card.style.transform = `rotateY(${x*15}deg) rotateX(${-y*15}deg)`;
        });
        card.addEventListener('mouseleave', () => { card.style.transform = 'rotateY(0) rotateX(0)'; });
    </script>
</body>
</html>