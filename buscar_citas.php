<?php
require 'db.php';
$citas = [];
$busqueda_realizada = false;
if (isset($_POST['telefono'])) {
    $busqueda_realizada = true;
    $tel_busqueda = preg_replace('/[^0-9]/', '', $_POST['telefono']);
    if (!empty($tel_busqueda)) {
        $sql = "SELECT c.*, b.nombre as barbero 
                FROM citas c 
                LEFT JOIN barberos b ON c.barbero_id = b.id 
                WHERE REPLACE(REPLACE(c.cliente_telefono, ' ', ''), '-', '') LIKE ? 
                ORDER BY c.fecha DESC LIMIT 10";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['%' . $tel_busqueda . '%']);
        $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ALCORTE — Consultar Turno</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        .search-wrap{background:var(--bg2);padding:5px;border-radius:var(--radius-lg);display:flex;border:1px solid rgba(255,255,255,.06);
            transition:var(--transition);box-shadow:var(--shadow)}
        .search-wrap:focus-within{border-color:var(--gold)}
        .search-wrap input{flex:1;padding:14px 16px;background:none;font-size:.8rem;font-weight:600}
        .search-wrap button{padding:0 20px;border-radius:var(--radius);background:linear-gradient(135deg,var(--green),var(--gold));color:var(--white);font-weight:700}
        .cita-card{position:relative;overflow:hidden;animation:fadeUp .5s ease both}
        .cita-card::before{content:'';position:absolute;left:0;top:0;bottom:0;width:3px;border-radius:0 3px 3px 0}
        .cita-card.approved::before{background:var(--green-light)}
        .cita-card.pending::before{background:var(--gold)}
        .timeline-dot{width:10px;height:10px;border-radius:50%;border:2px solid var(--gray700);flex-shrink:0}
        .timeline-dot.active{border-color:var(--gold);background:var(--gold);box-shadow:0 0 8px rgba(212,168,83,.4)}
    </style>
</head>
<body style="min-height:100vh;display:flex;flex-direction:column;justify-content:space-between">
    <canvas id="particles"></canvas>
    <div class="page-content container" style="padding-top:60px;flex:1">
        <div class="text-center" style="margin-bottom:32px" data-reveal>
            <h1 style="font-size:1.6rem;font-weight:900;letter-spacing:.06em;text-transform:uppercase;font-style:italic">AL<span class="text-gold" style="font-style:normal">CORTE</span></h1>
            <p style="font-size:.55rem;color:var(--gray500);font-weight:800;letter-spacing:.2em;text-transform:uppercase;margin-top:4px">Gestión y Validación de Citas</p>
        </div>

        <form method="POST" style="margin-bottom:32px" data-reveal>
            <div class="search-wrap">
                <input type="tel" name="telefono" placeholder="Ingresa tu número telefónico..." required>
                <button type="submit"><i class="fas fa-search"></i></button>
            </div>
        </form>

        <?php if ($busqueda_realizada): ?>
            <?php if (count($citas) > 0): ?>
                <div class="flex flex-col gap-4">
                    <?php foreach ($citas as $i => $c): ?>
                        <div class="cita-card glass <?= $c['estado_pago']=='verificado'?'approved':'pending' ?>" style="padding:20px;padding-left:20px;animation-delay:<?= $i*0.08 ?>s" data-reveal>
                            <div class="flex justify-between items-center" style="margin-bottom:12px">
                                <div>
                                    <h3 style="font-size:.8rem;font-weight:800;letter-spacing:.03em;text-transform:uppercase"><?= htmlspecialchars($c['servicio']) ?></h3>
                                    <p style="font-size:.7rem;color:var(--gray500);margin-top:3px">Atendido por: <span class="text-gold font-semibold"><?= htmlspecialchars($c['barbero']) ?></span></p>
                                </div>
                                <?php if($c['estado_pago'] == 'verificado'): ?>
                                    <span class="badge badge-green">Aprobada</span>
                                <?php else: ?>
                                    <span class="badge badge-gold">Pendiente</span>
                                <?php endif; ?>
                            </div>
                            <div class="flex items-center gap-4 font-mono" style="font-size:.75rem;color:var(--gray300);margin-bottom:14px">
                                <span><i class="far fa-calendar-alt text-gold" style="margin-right:6px"></i><?= $c['fecha'] ?></span>
                                <span><i class="far fa-clock text-gold" style="margin-right:6px"></i><?= substr($c['hora'], 0, 5) ?></span>
                            </div>
                            <?php if($c['estado_pago'] == 'verificado'): ?>
                                <a href="ver_ticket.php?id=<?= $c['id'] ?>" class="btn btn-primary btn-sm w-full">
                                    <i class="fas fa-ticket-alt"></i>
                                    <span>Ver Ticket de Acceso</span>
                                </a>
                            <?php else: ?>
                                <div style="background:rgba(255,255,255,.02);padding:12px;border-radius:var(--radius);border:1px solid rgba(255,255,255,.04);text-align:center">
                                    <p style="font-size:.65rem;color:var(--gray500);font-style:italic">Tu comprobante de pago está en proceso de validación por mesa de control.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="glass text-center" style="padding:48px 20px" data-reveal>
                    <div style="font-size:2.5rem;margin-bottom:12px;opacity:.5">🔍</div>
                    <h3 style="font-size:.9rem;font-weight:800">Sin registros coincidentes</h3>
                    <p style="font-size:.7rem;color:var(--gray500);margin-top:6px">Verifica la numeración telefónica cargada durante el proceso de reserva.</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div class="text-center" style="padding:24px 0">
        <a href="index.php" style="font-size:.75rem;color:var(--gray500);font-weight:500;transition:var(--transition)">← Volver a la Sala de Reservas</a>
    </div>
    <script src="app.js"></script>
    <script>new ParticleSystem('particles', 20);</script>
</body>
</html>