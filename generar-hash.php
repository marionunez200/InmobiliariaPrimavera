<?php

$password = "admin";

echo "<h2>Hash generado:</h2>";
echo "<textarea rows='3' cols='100'>";
echo password_hash($password, PASSWORD_DEFAULT);
echo "</textarea>";

?>