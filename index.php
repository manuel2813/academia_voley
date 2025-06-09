<?php
session_start();

// Si el usuario ya inició sesión, redirigir según rol
if (isset($_SESSION['usuario_id']) && isset($_SESSION['rol'])) {
    if ($_SESSION['rol'] === 'admin') {
        header('Location: admin/index.php');
        exit;
    } elseif ($_SESSION['rol'] === 'gestionador') {
        header('Location: gestionador/index.php');
        exit;
    }
}

// Si no hay sesión, mostrar pantalla de bienvenida / redirección a login
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Academia de Voleibol</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #f8f9fa, #e3f2fd);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .bienvenida-box {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 10px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body>
    <div class="bienvenida-box">
        <h1 class="mb-4">Bienvenido a la Academia de Voleibol 🏐</h1>
        <p class="mb-4">Por favor, inicia sesión para acceder al sistema.</p>
        <a href="auth/login.php" class="btn btn-primary btn-lg">Iniciar sesión</a>
    </div>
</body>
</html>
