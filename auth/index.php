<?php
session_start(); // Iniciar sesión para mantener el estado del usuario

// Verificar si el empleado no está autenticado
if (!isset($_SESSION['empleado_id'])) {
    // Si el empleado no está autenticado, redirigirlo a la página de inicio de sesión
    header("Location: ../login.php");
    exit();
}

$nombre_empleado = $_SESSION['empleado_nombre'];
date_default_timezone_set('America/Mexico_City');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comedor</title>
    <link rel="stylesheet" href="2.css">
    <style>
        /* Estilos generales */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        #imagenPrincipal {
            max-width: 100%;
            height: auto;
        }

        #container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }

        .platillo {
            width: 200px;
            margin: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .platillo img {
            width: 100%;
            height: auto;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }

        .platillo h2 {
            font-size: 18px;
            margin: 10px 0;
            text-align: center;
        }

        .platillo p {
            font-size: 14px;
            margin: 0 10px 10px;
        }

        #mensaje-reserva {
            background-color: #ffe6e6;
            border: 1px solid #ff9999;
            color: #ff3333;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 20px;
            max-width: 400px;
            margin: 20px auto;
        }

        #mensaje-reserva p {
            margin: 5px 0;
        }

        #ultima-reserva {
            background-color: #e6f7ff;
            border: 1px solid #99ccff;
            color: #007bff;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 20px;
            max-width: 400px;
            margin: 20px auto;
        }

        #ultima-reserva p {
            margin: 5px 0;
        }

        #nombre-empleado {
            text-align: center;
            font-size: 20px;
            margin: 20px 0;
        }

        #cerrar-sesion {
            display: block;
            text-align: center;
            margin-bottom: 20px;
        }

        #qr-code {
            max-width: 200px;
            margin: 0 auto;
        }
    </style>
</head>
<body>

<img id="imagenPrincipal" src="../logo.jpg" alt="Imagen Principal">

<h1 id="menu">Menú</h1>
<h2 id="menu">Elija su comida del día de hoy <?php echo date("d-m-Y") ?>.</h2>
<p id="nombre-empleado">Bienvenid@, <?php echo $nombre_empleado; ?>.</p>
<a href="cerrar.php" id="cerrar-sesion">Cerrar Sesión</a>

<div id="container">
    <?php
    // Conexión a la base de datos MySQL utilizando PDO
    $servername = "localhost";
    $username = "generous-library-moj";
    $password = "i5X45G)M2A-o+p3Fch";
    $database = "generous_library_moj_db";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$database;charset=utf8mb4", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Obtener la fecha actual de PHP
        $fecha_actual_php = date("Y-m-d");

        // Modificar el query para utilizar la fecha actual de PHP en lugar de CURDATE() de MySQL
        $query_check_reservation = "SELECT * FROM transaccion 
                                    WHERE IdEmpleado = ? AND FechaReserva = ?";
        $stmt_check_reservation = $conn->prepare($query_check_reservation);
        $stmt_check_reservation->execute([$_SESSION['empleado_id'], $fecha_actual_php]);

        // Verificar si el empleado ya ha realizado una reserva para algún menú en la misma fecha
        if ($stmt_check_reservation->rowCount() > 0) {
            echo "<div id='mensaje-reserva'>";
            echo "<p>Ya has realizado una reserva para hoy. No puedes reservar más platillos.</p>";

            // Mostrar los datos de la última reserva realizada por el empleado
            $query_last_reservation = "SELECT * FROM transaccion 
                                       WHERE IdEmpleado = ? 
                                       ORDER BY FechaReserva DESC 
                                       LIMIT 1";
            $stmt_last_reservation = $conn->prepare($query_last_reservation);
            $stmt_last_reservation->execute([$_SESSION['empleado_id']]);
            $last_reservation = $stmt_last_reservation->fetch(PDO::FETCH_ASSOC);

            if ($last_reservation) {
                echo "<div id='ultima-reserva'>";
                echo "<p>Última reserva realizada:</p>";
                echo "<p>ID del empleado: " . $last_reservation['IdEmpleado'] . "</p>";
                echo "<p>Nombre del empleado: " . $nombre_empleado . "</p>";
                echo "<p>Codigo de comida: " . $last_reservation['NumSerie'] . "</p>";
                echo "<p>Fecha de reserva: " . $last_reservation['FechaReserva'] . "</p>";
                echo "<p>Platillo reservado: " . $last_reservation['NombrePlatillo'] . "</p>";
                echo "<img id='qr-code' src='{$last_reservation['CodigoQR']}' alt='Código QR'>";
                echo "</div>";
            }

            echo "</div>";
        } else {
            // Consulta SQL para obtener los platillos que no han sido reservados por el empleado
            $query = "SELECT * FROM menu 
                      WHERE id NOT IN (SELECT id FROM transaccion WHERE IdEmpleado = ? AND Reservado = 1)";

            // Excluir el platillo más caro
            // $query .= " AND id != (SELECT id FROM menu ORDER BY Precio DESC LIMIT 1)";

            $stmt = $conn->prepare($query);
            $stmt->execute([$_SESSION['empleado_id']]);

            // Generar la estructura HTML para cada platillo
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<div class='platillo'>";
                echo "<a href='pagina_platillo.php?id={$row['id']}'>";
                echo "<img src='{$row['Imagen']}' alt='{$row['NombreMenu']}'>";
                echo "</a>";
                echo "<h2>{$row['NombreMenu']}</h2>";
                echo "<p>{$row['Descripcion']}</p>";
                echo "</div>";
            }
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
    ?>
</div>

</body>
</html>
