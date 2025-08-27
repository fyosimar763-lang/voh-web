<?php
session_start();

// Si no hay datos de comprobante en la sesión, redirige de vuelta al formulario.
if (!isset($_SESSION['comprobante'])) {
    header("Location: index.php");
    exit();
}

// Carga los datos guardados en la sesión.
$datos = $_SESSION['comprobante'];

// Opcional: Elimina los datos de la sesión para que no se puedan volver a ver al recargar.
unset($_SESSION['comprobante']);

// Convierte el ID de candidato a un nombre legible.
$candidato_nombre = '';
switch ($datos['candidato']) {
    case '1':
        $candidato_nombre = 'Nasry Asfura';
        break;
    case '2':
        $candidato_nombre = 'Salvador Nasralla';
        break;
    case '3':
        $candidato_nombre = 'Rixi Moncada';
        break;
    case '4':
        $candidato_nombre = 'Mario Rivera';
        break;
    case '6':
        $candidato_nombre = 'Jorge Ávila';
        break;
    case '7':
        $candidato_nombre = 'Sin Candidato Asignado'; // Para el caso especial que me enviaste
        break;
    default:
        $candidato_nombre = 'Candidato Desconocido';
        break;
}

?>

<!DOCTYPE html>
<html lang="es">
<head> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante Oficial de Votación</title>
    <link rel="stylesheet" href="estilo.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #e9ecef;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            text-align: center;
        }
        .comprobante-card {
            background-color: #fff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            width: 90%;
            max-width: 500px;
            text-align: left;
            position: relative;
        }
        .comprobante-card h1 {
            color: #007bff;
            text-align: center;
            margin-bottom: 20px;
            font-size: 1.8em;
            border-bottom: 2px solid #17a2b8;
            padding-bottom: 10px;
        }
        .comprobante-card p {
            margin: 8px 0;
            color: #343a40;
            font-size: 1em;
        }
        .comprobante-card strong {
            color: #495057;
        }
        .comprobante-info {
            border: 1px dashed #ced4da;
            padding: 20px;
            border-radius: 8px;
            background-color: #f8f9fa;
        }
        .acciones {
            margin-top: 30px;
            text-align: center;
        }
        .acciones button, .acciones a {
            padding: 12px 25px;
            margin: 8px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 1em;
            transition: background-color 0.3s ease;
        }
        #imprimirBtn {
            background-color: #28a745;
            color: #fff;
        }
        #imprimirBtn:hover {
            background-color: #218838;
        }
        #volverBtn {
            background-color: #6c757d;
            color: #fff;
        }
        #volverBtn:hover {
            background-color: #5a6268;
        }
        .header-logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .header-logo img {
            max-width: 80px;
            opacity: 0.85;
        }
        @media print {
            body {
                background-color: #fff;
            }
            .comprobante-card {
                box-shadow: none;
                border: 1px solid #000;
            }
            .acciones, .container a {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="comprobante-card">
        <div class="header-logo">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/1/18/Bandera_de_Honduras.svg/1200px-Bandera_de_Honduras.svg.png" alt="Bandera de Honduras">
        </div>
        <h1>Comprobante Oficial de Votación</h1>
        <div class="comprobante-info">
            <p><strong>Número de Urna:</strong> <?php echo htmlspecialchars($datos['urna_aleatoria']); ?></p>
            <p><strong>Fecha y Hora:</strong> <?php echo htmlspecialchars($datos['fecha_voto']); ?></p>
            <hr style="margin: 15px 0;">
            <p><strong>Nombre Completo:</strong> <?php echo htmlspecialchars($datos['nombre'] . ' ' . $datos['apellido']); ?></p>
            <p><strong>DNI:</strong> <?php echo htmlspecialchars($datos['dni']); ?></p>
            <p><strong>Departamento:</strong> <?php echo htmlspecialchars($datos['departamento']); ?></p>
            <p><strong>Candidato Seleccionado:</strong> <?php echo htmlspecialchars($candidato_nombre); ?></p>
        </div>
        <div class="acciones">
            <button id="imprimirBtn">🖨️ Imprimir Comprobante</button>
            <a href="index.php" id="volverBtn">🏠 Volver a Votar</a>
        </div>
    </div>
    
    <script>
        document.getElementById('imprimirBtn').addEventListener('click', function() {
            window.print();
        });
        
    </script>
</body>
</html>