<?php
session_start();
require_once __DIR__ . '/bootstrap.php';

$citas = [];
$telSesion = trim($_SESSION['usuario_telefono'] ?? '');
$nombreSesion = trim($_SESSION['usuario_nombre'] ?? '');
$logueado = !empty($_SESSION['usuario_id']) && ($_SESSION['usuario_rol'] ?? 'cliente') === 'cliente';

function cargarCortes(PDO $pdo, string $tel_busqueda): array
{
    $tel = preg_replace('/[^0-9]/', '', $tel_busqueda);
    if ($tel === '') {
        return [];
    }
    $sql = "SELECT c.*, b.nombre as barbero
            FROM citas c
            LEFT JOIN barberos b ON c.barbero_id = b.id
            WHERE REPLACE(REPLACE(REPLACE(c.cliente_telefono, ' ', ''), '-', ''), '+', '') LIKE ?
            ORDER BY c.fecha DESC, c.hora DESC
            LIMIT 20";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['%' . $tel . '%']);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$tel_consulta = '';
if (isset($_POST['telefono'])) {
    $tel_consulta = trim($_POST['telefono']);
} elseif ($telSesion !== '') {
    $tel_consulta = $telSesion;
}

$lista_mostrada = false;
if ($tel_consulta !== '') {
    $citas = cargarCortes($pdo, $tel_consulta);
    $lista_mostrada = true;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#cfa87b">
    <title>ALCORTE — Mis cortes</title>
    <link rel="stylesheet" href="assets/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        .cita-card{position:relative;overflow:hidden;animation:fadeUp .5s ease both}
        .cita-card::before{content:'';position:absolute;left:0;top:0;bottom:0;width:3px;border-radius:0 3px 3px 0}
        .cita-card.approved::before{background:var(--green-light)}
        .cita-card.pending::before{background:var(--gold)}
    </style>
</head>
<body style="min-height:100vh;display:flex;flex-direction:column;justify-content:space-between">
    <canvas id="particles"></canvas>
    <div class="page-content container" style="padding-top:60px;flex:1">
        <div class="text-center" style="margin-bottom:28px" data-reveal>
            <div style="width:48px;height:48px;margin:0 auto 12px;border-radius:14px;background:rgba(207,168,123,.12);border:1px solid rgba(207,168,123,.25);display:flex;align-items:center;justify-content:center">
                <i class="fas fa-scissors text-gold" style="font-size:1.1rem"></i>
            </div>
            <h1 style="font-size:1.4rem;font-weight:900;letter-spacing:.04em;text-transform:uppercase">Mis cortes</h1>
            <?php if ($nombreSesion !== ''): ?>
            <p style="font-size:1.1rem;color:var(--gold);font-weight:800;margin-top:8px">¡Hola, <?php echo htmlspecialchars(explode(' ', $nombreSesion)[0]); ?>!</p>
            <?php endif; ?>
            <p style="font-size:.65rem;color:var(--gray500);font-weight:700;letter-spacing:.15em;text-transform:uppercase;margin-top:6px">Historial de tus visitas</p>
        </div>

        <?php if (!$logueado): ?>
        <form method="POST" style="margin-bottom:28px" data-reveal>
            <label class="form-label" style="display:block;margin-bottom:8px;font-size:.65rem;text-transform:uppercase;letter-spacing:.1em;color:var(--gray500)">Tu teléfono</label>
            <div class="flex gap-2">
                <input type="tel" name="telefono" placeholder="0424..." required class="input flex-1"
                       value="<?php echo htmlspecialchars($_POST['telefono'] ?? ''); ?>">
                <button type="submit" class="btn btn-gold" style="padding:0 18px;white-space:nowrap">
                    <i class="fas fa-scissors"></i>
                </button>
            </div>
            <p style="font-size:.65rem;color:var(--gray500);margin-top:10px;text-align:center">
                <a href="login.php" class="text-gold font-bold">Inicia sesión</a> para ver tus cortes al instante.
            </p>
        </form>
        <?php endif; ?>

        <?php if ($lista_mostrada): ?>
            <?php if (count($citas) > 0): ?>
                <div class="flex flex-col gap-4">
                    <?php foreach ($citas as $i => $c): ?>
                        <div class="cita-card glass <?= $c['estado_pago']=='verificado'?'approved':'pending' ?>" style="padding:20px;animation-delay:<?= $i*0.08 ?>s" data-reveal>
                            <div class="flex justify-between items-center" style="margin-bottom:12px">
                                <div>
                                    <h3 style="font-size:.8rem;font-weight:800;letter-spacing:.03em;text-transform:uppercase"><?= htmlspecialchars($c['servicio']) ?></h3>
                                    <p style="font-size:.7rem;color:var(--gray500);margin-top:3px">Barbero: <span class="text-gold font-semibold"><?= htmlspecialchars($c['barbero'] ?? '—') ?></span></p>
                                </div>
                                <?php if($c['estado_pago'] == 'verificado'): ?>
                                    <span class="badge badge-green">Confirmado</span>
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
                                    <span>Ver ticket</span>
                                </a>
                            <?php else: ?>
                                <div style="background:rgba(255,255,255,.02);padding:12px;border-radius:var(--radius);border:1px solid rgba(255,255,255,.04);text-align:center">
                                    <p style="font-size:.65rem;color:var(--gray500);font-style:italic">Pago en validación. Te avisamos cuando esté listo.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="glass text-center" style="padding:48px 20px" data-reveal>
                    <div style="font-size:2.5rem;margin-bottom:12px;opacity:.5">✂️</div>
                    <h3 style="font-size:.9rem;font-weight:800">Aún no tienes cortes</h3>
                    <p style="font-size:.7rem;color:var(--gray500);margin-top:6px">Reserva tu primera cita y aparecerá aquí.</p>
                    <a href="index.php" class="btn btn-gold" style="margin-top:16px;display:inline-flex">Reservar ahora</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div class="text-center" style="padding:24px 0">
        <a href="index.php" style="font-size:.75rem;color:var(--gray500);font-weight:500">← Volver a reservar</a>
    </div>
    <script src="assets/app.js"></script>
    <script>new ParticleSystem('particles', 20);</script>
</body>
</html>
