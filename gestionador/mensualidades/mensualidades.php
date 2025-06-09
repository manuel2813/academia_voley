<?php
session_start();
require_once '../../includes/db_config.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'gestionador') {
    header('Location: ../../auth/login.php');
    exit;
}

$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

// Obtener jugadores activos
$jugadores = $pdo->query("SELECT id, nombres_apellidos FROM jugadores WHERE estado = 'activo' ORDER BY nombres_apellidos")->fetchAll();

// Filtros
$jugador_id = $_GET['jugador_id'] ?? '';
$categoria = $_GET['categoria'] ?? '';
$mes = $_GET['mes'] ?? '';
$estado_pago = $_GET['estado'] ?? '';

// Consulta con precio del paquete incluido
$query = "
    SELECT m.*, j.nombres_apellidos AS jugador, p.nombre_paquete, p.precio
    FROM mensualidades m
    JOIN jugadores j ON m.jugador_id = j.id
    JOIN paquetes p ON m.paquete_id = p.id
    WHERE 1=1
";

$params = [];

if ($jugador_id) {
    $query .= " AND m.jugador_id = ?";
    $params[] = $jugador_id;
}
if ($categoria) {
    $query .= " AND m.categoria = ?";
    $params[] = $categoria;
}
if ($mes) {
    $query .= " AND MONTH(m.fecha_inicio) = ?";
    $params[] = $mes;
}
if ($estado_pago) {
    $query .= " AND m.estado_pago = ?";
    $params[] = $estado_pago;
}

$query .= " ORDER BY m.fecha_inicio DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$mensualidades = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mensualidades | Academia Voleibol</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4faff; margin: 0; }
        .table-container {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 0 12px rgba(0,0,0,0.1);
        }
        .alert {
            max-width: 100%;
            margin-bottom: 20px;
        }
        .badge-parcial { background-color: #ffc107; }
        .badge-debe { background-color: #dc3545; }
        .badge-pagado { background-color: #28a745; }
    </style>
</head>
<body>
<div class="d-flex">
    <?php include '../../includes/sidebar_gestionador.php'; ?>

    <div class="flex-grow-1 p-4">
        <h2 class="mb-4">📅 Mensualidades con Filtros</h2>

        <?php if ($success): ?>
            <div class="alert alert-success text-center"><?= htmlspecialchars($success) ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label">Jugador</label>
                <select name="jugador_id" class="form-select">
                    <option value="">Todos</option>
                    <?php foreach ($jugadores as $j): ?>
                        <option value="<?= $j['id'] ?>" <?= $jugador_id == $j['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($j['nombres_apellidos']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Categoría</label>
                <select name="categoria" class="form-select">
                    <option value="">Todas</option>
                    <option value="4pm-6pm" <?= $categoria == '4pm-6pm' ? 'selected' : '' ?>>4pm - 6pm</option>
                    <option value="6pm-8pm" <?= $categoria == '6pm-8pm' ? 'selected' : '' ?>>6pm - 8pm</option>
                    <option value="8pm-9:30pm" <?= $categoria == '8pm-9:30pm' ? 'selected' : '' ?>>8pm - 9:30pm</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Mes de Inicio</label>
                <select name="mes" class="form-select">
                    <option value="">Todos</option>
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= $m ?>" <?= $mes == $m ? 'selected' : '' ?>>
                            <?= strftime('%B', mktime(0, 0, 0, $m, 1)) ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Estado de Pago</label>
                <select name="estado" class="form-select">
                    <option value="">Todos</option>
                    <option value="pagado" <?= $estado_pago == 'pagado' ? 'selected' : '' ?>>Pagado</option>
                    <option value="debe" <?= $estado_pago == 'debe' ? 'selected' : '' ?>>Debe</option>
                    <option value="parcial" <?= $estado_pago == 'parcial' ? 'selected' : '' ?>>Pagó una parte</option>
                </select>
            </div>

            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">🔍 Filtrar</button>
            </div>
        </form>

        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Resultados</h5>
                <a href="add_mensualidad.php" class="btn btn-success">➕ Nueva Mensualidad</a>
            </div>

            <table class="table table-bordered table-hover table-striped">
                <thead class="table-info text-center">
                    <tr>
                        <th>#</th>
                        <th>Jugador</th>
                        <th>Paquete</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <th>Categoría</th>
                        <th>Boleta</th>
                        <th>PAGO</th>
                        <th>$</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    <?php if ($mensualidades): ?>
                        <?php foreach ($mensualidades as $i => $m): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($m['jugador']) ?></td>
                                <td><?= htmlspecialchars($m['nombre_paquete']) ?></td>
                                <td><?= date('d/m/Y', strtotime($m['fecha_inicio'])) ?></td>
                                <td><?= date('d/m/Y', strtotime($m['fecha_fin'])) ?></td>
                                <td><?= htmlspecialchars($m['categoria']) ?></td>
                                <td><?= htmlspecialchars($m['numero_boleta']) ?></td>
                                <td>
                                    <?php
                                        $estado = $m['estado_pago'];
                                        $badge = $estado === 'pagado' ? 'badge-pagado' : ($estado === 'parcial' ? 'badge-parcial' : 'badge-debe');
                                        echo "<span class='badge $badge text-uppercase'>" . strtoupper($estado) . "</span>";
                                    ?>
                                </td>
                                <td>
                                    <?php
                                        if ($estado === 'pagado') {
                                            echo '0.00';
                                        } elseif ($estado === 'parcial') {
                                            $deuda = $m['precio'] - $m['monto_pagado'];
                                            echo number_format(max(0, $deuda), 2);
                                        } else {
                                            echo number_format($m['precio'], 2);
                                        }
                                    ?>
                                </td>
                                <td>
                                    <a href="edit_mensualidad.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-primary">✏️</a>
                                    <a href="delete_mensualidad.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro de eliminar esta mensualidad?')">🗑️</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="10" class="text-center">No se encontraron mensualidades con los filtros aplicados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
