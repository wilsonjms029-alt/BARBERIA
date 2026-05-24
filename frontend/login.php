<?php
session_start();
require_once __DIR__ . '/bootstrap.php';

if (isset($_SESSION['usuario_id'])) {
    $dest = (($_SESSION['usuario_rol'] ?? '') === 'admin') ? 'admin.php' : 'index.php';
    header("Location: $dest");
    exit;
}

$config = barberia_fetch_config($pdo);
$branding = barberia_branding_from_config($config);
$pageTitle = 'AlCorte — Acceso';
if ($branding['has_nombre']) {
    $pageTitle = htmlspecialchars($branding['nombre_negocio']) . ' · AlCorte';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#cfa87b">
    <link rel="manifest" href="assets/manifest.json">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="assets/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <?php include __DIR__ . '/includes/brand-styles.php'; ?>
    <style>
        .login-page{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;position:relative}
        .login-bg{position:absolute;inset:0;z-index:0}
        .login-bg img{width:100%;height:100%;object-fit:cover;opacity:.2;filter:blur(2px)}
        .login-bg::after{content:'';position:absolute;inset:0;background:linear-gradient(to top,var(--bg),rgba(7,13,25,.85),transparent)}
        .login-card{position:relative;z-index:2;width:100%;max-width:380px;padding:36px 28px;border-radius:var(--radius-xl);
            background:rgba(11,18,32,.8);border:1px solid rgba(255,255,255,.08);
            backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px);box-shadow:var(--shadow-lg)}
        .login-card::before{content:'';position:absolute;top:-1px;left:50%;transform:translateX(-50%);width:60px;height:3px;
            background:linear-gradient(90deg,transparent,var(--gold),transparent);border-radius:0 0 4px 4px}
        .input-icon{position:relative}
        .input-icon i{position:absolute;left:16px;top:50%;transform:translateY(-50%);color:var(--gray500);font-size:.8rem;transition:var(--transition)}
        .input-icon input{padding-left:44px}
        .input-icon:focus-within i{color:var(--gold)}
        .msg-box{padding:12px 16px;border-radius:var(--radius);font-size:.75rem;font-weight:600;margin-bottom:16px;animation:fadeUp .4s ease both}
        .msg-error{background:rgba(239,68,68,.1);color:#f87171;border:1px solid rgba(239,68,68,.2)}
        .msg-info{background:rgba(207,168,123,.1);color:var(--gold-light);border:1px solid rgba(207,168,123,.25)}
        .tel-status{font-size:.7rem;color:var(--gray500);min-height:1.1rem;margin-top:6px;padding-left:2px}
        .tel-status.ok{color:var(--green-light)}
        .tel-status.warn{color:var(--gold-light)}
        .step-title{font-size:.65rem;text-transform:uppercase;letter-spacing:.15em;color:var(--gold);font-weight:700;margin-bottom:12px}
        .spinner-inline{display:inline-block;width:14px;height:14px;border:2px solid rgba(255,255,255,.15);border-top-color:var(--gold);border-radius:50%;animation:spin .7s linear infinite;vertical-align:middle;margin-right:6px}
        @keyframes spin{to{transform:rotate(360deg)}}
        .field-block{animation:fadeUp .35s ease both}
    </style>
</head>
<body>
    <canvas id="particles"></canvas>
    <div class="login-page">
        <div class="login-bg">
            <img src="https://images.unsplash.com/photo-1585747860715-2ba37e788b70?w=1080" alt="">
        </div>
        <div class="login-card animate-scaleIn">
            <?php
            $brand_variant = 'center';
            $brand_subline = 'Acceso con tu teléfono';
            $brand_subline_muted = true;
            include __DIR__ . '/includes/brand-header.php';
            ?>

            <div id="msg-box" class="msg-box hidden"></div>

            <form id="auth-form" class="flex flex-col gap-3" autocomplete="on">
                <div>
                    <div class="input-icon">
                        <i class="fas fa-phone"></i>
                        <input type="tel" id="telefono" name="telefono" inputmode="tel"
                               placeholder="Tu número (Ej: 0424...)" required class="input"
                               autocomplete="tel">
                    </div>
                    <p id="tel-status" class="tel-status"></p>
                </div>

                <div id="block-nombre" class="hidden field-block">
                    <div class="input-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="nombre" name="nombre" placeholder="Tu nombre completo" class="input" autocomplete="name">
                    </div>
                </div>

                <div id="block-password" class="hidden field-block">
                    <p id="step-label" class="step-title"></p>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Contraseña" class="input" autocomplete="current-password">
                    </div>
                </div>

                <div id="block-password2" class="hidden field-block">
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password_confirm" name="password_confirm" placeholder="Repite la contraseña" class="input" autocomplete="new-password">
                    </div>
                </div>

                <button type="submit" id="btn-submit" class="btn btn-gold w-full hidden" style="margin-top:8px" disabled>
                    <span id="btn-label">Continuar</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <p class="text-center" style="font-size:.7rem;color:var(--gray500);margin-top:20px">
                <a href="index.php" class="text-gold font-bold" style="text-decoration:none">← Volver a reservar</a>
            </p>
        </div>
    </div>
    <script src="assets/app.js"></script>
    <script>
        new ParticleSystem('particles', 20);

        const API = 'api/auth.php';
        const telInput = document.getElementById('telefono');
        const telStatus = document.getElementById('tel-status');
        const blockNombre = document.getElementById('block-nombre');
        const blockPass = document.getElementById('block-password');
        const blockPass2 = document.getElementById('block-password2');
        const nombreInput = document.getElementById('nombre');
        const passInput = document.getElementById('password');
        const pass2Input = document.getElementById('password_confirm');
        const stepLabel = document.getElementById('step-label');
        const btnSubmit = document.getElementById('btn-submit');
        const btnLabel = document.getElementById('btn-label');
        const msgBox = document.getElementById('msg-box');
        const form = document.getElementById('auth-form');

        let mode = null;
        let checkTimer = null;
        let checking = false;
        let lastCheckedTel = '';

        function normTel(v) {
            return (v || '').replace(/\D/g, '');
        }

        function showMsg(text, type = 'error') {
            msgBox.textContent = text;
            msgBox.className = 'msg-box msg-' + (type === 'info' ? 'info' : 'error');
            msgBox.classList.remove('hidden');
        }

        function hideMsg() {
            msgBox.classList.add('hidden');
        }

        function resetSteps() {
            mode = null;
            lastCheckedTel = '';
            blockNombre.classList.add('hidden');
            blockPass.classList.add('hidden');
            blockPass2.classList.add('hidden');
            btnSubmit.classList.add('hidden');
            btnSubmit.disabled = true;
            nombreInput.required = false;
            passInput.required = false;
            pass2Input.required = false;
            passInput.value = '';
            pass2Input.value = '';
            passInput.autocomplete = 'current-password';
        }

        function setMode(newMode, data) {
            mode = newMode;
            hideMsg();
            blockPass.classList.remove('hidden');
            btnSubmit.classList.remove('hidden');
            btnSubmit.disabled = false;

            if (newMode === 'login') {
                telStatus.textContent = data.nombre ? '¡Hola, ' + data.nombre + '! Ingresa tu contraseña.' : 'Cuenta encontrada. Ingresa tu contraseña.';
                telStatus.className = 'tel-status ok';
                stepLabel.textContent = 'Iniciar sesión';
                passInput.placeholder = 'Tu contraseña';
                passInput.autocomplete = 'current-password';
                blockPass2.classList.add('hidden');
                blockNombre.classList.add('hidden');
                pass2Input.required = false;
                passInput.required = true;
                nombreInput.required = false;
                btnLabel.textContent = 'Entrar';
            } else {
                const esNuevo = newMode === 'register';
                telStatus.textContent = esNuevo
                    ? 'Número nuevo. Crea tu contraseña para continuar.'
                    : 'Activa tu cuenta creando una contraseña.';
                telStatus.className = 'tel-status warn';
                stepLabel.textContent = esNuevo ? 'Crear cuenta' : 'Activar cuenta';
                passInput.placeholder = 'Nueva contraseña (mín. 4 caracteres)';
                passInput.autocomplete = 'new-password';
                blockPass2.classList.remove('hidden');
                pass2Input.required = true;
                passInput.required = true;
                blockNombre.classList.remove('hidden');
                nombreInput.required = esNuevo || !data.nombre;
                if (data.nombre) {
                    nombreInput.value = data.nombre;
                    if (!esNuevo) nombreInput.required = false;
                }
                btnLabel.textContent = esNuevo ? 'Crear cuenta y entrar' : 'Guardar y entrar';
            }
            passInput.focus();
        }

        async function checkTelefono() {
            const tel = normTel(telInput.value);
            if (tel.length < 7) {
                resetSteps();
                telStatus.textContent = tel.length ? 'Escribe al menos 7 dígitos…' : '';
                telStatus.className = 'tel-status';
                return;
            }
            if (tel === lastCheckedTel && mode) return;

            checking = true;
            telStatus.innerHTML = '<span class="spinner-inline"></span>Verificando número…';
            telStatus.className = 'tel-status';

            try {
                const res = await fetch(API + '?action=check', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ telefono: tel })
                });
                const data = await res.json();
                if (!data.ok) {
                    resetSteps();
                    telStatus.textContent = data.error || 'No se pudo verificar.';
                    telStatus.className = 'tel-status';
                    return;
                }

                lastCheckedTel = tel;
                if (data.status === 'login') {
                    setMode('login', data);
                } else if (data.status === 'create_password') {
                    setMode('create_password', data);
                } else {
                    setMode('register', data);
                }
            } catch (e) {
                resetSteps();
                telStatus.textContent = 'Sin conexión. Revisa tu red.';
                telStatus.className = 'tel-status';
            } finally {
                checking = false;
            }
        }

        telInput.addEventListener('input', () => {
            const tel = normTel(telInput.value);
            if (tel !== lastCheckedTel) {
                resetSteps();
                telStatus.textContent = '';
            }
            clearTimeout(checkTimer);
            checkTimer = setTimeout(checkTelefono, 450);
        });

        telInput.addEventListener('blur', () => {
            clearTimeout(checkTimer);
            checkTelefono();
        });

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            hideMsg();

            const tel = normTel(telInput.value);
            if (tel.length < 7) {
                showMsg('Ingresa un número de teléfono válido.');
                return;
            }
            if (!mode) {
                await checkTelefono();
                if (!mode) {
                    showMsg('Espera a que se verifique tu número.');
                }
                return;
            }

            btnSubmit.disabled = true;
            const payload = { telefono: tel, password: passInput.value };

            try {
                let url, action;
                if (mode === 'login') {
                    action = 'login';
                } else {
                    action = 'set_password';
                    payload.password_confirm = pass2Input.value;
                    payload.nombre = nombreInput.value.trim();
                }

                const res = await fetch(API + '?action=' + action, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();

                if (!data.ok) {
                    showMsg(data.error || 'No se pudo completar la operación.');
                    btnSubmit.disabled = false;
                    return;
                }

                if (data.redirect) {
                    localStorage.setItem('cli_phone', tel);
                    if (payload.nombre) localStorage.setItem('cli_name', payload.nombre);
                    window.location.href = data.redirect;
                }
            } catch (err) {
                showMsg('Error de conexión. Intenta de nuevo.');
                btnSubmit.disabled = false;
            }
        });

        const saved = localStorage.getItem('cli_phone');
        if (saved) {
            telInput.value = saved;
            setTimeout(checkTelefono, 200);
        }
    </script>
</body>
</html>
