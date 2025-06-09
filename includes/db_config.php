<?php
// includes/db_config.php

$host = '127.0.0.1:3307';        // o 127.0.0.1
$db   = 'pasis';
$user = 'root';             // por defecto en XAMPP
$pass = '';                 // contraseña vacía en XAMPP
$charset = 'utf8mb4';

// DSN = Data Source Name
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Manejo de errores
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch como arreglo asociativo
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Usar prepared statements reales
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // Puedes probar conexión temporalmente con:
    // echo "Conexión exitosa.";
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
