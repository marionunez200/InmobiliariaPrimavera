<?php
// Sustituye 'TuContraseñaAqui' por la contraseña en texto plano para Administrador2
$password = 'Mario012';

// Genera un hash seguro compatible con PHP y MySQL
$hash = password_hash($password, PASSWORD_BCRYPT);

echo "Hash generado:\n" . $hash;
?>