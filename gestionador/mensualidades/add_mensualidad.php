<?php
session_start();
require_once '../../includes/db_config.php';

// Verificar acceso
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'gestionador') {
    header('Location: ../../auth/login.php');
    exit;
}

$error = '';
$success = '';

// Obtener jugadores y paquetes
$jugadores = $pdo->query("SELECT id, nombres_apellidos FROM jugadores WHERE estado = 'activo' ORDER BY nombres_apellidos")->fetchAll();
$paquetes = $pdo->query("SELECT id, nombre_paquete, duracion_dias, precio FROM paquetes ORDER BY nombre_paquete")->fetchAll();

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jugador_id = $_POST['jugador_id'] ?? '';
    $paquete_id = $_POST['paquete_id'] ?? '';
    $categoria = $_POST['categoria'] ?? '';
    $numero_boleta = trim($_POST['numero_boleta']);
    $estado_pago = $_POST['estado_pago'] ?? 'debe';
    $monto_pagado = $_POST['monto_pagado'] ?? 0;

    // Validaciones
    if (empty($jugador_id) || empty($paquete_id) || empty($categoria) || empty($numero_boleta)) {
        $error = "Todos los campos son obligatorios.";
    } else {
        // Obtener duración y precio del paquete
        $stmt = $pdo->prepare("SELECT duracion_dias, precio FROM paquetes WHERE id = ?");
        $stmt->execute([$paquete_id]);
        $paquete = $stmt->fetch();

        if (!$paquete) {
            $error = "Paquete inválido.";
        } else {
            $fecha_inicio = date('Y-m-d');
            $fecha_fin = date('Y-m-d', strtotime("+{$paquete['duracion_dias']} days"));
            $precio = $paquete['precio'];

            if ($estado_pago === 'pagado') {
                $monto_pagado = $precio;
            } elseif ($estado_pago === 'debe') {
                $monto_pagado = 0;
            }

            try {
                $insert = $pdo->prepare("INSERT INTO mensualidades (jugador_id, paquete_id, fecha_inicio, fecha_fin, categoria, numero_boleta, estado_pago, monto_pagado, precio) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $insert->execute([
                    $jugador_id,
                    $paquete_id,
                    $fecha_inicio,
                    $fecha_fin,
                    $categoria,
                    $numero_boleta,
                    $estado_pago,
                    $monto_pagado,
                    $precio
                ]);

                $_SESSION['success'] = "Mensualidad registrada correctamente.";
                header('Location: mensualidades.php');
                exit;
            } catch (PDOException $e) {
                $error = "Error al registrar mensualidad.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Mensualidad</title>
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
    <h3 class="text-center mb-4">Registrar Nueva Mensualidad</h3>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Jugador</label>
            <select class="form-select" name="jugador_id" required>
                <option value="">Seleccionar...</option>
                <?php foreach ($jugadores as $j): ?>
                    <option value="<?= $j['id'] ?>"><?= htmlspecialchars($j['nombres_apellidos']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Paquete</label>
            <select class="form-select" name="paquete_id" required>
                <option value="">Seleccionar...</option>
                <?php foreach ($paquetes as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= ucfirst($p['nombre_paquete']) ?> - <?= $p['duracion_dias'] ?> días (S/ <?= number_format($p['precio'], 2) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Categoría</label>
            <select class="form-select" name="categoria" required>
                <option value="">Seleccionar...</option>
                <option value="4pm-6pm">4pm - 6pm</option>
                <option value="6pm-8pm">6pm - 8pm</option>
                <option value="8pm-9:30pm">8pm - 9:30pm</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Número de Boleta</label>
            <input type="text" class="form-control" name="numero_boleta" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Estado de Pago</label>
            <select name="estado_pago" id="estado_pago" class="form-select" required onchange="toggleMontoParcial(this)">
                <option value="debe">Debe</option>
                <option value="pagado">Pagado</option>
                <option value="parcial">Pagó una parte</option>
            </select>
        </div>

        <div class="mb-3" id="monto_parcial_group" style="display:none;">
            <label class="form-label">Monto Pagado (S/.)</label>
            <input type="number" name="monto_pagado" class="form-control" step="0.01" min="0">
        </div>

        <button type="submit" class="btn btn-primary w-100">Registrar</button>
        <a href="mensualidades.php" class="btn btn-secondary w-100 mt-2">Volver</a>
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
