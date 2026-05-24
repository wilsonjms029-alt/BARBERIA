<?php
session_start();
require_once __DIR__ . '/bootstrap.php';

$logueado = !empty($_SESSION['usuario_id']) && ($_SESSION['usuario_rol'] ?? 'cliente') === 'cliente';
$nombreSesion = trim($_SESSION['usuario_nombre'] ?? '');
$telSesion = trim($_SESSION['usuario_telefono'] ?? '');
$primerNombre = $nombreSesion !== '' ? explode(' ', $nombreSesion)[0] : '';
try {
    $barberos = $pdo->query("SELECT * FROM barberos WHERE activo = 1")->fetchAll(PDO::FETCH_ASSOC);
    $servicios = $pdo->query("SELECT * FROM servicios WHERE activo = 1")->fetchAll(PDO::FETCH_ASSOC);
    $config = barberia_fetch_config($pdo);
} catch (Exception $e) {
    $barberos = [];
    $servicios = [];
    $config = barberia_config_defaults();
}
$branding = barberia_branding_from_config($config);

$pago_movil = ($config['estado_movil'] ?? '1') === '1';
$zelle      = ($config['estado_zelle'] ?? '1') === '1';
$efectivo   = ($config['estado_efectivo'] ?? '1') === '1';

$dias = [];
$hoy = new DateTime();
for ($i = 0; $i < 14; $i++) {
    $fecha = clone $hoy;
    $fecha->modify("+$i days");
    if ($i === 0) {
        $label = 'HOY';
    } elseif ($i === 1) {
        $label = 'MAÑANA';
    } else {
        $label = barberia_dia_corto($fecha);
    }
    $dias[] = ['label' => $label, 'dia' => $fecha->format('d'), 'val' => $fecha->format('Y-m-d')];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Language" content="es">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#cfa87b">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="AlCorte">
    <link rel="manifest" href="assets/manifest.json">
    <title>AlCorte — Reserva de citas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <?php include __DIR__ . '/includes/brand-styles.php'; ?>
    <style>
        :root {
            --bg: #070d19;
            --surface: #152238;
            --surface-2: #1c2d4a;
            --border: rgba(255, 255, 255, 0.14);
            --text: #ffffff;
            --text-muted: #c8d4e8;
            --gold: #e4c49a;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            -webkit-tap-highlight-color: transparent;
        }
        /* Tarjetas sólidas (sin efecto “cristal” opaco) */
        .glass-card {
            background: var(--surface);
            border: 1px solid var(--border);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
        }
        .panel-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 1rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.35);
        }
        .hide-scroll::-webkit-scrollbar { display: none; }
        .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
        .input-pro {
            background: var(--surface-2);
            border: 1px solid var(--border);
            color: var(--text);
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .input-pro::placeholder { color: #8fa3c4; opacity: 1; }
        .input-pro:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(207, 168, 123, 0.25);
            outline: none;
        }
        .peer:checked ~ .check-ring {
            border-color: var(--gold);
            box-shadow: 0 0 0 2px rgba(207, 168, 123, 0.45);
            transform: scale(1.04);
        }
        .peer:checked ~ .check-bg {
            border-color: var(--gold);
            background: #243a5c;
            box-shadow: 0 0 0 1px rgba(207, 168, 123, 0.35);
        }
        .dia-chip {
            background: var(--surface-2);
            border: 1px solid var(--border);
            color: var(--text);
        }
        .dia-chip .dia-label { color: var(--text-muted); opacity: 1; }
        .peer:checked ~ .dia-chip {
            background: #fff;
            color: #000;
            border-color: #fff;
        }
        .peer:checked ~ .dia-chip .dia-label { color: #4b5563; }
        .peer:checked ~ .dia-chip .dia-num { color: #000; }
        .loader { border-top-color: var(--gold); animation: spinner 1s linear infinite; }
        @keyframes spinner { to { transform: rotate(360deg); } }
        .section-title {
            font-size: 1rem;
            font-weight: 800;
            color: var(--text);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.75rem;
            padding-left: 0.25rem;
        }
        .text-muted { color: var(--text-muted) !important; }
        .hora-btn, .pay-btn {
            padding: 0.55rem 0.35rem;
            border-radius: 0.75rem;
            background: #fff;
            color: #000;
            font-size: 0.7rem;
            font-weight: 700;
            border: 2px solid #fff;
            transition: 0.2s;
        }
        .pay-btn { text-transform: uppercase; letter-spacing: 0.03em; }
        .hora-btn { font-family: ui-monospace, monospace; }
        .hora-btn--selected, .pay-btn--selected {
            border-color: var(--gold);
            box-shadow: 0 0 0 2px var(--gold);
        }
        #payment-info {
            background: #fff !important;
            color: #111827 !important;
            border: 1px solid #e5e7eb !important;
        }
        #payment-info b { color: #000 !important; }
        .nav-bar, .footer-bar {
            background: var(--bg);
            border-color: var(--border);
        }
        .nav-mis-cortes {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px;
            border-radius: 12px;
            color: #e4c49a;
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            text-decoration: none;
            transition: background 0.2s;
        }
        .nav-mis-cortes:hover { background: rgba(255, 255, 255, 0.06); }
        .nav-mis-cortes i { font-size: 0.75rem; }
        .client-greeting {
            margin: 0 0 0.5rem;
            font-size: 1.4rem;
            font-weight: 800;
            color: #e4c49a;
            letter-spacing: 0.02em;
            line-height: 1.25;
        }
    </style>
</head>
<body class="pb-32 selection:bg-[#cfa87b] selection:text-black">

    <nav class="nav-bar fixed top-0 w-full z-50 px-4 py-3 flex justify-between items-start border-b">
        <?php
        $brand_subline = $logueado ? '' : 'Citas en línea';
        $brand_subline_muted = true;
        include __DIR__ . '/includes/brand-header.php';
        ?>
        <?php if ($logueado): ?>
        <a href="mis_cortes.php" class="nav-mis-cortes glass-card shrink-0 mt-0.5">
            <i class="fas fa-scissors"></i>
            <span>Mis cortes</span>
        </a>
        <?php else: ?>
        <a href="login.php" title="Iniciar sesión" class="w-10 h-10 rounded-xl glass-card flex items-center justify-center text-[#cfa87b] hover:bg-white/5 transition shrink-0 mt-0.5">
            <i class="fas fa-right-to-bracket text-sm"></i>
        </a>
        <?php endif; ?>
    </nav>

    <div class="pt-[108px] px-4 max-w-lg mx-auto space-y-6">
        <?php if ($logueado && $primerNombre !== ''): ?>
        <p class="client-greeting">¡Hola, <?php echo htmlspecialchars($primerNombre); ?>!</p>
        <?php endif; ?>

        <form action="api/citas.php" method="POST" id="bookingForm" class="space-y-6">

            <div>
                <h3 class="section-title">Barberos</h3>
                <div class="flex gap-4 overflow-x-auto hide-scroll pb-1 px-1">
                    <?php foreach($barberos as $i => $b): ?>
                    <label class="cursor-pointer group flex-shrink-0 text-center relative">
                        <input type="radio" name="barbero_id" value="<?php echo $b['id']; ?>" class="peer hidden" <?php echo $i===0?'checked':''; ?>>
                        <div class="check-ring w-16 h-16 rounded-full p-[2px] border-2 border-[#3d5278] transition-all duration-300 relative mx-auto bg-[#1c2d4a]">
                            <img src="<?php echo $b['foto_url'] ?: 'https://ui-avatars.com/api/?name='.$b['nombre'].'&background=0b1220&color=fff'; ?>" 
                                 class="w-full h-full rounded-full object-cover">
                        </div>
                        <span class="text-[11px] font-bold text-white mt-2 block"><?php echo htmlspecialchars(explode(' ',$b['nombre'])[0]); ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div>
                <h3 class="section-title">Servicios</h3>
                <div class="space-y-2.5">
                    <?php foreach($servicios as $i => $s): ?>
                    <label class="block cursor-pointer">
                        <input type="radio" name="servicio" value="<?php echo $s['id']; ?>" class="peer hidden" <?php echo $i===0?'checked':''; ?>>
                        <div class="check-bg glass-card p-3.5 rounded-xl flex items-center gap-3.5 border border-white/5 transition-all">
                            <img src="<?php echo $s['imagen']; ?>" class="w-11 h-11 rounded-lg object-cover">
                            <div class="flex-1">
                                <h4 class="font-bold text-white text-xs tracking-wide"><?php echo htmlspecialchars($s['nombre']); ?></h4>
                                <p class="text-[10px] text-muted uppercase font-mono mt-0.5"><?php echo $s['duracion']; ?> min aprox.</p>
                            </div>
                            <div class="text-right flex flex-col items-end gap-1">
                                <span class="block text-[#cfa87b] font-bold font-mono text-sm">$<?php echo $s['precio']; ?></span>
                                <i class="fas fa-check-circle text-[#2e6f40] opacity-0 peer-checked:opacity-100 transition text-[10px]"></i>
                            </div>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div>
                <h3 class="section-title">Fecha y hora</h3>
                <div class="flex gap-2.5 overflow-x-auto hide-scroll mb-4 pb-1">
                    <?php foreach($dias as $i => $d): ?>
                    <label class="flex-shrink-0 cursor-pointer">
                        <input type="radio" name="fecha" value="<?php echo $d['val']; ?>" class="peer hidden" <?php echo $i===0?'checked':''; ?> onchange="loadHours()">
                        <div class="dia-chip w-14 h-16 rounded-xl flex flex-col items-center justify-center transition-all">
                            <span class="dia-label text-[8px] font-bold uppercase mb-0.5"><?php echo $d['label']; ?></span>
                            <span class="text-lg font-black font-mono dia-num"><?php echo $d['dia']; ?></span>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
                <div id="grid-horas" class="grid grid-cols-4 gap-2"></div>
                <input type="hidden" name="hora" id="selected-hour" required>
            </div>

            <div class="panel-card p-5">
                <h3 class="section-title mb-4">Tus datos</h3>
                <div class="space-y-3 mb-5">
                    <div>
                        <input type="text" name="nombre" id="clientName" placeholder="Tu nombre completo" required
                               value="<?php echo $logueado ? htmlspecialchars($nombreSesion) : ''; ?>"
                               class="w-full p-3 rounded-xl input-pro text-xs font-bold text-white outline-none">
                    </div>
                    <div>
                        <input type="tel" name="telefono" id="clientPhone" placeholder="Tu teléfono (ej. 0424…)" required
                               value="<?php echo $logueado ? htmlspecialchars($telSesion) : ''; ?>"
                               class="w-full p-3 rounded-xl input-pro text-xs font-bold text-white outline-none">
                    </div>
                </div>

                <p class="section-title mb-2">Métodos de pago</p>
                <div class="grid grid-cols-3 gap-2 mb-4">
                    <?php if($pago_movil): ?>
                    <button type="button" onclick="setPay('movil', this)" class="pay-btn">Pago móvil</button>
                    <?php endif; ?>
                    <?php if($zelle): ?>
                    <button type="button" onclick="setPay('zelle', this)" class="pay-btn">Zelle</button>
                    <?php endif; ?>
                    <?php if($efectivo): ?>
                    <button type="button" onclick="setPay('efectivo', this)" class="pay-btn">Efectivo</button>
                    <?php endif; ?>
                </div>
                <input type="hidden" name="metodo_pago" id="paymentMethod" required>

                <div id="payment-info" class="hidden rounded-xl p-4 text-[12px] space-y-1.5"></div>
                <input type="text" name="referencia" id="refInput" placeholder="Número de referencia o titular" class="hidden w-full mt-3 p-3 rounded-xl input-pro text-center text-[#cfa87b] font-mono uppercase tracking-widest text-xs outline-none">
            </div>

            <div class="h-4"></div>
        </form>
    </div>

    <div class="footer-bar fixed bottom-0 left-0 w-full p-4 border-t z-40">
        <button onclick="submitForm()" class="w-full max-w-lg mx-auto bg-white text-black font-bold py-3.5 rounded-xl transition active:scale-[0.99] flex items-center justify-between px-6 shadow-xl">
            <span class="tracking-widest text-xs uppercase font-extrabold">Confirmar reserva</span>
            <i class="fas fa-arrow-right text-xs"></i>
        </button>
    </div>

    <script>
        const sesionCliente = <?php echo json_encode([
            'logueado' => $logueado,
            'nombre' => $nombreSesion,
            'telefono' => $telSesion,
        ], JSON_UNESCAPED_UNICODE); ?>;

        const bankData = {
            bs: { bank: "<?php echo htmlspecialchars($config['banco_nombre'] ?? ''); ?>", id: "<?php echo htmlspecialchars($config['banco_ci'] ?? ''); ?>", tel: "<?php echo htmlspecialchars($config['banco_telefono'] ?? ''); ?>" },
            zelle: "<?php echo htmlspecialchars($config['zelle_email'] ?? ''); ?>"
        };

        async function loadHours() {
            const barber = document.querySelector('input[name="barbero_id"]:checked').value;
            const date = document.querySelector('input[name="fecha"]:checked').value;
            const grid = document.getElementById('grid-horas');
            grid.innerHTML = '<div class="col-span-4 flex justify-center py-2"><div class="loader ease-linear rounded-full border-2 border-t-2 border-gray-800 h-5 w-5"></div></div>';
            
            try {
                // Buscamos dinámicamente usando el id de servicio activo
                const serviceId = document.querySelector('input[name="servicio"]:checked').value;
                const req = await fetch(`api/horarios.php?fecha=${date}&barbero_id=${barber}&servicio_id=${serviceId}`);
                const slots = await req.json();
                grid.innerHTML = "";

                if(slots.length === 0) {
                    grid.innerHTML = '<div class="col-span-4 text-center text-[11px] text-red-400 py-2 border border-red-950/30 rounded-xl bg-red-950/10">Sin turnos disponibles</div>';
                    return;
                }

                slots.forEach(time => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.textContent = time;
                    btn.className = 'hora-btn';
                    btn.onclick = () => {
                        document.querySelectorAll('#grid-horas .hora-btn').forEach(b => b.classList.remove('hora-btn--selected'));
                        btn.classList.add('hora-btn--selected');
                        document.getElementById('selected-hour').value = time + ":00";
                    };
                    grid.appendChild(btn);
                });
            } catch(e) { grid.innerHTML = '<div class="col-span-4 text-center text-xs text-gray-500">Error de comunicación</div>'; }
        }

        function setPay(type, btn) {
            document.querySelectorAll('.pay-btn').forEach(b => b.classList.remove('pay-btn--selected'));
            btn.classList.add('pay-btn--selected');
            
            document.getElementById('paymentMethod').value = type;
            const infoDiv = document.getElementById('payment-info');
            const refInput = document.getElementById('refInput');
            infoDiv.classList.remove('hidden');
            
            if(type === 'movil') {
                infoDiv.innerHTML = `<div class="flex justify-between border-b border-gray-200 pb-1"><span>Banco:</span> <b>${bankData.bs.bank}</b></div>
                                     <div class="flex justify-between border-b border-gray-200 py-1"><span>Cédula/RIF:</span> <b>${bankData.bs.id}</b></div>
                                     <div class="flex justify-between pt-1"><span>Teléfono:</span> <b class="font-mono" style="color:#b8860b">${bankData.bs.tel}</b></div>`;
                refInput.classList.remove('hidden'); refInput.required = true;
            } else if(type === 'zelle') {
                infoDiv.innerHTML = `<div class="text-center py-1">Paga a este correo Zelle:<br><b class="select-all text-xs block mt-1 font-mono">${bankData.zelle}</b></div>`;
                refInput.classList.remove('hidden'); refInput.required = true;
            } else {
                infoDiv.innerHTML = "<div class='text-center font-bold py-1' style='color:#15803d'><i class='fas fa-check-circle mr-1'></i> Pago en el local al llegar</div>";
                refInput.classList.add('hidden'); refInput.required = false; refInput.value = "SITIO";
            }
        }

        function submitForm() {
            if(!document.getElementById('selected-hour').value) { alert("Selecciona un horario válido."); return; }
            if(!document.getElementById('paymentMethod').value) { alert("Selecciona un método de pago."); return; }
            if(!document.getElementById('bookingForm').checkValidity()) { alert("Completa todos tus datos."); return; }
            document.getElementById('bookingForm').submit();
        }

        window.onload = () => {
            if (sesionCliente.logueado) {
                if (sesionCliente.nombre) {
                    document.getElementById('clientName').value = sesionCliente.nombre;
                    localStorage.setItem('cli_name', sesionCliente.nombre);
                }
                if (sesionCliente.telefono) {
                    document.getElementById('clientPhone').value = sesionCliente.telefono;
                    localStorage.setItem('cli_phone', sesionCliente.telefono);
                }
            } else if (localStorage.getItem('cli_phone')) {
                document.getElementById('clientPhone').value = localStorage.getItem('cli_phone');
                document.getElementById('clientName').value = localStorage.getItem('cli_name') || '';
            }
            loadHours();
        };
    </script>
</body>
</html>