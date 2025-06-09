<?php
session_start();
require_once '../../includes/db_config.php';

// Verificar sesión de gestionador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'gestionador') {
    header('Location: ../../auth/login.php');
    exit;
}

// Verificar ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID inválido.";
    header('Location: paquetes.php');
    exit;
}

$id = intval($_GET['id']);

try {
    // Verificar si existe
    $stmt = $pdo->prepare("SELECT * FROM paquetes WHERE id = ?");
    $stmt->execute([$id]);
    $paquete = $stmt->fetch();

    if ($paquete) {
        $del = $pdo->prepare("DELETE FROM paquetes WHERE id = ?");
        $del->execute([$id]);
        $_SESSION['success'] = "Paquete eliminado correctamente.";
    } else {
        $_SESSION['error'] = "Paquete no encontrado.";
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Error al intentar eliminar el paquete.";
}

header('Location: paquetes.php');
exit;
