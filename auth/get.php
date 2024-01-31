<?php
session_start(); // Iniciar sesión para mantener el estado del usuario

// Verificar si el empleado está autenticado
if (!isset($_SESSION['empleado_id'])) {
    // Si el empleado no está autenticado, redirigirlo a la página de inicio de sesión
    header("Location: ../login.php");
    exit();
}

// Obtener el nombre y ID del empleado de la sesión
$nombre_empleado = $_SESSION['empleado_nombre'];
$id_empleado = $_SESSION['empleado_id'];

// Información del platillo (puedes obtenerla de tu base de datos)
$nombre_platillo = "Nombre del Platillo";

// Generar el texto del código QR con la información del empleado y del platillo
$texto_qr = "ID Empleado: $id_empleado\nNombre Empleado: $nombre_empleado\nPlatillo: $nombre_platillo";

// Generar el código QR
require_once "../phpqrcode/qrlib.php";
$archivo_qr = "codigo_qr.png";
QRcode::png($texto_qr, $archivo_qr);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Éxito de Reserva</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            text-align: center;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #333;
        }
        p {
            color: #666;
        }
        img {
            margin-top: 20px;
            max-width: 100%;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>¡Reserva Exitosa!</h1>
        <p>¡Hola, <?php echo $nombre_empleado; ?>! Tu reserva de hoy se ha realizado con éxito.</p>
        <img src="<?php echo $archivo_qr; ?>" alt="Código QR">
    </div>
    <a href="index.php">Regresar al Menú.</a>
</body>
</html>
