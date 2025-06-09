<?php
session_start();
require_once '../../includes/db_config.php';

// Verificar sesión de gestionador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'gestionador') {
    header('Location: ../../auth/login.php');
    exit;
}

$error = '';
$success = '';

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
            $stmt = $pdo->prepare("INSERT INTO paquetes (nombre_paquete, duracion_dias, precio) VALUES (?, ?, ?)");
            $stmt->execute([$nombre_paquete, $duracion_dias, $precio]);
            $_SESSION['success'] = "Paquete registrado correctamente.";
            header('Location: paquetes.php');
            exit;
        } catch (PDOException $e) {
            $error = "Error al registrar paquete.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Paquete</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f0f8ff;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .card {
            width: 100%;
            max-width: 500px;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 0 12px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
<div class="card">
    <h3 class="text-center mb-4">Registrar Nuevo Paquete</h3>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Nombre del Paquete</label>
            <input type="text" class="form-control" name="nombre_paquete" required placeholder="Ej. Mensual, Quincenal, Anual">
        </div>
        <div class="mb-3">
            <label class="form-label">Duración (en días)</label>
            <input type="number" class="form-control" name="duracion_dias" min="1" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Precio (S/.)</label>
            <input type="number" step="0.01" class="form-control" name="precio" min="0" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Registrar</button>
        <a href="paquetes.php" class="btn btn-secondary w-100 mt-2">Volver</a>
    </form>
</div>
</body>
</html>
