<?php
session_start();
require_once __DIR__ . '/bootstrap.php';
if (!isset($_SESSION['usuario_id'])) { $_SESSION['usuario_id']=1; $_SESSION['usuario_nombre']="Jefe"; $_SESSION['usuario_rol']="admin"; }
$hoy = date('Y-m-d');
$ws_url = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- LÓGICA DE CITAS ---
    if (isset($_POST['accion_cita'])) {
        if ($_POST['accion_cita'] == 'aprobar') {
            $cita_id = $_POST['cita_id'];
            $pdo->prepare("UPDATE citas SET estado_pago='verificado' WHERE id=?")->execute([$cita_id]);
            $stmt = $pdo->prepare("SELECT * FROM citas WHERE id = ?"); $stmt->execute([$cita_id]); $cita = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($cita) {
                $tel = preg_replace('/[^0-9]/', '', $cita['cliente_telefono']);
                $chk = $pdo->prepare("SELECT id FROM clientes WHERE telefono LIKE ?"); $chk->execute(["%$tel%"]); $cli = $chk->fetch();
                if($cli) { $pdo->prepare("UPDATE clientes SET puntos = puntos + 1, ultima_visita = NOW() WHERE id = ?")->execute([$cli['id']]); }
                else { $pdo->prepare("INSERT INTO clientes (telefono, nombre, puntos, ultima_visita) VALUES (?, ?, 1, NOW())")->execute([$tel, $cita['cliente_nombre']]); }
                if (strlen($tel) == 10) $tel = "58" . $tel;
                $msg = "✅ *CITA CONFIRMADA*%0AHola " . explode(' ', $cita['cliente_nombre'])[0] . ", tu turno para *" . $cita['servicio'] . "* está listo.%0A📅 " . date("d/m", strtotime($cita['fecha'])) . " ⏰ " . substr($cita['hora'],0,5);
                $ws_url = "https://wa.me/" . $tel . "?text=" . $msg;
            }
        } elseif ($_POST['accion_cita'] == 'borrar') { $pdo->prepare("DELETE FROM citas WHERE id=?")->execute([$_POST['cita_id']]); }
    }

    // --- LÓGICA DE SERVICIOS ---
    if (isset($_POST['accion_servicio'])) {
        if ($_POST['accion_servicio'] == 'crear') {
            $img = "https://images.unsplash.com/photo-1621605815971-fbc98d665033?q=80&w=400"; // Imagen por defecto
            
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == UPLOAD_ERR_OK) {
                $dir = 'uploads/';
                if (!is_dir($dir)) mkdir($dir, 0777, true);
                $nombre_archivo = time() . '_serv_' . basename($_FILES['imagen']['name']);
                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $dir . $nombre_archivo)) {
                    $img = $dir . $nombre_archivo;
                }
            }
            $pdo->prepare("INSERT INTO servicios (nombre, precio, duracion, imagen, activo) VALUES (?, ?, ?, ?, 1)")->execute([$_POST['nombre'], $_POST['precio'], $_POST['duracion'], $img]);
        
        // NUEVA LÓGICA: EDITAR SERVICIO
        } elseif ($_POST['accion_servicio'] == 'editar') {
            $id = $_POST['id'];
            $nombre = $_POST['nombre'];
            $precio = $_POST['precio'];
            $duracion = $_POST['duracion'];
            
            $query = "UPDATE servicios SET nombre=?, precio=?, duracion=? WHERE id=?";
            $params = [$nombre, $precio, $duracion, $id];

            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == UPLOAD_ERR_OK) {
                $dir = 'uploads/';
                if (!is_dir($dir)) mkdir($dir, 0777, true);
                $nombre_archivo = time() . '_serv_' . basename($_FILES['imagen']['name']);
                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $dir . $nombre_archivo)) {
                    $img = $dir . $nombre_archivo;
                    $query = "UPDATE servicios SET nombre=?, precio=?, duracion=?, imagen=? WHERE id=?";
                    $params = [$nombre, $precio, $duracion, $img, $id];
                }
            }
            $pdo->prepare($query)->execute($params);

        } elseif ($_POST['accion_servicio'] == 'borrar') { 
            $pdo->prepare("DELETE FROM servicios WHERE id=?")->execute([$_POST['id']]); 
        }
    }

    // --- LÓGICA DE BARBEROS ---
    if (isset($_POST['accion_barbero'])) {
        if ($_POST['accion_barbero'] == 'crear') {
            $foto = "https://ui-avatars.com/api/?background=0b1220&color=fff&name=" . urlencode($_POST['nombre']);
            
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] == UPLOAD_ERR_OK) {
                $dir = 'uploads/';
                if (!is_dir($dir)) mkdir($dir, 0777, true);
                $nombre_archivo = time() . '_barb_' . basename($_FILES['foto']['name']);
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $dir . $nombre_archivo)) {
                    $foto = $dir . $nombre_archivo;
                }
            }

            $d_ini = !empty($_POST['h_desc_ini']) ? $_POST['h_desc_ini'] : null;
            $d_fin = !empty($_POST['h_desc_fin']) ? $_POST['h_desc_fin'] : null;
            $pdo->prepare("INSERT INTO barberos (nombre, hora_inicio, hora_fin, hora_descanso_inicio, hora_descanso_fin, foto_url, activo) VALUES (?, ?, ?, ?, ?, ?, 1)")->execute([$_POST['nombre'], $_POST['h_ini'], $_POST['h_fin'], $d_ini, $d_fin, $foto]);
        } elseif ($_POST['accion_barbero'] == 'borrar') { $pdo->prepare("DELETE FROM barberos WHERE id=?")->execute([$_POST['id']]); }
    }

    // --- LÓGICA DE CONFIGURACIÓN ---
    if (isset($_POST['guardar_config'])) {
        $data = $_POST;
        unset($data['guardar_config']);
        $checks = ['estado_movil', 'estado_zelle', 'estado_efectivo'];
        foreach ($checks as $k) {
            $data[$k] = isset($_POST[$k]) ? '1' : '0';
        }
        if (!empty($_FILES['logo']['name'])) {
            $nuevoLogo = barberia_upload_logo($_FILES['logo'], __DIR__ . '/uploads/');
            if ($nuevoLogo) {
                barberia_config_set($pdo, 'logo_url', $nuevoLogo);
            }
        }
        $permitidas = array_keys(barberia_config_defaults());
        foreach ($data as $key => $val) {
            if (!in_array($key, $permitidas, true) || $key === 'logo_url') {
                continue;
            }
            barberia_config_set($pdo, $key, is_string($val) ? trim($val) : '');
        }
    }
}

