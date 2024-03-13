<?php
session_start(); // Iniciar sesión para mantener el estado del usuario

// Verificar si el empleado está autenticado
if (!isset($_SESSION['empleado_id'])) {
    // Si el empleado no está autenticado, redirigirlo a la página de inicio de sesión
    header("Location: ../login.php");
    exit();
}

// Obtener el ID del empleado y la fecha actual
$id_empleado = $_SESSION['empleado_id'];
date_default_timezone_set('America/Mexico_City');
$fecha_actual = date("Y-m-d");

// Generar un ID aleatorio para el empleado
$id_aleatorio = generarIDAleatorio();

// Realizar la conexión a la base de datos
$servername = "localhost";
$username = "generous-library-moj";
$password = "i5X45G)M2A-o+p3Fch";
$database = "generous_library_moj_db";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$database;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Consulta SQL para verificar si el empleado ya ha reservado para algún menú en la misma fecha
    $query_check_reservation = "SELECT * FROM transaccion 
                                WHERE IdEmpleado = ? AND FechaReserva = ?";
    $stmt_check_reservation = $conn->prepare($query_check_reservation);
    $stmt_check_reservation->execute([$id_empleado, $fecha_actual]);

    // Verificar si el empleado ya ha realizado una reserva para algún menú en la misma fecha
    if ($stmt_check_reservation->rowCount() > 0) {
        header("Location: index.php");
        exit();
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Función para generar un ID aleatorio no mayor a 15 caracteres alfanuméricos
function generarIDAleatorio() {
    $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $longitud = 15;
    $id_aleatorio = '';
    for ($i = 0; $i < $longitud; $i++) {
        $id_aleatorio .= $caracteres[rand(0, strlen($caracteres) - 1)];
    }
    return $id_aleatorio;
}

// Verificar si se ha enviado una solicitud POST para reservar el platillo
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['platillo_id'])) {
    // Obtener el ID del platillo de la solicitud POST
    $platillo_id = $_POST['platillo_id'];

    // Obtener la fecha actual
    $fecha_reserva = date("Y-m-d");

    try {
        // Realizar la conexión a la base de datos y comenzar la transacción
        $conn = new PDO("mysql:host=$servername;dbname=$database;charset=utf8mb4", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->beginTransaction();

        // Obtener el ID del empleado y el ID del platillo
        $id_empleado = $_SESSION['empleado_id'];

        // Consultar códigos de serie disponibles en la tabla alm_platillo
        $stmt_get_serial = $conn->prepare("SELECT NumSerie FROM alm_platillos WHERE Status = 'D' AND NumSerie LIKE :prefix LIMIT 1 FOR UPDATE");
        $prefix = $platillo_id . '%';
        $stmt_get_serial->bindParam(':prefix', $prefix, PDO::PARAM_STR);
        $stmt_get_serial->execute();
        $row = $stmt_get_serial->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $num_serie = $row['NumSerie'];
            $fecha_reserva = date("Y-m-d");

            // Actualizar el estado del código de serie a no disponible (Ocupado)
            $stmt_update_status = $conn->prepare("UPDATE alm_platillos SET Status = 'R', IdEmpleado = ?, IdMenu = ? WHERE NumSerie = ?");
            $stmt_update_status->execute([$id_empleado, $platillo_id, $num_serie]);

            // Insertar una nueva fila en la tabla de transacciones
            $query = "INSERT INTO transaccion (id, IdEmpleado, IdMenu, NombrePlatillo, NumSerie, Reservado, FechaReserva) VALUES (?, ?, ?, ?, ?, 1, ?)";
            $stmt = $conn->prepare($query);
            $stmt->execute([$id_aleatorio, $id_empleado, $platillo_id, $_POST['platillo_nombre'], $num_serie, $fecha_reserva]);

            // Confirmar la transacción
            $conn->commit();

            // Redirigir a la página de confirmación de reserva
            header("Location: get.php?exito=1");
            exit();
        } else {
            // No hay códigos de serie disponibles que coincidan con el prefijo del ID del platillo
            header("Location: error.php");
        }
    } catch (PDOException $e) {
        // Revertir la transacción en caso de error
        $conn->rollBack();
        echo "Error: " . $e->getMessage();
    } finally {
        $conn = null;
    }
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Platillo</title>
    <style>
        /* Estilos generales */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .platillo-imagen {
            flex: 1;
            text-align: center;
        }

        .platillo-detalle {
            flex: 1;
            padding: 20px;
            box-sizing: border-box;
        }

        .platillo-detalle h2 {
            margin-top: 0;
        }

        .platillo-detalle p {
            margin-bottom: 20px;
        }

        .reservar-btn {
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .reservar-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="container">
    <?php
    // Obtener el ID del platillo de la URL
    $platillo_id = $_GET['id'];

    // Realizar una consulta a la base de datos para obtener la información del platillo según su ID
    $servername = "localhost";
    $username = "generous-library-moj";
    $password = "i5X45G)M2A-o+p3Fch";
    $database = "generous_library_moj_db";

    try {
        $conn = new PDO("mysql:Server=$servername;Database=$database;charset=utf8mb4", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->exec("USE $database");

        $query = "SELECT * FROM menu WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$platillo_id]);

        $platillo = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($platillo) {
            echo "<div class='platillo-imagen'>";
            echo "<img src='{$platillo['Imagen']}' alt='{$platillo['NombreMenu']}' width='100%'>";
            echo "</div>";
        
            echo "<div class='platillo-detalle'>";
            echo "<h2>{$platillo['NombreMenu']}</h2>";
            echo "<p><strong>Descripción:</strong> {$platillo['Descripcion']}</p>";
            echo "<form method='post'>";
            echo "<input type='hidden' name='platillo_id' value='$platillo_id'>";
            echo "<input type='hidden' name='platillo_nombre' value='{$platillo['NombreMenu']}'>";
            echo "<button class='reservar-btn' type='submit'>Reservar ahora</button>";
            echo "</form>";
            echo "</div>";
        } else {
            echo "<p>Platillo no encontrado.</p>";
        }        
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
    ?>
</div>

</body>
</html>
