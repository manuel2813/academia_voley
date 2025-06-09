<?php
session_start();
require_once '../../includes/db_config.php';

// Verificar sesión de gestionador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'gestionador') {
    header('Location: ../../auth/login.php');
    exit;
}

// Validar ID
if (!isset($_GET['id'])) {
    header('Location: paquetes.php');
    exit;
}

$id = $_GET['id'];
$error = '';
$success = '';

// Obtener datos del paquete
$stmt = $pdo->prepare("SELECT * FROM paquetes WHERE id = ?");
$stmt->execute([$id]);
$paquete = $stmt->fetch();

if (!$paquete) {
    $_SESSION['error'] = "Paquete no encontrado.";
    header("Location: paquetes.php");
    exit;
}

// Procesar edición
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_paquete = trim($_POST['nombre_paquete'] ?? '');
    $duracion_dias = $_POST['duracion_dias'] ?? '';
    $precio = $_POST['precio'] ?? '';

    if (empty($nombre_paquete)) {
        $error = "El nombre del paquete es obligatorio.";
    } elseif (!is_numeric($duracion_dias) || $duracion_dias <= 0) {
        $error = "Duración inválida.";
    } elseif (!is_numeric($precio) || $precio < 0) {
        $error = "Precio inválido.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE paquetes SET nombre_paquete = ?, duracion_dias = ?, precio = ? WHERE id = ?");
            $stmt->execute([$nombre_paquete, $duracion_dias, $precio, $id]);
            $_SESSION['success'] = "Paquete actualizado correctamente.";
            header("Location: paquetes.php");
            exit;
        } catch (PDOException $e) {
            $error = "Error al actualizar el paquete.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Paquete</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f5faff;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .card {
            max-width: 500px;
            width: 100%;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 0 12px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
<div class="card">
    <h3 class="text-center mb-4">Editar Paquete</h3>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Nombre del Paquete</label>
            <input type="text" class="form-control" name="nombre_paquete" value="<?= htmlspecialchars($paquete['nombre_paquete']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Duración (en días)</label>
            <input type="number" class="form-control" name="duracion_dias" min="1" value="<?= $paquete['duracion_dias'] ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Precio (S/.)</label>
            <input type="number" step="0.01" class="form-control" name="precio" min="0" value="<?= $paquete['precio'] ?>" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Guardar Cambios</button>
        <a href="paquetes.php" class="btn btn-secondary w-100 mt-2">Volver</a>
    </form>
</div>
</body>
</html>
