<?php
session_start();
require_once '../includes/db_config.php';

// Verificación de rol admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

// Verificar si llega un ID
if (!isset($_GET['id'])) {
    header('Location: users.php');
    exit;
}

$id = $_GET['id'];
$error = '';
$success = '';

// Obtener usuario
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$usuario = $stmt->fetch();

if (!$usuario) {
    $error = "Usuario no encontrado.";
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombres = trim($_POST['nombres_completos']);
    $rol = $_POST['rol'];
    $nueva_contra = $_POST['contrasena'];

    if (empty($nombres) || empty($rol)) {
        $error = "Nombres y rol son obligatorios.";
    } elseif (!in_array($rol, ['admin', 'gestionador'])) {
        $error = "Rol inválido.";
    } else {
        try {
            // Actualizar
            if (!empty($nueva_contra)) {
    $sql = "UPDATE usuarios SET nombres_completos = ?, contrasena = ?, rol = ? WHERE id = ?";
    $pdo->prepare($sql)->execute([$nombres, $nueva_contra, $rol, $id]);
    $success = "Usuario actualizado con nueva contraseña.";
}
 else {
                $sql = "UPDATE usuarios SET nombres_completos = ?, rol = ? WHERE id = ?";
                $pdo->prepare($sql)->execute([$nombres, $rol, $id]);
                $success = "Usuario actualizado sin cambiar contraseña.";
            }
        } catch (PDOException $e) {
            $error = "Error al actualizar usuario.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f3f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .card {
            width: 100%;
            max-width: 500px;
            border-radius: 15px;
            box-shadow: 0 0 10px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body>
    <div class="card p-4">
        <h3 class="text-center mb-4">Editar Usuario</h3>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if ($usuario): ?>
        <form method="POST">
            <div class="mb-3">
                <label for="nombres" class="form-label">Nombres Completos</label>
                <input type="text" class="form-control" id="nombres" name="nombres_completos" value="<?= htmlspecialchars($usuario['nombres_completos']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="contrasena" class="form-label">Nueva Contraseña (opcional)</label>
                <input type="password" class="form-control" id="contrasena" name="contrasena" placeholder="Dejar en blanco para no cambiar">
            </div>
            <div class="mb-3">
                <label for="rol" class="form-label">Rol</label>
                <select class="form-select" id="rol" name="rol" required>
                    <option value="admin" <?= $usuario['rol'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
                    <option value="gestionador" <?= $usuario['rol'] === 'gestionador' ? 'selected' : '' ?>>Gestionador</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary w-100">Actualizar</button>
            <a href="users.php" class="btn btn-secondary w-100 mt-2">Volver</a>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>
