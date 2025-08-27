<?php
session_start();
// Incluir el archivo de conexión a la base de datos
include 'db.php'; 

// Variable para mensajes de feedback
$mensaje_feedback = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dni = $_POST['dni'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($dni) || empty($password)) {
        $mensaje_feedback = "<h3 style='color:red; text-align:center;'>❌ Por favor, ingrese DNI y contraseña.</h3>";
    } else {
        // Preparar la consulta para buscar el votante por DNI
        $stmt = $conn->prepare("SELECT dni, password_hash, ha_votado FROM votantes WHERE dni = ?");
        $stmt->bind_param("s", $dni);
        $stmt->execute();
        $stmt->store_result();
        
        // Si el DNI existe
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($db_dni, $db_password_hash, $db_ha_votado);
            $stmt->fetch();

            // Verificar la contraseña con el hash guardado
            if (password_verify($password, $db_password_hash)) {
                $mensaje_feedback = "<h3 style='color:green; text-align:center;'>✅ ¡Inicio de sesión exitoso!</h3>";
                
                // Aquí podrías redirigir al usuario a una página de bienvenida
                // header("Location: bienvenida.php");
                // exit();
                
            } else {
                $mensaje_feedback = "<h3 style='color:red; text-align:center;'>❌ Contraseña incorrecta.</h3>";
            }
        } else {
            $mensaje_feedback = "<h3 style='color:red; text-align:center;'>❌ DNI no registrado.</h3>";
        }
        $stmt->close();
    }
}
$conn->close();
?>