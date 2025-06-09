<?php
session_start();
require_once '../../includes/db_config.php';

// Verificar que sea gestionador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'gestionador') {
    header('Location: ../../auth/login.php');
    exit;
}

// Verificar ID recibido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: jugadores.php');
    exit;
}

$id = intval($_GET['id']);
$error = '';
$success = '';

// Obtener datos del jugador
$stmt = $pdo->prepare("SELECT * FROM jugadores WHERE id = ?");
$stmt->execute([$id]);
$jugador = $stmt->fetch();

if (!$jugador) {
    $error = "Jugador no encontrado.";
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombres_apellidos = trim($_POST['nombres_apellidos']);
    $dni = trim($_POST['dni']);
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $telefono = trim($_POST['telefono']);
    $estado = $_POST['estado'];

    if (empty($nombres_apellidos) || empty($dni) || empty($fecha_nacimiento) || empty($telefono) || empty($estado)) {
        $error = "Todos los campos son obligatorios.";
    } elseif (!preg_match('/^\d{8}$/', $dni)) {
        $error = "DNI inválido.";
    } elseif (!preg_match('/^\d{9}$/', $telefono)) {
        $error = "Teléfono inválido.";
    } elseif (!in_array($estado, ['activo', 'inactivo'])) {
        $error = "Estado inválido.";
    } else {
        try {
            $update = $pdo->prepare("UPDATE jugadores SET nombres_apellidos = ?, dni = ?, fecha_nacimiento = ?, telefono = ?, estado = ? WHERE id = ?");
            $update->execute([$nombres_apellidos, $dni, $fecha_nacimiento, $telefono, $estado, $id]);
            $_SESSION['success'] = "Jugador actualizado correctamente.";
            header('Location: jugadores.php');
            exit;
        } catch (PDOException $e) {
            $error = "Error al actualizar jugador (¿DNI duplicado?).";
        }
    }
}

// Función para calcular edad
function calcularEdad($fecha) {
    $nacimiento = new DateTime($fecha);
    $hoy = new DateTime();
    return $nacimiento->diff($hoy)->y;
}
$edad = calcularEdad($jugador['fecha_nacimiento']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Jugador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #eaf6ff;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .card {
            width: 100%;
            max-width: 550px;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 0 12px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body>
    <div class="card">
        <h3 class="text-center mb-4">Editar Jugador</h3>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Nombres y Apellidos</label>
                <input type="text" class="form-control" name="nombres_apellidos" value="<?= htmlspecialchars($jugador['nombres_apellidos']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">DNI</label>
                <input type="text" class="form-control" name="dni" value="<?= htmlspecialchars($jugador['dni']) ?>" maxlength="8" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Fecha de Nacimiento</label>
                <input type="date" class="form-control" name="fecha_nacimiento" value="<?= $jugador['fecha_nacimiento'] ?>" required>
                <small class="text-muted">Edad actual: <?= $edad ?> años</small>
            </div>
            <div class="mb-3">
                <label class="form-label">Teléfono</label>
                <input type="text" class="form-control" name="telefono" value="<?= htmlspecialchars($jugador['telefono']) ?>" maxlength="9" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Estado</label>
                <select class="form-select" name="estado" required>
                    <option value="activo" <?= $jugador['estado'] === 'activo' ? 'selected' : '' ?>>Activo</option>
                    <option value="inactivo" <?= $jugador['estado'] === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary w-100">Actualizar</button>
            <a href="jugadores.php" class="btn btn-secondary w-100 mt-2">Volver</a>
        </form>
    </div>
</body>
</html>
