<?php
require_once 'database.php';

try {
    $pdo = db();

    echo "<h1>✅ Conexión exitosa a MySQL</h1>";

    echo "<h2>Base de datos conectada:</h2>";
    echo "<p>" . DB_NAME . "</p>";

    echo "<h2>Tablas encontradas:</h2>";

    $tablas = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

    if (!$tablas) {
        echo "<p>⚠️ No hay tablas en esta base de datos.</p>";
    } else {
        echo "<ul>";
        foreach ($tablas as $tabla) {
            echo "<li>" . htmlspecialchars($tabla) . "</li>";
        }
        echo "</ul>";
    }

    echo "<h2>Conteo de registros:</h2>";

    $tablasAProbar = ['agentes', 'propiedades', 'propiedad_imagenes'];

    foreach ($tablasAProbar as $tabla) {
        if (in_array($tabla, $tablas)) {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $tabla");
            $total = $stmt->fetchColumn();

            echo "<p>$tabla: $total registros</p>";
        } else {
            echo "<p>⚠️ La tabla $tabla no existe.</p>";
        }
    }

} catch (Exception $e) {
    echo "<h1>❌ Error</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}