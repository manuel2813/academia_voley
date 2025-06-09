<?php
session_start();
require_once '../includes/db_config.php';

// Verificar si es administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

// Verificar que se haya recibido un ID válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: users.php');
    exit;
}

$id = intval($_GET['id']);

// Evitar que se elimine a sí mismo
if ($_SESSION['usuario_id'] == $id) {
    $_SESSION['error'] = "No puedes eliminar tu propio usuario.";
    header('Location: users.php');
    exit;
}

try {
    // Verificar si existe el usuario
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    $usuario = $stmt->fetch();

    if ($usuario) {
        // Eliminar
        $delete = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $delete->execute([$id]);
        $_SESSION['success'] = "Usuario eliminado correctamente.";
    } else {
        $_SESSION['error'] = "Usuario no encontrado.";
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Error al eliminar usuario.";
}

// Redirigir
header('Location: users.php');
exit;
