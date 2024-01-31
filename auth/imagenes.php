<?php
// Datos de conexión a la base de datos
$servername = "DESKTOP-M0T0SDR\SQLEXPRESS";
$username = "sa";
$password = "Espana3";
$database = "SYM_Comedor";

try {
    // Conexión a la base de datos
    $conn = new PDO("sqlsrv:Server=$servername;Database=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Ruta de la imagen a insertar
    $imagen_ruta = "imagen1.jpg";

    // Leer la imagen en formato binario
    $imagen_binario = file_get_contents($imagen_ruta, FILE_BINARY);

    // Consulta SQL para insertar la imagen en la tabla
    $query = "INSERT INTO Platillos (Nombre, Descripcion, Imagen) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->execute(["Nombre del Platillo", "Descripción del Platillo", $imagen_binario]);

    echo "Imagen insertada correctamente.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

$conn = null;
?>