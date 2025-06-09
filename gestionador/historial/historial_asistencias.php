<?php
session_start();
require_once '../../includes/db_config.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'gestionador') {
    header('Location: ../../auth/login.php');
    exit;
}

// Obtener jugadores activos
$jugadores = $pdo->query("SELECT id, nombres_apellidos FROM jugadores WHERE estado = 'activo' ORDER BY nombres_apellidos")->fetchAll();

// Filtros
$jugador_id = $_GET['jugador_id'] ?? '';
$categoria = $_GET['categoria'] ?? '';
$desde = $_GET['desde'] ?? '';
$hasta = $_GET['hasta'] ?? '';

// Consulta base
$query = "
    SELECT a.*, j.nombres_apellidos 
    FROM asistencias a
    JOIN jugadores j ON a.jugador_id = j.id
    WHERE 1=1
";
$params = [];

if ($jugador_id) {
    $query .= " AND a.jugador_id = ?";
    $params[] = $jugador_id;
}
if ($categoria) {
    $query .= " AND a.categoria = ?";
    $params[] = $categoria;
}
if ($desde && $hasta) {
    $query .= " AND a.fecha BETWEEN ? AND ?";
    $params[] = $desde;
    $params[] = $hasta;
} elseif ($desde) {
    $query .= " AND a.fecha >= ?";
    $params[] = $desde;
} elseif ($hasta) {
    $query .= " AND a.fecha <= ?";
    $params[] = $hasta;
}

$query .= " ORDER BY a.fecha DESC, j.nombres_apellidos";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$registros = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Asistencias</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f4faff;
            margin: 0;
        }
        .box {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 0 12px rgba(0,0,0,0.1);
        }
        .badge-success {
            background-color: #28a745;
        }
        .badge-secondary {
            background-color: #dc3545;
        }
    </style>
</head>
<body>
<div class="d-flex">
    <?php include '../../includes/sidebar_gestionador.php'; ?>

    <div class="flex-grow-1 p-4">
        <h2 class="mb-4">📖 Historial de Asistencias</h2>

        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-4">
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
            <div class="col-md-2">
                <label class="form-label">Desde</label>
                <input type="date" name="desde" class="form-control" value="<?= $desde ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Hasta</label>
                <input type="date" name="hasta" class="form-control" value="<?= $hasta ?>">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">🔍</button>
            </div>
        </form>

        <div class="box">
            <table class="table table-bordered table-hover table-striped">
                <thead class="table-info">
                    <tr>
                        <th>#</th>
                        <th>Fecha</th>
                        <th>Jugador</th>
                        <th>Categoría</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($registros): ?>
                        <?php foreach ($registros as $i => $r): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= date('d/m/Y', strtotime($r['fecha'])) ?></td>
                                <td><?= htmlspecialchars($r['nombres_apellidos']) ?></td>
                                <td><?= htmlspecialchars($r['categoria']) ?></td>
                                <td>
                                    <?php
    $estado = strtolower(trim($r['estado']));
    $badgeClass = $estado === 'presente' ? 'badge-success' : 'badge-secondary';
?>
<span class="badge <?= $badgeClass ?>">
    <?= ucfirst($estado) ?>
</span>

                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center">No se encontraron registros.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
