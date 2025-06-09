<?php
session_start();
require_once '../../includes/db_config.php';

// Verificar acceso
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'gestionador') {
    header('Location: ../../auth/login.php');
    exit;
}

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    $_SESSION['error'] = 'ID inválido.';
    header('Location: mensualidades.php');
    exit;
}

try {
    // Comprobar si existe
    $stmt = $pdo->prepare("SELECT id FROM mensualidades WHERE id = ?");
    $stmt->execute([$id]);
    $existe = $stmt->fetch();

    if (!$existe) {
        $_SESSION['error'] = 'Mensualidad no encontrada.';
    } else {
        $delete = $pdo->prepare("DELETE FROM mensualidades WHERE id = ?");
        $delete->execute([$id]);
        $_SESSION['success'] = 'Mensualidad eliminada correctamente.';
    }
} catch (PDOException $e) {
    $_SESSION['error'] = 'Error al eliminar la mensualidad.';
}

header('Location: mensualidades.php');
exit;
