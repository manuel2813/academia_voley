<?php
session_start();
require_once '../includes/db_config.php';

// Verificar sesión de admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

$nombres = $_SESSION['nombres_completos'] ?? 'Administrador';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Administrador | Academia Voleibol</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="d-flex">
    <!-- Barra lateral admin -->
    <?php include '../includes/sidebar_admin.php'; ?>

    <!-- Contenido principal -->
    <div class="flex-grow-1 p-4">
        <h2 class="mb-3">Bienvenido, <?= htmlspecialchars($nombres) ?> 👋</h2>
        <p>Este es tu panel principal de administración.</p>

        <div class="mt-4">
            <a href="users.php" class="btn btn-primary">👥 Ir a Gestión de Usuarios</a>
        </div>
    </div>
</div>
</body>
</html>
