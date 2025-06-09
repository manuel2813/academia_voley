<?php
session_start();
require_once '../../includes/db_config.php';

// Verificar sesión de gestionador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'gestionador') {
    header('Location: ../../auth/login.php');
    exit;
}

// Mensajes desde sesión
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

// Obtener paquetes
$stmt = $pdo->query("SELECT * FROM paquetes ORDER BY id DESC");
$paquetes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Paquetes | Academia Voleibol</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f4faff;
            margin: 0;
        }
        .table-container {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .alert {
            max-width: 100%;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="d-flex">
    <?php include '../../includes/sidebar_gestionador.php'; ?>

    <div class="flex-grow-1 p-4">
        <h2 class="mb-4">📦 Gestión de Paquetes</h2>

        <?php if ($success): ?>
            <div class="alert alert-success text-center"><?= htmlspecialchars($success) ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Lista de Paquetes</h5>
                <a href="add_paquete.php" class="btn btn-success">➕ Nuevo Paquete</a>
            </div>

            <table class="table table-bordered table-striped table-hover">
                <thead class="table-primary text-center">
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Duración (días)</th>
                        <th>Precio (S/.)</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    <?php if ($paquetes): ?>
                        <?php foreach ($paquetes as $i => $p): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= ucfirst($p['nombre_paquete']) ?></td>
                                <td><?= $p['duracion_dias'] ?></td>
                                <td><?= number_format($p['precio'], 2) ?></td>
                                <td>
                                    <a href="edit_paquete.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-primary">✏️ Editar</a>
                                    <a href="delete_paquete.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-danger"
                                       onclick="return confirm('¿Eliminar este paquete?');">🗑️ Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center">No hay paquetes registrados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
