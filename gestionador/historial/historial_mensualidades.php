<?php
session_start();
require_once '../../includes/db_config.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'gestionador') {
    header('Location: ../../auth/login.php');
    exit;
}

// Obtener jugadores
$jugadores = $pdo->query("SELECT id, nombres_apellidos FROM jugadores ORDER BY nombres_apellidos")->fetchAll();

// Filtros
$jugador_id = $_GET['jugador_id'] ?? '';
$categoria = $_GET['categoria'] ?? '';
$mes = $_GET['mes'] ?? '';
$estado = $_GET['estado'] ?? '';

// Consulta principal
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
if ($estado === 'vigente') {
    $query .= " AND CURDATE() BETWEEN m.fecha_inicio AND m.fecha_fin";
} elseif ($estado === 'vencido') {
    $query .= " AND CURDATE() > m.fecha_fin";
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
    <title>Historial de Mensualidades</title>
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
        .badge-vigente {
            background-color: #28a745;
        }
        .badge-vencido {
            background-color: #dc3545;
        }
        .badge-pagado {
            background-color: #28a745;
        }
        .badge-parcial {
            background-color: #ffc107;
        }
        .badge-debe {
            background-color: #dc3545;
        }
    </style>
</head>
<body>
<div class="d-flex">
    <?php include '../../includes/sidebar_gestionador.php'; ?>

    <div class="flex-grow-1 p-4">
        <h2 class="mb-4">📊 Historial de Mensualidades</h2>

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
            <div class="col-md-2">
                <label class="form-label">Categoría</label>
                <select name="categoria" class="form-select">
                    <option value="">Todas</option>
                    <option value="4pm-6pm" <?= $categoria == '4pm-6pm' ? 'selected' : '' ?>>4pm - 6pm</option>
                    <option value="6pm-8pm" <?= $categoria == '6pm-8pm' ? 'selected' : '' ?>>6pm - 8pm</option>
                    <option value="8pm-9:30pm" <?= $categoria == '8pm-9:30pm' ? 'selected' : '' ?>>8pm - 9:30pm</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Mes</label>
                <select name="mes" class="form-select">
                    <option value="">Todos</option>
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= $m ?>" <?= $mes == $m ? 'selected' : '' ?>>
                            <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Estado</label>
                <select name="estado" class="form-select">
                    <option value="">Todos</option>
                    <option value="vigente" <?= $estado == 'vigente' ? 'selected' : '' ?>>Vigente</option>
                    <option value="vencido" <?= $estado == 'vencido' ? 'selected' : '' ?>>Vencido</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">🔍 Filtrar</button>
            </div>
        </form>

        <div class="box">
            <table class="table table-bordered table-striped table-hover">
                <thead class="table-info text-center">
                    <tr>
                        <th>#</th>
                        <th>Jugador</th>
                        <th>Paquete</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <th>Categoría</th>
                        <th>Boleta</th>
                        <th>Estado</th>
                        <th>Pago</th>
                        <th>Saldo</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    <?php if ($mensualidades): ?>
                        <?php foreach ($mensualidades as $i => $m): ?>
                            <?php
                                $hoy = date('Y-m-d');
                                $vigencia = ($hoy >= $m['fecha_inicio'] && $hoy <= $m['fecha_fin']) ? 'vigente' : 'vencido';
                                $badge_estado = $vigencia === 'vigente' ? 'badge-vigente' : 'badge-vencido';
                                $estado_pago = strtolower($m['estado_pago']);
                                $badge_pago = $estado_pago === 'pagado' ? 'badge-pagado' : ($estado_pago === 'parcial' ? 'badge-parcial' : 'badge-debe');
                                $saldo = 0.00;
                                if ($estado_pago === 'parcial') {
                                    $saldo = max(0, $m['precio'] - $m['monto_pagado']);
                                } elseif ($estado_pago === 'debe') {
                                    $saldo = $m['precio'];
                                }
                            ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($m['jugador']) ?></td>
                                <td><?= htmlspecialchars($m['nombre_paquete']) ?></td>
                                <td><?= date('d/m/Y', strtotime($m['fecha_inicio'])) ?></td>
                                <td><?= date('d/m/Y', strtotime($m['fecha_fin'])) ?></td>
                                <td><?= htmlspecialchars($m['categoria']) ?></td>
                                <td><?= htmlspecialchars($m['numero_boleta']) ?></td>
                                <td><span class="badge <?= $badge_estado ?>"><?= ucfirst($vigencia) ?></span></td>
                                <td><span class="badge <?= $badge_pago ?>"><?= ucfirst($estado_pago) ?></span></td>
                                <td><?= number_format($saldo, 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="10" class="text-center">No hay mensualidades que coincidan con los filtros.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
