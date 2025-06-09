<?php
session_start();
require_once '../../includes/db_config.php';

// Verificar rol gestionador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'gestionador') {
    header('Location: ../../auth/login.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombres_apellidos = trim($_POST['nombres_apellidos']);
    $dni = trim($_POST['dni']);
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $telefono = trim($_POST['telefono']);
    $estado = $_POST['estado'];

    // Validaciones
    if (empty($nombres_apellidos) || empty($dni) || empty($fecha_nacimiento) || empty($telefono) || empty($estado)) {
        $error = "Todos los campos son obligatorios.";
    } elseif (!preg_match('/^\d{8}$/', $dni)) {
        $error = "DNI inválido. Debe tener 8 dígitos numéricos.";
    } elseif (!preg_match('/^\d{9}$/', $telefono)) {
        $error = "Teléfono inválido. Debe tener 9 dígitos.";
    } elseif (!in_array($estado, ['activo', 'inactivo'])) {
        $error = "Estado inválido.";
    } else {
        try {
            // Calcular edad
            $nac = new DateTime($fecha_nacimiento);
            $hoy = new DateTime();
            $edad = $nac->diff($hoy)->y;

            // Insertar en base de datos
            $stmt = $pdo->prepare("INSERT INTO jugadores (nombres_apellidos, dni, fecha_nacimiento, telefono, estado) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nombres_apellidos, $dni, $fecha_nacimiento, $telefono, $estado]);
            $success = "Jugador registrado correctamente. Edad: {$edad} años.";
        } catch (PDOException $e) {
            $error = "Error al registrar jugador (¿DNI duplicado?).";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Jugador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #e9f7ff;
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
        <h3 class="text-center mb-4">Registrar Jugador</h3>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="nombres_apellidos" class="form-label">Nombres y Apellidos</label>
                <input type="text" class="form-control" id="nombres_apellidos" name="nombres_apellidos" required>
            </div>
            <div class="mb-3">
                <label for="dni" class="form-label">DNI</label>
                <input type="text" class="form-control" id="dni" name="dni" maxlength="8" required>
            </div>
            <div class="mb-3">
                <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" required>
            </div>
            <div class="mb-3">
                <label for="telefono" class="form-label">Teléfono</label>
                <input type="text" class="form-control" id="telefono" name="telefono" maxlength="9" required>
            </div>
            <div class="mb-3">
                <label for="estado" class="form-label">Estado</label>
                <select class="form-select" id="estado" name="estado" required>
                    <option value="">Seleccionar</option>
                    <option value="activo">Activo</option>
                    <option value="inactivo">Inactivo</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary w-100">Registrar</button>
            <a href="jugadores.php" class="btn btn-secondary w-100 mt-2">Volver</a>
        </form>
    </div>
</body>
</html>
