<?php
session_start();
require_once '../../includes/db_config.php';

// Verificar rol
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'gestionador') {
    header('Location: ../../auth/login.php');
    exit;
}

// Verificar ID válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID inválido.";
    header('Location: jugadores.php');
    exit;
}

$id = intval($_GET['id']);

try {
    // Verificar si el jugador existe
    $stmt = $pdo->prepare("SELECT * FROM jugadores WHERE id = ?");
    $stmt->execute([$id]);
    $jugador = $stmt->fetch();

    if ($jugador) {
        // Eliminar jugador
        $del = $pdo->prepare("DELETE FROM jugadores WHERE id = ?");
        $del->execute([$id]);
        $_SESSION['success'] = "Jugador eliminado correctamente.";
    } else {
        $_SESSION['error'] = "Jugador no encontrado.";
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Error al intentar eliminar.";
}

// Redirigir
header('Location: jugadores.php');
exit;
