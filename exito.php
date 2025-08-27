<?php
session_start();

// Si no hay datos de sesión, redirigimos al usuario al inicio
if (!isset($_SESSION['voto_datos'])) {
    header("Location: index.php");
    exit();
}

$datos = $_SESSION['voto_datos'];
$nombre = htmlspecialchars($datos['nombre']);
$apellido = htmlspecialchars($datos['apellido']);
$dni = htmlspecialchars($datos['dni']);
$departamento = htmlspecialchars($datos['departamento']);
$genero = htmlspecialchars($datos['genero']);
$candidato_id = htmlspecialchars($datos['candidato']);
$fecha_voto = date("d/m/Y");
$hora_voto = date("H:i:s");
$numero_urna = rand(1000, 9999);

// Mapear el ID del candidato al nombre del candidato para el comprobante
$candidatos = [
    1 => 'Nasry Asfura - Partido Nacional',
    2 => 'Salvador Nasralla - Partido Liberal',
    3 => 'Rixi Moncada - Partido Libre',
    4 => 'Mario Rivera - Democracia Cristiana',
    6 => 'Jorge Ávila - PINU',
    7 => 'Nelson Ávila - PINU' // Reemplaza si el nombre del candidato es diferente
];

$nombre_candidato = $candidatos[$candidato_id] ?? 'Candidato Desconocido';

// Opcional: limpiar los datos de la sesión para que no se puedan volver a ver
session_unset();
session_destroy();

?>
<!DOCTYPE html>
<html lang="es">
<head><link rel="stylesheet" href="estilo.css">
    <meta charset="UTF-8">
    <title>Comprobante de Votación</title>
    <link rel="stylesheet" href="estilos.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            color: #333;
        }
        .comprobante-card {
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 500px;
            text-align: center;
            border: 2px solid #007bff;
        }
        h1 {
            color: #007bff;
            font-size: 2.5em;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .icon {
            font-size: 1.5em;
            margin-right: 10px;
        }
        h2 {
            font-size: 1.2em;
            color: #555;
            margin-bottom: 30px;
        }
        .details {
            text-align: left;
            margin-top: 20px;
            line-height: 1.8;
        }
        .detail-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            border-bottom: 1px dashed #e0e0e0;
            padding-bottom: 5px;
        }
        .detail-item:last-child {
            border-bottom: none;
        }
        .detail-item strong {
            color: #007bff;
            width: 150px;
            flex-shrink: 0;
        }
        .detail-item span {
            flex-grow: 1;
        }
        .actions {
            margin-top: 40px;
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 50px;
            font-size: 1em;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .btn-print {
            background-color: #28a745;
            color: white;
        }
        .btn-volver {
            background-color: #6c757d;
            color: white;
        }

        /* Iconos de emoji */
        .emoji {
            font-size: 1.2em;
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <div class="comprobante-card">
        <h1><span class="emoji">✅</span> Votación Exitosa</h1>
        <h2>¡Tu voto ha sido registrado de forma segura!</h2>
        
        <div class="details">
            <div class="detail-item">
                <span class="emoji">👤</span> <strong>Votante:</strong> <span><?php echo $nombre . ' ' . $apellido; ?></span>
            </div>
            <div class="detail-item">
                <span class="emoji">🆔</span> <strong>DNI:</strong> <span><?php echo $dni; ?></span>
            </div>
            <div class="detail-item">
                <span class="emoji">📍</span> <strong>Departamento:</strong> <span><?php echo $departamento; ?></span>
            </div>
            <div class="detail-item">
                <span class="emoji">🗳️</span> <strong>Candidato:</strong> <span><?php echo $nombre_candidato; ?></span>
            </div>
            <div class="detail-item">
                <span class="emoji">📅</span> <strong>Fecha:</strong> <span><?php echo $fecha_voto; ?></span>
            </div>
            <div class="detail-item">
                <span class="emoji">⏰</span> <strong>Hora:</strong> <span><?php echo $hora_voto; ?></span>
            </div>
            <div class="detail-item">
                <span class="emoji">🔢</span> <strong>N° de Urna:</strong> <span><?php echo $numero_urna; ?></span>
            </div>
        </div>

        <div class="actions">
            <button class="btn btn-print" onclick="window.print()">Imprimir Comprobante</button>
            <button class="btn btn-volver" onclick="window.location.href='index.php'">Volver a Votar</button>
        </div>
    </div>
</body>
</html>