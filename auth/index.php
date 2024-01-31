<?php
session_start(); // Iniciar sesión para mantener el estado del usuario

// Verificar si el empleado no está autenticado
if (!isset($_SESSION['empleado_id'])) {
    // Si el empleado no está autenticado, redirigirlo a la página de inicio de sesión
    header("Location: ../login.php");
    exit();
}

$nombre_empleado = $_SESSION['empleado_nombre'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comedor</title>
    <link rel="stylesheet" href="2.css">
</head>
<body>

<img id="imagenPrincipal" src="1630561347310.jpg" alt="Imagen Principal">

<!-- <div id="controles">
    <span class="flecha" onclick="cambiarImagen(-1)">❮</span>
    <span id="puntos"></span>
    <span class="flecha" onclick="cambiarImagen(1)">❯</span>
</div> -->

<h1 id="menu">Menú.</h1>
<p id="nombre-empleado">Bienvenido, <?php echo $nombre_empleado; ?></p>
<a href="cerrar.php" id="cerrar-sesion">Cerrar Sesion</a>

<div id="container">
    <?php
    // Conexión a la base de datos SQL Server (utilizando PDO)
    $servername = "DESKTOP-M0T0SDR\SQLEXPRESS";
    $username = "sa";
    $password = "Espana3";
    $database = "SYM_Comedor";

    try {
        $conn = new PDO("sqlsrv:Server=$servername;Database=$database", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Consulta SQL para obtener los platillos que no han sido reservados por el empleado
        $query = "SELECT * FROM Menu";
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
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
    ?>
</div>

<script src="index.js"></script>
</body>
</html>