$pendientes = $pdo->query("SELECT c.*, b.nombre as barbero FROM citas c LEFT JOIN barberos b ON c.barbero_id=b.id WHERE c.estado_pago='pendiente' ORDER BY c.fecha, c.hora")->fetchAll(PDO::FETCH_ASSOC);
$hoy_citas = $pdo->query("SELECT c.*, b.nombre as barbero FROM citas c LEFT JOIN barberos b ON c.barbero_id=b.id WHERE c.estado_pago='verificado' AND c.fecha='$hoy' ORDER BY c.hora")->fetchAll(PDO::FETCH_ASSOC);
$historial = $pdo->query("SELECT c.*, b.nombre as barbero FROM citas c LEFT JOIN barberos b ON c.barbero_id=b.id ORDER BY c.fecha DESC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
$servicios = $pdo->query("SELECT * FROM servicios")->fetchAll(PDO::FETCH_ASSOC);
$barberos = $pdo->query("SELECT * FROM barberos")->fetchAll(PDO::FETCH_ASSOC);
$conf = barberia_fetch_config($pdo);
$branding = barberia_branding_from_config($conf);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ALCORTE — Panel Maestro</title>
    <link rel="stylesheet" href="assets/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        .admin-nav{position:sticky;top:0;z-index:50;padding:16px 24px;background:rgba(7,13,25,.92);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border-bottom:1px solid rgba(255,255,255,.05)}
        .nav-tabs{display:flex;flex-wrap:wrap;gap:4px 20px;justify-content:center}
        .nav-tab{padding:8px 0;font-size:.8rem;font-weight:600;color:var(--gray500);border-bottom:2px solid transparent;transition:var(--transition);cursor:pointer;background:none}
        .nav-tab:hover{color:var(--gray300)}
        .nav-tab.active{color:var(--white);border-bottom-color:var(--gold)}
        .nav-tab i{margin-right:6px;font-size:.7rem}
        .view{display:none;animation:fadeUp .4s ease both}.view.active{display:block}
        .kpi{padding:24px;border-radius:var(--radius-lg);background:var(--white);color:var(--bg)}
        .kpi .label{font-size:.6rem;font-weight:800;color:rgba(7,13,25,.5);text-transform:uppercase;letter-spacing:.1em}
        .kpi .value{font-size:2.2rem;font-weight:900;font-family:var(--font-mono);margin-top:4px;color:var(--bg)}
        .pending-card{position:relative;overflow:hidden}
        .pending-card::before{content:'';position:absolute;left:0;top:0;bottom:0;width:3px;background:var(--gold);border-radius:0 3px 3px 0}
        .tbl{width:100%;text-align:left;border-collapse:collapse}
        .tbl th{padding:14px 16px;font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:var(--gray500);background:rgba(0,0,0,.2);border-bottom:1px solid rgba(255,255,255,.05)}
        .tbl td{padding:12px 16px;font-size:.75rem;border-bottom:1px solid rgba(255,255,255,.03)}
        .tbl tr:hover td{background:rgba(255,255,255,.01)}
        .form-grid{display:grid;gap:16px}
        .form-label{display:block;font-size:.55rem;font-weight:800;color:var(--gray500);text-transform:uppercase;letter-spacing:.1em;margin-bottom:6px;padding-left:4px}
        .toggle-wrap{display:flex;align-items:center;gap:10px;cursor:pointer;user-select:none}
        .toggle-box{width:18px;height:18px;border-radius:5px;border:1.5px solid rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;transition:var(--transition);font-size:.55rem;color:var(--bg)}
        .toggle-wrap input:checked~.toggle-box{background:var(--gold);border-color:var(--gold)}
        .toggle-wrap input{display:none}
        
        /* Selector de archivos elegante */
        input[type="file"] { font-size: .7rem; color: var(--gray500); padding: 8px 0; cursor: pointer; }
        input[type="file"]::file-selector-button { background: var(--gold); color: var(--bg); border: none; padding: 6px 12px; border-radius: 4px; font-weight: 700; font-size: .65rem; margin-right: 12px; cursor: pointer; transition: 0.3s; text-transform: uppercase; letter-spacing: .05em; }
        input[type="file"]::file-selector-button:hover { background: var(--white); }
        
        /* Estilos del Modal Limpio */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); backdrop-filter: blur(8px); z-index: 1000; display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s; pointer-events: none; }
        .modal-overlay.active { opacity: 1; pointer-events: all; }
        .modal-content { width: 100%; max-width: 450px; padding: 32px; position: relative; transform: translateY(20px); transition: transform 0.3s; border: 1px solid rgba(255,255,255,0.05); }
        .modal-overlay.active .modal-content { transform: translateY(0); }
        .close-modal { position: absolute; top: 16px; right: 20px; background: none; border: none; color: var(--gray500); cursor: pointer; font-size: 1.2rem; transition: 0.3s; }
        .close-modal:hover { color: var(--white); transform: scale(1.1); }

        @media(min-width:768px){.form-grid-4{grid-template-columns:repeat(4,1fr)}.form-grid-2{grid-template-columns:1fr 1fr}}
    </style>
