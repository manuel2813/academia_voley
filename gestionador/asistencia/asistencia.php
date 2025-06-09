<?php
session_start();
require_once '../../includes/db_config.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'gestionador') {
    header('Location: ../../auth/login.php');
    exit;
}

// Determinar la fecha actual o seleccionada
$fecha_actual = $_GET['fecha'] ?? $_POST['fecha'] ?? date('Y-m-d');
$mensaje = '';

// Procesar registro de asistencias
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['estado'])) {
    $estados = $_POST['estado'];

    foreach ($estados as $jugador_id => $estado) {
        $check = $pdo->prepare("SELECT id FROM asistencias WHERE jugador_id = ? AND fecha = ?");
        $check->execute([$jugador_id, $fecha_actual]);

        if ($check->rowCount() == 0) {
            $cat_query = $pdo->prepare("
                SELECT 
                    CASE 
                        WHEN id % 3 = 0 THEN '8pm-9:30pm'
                        WHEN id % 2 = 0 THEN '6pm-8pm'
                        ELSE '4pm-6pm'
                    END AS categoria 
                FROM jugadores WHERE id = ?
            ");
            $cat_query->execute([$jugador_id]);
            $cat = $cat_query->fetchColumn();

            $insert = $pdo->prepare("INSERT INTO asistencias (jugador_id, fecha, estado, categoria) VALUES (?, ?, ?, ?)");
            $insert->execute([$jugador_id, $fecha_actual, $estado, $cat]);
        } else {
            $update = $pdo->prepare("UPDATE asistencias SET estado = ? WHERE jugador_id = ? AND fecha = ?");
            $update->execute([$estado, $jugador_id, $fecha_actual]);
        }
    }

    $mensaje = "✅ Asistencias registradas para el " . date('d/m/Y', strtotime($fecha_actual));
}

// Obtener jugadores activos y categorías calculadas
$jugadores = $pdo->query("
    SELECT id, nombres_apellidos, dni,
        CASE 
            WHEN id % 3 = 0 THEN '8pm-9:30pm'
            WHEN id % 2 = 0 THEN '6pm-8pm'
            ELSE '4pm-6pm'
        END AS categoria
    FROM jugadores
    WHERE estado = 'activo'
    ORDER BY categoria, nombres_apellidos
")->fetchAll();

// Obtener asistencias existentes de la fecha
$asistencias_registradas = [];
$stmt = $pdo->prepare("SELECT jugador_id, estado FROM asistencias WHERE fecha = ?");
$stmt->execute([$fecha_actual]);
foreach ($stmt->fetchAll() as $asistencia) {
    $asistencias_registradas[$asistencia['jugador_id']] = $asistencia['estado'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Control de Asistencia | Academia Voleibol</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #eef9ff; margin: 0; }
        .asistencia-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .categoria-header {
            font-weight: bold;
            font-size: 1.2em;
            background-color: #d1ecf1;
            padding: 10px 15px;
            border-radius: 10px;
            margin-top: 25px;
        }
        .jugador-row {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 10px;
            background: #f9f9f9;
        }
    </style>
</head>
<body>
<div class="d-flex">
    <?php include '../../includes/sidebar_gestionador.php'; ?>

    <div class="flex-grow-1 p-4">
        <h2 class="mb-4">✅ Control Diario de Asistencia</h2>

        <?php if ($mensaje): ?>
            <div class="alert alert-success"><?= $mensaje ?></div>
        <?php endif; ?>

        <!-- Buscador por fecha -->
        <form method="GET" class="mb-3 d-flex align-items-end">
            <div class="me-2">
                <label class="form-label"><strong>Seleccionar Fecha:</strong></label>
                <input type="date" name="fecha" class="form-control" value="<?= $fecha_actual ?>" required>
            </div>
            <button type="submit" class="btn btn-secondary mb-1">🔍 Buscar</button>
        </form>

        <!-- Formulario de registro -->
        <form method="POST" class="asistencia-card">
            <input type="hidden" name="fecha" value="<?= $fecha_actual ?>">

            <?php
            $actual_categoria = '';
            foreach ($jugadores as $j):
                if ($j['categoria'] !== $actual_categoria):
                    if ($actual_categoria !== '') echo '</div>';
                    $actual_categoria = $j['categoria'];
                    echo "<div class='categoria-header'>Categoría: {$actual_categoria}</div><div class='mb-3'>";
                endif;

                $estado_guardado = $asistencias_registradas[$j['id']] ?? '';
            ?>
                <div class="d-flex justify-content-between align-items-center jugador-row">
                    <div>
                        <strong><?= htmlspecialchars($j['nombres_apellidos']) ?></strong>
                        <small>(DNI: <?= $j['dni'] ?>)</small>
                    </div>
                    <div>
                        <select name="estado[<?= $j['id'] ?>]" class="form-select w-auto">
                            <option value="Presente" <?= strtolower($estado_guardado) === 'presente' ? 'selected' : '' ?>>Presente</option>
                            <option value="Ausente" <?= strtolower($estado_guardado) === 'ausente' ? 'selected' : '' ?>>Ausente</option>
                        </select>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>

            <div class="mt-4 text-end">
                <button type="submit" class="btn btn-primary">💾 Registrar Asistencia</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
