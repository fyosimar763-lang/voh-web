<?php
// Establecer el encabezado para que la respuesta sea JSON
header('Content-Type: application/json');

// Incluir el archivo de conexión a la base de datos
// Asegúrate de que 'db.php' es el nombre correcto de tu archivo de conexión.
include 'db.php'; 

// Crear un array de respuesta. Por defecto, 'exists' es falso.
$response = ['exists' => false];

// Verificar si se ha enviado un DNI a través de POST
if (isset($_POST['dni'])) {
    $dni = $_POST['dni'];

    // Preparar una consulta segura para verificar el DNI
    $stmt = $conn->prepare("SELECT COUNT(*) FROM votantes WHERE dni = ?");
    $stmt->bind_param("s", $dni);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    // Si el conteo es mayor que 0, significa que el DNI ya existe en la base de datos
    if ($count > 0) {
        $response['exists'] = true;
    }
}

// Devolver la respuesta en formato JSON
echo json_encode($response);

// Cerrar la conexión a la base de datos
$conn->close();
?>