</head>
<body style="padding-bottom:40px">
    <nav class="admin-nav">
        <div class="container-lg flex justify-between items-center" style="flex-wrap:wrap;gap:16px">
            <div class="flex items-center gap-3">
                <span style="font-size:1.15rem;font-weight:900;letter-spacing:.08em;text-transform:uppercase;font-style:italic">AL<span class="text-gold" style="font-style:normal">CORTE</span></span>
                <span class="badge badge-gray" style="display:none" id="badge-desktop">GESTIÓN</span>
            </div>
            <div class="nav-tabs">
                <button class="nav-tab active" onclick="ver('dashboard')"><i class="fas fa-calendar-alt"></i>Agenda</button>
                <button class="nav-tab" onclick="ver('servicios')"><i class="fas fa-cut"></i>Servicios</button>
                <button class="nav-tab" onclick="ver('equipo')"><i class="fas fa-users"></i>Equipo</button>
                <button class="nav-tab" onclick="ver('historial')"><i class="fas fa-chart-bar"></i>Reportes</button>
                <button class="nav-tab" onclick="ver('config')"><i class="fas fa-cog"></i>Configuración</button>
            </div>
            <a href="acceso-movil.php" class="btn btn-ghost btn-sm" title="URL para abrir en el móvil"><i class="fas fa-mobile-alt"></i> Móvil</a>
            <a href="doc/README.md" class="btn btn-ghost btn-sm" title="Documentación" download><i class="fas fa-book"></i> Doc</a>
            <a href="index.php" target="_blank" class="btn btn-ghost btn-sm" style="display:none" id="link-desktop"><i class="fas fa-external-link-alt"></i> Ver Sitio</a>
        </div>
    </nav>

    <main class="container-lg" style="margin-top:32px">

        <section class="view active" id="v-dashboard">
            <div class="grid gap-8" style="grid-template-columns:1fr;align-items:start">
                <div class="grid grid-2 gap-4" style="grid-template-columns:repeat(auto-fit,minmax(160px,1fr))">
                    <div class="kpi"><p class="label">Citas Hoy</p><p class="value"><?= count($hoy_citas) + count($pendientes) ?></p></div>
                    <div class="kpi"><p class="label">Ingresos Est.</p><p class="value">$<?= count($hoy_citas)*15 ?></p></div>
                    <div class="kpi"><p class="label">Pendientes</p><p class="value" style="color:var(--gold-dark)"><?= count($pendientes) ?></p></div>
                    <div class="kpi"><p class="label">Confirmadas</p><p class="value" style="color:var(--green)"><?= count($hoy_citas) ?></p></div>
                </div>

                <div>
                    <h3 class="flex items-center gap-2" style="font-size:.7rem;font-weight:800;color:var(--gold);text-transform:uppercase;letter-spacing:.15em;margin-bottom:16px"><i class="fas fa-wallet"></i> Por Verificar</h3>
                    <?php if(empty($pendientes)): ?>
                        <div class="glass text-center" style="padding:24px;font-size:.8rem;color:var(--gray500);font-style:italic">No hay pagos pendientes por revisar.</div>
                    <?php else: ?>
                        <div class="grid gap-3" style="grid-template-columns:repeat(auto-fill,minmax(300px,1fr))">
                            <?php foreach($pendientes as $p): ?>
                            <div class="glass pending-card" style="padding:16px 16px 16px 20px">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p style="font-size:.85rem;font-weight:800"><?= htmlspecialchars($p['cliente_nombre']) ?></p>
                                        <p style="font-size:.7rem;color:var(--gray500);margin-top:2px"><?= htmlspecialchars($p['servicio']) ?> (<?= htmlspecialchars($p['metodo_pago']) ?>)</p>
                                        <span class="badge badge-gray font-mono" style="margin-top:6px">Ref: <?= htmlspecialchars($p['referencia_pago']) ?></span>
                                    </div>
                                    <div class="flex gap-2">
                                        <form method="POST"><input type="hidden" name="cita_id" value="<?= $p['id'] ?>">
                                            <button name="accion_cita" value="aprobar" class="btn btn-sm" style="background:var(--green);color:var(--white);width:36px;height:36px;padding:0"><i class="fas fa-check"></i></button>
                                        </form>
                                        <form method="POST" onsubmit="return confirm('¿Borrar cita?')"><input type="hidden" name="cita_id" value="<?= $p['id'] ?>">
                                            <button name="accion_cita" value="borrar" class="btn btn-danger btn-sm" style="width:36px;height:36px;padding:0"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div>
                    <h3 style="font-size:.9rem;font-weight:800;margin-bottom:16px">Calendario de Hoy</h3>
                    <?php if(empty($hoy_citas)): ?>
                        <div class="glass text-center" style="padding:32px;color:var(--gray500);font-size:.8rem;font-style:italic">Sin citas confirmadas activas para hoy.</div>
                    <?php else: ?>
                        <div class="flex flex-col gap-3">
                            <?php foreach($hoy_citas as $c): ?>
                            <div class="flex items-center gap-4">
                                <span class="font-mono font-bold" style="font-size:.75rem;color:var(--gray500);width:44px;text-align:right"><?= substr($c['hora'],0,5) ?></span>
                                <div class="glass flex-1 flex justify-between items-center" style="padding:14px 16px;background:linear-gradient(135deg,rgba(20,40,27,.3),rgba(32,28,19,.3))">
                                    <div>
                                        <h4 style="font-size:.75rem;font-weight:800;text-transform:uppercase;letter-spacing:.03em"><?= htmlspecialchars($c['cliente_nombre']) ?></h4>
                                        <p style="font-size:.65rem;color:var(--gray500);margin-top:2px"><?= htmlspecialchars($c['servicio']) ?> • <span class="text-gold">Con <?= htmlspecialchars($c['barbero']) ?></span></p>
                                    </div>
                                    <i class="fas fa-chevron-right" style="color:rgba(255,255,255,.15);font-size:.6rem"></i>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="view" id="v-servicios">
            <h2 style="font-size:1.1rem;font-weight:800;margin-bottom:20px">Gestión de Servicios</h2>
            <div class="glass" style="padding:24px;margin-bottom:24px">
                <form method="POST" enctype="multipart/form-data" class="form-grid form-grid-4">
                    <div style="grid-column:span 2"><label class="form-label">Nombre</label><input name="nombre" required placeholder="Ej: Corte + Barba" class="input"></div>
                    <div><label class="form-label">Precio ($)</label><input name="precio" type="number" step="0.01" required placeholder="15.00" class="input"></div>
                    <div><label class="form-label">Duración (min)</label><input name="duracion" type="number" step="5" value="30" class="input"></div>
                    <div style="grid-column:1/-1"><label class="form-label">Subir Imagen del Servicio</label><input type="file" name="imagen" accept="image/*" class="input" style="border:none; padding:8px 0; background:transparent;"></div>
                    <div style="grid-column:1/-1"><button type="submit" name="accion_servicio" value="crear" class="btn btn-gold w-full">Agregar Nuevo Servicio</button></div>
                </form>
            </div>
            
            <div class="grid gap-4" style="grid-template-columns:repeat(auto-fill,minmax(280px,1fr))">
                <?php foreach($servicios as $s): $safe_img = !empty($s['imagen']) ? $s['imagen'] : 'https://images.unsplash.com/photo-1621605815971-fbc98d665033?q=80&w=400'; ?>
                <div class="glass flex items-center gap-4 relative" style="padding:16px;overflow:visible">
                    <img src="<?= htmlspecialchars($safe_img) ?>" style="width:56px;height:56px;border-radius:var(--radius);object-fit:cover">
                    <div>
                        <p style="font-size:.85rem;font-weight:800"><?= htmlspecialchars($s['nombre']) ?></p>
                        <p class="text-gold font-mono font-black" style="font-size:.9rem;margin-top:2px">$<?= $s['precio'] ?></p>
                        <p class="font-mono" style="font-size:.65rem;color:var(--gray500)"><?= $s['duracion'] ?> min</p>
                    </div>
                    
                    <div style="position:absolute; top:10px; right:10px; display:flex; gap:6px;">
                        <button type="button" onclick="abrirModalServicio(<?= $s['id'] ?>, '<?= htmlspecialchars($s['nombre'], ENT_QUOTES) ?>', <?= $s['precio'] ?>, <?= $s['duracion'] ?>)" class="btn btn-icon btn-sm" style="background:rgba(255,255,255,0.1); width:28px; height:28px;"><i class="fas fa-pen" style="font-size:.6rem; color:var(--white);"></i></button>
                        
                        <form method="POST" onsubmit="return confirm('¿Eliminar?')">
                            <input type="hidden" name="id" value="<?= $s['id'] ?>">
                            <button name="accion_servicio" value="borrar" class="btn btn-danger btn-icon btn-sm" style="width:28px;height:28px"><i class="fas fa-trash" style="font-size:.6rem"></i></button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="view" id="v-equipo">
            <h2 style="font-size:1.1rem;font-weight:800;margin-bottom:20px">Gestión de Personal</h2>
            <div class="glass" style="padding:24px;margin-bottom:24px">
                <form method="POST" enctype="multipart/form-data" class="form-grid form-grid-4">
                    <div style="grid-column:span 2"><label class="form-label">Nombre</label><input name="nombre" required placeholder="Ej: Joshy" class="input"></div>
                    <div><label class="form-label">Hora Entrada</label><input name="h_ini" type="time" value="09:00" class="input"></div>
                    <div><label class="form-label">Hora Salida</label><input name="h_fin" type="time" value="18:00" class="input"></div>
                    <div><label class="form-label">Descanso Inicio</label><input name="h_desc_ini" type="time" class="input" title="Dejar vacío si no aplica"></div>
                    <div><label class="form-label">Descanso Fin</label><input name="h_desc_fin" type="time" class="input" title="Dejar vacío si no aplica"></div>
                    <div style="grid-column:span 2"><label class="form-label">Subir Foto de Perfil</label><input type="file" name="foto" accept="image/*" class="input" style="border:none; padding:8px 0; background:transparent;"></div>
                    <div style="grid-column:1/-1"><button type="submit" name="accion_barbero" value="crear" class="btn btn-gold w-full">Registrar barbero</button></div>
                </form>
            </div>
            <div class="grid gap-4" style="grid-template-columns:repeat(auto-fill,minmax(280px,1fr))">
                <?php foreach($barberos as $b): ?>
                <div class="glass flex items-center justify-between" style="padding:16px">
                    <div class="flex items-center gap-3">
                        <img src="<?= htmlspecialchars($b['foto_url'] ?: 'https://ui-avatars.com/api/?background=0b1220&color=fff&name='.$b['nombre']) ?>" style="width:48px;height:48px;border-radius:50%;object-fit:cover;border:1px solid rgba(255,255,255,.08)">
                        <div>
                            <p style="font-size:.85rem;font-weight:800"><?= htmlspecialchars($b['nombre']) ?></p>
                            <p class="font-mono" style="font-size:.7rem;color:var(--gray500)"><?= substr($b['hora_inicio'],0,5) ?> - <?= substr($b['hora_fin'],0,5) ?></p>
                            <?php if(!empty($b['hora_descanso_inicio'])): ?>
                                <p class="font-mono" style="font-size:.65rem;color:var(--gold)">Descanso: <?= substr($b['hora_descanso_inicio'],0,5) ?> - <?= substr($b['hora_descanso_fin'],0,5) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <form method="POST" onsubmit="return confirm('¿Eliminar?')">
                        <input type="hidden" name="id" value="<?= $b['id'] ?>">
                        <button name="accion_barbero" value="borrar" class="btn btn-danger btn-icon btn-sm" style="width:32px;height:32px"><i class="fas fa-trash" style="font-size:.6rem"></i></button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="view" id="v-historial">
            <h2 style="font-size:1.1rem;font-weight:800;margin-bottom:20px">Historial de Operaciones</h2>
            <div class="glass overflow-hidden" style="border-radius:var(--radius-lg)">
                <div style="overflow-x:auto">
                    <table class="tbl">
                        <thead><tr><th>Fecha</th><th>Cliente</th><th>Servicio</th><th>Barbero</th><th>Pago</th></tr></thead>
                        <tbody>
                            <?php foreach($historial as $h): ?>
                            <tr>
                                <td class="font-mono" style="color:var(--gray500)"><?= $h['fecha'] ?></td>
                                <td style="font-weight:700;text-transform:uppercase;letter-spacing:.03em"><?= htmlspecialchars($h['cliente_nombre']) ?></td>
                                <td style="color:var(--gray300)"><?= htmlspecialchars($h['servicio']) ?></td>
                                <td class="text-gold font-semibold"><?= htmlspecialchars($h['barbero']) ?></td>
                                <td><span class="badge badge-gray"><?= htmlspecialchars($h['metodo_pago']) ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="view" id="v-config">
            <h2 style="font-size:1.1rem;font-weight:800;margin-bottom:20px">Configuración Global</h2>
            <div class="glass" style="padding:24px;max-width:640px">
                <form method="POST" enctype="multipart/form-data" class="flex flex-col gap-6">
                    <div style="border-bottom:1px solid rgba(255,255,255,.08);padding-bottom:20px">
                        <h3 style="font-size:.65rem;font-weight:800;color:var(--gold);text-transform:uppercase;letter-spacing:.15em;margin-bottom:14px">Marca de tu barbería (SaaS)</h3>
                        <p style="font-size:.7rem;color:var(--gray500);margin-bottom:14px;line-height:1.5">El logo y nombre se muestran a tus clientes. <strong style="color:var(--gray300)">AlCorte</strong> sigue visible como plataforma de reservas.</p>
                        <div class="form-grid" style="gap:12px">
                            <div>
                                <label class="form-label">Nombre del negocio</label>
                                <input name="nombre_negocio" value="<?= htmlspecialchars($conf['nombre_negocio']) ?>" placeholder="Ej: Barbería El Estilo" class="input">
                            </div>
                            <div>
                                <label class="form-label">Logo (PNG, JPG o WebP)</label>
                                <input type="file" name="logo" accept="image/png,image/jpeg,image/webp,image/gif" class="input" style="border:none;padding:8px 0;background:transparent">
                                <?php if ($branding['has_logo']): ?>
                                <div style="margin-top:12px;display:flex;align-items:center;gap:12px">
                                    <img src="<?= htmlspecialchars($branding['logo_url']) ?>" alt="Logo actual" style="width:56px;height:56px;object-fit:cover;border-radius:50%;border:2px solid rgba(207,168,123,.4);background:transparent">
                                    <span style="font-size:.65rem;color:var(--gray500)">Vista previa del logo actual</span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h3 style="font-size:.65rem;font-weight:800;color:var(--gold);text-transform:uppercase;letter-spacing:.15em;margin-bottom:14px">Pasarelas Habilitadas</h3>
                        <div class="flex flex-wrap gap-6">
                            <label class="toggle-wrap"><input type="checkbox" name="estado_movil" <?= ($conf['estado_movil']=='1')?'checked':'' ?>><div class="toggle-box"><i class="fas fa-check"></i></div><span style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--gray300)">Pago Móvil</span></label>
                            <label class="toggle-wrap"><input type="checkbox" name="estado_zelle" <?= ($conf['estado_zelle']=='1')?'checked':'' ?>><div class="toggle-box"><i class="fas fa-check"></i></div><span style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--gray300)">Zelle</span></label>
                            <label class="toggle-wrap"><input type="checkbox" name="estado_efectivo" <?= ($conf['estado_efectivo']=='1')?'checked':'' ?>><div class="toggle-box"><i class="fas fa-check"></i></div><span style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--gray300)">Efectivo</span></label>
                        </div>
                    </div>
                    <div style="border-top:1px solid rgba(255,255,255,.05);padding-top:20px">
                        <h3 style="font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;margin-bottom:14px">Cuentas Receptoras</h3>
                        <div class="form-grid" style="gap:12px">
                            <div><label class="form-label">Entidad Bancaria</label><input name="banco_nombre" value="<?= htmlspecialchars($conf['banco_nombre']) ?>" placeholder="Ej: Banesco" class="input"></div>
                            <div class="form-grid form-grid-2">
                                <div><label class="form-label">Cédula / RIF</label><input name="banco_ci" value="<?= htmlspecialchars($conf['banco_ci']) ?>" placeholder="V-00000000" class="input"></div>
                                <div><label class="form-label">Teléfono Pago Móvil</label><input name="banco_telefono" value="<?= htmlspecialchars($conf['banco_telefono']) ?>" placeholder="0424..." class="input"></div>
                            </div>
                            <div><label class="form-label">Correo Zelle</label><input name="zelle_email" value="<?= htmlspecialchars($conf['zelle_email']) ?>" placeholder="pagos@alcorte.com" class="input"></div>
                        </div>
                    </div>
                    <button type="submit" name="guardar_config" class="btn btn-gold w-full">Guardar configuración</button>
                </form>
            </div>
        </section>
    </main>

    <div id="modalEditServicio" class="modal-overlay">
        <div class="glass modal-content" style="border-radius: var(--radius-lg);">
            <button type="button" class="close-modal" onclick="cerrarModal()"><i class="fas fa-times"></i></button>
            <h3 style="font-size:1.1rem;font-weight:800;margin-bottom:20px;color:var(--white);">Editar servicio</h3>
            
            <form method="POST" enctype="multipart/form-data" class="form-grid">
                <input type="hidden" name="id" id="edit_id">
                
                <div>
                    <label class="form-label">Nombre del Servicio</label>
                    <input name="nombre" id="edit_nombre" required class="input">
                </div>
                
                <div class="form-grid form-grid-2">
                    <div>
                        <label class="form-label">Precio ($)</label>
                        <input name="precio" id="edit_precio" type="number" step="0.01" required class="input">
                    </div>
                    <div>
                        <label class="form-label">Duración (min)</label>
                        <input name="duracion" id="edit_duracion" type="number" step="5" required class="input">
                    </div>
                </div>
                
                <div>
                    <label class="form-label">Actualizar Imagen (Opcional)</label>
                    <input type="file" name="imagen" accept="image/*" class="input" style="border:none; padding:8px 0; background:transparent; width:100%;">
                </div>
                
                <button type="submit" name="accion_servicio" value="editar" class="btn btn-gold w-full" style="margin-top:10px;">Guardar Cambios</button>
            </form>
        </div>
    </div>

    <script src="assets/app.js"></script>
    <script>
        // Navegación de Pestañas
        function ver(id) {
            document.querySelectorAll('.view').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.nav-tab').forEach(btn => btn.classList.remove('active'));
            document.getElementById('v-'+id).classList.add('active');
            event.currentTarget.classList.add('active');
        }

        // Lógica del Modal de Edición
        function abrirModalServicio(id, nombre, precio, duracion) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nombre').value = nombre;
            document.getElementById('edit_precio').value = precio;
            document.getElementById('edit_duracion').value = duracion;
            document.getElementById('modalEditServicio').classList.add('active');
        }

        function cerrarModal() {
            document.getElementById('modalEditServicio').classList.remove('active');
        }

        // Cerrar modal al hacer clic fuera del recuadro
        document.getElementById('modalEditServicio').addEventListener('click', function(e) {
            if (e.target === this) cerrarModal();
        });

        // WhatsApp redirect
        <?php if(!empty($ws_url)): ?>
            window.open("<?= $ws_url ?>", "_blank");
            window.location.href = "admin.php";
        <?php endif; ?>
        
        // Elementos Desktop
        if(window.innerWidth >= 768) {
            document.getElementById('badge-desktop').style.display = '';
            document.getElementById('link-desktop').style.display = '';
        }
    </script>
</body>
</html>