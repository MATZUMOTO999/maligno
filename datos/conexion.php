<?php
// conexion.php
$host = "localhost";
$db   = "tu_base_de_datos";
$user = "postgres";
$pass = "tu_password";

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>