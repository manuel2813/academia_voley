<?php
session_start();
require_once '../includes/db_config.php';

// Si ya está logueado, redirigir directamente
if (isset($_SESSION['usuario_id']) && isset($_SESSION['rol'])) {
    if ($_SESSION['rol'] === 'admin') {
        header('Location: ../admin/index.php');
        exit;
    } elseif ($_SESSION['rol'] === 'gestionador') {
        header('Location: ../gestionador/index.php');
        exit;
    }
}

// Procesamiento del login
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dni = $_POST['dni'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';

    if (strlen($dni) !== 8 || !is_numeric($dni)) {
        $error = "DNI inválido.";
    } elseif (strlen($contrasena) < 8) {
        $error = "Contraseña inválida.";
    } else {
        // Buscar usuario en la BD
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE dni = ?");
        $stmt->execute([$dni]);
        $usuario = $stmt->fetch();

        if ($usuario && $contrasena === $usuario['contrasena']) {

            // Autenticación exitosa
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['nombres_completos'] = $usuario['nombres_completos'];
            $_SESSION['rol'] = $usuario['rol'];

            // Redirigir según rol
            if ($usuario['rol'] === 'admin') {
                header('Location: ../admin/index.php');
            } else {
                header('Location: ../gestionador/index.php');
            }
            exit;
        } else {
            $error = "DNI o contraseña incorrectos.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login | Academia Voleibol</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #e0f7fa, #ffffff);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .login-box {
            background: white;
            padding: 35px;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2 class="text-center mb-4">Iniciar Sesión</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="dni" class="form-label">DNI</label>
                <input type="text" class="form-control" id="dni" name="dni" maxlength="8" required>
            </div>
            <div class="mb-3">
                <label for="contrasena" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="contrasena" name="contrasena" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Ingresar</button>
        </form>
    </div>
</body>
</html>
