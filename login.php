<?php
session_start(); // Iniciar sesión para mantener el estado del usuario

// Verificar si el empleado ya está autenticado
if (isset($_SESSION['empleado_id'])) {
    // Si el empleado ya está autenticado, redirigirlo a la página de inicio
    header("Location: auth/index.php");
    exit();
}

// Verificar si se enviaron los datos del formulario de inicio de sesión
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Datos de conexión a la base de datos
    $servername = "DESKTOP-M0T0SDR\SQLEXPRESS";
    $username = "sa";
    $password = "Espana3";
    $database = "SYM_Comedor";

    // Obtener los datos del formulario de inicio de sesión
    $usuario = $_POST['usuario'];
    $contraseña = $_POST['contraseña'];

    try {
        // Conexión a la base de datos
        $conn = new PDO("sqlsrv:Server=$servername;Database=$database", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Consulta SQL para verificar las credenciales del empleado
        $query = "SELECT ID, Nombre FROM Empleados WHERE Usuario = ? AND Contraseña = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$usuario, $contraseña]);
        $empleado = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($empleado) {
            // Si las credenciales son válidas, iniciar sesión y redirigir al usuario a la página de inicio
            $_SESSION['empleado_id'] = $empleado['ID'];
            $_SESSION['empleado_nombre'] = $empleado['Nombre'];
            header("Location: auth/index.php");
            exit();
        } else {
            // Si las credenciales no son válidas, mostrar un mensaje de error
            $error_message = "Credenciales incorrectas. Por favor, inténtalo de nuevo.";
        }
    } catch (PDOException $e) {
        // Si ocurre un error de base de datos, mostrar un mensaje de error
        $error_message = "Error de base de datos: " . $e->getMessage();
    }

    $conn = null;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f3f3f3;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 400px;
            margin: 100px auto;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            box-sizing: border-box;
        }

        h2 {
            text-align: center;
            margin-top: 0;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            font-weight: bold;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="password"] {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        input[type="submit"] {
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo img {
            max-width: 100%;
            height: auto;
        }

        .error-message {
            color: #ff0000;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="logo.jpg" alt="Logo">
        </div>
        <h2>Iniciar Sesión</h2>
        <?php
        // Mostrar mensaje de error si existe
        if (isset($error_message)) {
            echo '<p class="error-message">' . $error_message . '</p>';
        }
        ?>
        <form method="post">
            <label for="usuario">Usuario:</label>
            <input type="text" id="usuario" name="usuario">
            <label for="contraseña">Contraseña:</label>
            <input type="password" id="contraseña" name="contraseña">
            <input type="submit" value="Iniciar Sesión">
        </form>
    </div>
</body>
</html>
