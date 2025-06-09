<?php
session_start();
require_once '../includes/db_config.php';

// Verificar sesión de gestionador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'gestionador') {
    header('Location: ../auth/login.php');
    exit;
}

$nombres = $_SESSION['nombres_completos'] ?? 'Gestionador';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Gestionador | Academia Voleibol</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f0f8ff;
            margin: 0;
        }
        .dashboard {
            padding: 40px;
        }
        .dashboard h2 {
            font-weight: 600;
        }
        .btn-group-custom .btn {
            margin-right: 15px;
            min-width: 180px;
        }
    </style>
</head>
<body>
<div class="d-flex">
    <!-- Barra lateral -->
    <?php include '../includes/sidebar_gestionador.php'; ?>

    <!-- Contenido principal -->
    <div class="flex-grow-1 dashboard">
        <h2 class="mb-3">Hola, <?= htmlspecialchars($nombres) ?> 👋</h2>
        <p>Bienvenido al panel de gestión de la Academia de Voleibol.</p>

        <div class="btn-group-custom mt-4">
            <a href="jugadores/jugadores.php" class="btn btn-outline-primary">👤 Ver Jugadores</a>
            <a href="paquetes/paquetes.php" class="btn btn-outline-success">📦 Ver Paquetes</a>
            <a href="mensualidades/mensualidades.php" class="btn btn-outline-info">📅 Ver Mensualidades</a>
            <a href="asistencia/asistencia.php" class="btn btn-outline-warning">✅ Control de Asistencia</a>
            <a href="historial/historial_mensualidades.php" class="btn btn-outline-dark">📚 Historial</a>
        </div>
    </div>
</div>
</body>
</html>
