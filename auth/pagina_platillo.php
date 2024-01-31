<?php
session_start(); // Iniciar sesión para mantener el estado del usuario

// Verificar si el empleado no está autenticado
if (!isset($_SESSION['empleado_id'])) {
    // Si el empleado no está autenticado, redirigirlo a la página de inicio de sesión
    header("Location: ../login.php");
    exit();
}

// Verificar si se ha enviado una solicitud POST para reservar el platillo
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['platillo_id'])) {
    // Obtener el ID del platillo de la solicitud POST
    $platillo_id = $_POST['platillo_id'];

    // Realizar la conexión a la base de datos
    $servername = "DESKTOP-M0T0SDR\SQLEXPRESS";
    $username = "sa";
    $password = "Espana3";
    $database = "SYM_Comedor";

    try {
        $conn = new PDO("sqlsrv:Server=$servername;Database=$database", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Insertar una nueva fila en la tabla de transacciones
        $query = "INSERT INTO Transaccion (IdEmpleado, IdMenu, NombrePlatillo, Reservado) VALUES (?, ?, ?, 1)";
        $stmt = $conn->prepare($query);
        $stmt->execute([$_SESSION['empleado_id'], $platillo_id, $_POST['platillo_nombre']]);

        // Redirigir a la página de confirmación de reserva
        header("Location: get.php?exito=1");
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
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
    $servername = "DESKTOP-M0T0SDR\SQLEXPRESS";
    $username = "sa";
    $password = "Espana3";
    $database = "SYM_Comedor";

    try {
        $conn = new PDO("sqlsrv:Server=$servername;Database=$database", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $query = "SELECT * FROM Menu WHERE id = ?";
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
