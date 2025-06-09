<?php
session_start();
require_once '../../includes/db_config.php';

// Verificar que sea gestionador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'gestionador') {
    header('Location: ../../auth/login.php');
    exit;
}

// Mensajes desde sesión
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

// Obtener todos los jugadores
$stmt = $pdo->query("SELECT * FROM jugadores ORDER BY fecha_registro DESC");
$jugadores = $stmt->fetchAll();

// Función para calcular edad
function calcularEdad($fecha) {
    $nacimiento = new DateTime($fecha);
    $hoy = new DateTime();
    return $nacimiento->diff($hoy)->y;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Jugadores Registrados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f7fafd;
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
        <h2 class="mb-4 text-center">Jugadores Registrados</h2>

        <?php if ($success): ?>
            <div class="alert alert-success text-center"><?= htmlspecialchars($success) ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Lista de Jugadores</h5>
                <a href="add_jugador.php" class="btn btn-success">➕ Nuevo Jugador</a>
            </div>

            <table class="table table-bordered table-hover table-striped">
                <thead class="table-primary text-center">
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>DNI</th>
                        <th>F. Nacimiento</th>
                        <th>Edad</th>
                        <th>Teléfono</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    <?php if (count($jugadores) > 0): ?>
                        <?php foreach ($jugadores as $i => $jugador): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($jugador['nombres_apellidos']) ?></td>
                                <td><?= $jugador['dni'] ?></td>
                                <td><?= date('d/m/Y', strtotime($jugador['fecha_nacimiento'])) ?></td>
                                <td><?= calcularEdad($jugador['fecha_nacimiento']) ?></td>
                                <td><?= $jugador['telefono'] ?></td>
                                <td>
                                    <span class="badge bg-<?= $jugador['estado'] === 'activo' ? 'success' : 'secondary' ?>">
                                        <?= ucfirst($jugador['estado']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="edit_jugador.php?id=<?= $jugador['id'] ?>" class="btn btn-sm btn-primary">✏️ Editar</a>
                                    <a href="delete_jugador.php?id=<?= $jugador['id'] ?>" class="btn btn-sm btn-danger"
                                       onclick="return confirm('¿Eliminar este jugador?');">🗑️ Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center">No hay jugadores registrados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
