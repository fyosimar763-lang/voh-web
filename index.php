<?php
session_start();
// Incluir el archivo de conexión a la base de datos
include 'db.php';

// Variable para almacenar y mostrar mensajes de éxito o error al usuario
$mensaje_feedback = '';

// Verificar si la solicitud es un POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger y sanear los datos del formulario
    $nombre = $_POST['nombre'] ?? '';
    $apellido = $_POST['apellido'] ?? '';
    $dni = $_POST['dni'] ?? '';
    $password = $_POST['password'] ?? '';
    $nacimiento = $_POST['nacimiento'] ?? '';
    $departamento = $_POST['departamento'] ?? '';
    $genero = $_POST['genero'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $id_candidato = $_POST['candidato'] ?? '';
    $imagen_rostro_base64 = $_POST['imagen_rostro'] ?? '';

    // Decodificar la imagen de Base64 y guardarla
    if (!empty($imagen_rostro_base64)) {
        // Remover el prefijo de datos de la imagen
        $imagen_rostro_data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imagen_rostro_base64));

        // Generar un nombre único para el archivo
        $nombre_archivo_rostro = uniqid('rostro_', true) . '.png';
        $ruta_guardar_rostro = 'uploads/rostros/' . $nombre_archivo_rostro;

        // Guardar la imagen en el servidor
        file_put_contents($ruta_guardar_rostro, $imagen_rostro_data);

        // Asignar la ruta de la imagen a la variable que se guardará en la base de datos
        $imagen_rostro = $ruta_guardar_rostro;
    } else {
        $imagen_rostro = null;
    }

    // Verificar si algún campo obligatorio está vacío
    if (empty($nombre) || empty($apellido) || empty($dni) || empty($password) || empty($id_candidato)) {
        $mensaje_feedback = "<h3 style='color:red; text-align:center;'>❌ Faltan campos obligatorios.</h3>";
    } else {
        // Preparar la consulta para verificar si el DNI ya está registrado
        $verificar = $conn->prepare("SELECT id FROM votantes WHERE dni = ?");
        $verificar->bind_param("s", $dni);
        $verificar->execute();
        $verificar->store_result();

        if ($verificar->num_rows > 0) {
            $mensaje_feedback = "<h3 style='color:red; text-align:center;'>❌ Este número de identidad ya está registrado.</h3>";
        } else {
            // Hash de la contraseña para seguridad antes de guardarla en la base de datos
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $ha_votado = 1; // 1 significa que ya ha votado

            // Preparar la consulta SQL para insertar los datos del votante y su voto
            $insert = "INSERT INTO votantes (nombre, apellido, dni, password_hash, ha_votado, imagen_rostro, nacimiento, departamento, genero, correo, id_candidato) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert);

            // Enlazar los parámetros a la consulta SQL
            $stmt->bind_param("ssssisssssi", $nombre, $apellido, $dni, $password_hash, $ha_votado, $imagen_rostro, $nacimiento, $departamento, $genero, $correo, $id_candidato);

            // Ejecutar la consulta
            if ($stmt->execute()) {
                // Aquí se guardan los datos del votante en una variable de sesión
                $_SESSION['voto_datos'] = [
                    'nombre' => $nombre,
                    'apellido' => $apellido,
                    'dni' => $dni,
                    'departamento' => $departamento,
                    'genero' => $genero,
                    'correo' => $correo,
                    'candidato' => $id_candidato
                ];

                // Y luego se redirige a la página de éxito
                header("Location: exito.php");
                exit();
            } else {
                // El código para cuando hay un error al guardar en la base de datos
                $mensaje_feedback = "<h3 style='color:red; text-align:center;'>❌ Error al guardar el votante: " . $stmt->error . "</h3>";
            }
        }
    }
}
// Cerrar la conexión a la base de datos al finalizar
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Votación Ciudadana</title>
    <link rel="stylesheet" href="estilos.css">
    <style>
        body {
            font-family: sans-serif;
            background: #f4f4f4;
            padding: 20px;
        }
        .contenedor {
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        label {
            font-weight: bold;
            margin-top: 10px;
            display: block;
        }
        input, select, button {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button[type="submit"] {
            background-color: #007bff;
            color: white;
            border: none;
            font-weight: bold;
            cursor: pointer;
        }
        video, canvas {
            display: block;
            margin: 10px auto;
            max-width: 100%;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        /* Estilos para la boleta */
        .boleta {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }
        .tarjeta {
            flex: 1 1 45%;
            border: 2px solid #007bff;
            border-radius: 8px;
            padding: 15px;
            background: #e9f5ff;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        .tarjeta:hover {
            transform: scale(1.02);
        }
        .tarjeta input {
            display: none;
        }
        .tarjeta .contenido {
            text-align: center;
        }
        .tarjeta img {
            width: 60px;
            margin-bottom: 10px;
        }
        .tarjeta h3 {
            margin: 5px 0;
            color: #007bff;
        }
        .tarjeta p {
            margin: 0;
            font-size: 14px;
            color: #333;
        }
        .tarjeta input:checked + .contenido {
            background-color: #cce5ff;
            border-radius: 6px;
        }
    </style>
</head>
<body>
    <div class="contenedor">
        <h1>Registro de Votación</h1>
        <!-- Mostrar el mensaje de feedback si existe -->
        <?php echo $mensaje_feedback; ?>
        
        <!-- El formulario envía los datos a este mismo archivo PHP -->
        <!-- Se ha eliminado el 'enctype="multipart/form-data"' ya que no se gestiona ninguna subida de archivo -->
        <form action="index.php" method="POST" id="formularioVoto">
            <label>Nombre:</label>
            <input type="text" name="nombre" required>

            <label>Apellido:</label>
            <input type="text" name="apellido" required>

           <input type="text" id="dni" name="dni" required placeholder="Número de Identidad">
<div id="mensaje-dni"></div>

            <label>Contraseña:</label>
            <input type="password" name="password" required>

            <label>Fecha de nacimiento:</label>
            <input type="date" name="nacimiento" required>

            <label>Departamento:</label>
            <select name="departamento" required>
                <option value="">Selecciona tu departamento</option>
                <option value="Atlántida">Atlántida</option>
                <option value="Choluteca">Choluteca</option>
                <option value="Colón">Colón</option>
                <option value="Comayagua">Comayagua</option>
                <option value="Copán">Copán</option>
                <option value="Cortés">Cortés</option>
                <option value="El Paraíso">El Paraíso</option>
                <option value="Francisco Morazán">Francisco Morazán</option>
                <option value="Gracias a Dios">Gracias a Dios</option>
                <option value="Intibucá">Intibucá</option>
                <option value="Islas de la Bahía">Islas de la Bahía</option>
                <option value="La Paz">La Paz</option>
                <option value="Lempira">Lempira</option>
                <option value="Ocotepeque">Ocotepeque</option>
                <option value="Olancho">Olancho</option>
                <option value="Santa Bárbara">Santa Bárbara</option>
                <option value="Valle">Valle</option>
                <option value="Yoro">Yoro</option>
            </select>

            <label>Género:</label>
            <select name="genero" required>
                <option value="">Selecciona tu género</option>
                <option value="Masculino">Masculino</option>
                <option value="Femenino">Femenino</option>
                <option value="Otro">Otro</option>
            </select>

            <label>Correo electrónico:</label>
            <input type="email" name="correo" required>

            <label>Selecciona tu candidato:</label>
            <div class="boleta">
                <label class="tarjeta">
                    <input type="radio" name="candidato" value="1" required>
                    <div class="contenido">
                        <!-- Las URLs de las imágenes son externas, se recomienda guardarlas localmente para evitar que se rompan -->
                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/7/7e/National_Party_of_Honduras_Logo.svg/2560px-National_Party_of_Honduras_Logo.svg.png" alt="Bandera Partido Nacional">
                        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT_9lOEAB1vecr8Wj-mcG71glYw-r44eNEK-A&s" alt="Foto Candidato 1" style="width:80px; border-radius:50%; margin-top:10px;">
                        <h3>Nasry Asfura</h3>
                        <p>Partido Nacional</p>
                    </div>
                </label>

                <label class="tarjeta">
                    <input type="radio" name="candidato" value="2" required>
                    <div class="contenido">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/3/31/Liberal_Party_of_Honduras_flag.svg/600px-Liberal_Party_of_Honduras_flag.svg.png" alt="Bandera Partido Liberal">
                        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT4e_-gL9H02qzs1iMqCf0h-t1nEtWikXNJ6g&s" alt="Foto Candidato 2" style="width:80px; border-radius:50%; margin-top:10px;">
                        <h3>Salvador Nasralla</h3>
                        <p>Partido Liberal</p>
                    </div>
                </label>

                <label class="tarjeta">
                    <input type="radio" name="candidato" value="3" required>
                    <div class="contenido">
                        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTOYhKFN2Ro_SMwG_6tVMI4KjsBxM-HJG4M_g&s" alt="Bandera Partido Libre">
                        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT5Jwhxf5C2CID5gNEe2YGQUkZGY08T7vhYpg&s" alt="Foto Candidato 3" style="width:80px; border-radius:50%; margin-top:10px;">
                        <h3>Rixi Moncada</h3>
                        <p>Partido Libre</p>
                    </div>
                </label>

                <label class="tarjeta">
                    <input type="radio" name="candidato" value="4" required>
                    <div class="contenido">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/af/Christian_Democratic_Party_of_Honduras_logo.svg/640px-Christian_Democratic_Party_of_Honduras_logo.svg.png" alt="Bandera Democracia Cristiana">
                        <img src="https://www.elheraldo.hn/binrepository/900x900/0c0/0d0/none/45933/AYPU/fp-chano-221117_EH1131411_MG117510669.jpg" alt="Foto Candidato 4" style="width:80px; border-radius:50%; margin-top:10px;">
                        <h3>Mario Rivera</h3>
                        <p>Democracia Cristiana</p>
                    </div>
                </label>

                <label class="tarjeta">
                    <input type="radio" name="candidato" value="6" required>
                    <div class="contenido">
                        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSz-NU5m1EkBb_Fxzek3UVnxBKrSdmSrsht3A&s" alt="Bandera PINU">
                        <img src="https://proceso.hn/wp-content/uploads/2021/02/Nelson-Avila.jpg" alt="Foto Candidato 6" style="width:80px; border-radius:50%; margin-top:10px;">
                        <h3>Jorge Ávila</h3>
                        <p>PINU</p>
                    </div>
                </label>

  <label class="tarjeta">
    <input type="radio" name="candidato" value="7" required>
    <div class="contenido">
      <img src="data:image/png;base64,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" alt="Bandera Partido Vamos">
      <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTjiQnfLGsvov921H_xwh46CRoaIK6JB08kyA&s" alt="Foto Candidato 7" style="width:80px; border-radius:50%; margin-top:10px;">
      <h3>José Coto </h3>
      <p>Partido Vamos</p>
    </div>
  </label>


  <label class="tarjeta">
    <input type="radio" name="candidato" value="9" required>
    <div class="contenido">
      <img src="data:image/png;base64,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" alt="Bandera Salvador de Honduras">
      <img src="https://cdn-icons-png.flaticon.com/512/660/660611.png" alt="Foto Candidato 9" style="width:80px; border-radius:50%; margin-top:10px;">
      <h3>sin candidato</h3>
      <p>Salvador de Honduras</p>
    </div>
  </label>

  <label class="tarjeta">
    <input type="radio" name="candidato" value="10" required>
    <div class="contenido">
      <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/a6/Logo-Todos_Somos_Honduras.svg/1200px-Logo-Todos_Somos_Honduras.svg.png" alt="Bandera Todos Somos Honduras">
      <img src="https://lh5.googleusercontent.com/proxy/E8Us-rVco835JB_OYGotPfhb6AUTvxEwEmOD65pUPQGmGTmiquBk_BeZFctp9EAziX2G91l_2mIhYCl_4N7rM_us41xhSwyCG2L0is7uF2dQ9NTnd3gBxemY-vlR8H4GsEsuIMU" alt="Foto Candidato 10" style="width:80px; border-radius:50%; margin-top:10px;">
      <h3>Marlon Escoto </h3>
      <p>Todos Somos Honduras</p>
    </div>
  </label>

  <label class="tarjeta">
    <input type="radio" name="candidato" value="11" required>
    <div class="contenido">
      <img src="data:image/png;base64,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" alt="Bandera Unificación Democrática">
      <img src="https://www.laprensa.hn/binrepository/1200x600/0c0/0d0/none/11004/QNJB/imagen-9_384385_20211102120621.jpg" alt="Foto Candidato 11" style="width:80px; border-radius:50%; margin-top:10px;">
      <h3>José  Díaz </h3>
      <p>Unificación Democrática</p>
    </div>
  </label>

  <label class="tarjeta">
    <input type="radio" name="candidato" value="12" required>
    <div class="contenido">
      <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/3/3f/Frente_Amplio_%28Hn%29.svg/800px-Frente_Amplio_%28Hn%29.svg.png" alt="Bandera Frente Amplio">
      <img src="https://www.laprensa.hn/binrepository/310x155/3c0/303d155/none/11004/PKBC/2_382961_20211101214733.jpg" alt="Foto Candidato 12" style="width:80px; border-radius:50%; margin-top:10px;">
      <h3>Kelin Pérez </h3>
      <p>Frente Amplio</p>
    </div>
  </label>

  <label class="tarjeta">
    <input type="radio" name="candidato" value="13" required>
    <div class="contenido">
      <img src="data:image/png;base64,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" alt="Bandera Centro Social">
      <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcScZ5uyYq47lfljG5QCBHz8GkgW5lE6QNbfCQ&s" alt="Foto Candidato 13" style="width:80px; border-radius:50%; margin-top:10px;">
      <h3>Romeo Vásquez</h3>
      <p>Alianza Patriótica</p>
    </div>
  </label>
</div>



<label>Documento de identidad (PDF, JPG, PNG):</label>
<input type="file" name="documento_identidad" accept=".pdf,.jpg,.png" required>

<label>Captura facial:</label>
<video id="video" autoplay></video>
<canvas id="canvas" style="display:none;"></canvas>
<input type="hidden" name="imagen_rostro" id="imagen-rostro">
<button type="button" id="capturar-btn">Capturar Rostro</button>
<button type="submit">Emitir Voto</button>
</form>
<script>
    // Tu código JavaScript para manejar la cámara, el lienzo y el formulario
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const capturarBtn = document.getElementById('capturar-btn');
    const imagenInput = document.getElementById('imagen-rostro');

    // Acceder a la cámara
    navigator.mediaDevices.getUserMedia({ video: true })
        .then(stream => {
            video.srcObject = stream;
        })
        .catch(err => {
            console.error("Error al acceder a la cámara: " + err);
            alert("No se pudo acceder a la cámara. Asegúrate de dar los permisos necesarios.");
        });

    // Función para capturar la imagen
    function capturar() {
        canvas.style.display = 'block';
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);
        const dataUrl = canvas.toDataURL('image/png');
        imagenInput.value = dataUrl;
        alert("¡Imagen capturada con éxito!");
    }

    // Evento del botón de captura
    capturarBtn.addEventListener('click', function(event) {
        event.preventDefault(); // Evita que el formulario se envíe
        capturar();
    });

    // --- LÓGICA DEL BOTÓN DE "EMITIR VOTO" Y VALIDACIÓN ---

    const formulario = document.getElementById('formularioVoto');

    formulario.addEventListener('submit', function(event) {
        let candidatoSeleccionado = document.querySelector('input[name="candidato"]:checked');
        let imagenCapturada = document.getElementById('imagen-rostro').value;
        
        // Valida que se haya seleccionado un candidato
        if (!candidatoSeleccionado) {
            alert('Por favor, selecciona un candidato antes de votar.');
            event.preventDefault(); // Detiene el envío del formulario
            return;
        }
        
        // Valida que se haya capturado una imagen
        if (!imagenCapturada) {
            alert('Por favor, captura tu rostro antes de votar.');
            event.preventDefault(); // Detiene el envío del formulario
            return;
        }
    });
</script>