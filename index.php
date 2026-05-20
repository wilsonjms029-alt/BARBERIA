<?php
require 'db.php';

try {
    $barberos = $pdo->query("SELECT * FROM barberos WHERE activo = 1")->fetchAll(PDO::FETCH_ASSOC);
    $servicios = $pdo->query("SELECT * FROM servicios WHERE activo = 1")->fetchAll(PDO::FETCH_ASSOC);
    $config = $pdo->query("SELECT clave, valor FROM configuracion")->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (Exception $e) { $barberos=[]; $servicios=[]; $config=[]; }

$pago_movil = ($config['estado_movil'] ?? '1') === '1';
$zelle      = ($config['estado_zelle'] ?? '1') === '1';
$efectivo   = ($config['estado_efectivo'] ?? '1') === '1';

$dias = [];
setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'Spanish_Spain');
$hoy = new DateTime();
for($i=0; $i<14; $i++) {
    $fecha = clone $hoy;
    $fecha->modify("+$i days");
    $label = ($i==0) ? 'HOY' : (($i==1) ? 'MAÑANA' : date('D', $fecha->getTimestamp()));
    $dias[] = ['label'=>strtoupper($label), 'dia'=>$fecha->format('d'), 'val'=>$fecha->format('Y-m-d')];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>ALCORTE - Reserva de Citas Premium</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #070d19; color: #fff; -webkit-tap-highlight-color: transparent; }
        .glass-card { background: #0b1220; border: 1px solid rgba(255, 255, 255, 0.05); }
        .hide-scroll::-webkit-scrollbar { display: none; }
        .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
        .input-pro { background: rgba(7, 13, 25, 0.6); border: 1px solid rgba(255,255,255,0.08); transition: 0.3s; }
        .input-pro:focus { border-color: #cfa87b; }
        .peer:checked ~ .check-ring { border-color: #cfa87b; transform: scale(1.02); }
        .peer:checked ~ .check-bg { border-color: #cfa87b; background: rgba(207, 168, 123, 0.03); }
        .loader { border-top-color: #cfa87b; animation: spinner 1s linear infinite; }
        @keyframes spinner { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body class="pb-32 selection:bg-[#cfa87b] selection:text-black">

    <nav class="fixed top-0 w-full z-50 px-6 py-4 flex justify-between items-center bg-[#070d19]/90 backdrop-blur-xl border-b border-white/5">
        <div>
            <h1 class="text-lg font-black tracking-wider text-white uppercase italic">Al<span class="text-[#cfa87b] not-italic">Corte</span></h1>
            <p class="text-[8px] text-gray-500 uppercase tracking-widest font-bold">Studio Premium</p>
        </div>
        <a href="buscar_citas.php" class="w-10 h-10 rounded-xl glass-card flex items-center justify-center text-[#cfa87b] hover:bg-white/5 transition">
            <i class="fas fa-search text-sm"></i>
        </a>
    </nav>

    <div class="pt-24 px-4 max-w-lg mx-auto space-y-6">

        <div class="glass-card rounded-2xl p-5 relative overflow-hidden">
            <div class="flex justify-between items-start mb-3">
                <div>
                    <h2 class="text-[#cfa87b] font-bold uppercase text-[10px] tracking-widest">Black Card Member</h2>
                    <p class="text-gray-400 font-medium text-xs">Acumula tus visitas digitales</p>
                </div>
                <i class="fas fa-crown text-[#cfa87b] text-xl opacity-80"></i>
            </div>
            <div class="flex items-end gap-1.5 mb-2">
                <span class="text-4xl font-black text-white font-mono" id="puntos-display">0</span>
                <span class="text-xs text-gray-500 mb-1.5 font-medium">/ 6 Visitas</span>
            </div>
            <div class="w-full bg-gray-950 h-1.5 rounded-full overflow-hidden border border-white/5">
                <div class="bg-gradient-to-r from-[#2e6f40] to-[#cfa87b] h-full rounded-full transition-all duration-700" style="width: 0%" id="barra-puntos"></div>
            </div>
            <p class="text-[9px] text-center mt-2.5 text-gray-500">Tu sexto servicio incluye una bonificación exclusiva de la casa.</p>
        </div>

        <form action="procesar_cita.php" method="POST" id="bookingForm" class="space-y-6">

            <div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3 px-1">Especialista</h3>
                <div class="flex gap-4 overflow-x-auto hide-scroll pb-1 px-1">
                    <?php foreach($barberos as $i => $b): ?>
                    <label class="cursor-pointer group flex-shrink-0 text-center relative">
                        <input type="radio" name="barbero_id" value="<?php echo $b['id']; ?>" class="peer hidden" <?php echo $i===0?'checked':''; ?>>
                        <div class="check-ring w-16 h-16 rounded-full p-[2px] border border-white/10 transition-all duration-300 relative mx-auto">
                            <img src="<?php echo $b['foto_url'] ?: 'https://ui-avatars.com/api/?name='.$b['nombre'].'&background=0b1220&color=fff'; ?>" 
                                 class="w-full h-full rounded-full object-cover">
                        </div>
                        <span class="text-[11px] font-bold text-gray-400 mt-2 block group-hover:text-white transition"><?php echo htmlspecialchars(explode(' ',$b['nombre'])[0]); ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3 px-1">Servicio Premium</h3>
                <div class="space-y-2.5">
                    <?php foreach($servicios as $i => $s): ?>
                    <label class="block cursor-pointer">
                        <input type="radio" name="servicio" value="<?php echo $s['id']; ?>" class="peer hidden" <?php echo $i===0?'checked':''; ?>>
                        <div class="check-bg glass-card p-3.5 rounded-xl flex items-center gap-3.5 border border-white/5 transition-all">
                            <img src="<?php echo $s['imagen']; ?>" class="w-11 h-11 rounded-lg object-cover">
                            <div class="flex-1">
                                <h4 class="font-bold text-white text-xs tracking-wide"><?php echo htmlspecialchars($s['nombre']); ?></h4>
                                <p class="text-[10px] text-gray-500 uppercase font-mono mt-0.5"><?php echo $s['duracion']; ?> min • Estimado</p>
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
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3 px-1">Programación</h3>
                <div class="flex gap-2.5 overflow-x-auto hide-scroll mb-4 pb-1">
                    <?php foreach($dias as $i => $d): ?>
                    <label class="flex-shrink-0 cursor-pointer">
                        <input type="radio" name="fecha" value="<?php echo $d['val']; ?>" class="peer hidden" <?php echo $i===0?'checked':''; ?> onchange="loadHours()">
                        <div class="w-14 h-16 glass-card rounded-xl flex flex-col items-center justify-center transition-all peer-checked:bg-white peer-checked:text-black">
                            <span class="text-[8px] font-bold uppercase opacity-60 mb-0.5"><?php echo $d['label']; ?></span>
                            <span class="text-lg font-black font-mono"><?php echo $d['dia']; ?></span>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
                <div id="grid-horas" class="grid grid-cols-4 gap-2"></div>
                <input type="hidden" name="hora" id="selected-hour" required>
            </div>

            <div class="glass-card p-5 rounded-2xl border-t border-white/10">
                <h3 class="text-xs font-bold text-white uppercase tracking-wider mb-4">Datos de Identidad</h3>
                <div class="space-y-3 mb-5">
                    <div>
                        <input type="text" name="nombre" id="clientName" placeholder="Tu Nombre Completo" required class="w-full p-3 rounded-xl input-pro text-xs font-bold text-white outline-none">
                    </div>
                    <div>
                        <input type="tel" name="telefono" id="clientPhone" placeholder="Tu Número Telefónico (Ej: 0424...)" required class="w-full p-3 rounded-xl input-pro text-xs font-bold text-white outline-none" onblur="checkPoints(this.value)">
                    </div>
                </div>

                <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider mb-2 px-1">Canal de Pago</p>
                <div class="grid grid-cols-3 gap-2 mb-4">
                    <?php if($pago_movil): ?>
                    <button type="button" onclick="setPay('movil', this)" class="pay-btn py-2.5 rounded-xl glass-card text-[10px] font-bold uppercase text-gray-400 transition">Pago Móvil</button>
                    <?php endif; ?>
                    <?php if($zelle): ?>
                    <button type="button" onclick="setPay('zelle', this)" class="pay-btn py-2.5 rounded-xl glass-card text-[10px] font-bold uppercase text-gray-400 transition">Zelle</button>
                    <?php endif; ?>
                    <?php if($efectivo): ?>
                    <button type="button" onclick="setPay('efectivo', this)" class="pay-btn py-2.5 rounded-xl glass-card text-[10px] font-bold uppercase text-gray-400 transition">Efectivo</button>
                    <?php endif; ?>
                </div>
                <input type="hidden" name="metodo_pago" id="paymentMethod" required>

                <div id="payment-info" class="hidden bg-black/40 rounded-xl p-4 text-[11px] text-gray-400 space-y-1.5 border border-white/5"></div>
                <input type="text" name="referencia" id="refInput" placeholder="NÚMERO DE REFERENCIA / TITULAR" class="hidden w-full mt-3 p-3 rounded-xl input-pro text-center text-[#cfa87b] font-mono uppercase tracking-widest text-xs outline-none">
            </div>

            <div class="h-4"></div>
        </form>
    </div>

    <div class="fixed bottom-0 left-0 w-full p-4 bg-[#070d19]/80 backdrop-blur-md z-40">
        <button onclick="submitForm()" class="w-full max-w-lg mx-auto bg-white text-black font-bold py-3.5 rounded-xl transition active:scale-[0.99] flex items-center justify-between px-6 shadow-xl">
            <span class="tracking-widest text-xs uppercase font-extrabold">Confirmar Solicitud</span>
            <i class="fas fa-arrow-right text-xs"></i>
        </button>
    </div>

    <script>
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
                const req = await fetch(`obtener_horarios.php?fecha=${date}&barbero_id=${barber}&servicio_id=${serviceId}`);
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
                    btn.className = "py-2 rounded-xl glass-card text-xs font-mono font-bold text-gray-400 transition hover:text-white";
                    btn.onclick = () => {
                        document.querySelectorAll('#grid-horas button').forEach(b => b.className = b.className.replace('bg-[#cfa87b] text-black', 'glass-card text-gray-400'));
                        btn.className = "py-2 rounded-xl bg-[#cfa87b] text-black text-xs font-mono font-bold shadow-md";
                        document.getElementById('selected-hour').value = time + ":00";
                    };
                    grid.appendChild(btn);
                });
            } catch(e) { grid.innerHTML = '<div class="col-span-4 text-center text-xs text-gray-500">Error de comunicación</div>'; }
        }

        function setPay(type, btn) {
            document.querySelectorAll('.pay-btn').forEach(b => b.className = b.className.replace('bg-white text-black', 'glass-card text-gray-400'));
            btn.className = "pay-btn py-2.5 rounded-xl bg-white text-black text-[10px] font-bold uppercase";
            
            document.getElementById('paymentMethod').value = type;
            const infoDiv = document.getElementById('payment-info');
            const refInput = document.getElementById('refInput');
            infoDiv.classList.remove('hidden');
            
            if(type === 'movil') {
                infoDiv.innerHTML = `<div class="flex justify-between border-b border-white/5 pb-1"><span>Banco:</span> <b class="text-white">${bankData.bs.bank}</b></div>
                                     <div class="flex justify-between border-b border-white/5 py-1"><span>Cédula/RIF:</span> <b class="text-white">${bankData.bs.id}</b></div>
                                     <div class="flex justify-between pt-1"><span>Teléfono:</span> <b class="text-[#cfa87b] font-mono">${bankData.bs.tel}</b></div>`;
                refInput.classList.remove('hidden'); refInput.required = true;
            } else if(type === 'zelle') {
                infoDiv.innerHTML = `<div class="text-center py-1">Efectuar pago a correo Zelle:<br><b class="text-white select-all text-xs block mt-1 font-mono">${bankData.zelle}</b></div>`;
                refInput.classList.remove('hidden'); refInput.required = true;
            } else {
                infoDiv.innerHTML = "<div class='text-center text-emerald-400 font-bold py-1'><i class='fas fa-check-circle mr-1'></i> Abono directo en el establecimiento</div>";
                refInput.classList.add('hidden'); refInput.required = false; refInput.value = "SITIO";
            }
        }

        function checkPoints(phone) {
            if(phone.length > 6) {
                localStorage.setItem('cli_phone', phone);
                localStorage.setItem('cli_name', document.getElementById('clientName').value);
                let hash = 0;
                for(let i=0; i<phone.length; i++) hash += phone.charCodeAt(i);
                let pts = hash % 6; 
                document.getElementById('puntos-display').innerText = pts;
                document.getElementById('barra-puntos').style.width = (pts/6*100) + "%";
            }
        }

        function submitForm() {
            if(!document.getElementById('selected-hour').value) { alert("Por favor seleccione un horario válido."); return; }
            if(!document.getElementById('paymentMethod').value) { alert("Por favor seleccione su método de pago."); return; }
            if(!document.getElementById('bookingForm').checkValidity()) { alert("Por favor complete todos sus datos."); return; }
            document.getElementById('bookingForm').submit();
        }

        window.onload = () => {
            if(localStorage.getItem('cli_phone')) {
                document.getElementById('clientPhone').value = localStorage.getItem('cli_phone');
                document.getElementById('clientName').value = localStorage.getItem('cli_name');
                checkPoints(localStorage.getItem('cli_phone'));
            }
            loadHours();
        };
    </script>
</body>
</html>