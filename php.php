<?php

echo phpinfo()

?>

$query = "SELECT * FROM Menu 
                  WHERE id NOT IN (SELECT IdMenu FROM Transaccion WHERE IdEmpleado = ? AND Reservado = 1)";