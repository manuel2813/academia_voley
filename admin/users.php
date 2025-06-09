<?php
session_start();
require_once '../includes/db_config.php';

// Verificar si el usuario es administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

// Mensajes desde sesión
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

// Obtener usuarios
$stmt = $pdo->query("SELECT id, nombres_completos, dni, rol, fecha_registro FROM usuarios ORDER BY fecha_registro DESC");
$usuarios = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Usuarios | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f0f2f5;
            padding: 20px;
        }
        .table-container {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .alert {
            max-width: 700px;
            margin: 0 auto 20px auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center mb-4">Usuarios del Sistema</h2>

        <?php if ($success): ?>
            <div class="alert alert-success text-center"><?= htmlspecialchars($success) ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Lista de Usuarios</h5>
                <a href="add_user.php" class="btn btn-success">➕ Nuevo Usuario</a>
            </div>

            <table class="table table-bordered table-hover table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Nombres Completos</th>
                        <th>DNI</th>
                        <th>Rol</th>
                        <th>Fecha Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($usuarios) > 0): ?>
                        <?php foreach ($usuarios as $i => $usuario): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($usuario['nombres_completos']) ?></td>
                                <td><?= htmlspecialchars($usuario['dni']) ?></td>
                                <td><?= ucfirst($usuario['rol']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($usuario['fecha_registro'])) ?></td>
                                <td>
                                    <a href="edit_user.php?id=<?= $usuario['id'] ?>" class="btn btn-sm btn-primary">✏️ Editar</a>
                                    <a href="delete_user.php?id=<?= $usuario['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de eliminar este usuario?');">🗑️ Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center">No hay usuarios registrados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
