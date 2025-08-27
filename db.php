<?php
$host = "localhost";       // o la IP si estás usando HeyDeySQL remotamente
$user = "root";            // tu usuario
$password = "";            // sin contraseña
$database = "votacion"; // reemplaza con el nombre real

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("❌ Error de conexión: " . $conn->connect_error);
}
?>
