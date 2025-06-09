<?php
session_start();
require_once '../../includes/db_config.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'gestionador') {
    header('Location: ../../auth/login.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: mensualidades.php');
    exit;
}

$error = '';

// Obtener mensualidad
$stmt = $pdo->prepare("SELECT * FROM mensualidades WHERE id = ?");
$stmt->execute([$id]);
$mensualidad = $stmt->fetch();

if (!$mensualidad) {
    $_SESSION['error'] = "Mensualidad no encontrada.";
    header('Location: mensualidades.php');
    exit;
}

// Obtener jugadores y paquetes
$jugadores = $pdo->query("SELECT id, nombres_apellidos FROM jugadores WHERE estado = 'activo' ORDER BY nombres_apellidos")->fetchAll();
$paquetes = $pdo->query("SELECT id, nombre_paquete, duracion_dias, precio FROM paquetes ORDER BY nombre_paquete")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jugador_id = $_POST['jugador_id'] ?? '';
    $paquete_id = $_POST['paquete_id'] ?? '';
    $categoria = $_POST['categoria'] ?? '';
    $numero_boleta = trim($_POST['numero_boleta']);
    $estado_pago = $_POST['estado_pago'] ?? 'debe';
    $monto_pagado = $_POST['monto_pagado'] ?? 0;

    if (empty($jugador_id) || empty($paquete_id) || empty($categoria) || empty($numero_boleta)) {
        $error = "Todos los campos son obligatorios.";
    } else {
        $stmt = $pdo->prepare("SELECT duracion_dias, precio FROM paquetes WHERE id = ?");
        $stmt->execute([$paquete_id]);
        $paquete = $stmt->fetch();

        if (!$paquete) {
            $error = "Paquete inválido.";
        } else {
            $fecha_inicio = $mensualidad['fecha_inicio'];
            $fecha_fin = date('Y-m-d', strtotime("$fecha_inicio +{$paquete['duracion_dias']} days"));
            $precio = $paquete['precio'];

            if ($estado_pago === 'pagado') {
                $monto_pagado = $precio;
            } elseif ($estado_pago === 'debe') {
                $monto_pagado = 0;
            }

            try {
                $update = $pdo->prepare("UPDATE mensualidades SET jugador_id = ?, paquete_id = ?, fecha_fin = ?, categoria = ?, numero_boleta = ?, estado_pago = ?, monto_pagado = ?, precio = ? WHERE id = ?");
                $update->execute([$jugador_id, $paquete_id, $fecha_fin, $categoria, $numero_boleta, $estado_pago, $monto_pagado, $precio, $id]);

                $_SESSION['success'] = "Mensualidad actualizada correctamente.";
                header('Location: mensualidades.php');
                exit;
            } catch (PDOException $e) {
                $error = "Error al actualizar mensualidad.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Mensualidad</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f1f9ff;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .card {
            max-width: 550px;
            width: 100%;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 0 12px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
<div class="card">
    <h3 class="text-center mb-4">Editar Mensualidad</h3>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Jugador</label>
            <select class="form-select" name="jugador_id" required>
                <?php foreach ($jugadores as $j): ?>
                    <option value="<?= $j['id'] ?>" <?= $mensualidad['jugador_id'] == $j['id'] ? 'selected' : '' ?>><?= htmlspecialchars($j['nombres_apellidos']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Paquete</label>
            <select class="form-select" name="paquete_id" required>
                <?php foreach ($paquetes as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $mensualidad['paquete_id'] == $p['id'] ? 'selected' : '' ?>><?= ucfirst($p['nombre_paquete']) ?> - <?= $p['duracion_dias'] ?> días (S/ <?= number_format($p['precio'], 2) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Categoría</label>
            <select class="form-select" name="categoria" required>
                <option value="4pm-6pm" <?= $mensualidad['categoria'] == '4pm-6pm' ? 'selected' : '' ?>>4pm - 6pm</option>
                <option value="6pm-8pm" <?= $mensualidad['categoria'] == '6pm-8pm' ? 'selected' : '' ?>>6pm - 8pm</option>
                <option value="8pm-9:30pm" <?= $mensualidad['categoria'] == '8pm-9:30pm' ? 'selected' : '' ?>>8pm - 9:30pm</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Número de Boleta</label>
            <input type="text" class="form-control" name="numero_boleta" value="<?= htmlspecialchars($mensualidad['numero_boleta']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Estado de Pago</label>
            <select name="estado_pago" id="estado_pago" class="form-select" required onchange="toggleMontoParcial(this)">
                <option value="debe" <?= $mensualidad['estado_pago'] == 'debe' ? 'selected' : '' ?>>Debe</option>
                <option value="pagado" <?= $mensualidad['estado_pago'] == 'pagado' ? 'selected' : '' ?>>Pagado</option>
                <option value="parcial" <?= $mensualidad['estado_pago'] == 'parcial' ? 'selected' : '' ?>>Pagó una parte</option>
            </select>
        </div>

        <div class="mb-3" id="monto_parcial_group" style="display:<?= $mensualidad['estado_pago'] == 'parcial' ? 'block' : 'none' ?>;">
            <label class="form-label">Monto Pagado (S/.)</label>
            <input type="number" name="monto_pagado" class="form-control" step="0.01" min="0" value="<?= $mensualidad['monto_pagado'] ?>">
        </div>

        <button type="submit" class="btn btn-primary w-100">Actualizar</button>
        <a href="mensualidades.php" class="btn btn-secondary w-100 mt-2">Cancelar</a>
    </form>
</div>

<script>
function toggleMontoParcial(select) {
    const partialGroup = document.getElementById('monto_parcial_group');
    if (select.value === 'parcial') {
        partialGroup.style.display = 'block';
    } else {
        partialGroup.style.display = 'none';
        document.querySelector('[name="monto_pagado"]').value = '';
    }
}
</script>
</body>
</html>
