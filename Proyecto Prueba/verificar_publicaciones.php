<?php
$conn = new mysqli("127.0.0.1", "root", "", "nexus_db", 3306);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

echo "<h2>Estructura de la tabla 'publicaciones':</h2>";
$result = $conn->query("DESCRIBE publicaciones");

echo "<table border='1' style='border-collapse: collapse; margin: 20px;'>";
echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th></tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "</tr>";
}

echo "</table>";

$conn->close();
?>
