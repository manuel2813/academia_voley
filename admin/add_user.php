<?php
session_start();
require_once '../includes/db_config.php';

// Verificación de rol admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombres = trim($_POST['nombres_completos'] ?? '');
    $dni = trim($_POST['dni'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';
    $rol = $_POST['rol'] ?? '';

    // Validaciones básicas
    if (empty($nombres) || empty($dni) || empty($contrasena) || empty($rol)) {
        $error = "Todos los campos son obligatorios.";
    } elseif (!preg_match('/^\d{8}$/', $dni)) {
        $error = "El DNI debe tener 8 dígitos numéricos.";
    } elseif (strlen($contrasena) < 1) {
    $error = "La contraseña no puede estar vacía.";

    } elseif (!in_array($rol, ['admin', 'gestionador'])) {
        $error = "Rol inválido.";
    } else {
        // Verificar que el DNI no esté registrado
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE dni = ?");
        $stmt->execute([$dni]);
        if ($stmt->fetch()) {
            $error = "Este DNI ya está registrado.";
        } else {
            // Registrar usuario
            $insert = $pdo->prepare("INSERT INTO usuarios (nombres_completos, dni, contrasena, rol) VALUES (?, ?, ?, ?)");
            $insert->execute([$nombres, $dni, $contrasena, $rol]);
            $success = "Usuario registrado correctamente.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Usuario | Admin</title>
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
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            border-radius: 15px;
        }
    </style>
</head>
<body>
    <div class="card p-4">
        <h3 class="text-center mb-4">Registrar Nuevo Usuario</h3>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="nombres" class="form-label">Nombres Completos</label>
                <input type="text" class="form-control" id="nombres" name="nombres_completos" required>
            </div>
            <div class="mb-3">
                <label for="dni" class="form-label">DNI</label>
                <input type="text" class="form-control" id="dni" name="dni" maxlength="8" required>
            </div>
            <div class="mb-3">
                <label for="contrasena" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="contrasena" name="contrasena" required>
                <small class="text-muted">La contraseña no debe estar vacía</small>
            </div>
            <div class="mb-3">
                <label for="rol" class="form-label">Rol</label>
                <select class="form-select" id="rol" name="rol" required>
                    <option value="">Seleccionar</option>
                    <option value="admin">Administrador</option>
                    <option value="gestionador">Gestionador</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary w-100">Registrar</button>
        </form>
    </div>
</body>
</html>
