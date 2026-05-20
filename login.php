<?php
session_start();
require 'db.php';
$mensaje = "";
if (isset($_POST['registro'])) {
    $user = trim($_POST['usuario']);
    $nombre = $_POST['nombre'];
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $tel = $_POST['telefono'];
    try {
        $stmt = $pdo->prepare("INSERT INTO usuarios (usuario, nombre, password, telefono, rol) VALUES (?, ?, ?, ?, 'cliente')");
        $stmt->execute([$user, $nombre, $pass, $tel]);
        $mensaje = "success|¡Cuenta creada! Inicia sesión como: <b>$user</b>";
    } catch (PDOException $e) {
        $mensaje = "error|El usuario '$user' ya existe.";
    }
}
if (isset($_POST['login'])) {
    $user = trim($_POST['usuario']);
    $pass = $_POST['password'];
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ?");
    $stmt->execute([$user]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($usuario && password_verify($pass, $usuario['password'])) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
        $_SESSION['usuario_rol'] = $usuario['rol'];
        if ($usuario['rol'] === 'admin' || $usuario['usuario'] === 'admin') {
            header("Location: admin.php");
        } else {
            header("Location: index.php");
        }
        exit;
    } else {
        $mensaje = "error|Usuario o contraseña incorrectos.";
    }
}
$msg_type = ''; $msg_text = '';
if($mensaje) { list($msg_type, $msg_text) = explode('|', $mensaje, 2); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ALCORTE — Acceso</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
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
        .divider{display:flex;align-items:center;gap:12px;margin:20px 0;font-size:.6rem;color:var(--gray500);text-transform:uppercase;letter-spacing:.15em;font-weight:700}
        .divider::before,.divider::after{content:'';flex:1;height:1px;background:rgba(255,255,255,.06)}
        .msg-box{padding:12px 16px;border-radius:var(--radius);font-size:.75rem;font-weight:600;margin-bottom:16px;animation:fadeUp .4s ease both}
        .msg-success{background:rgba(46,111,64,.15);color:var(--green-light);border:1px solid rgba(46,111,64,.3)}
        .msg-error{background:rgba(239,68,68,.1);color:#f87171;border:1px solid rgba(239,68,68,.2)}
    </style>
</head>
<body>
    <canvas id="particles"></canvas>
    <div class="login-page">
        <div class="login-bg">
            <img src="https://images.unsplash.com/photo-1585747860715-2ba37e788b70?w=1080" alt="">
        </div>
        <div class="login-card animate-scaleIn">
            <div class="text-center" style="margin-bottom:28px">
                <h1 style="font-size:1.8rem;font-weight:900;letter-spacing:.06em;text-transform:uppercase;font-style:italic">Al<span class="text-gold" style="font-style:normal">Corte</span></h1>
                <p style="font-size:.55rem;color:var(--gray500);letter-spacing:.3em;text-transform:uppercase;font-weight:700;margin-top:4px">Panel de Acceso</p>
            </div>

            <?php if($mensaje): ?>
                <div class="msg-box msg-<?= $msg_type ?>"><?= $msg_text ?></div>
            <?php endif; ?>

            <div id="login-block">
                <form method="POST" class="flex flex-col gap-3">
                    <div class="input-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" name="usuario" placeholder="Usuario (ej: admin)" required class="input">
                    </div>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" placeholder="Contraseña" required class="input">
                    </div>
                    <button type="submit" name="login" class="btn btn-gold w-full" style="margin-top:8px">
                        <span>Entrar</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </form>
                <div class="divider">o</div>
                <p class="text-center" style="font-size:.75rem;color:var(--gray500)">
                    ¿No tienes cuenta? <a href="#" onclick="toggle()" class="text-gold font-bold" style="text-decoration:none">Regístrate</a>
                </p>
            </div>

            <div id="register-block" class="hidden">
                <form method="POST" class="flex flex-col gap-3">
                    <input type="text" name="nombre" placeholder="Nombre Completo" required class="input">
                    <input type="text" name="usuario" placeholder="Crea tu Usuario" required class="input">
                    <input type="tel" name="telefono" placeholder="Teléfono" required class="input">
                    <input type="password" name="password" placeholder="Contraseña" required class="input">
                    <button type="submit" name="registro" class="btn btn-ghost w-full" style="margin-top:8px">
                        <span>Crear Cuenta</span>
                    </button>
                </form>
                <div class="divider">—</div>
                <p class="text-center" style="font-size:.75rem">
                    <a href="#" onclick="toggle()" class="text-gold font-bold">← Volver al Login</a>
                </p>
            </div>
        </div>
    </div>
    <script src="app.js"></script>
    <script>
        new ParticleSystem('particles', 20);
        function toggle() {
            document.getElementById('login-block').classList.toggle('hidden');
            document.getElementById('register-block').classList.toggle('hidden');
        }
    </script>
</body>
</html>