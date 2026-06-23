<?php
/**
 * database.example.php
 *
 * Archivo de ejemplo para la conexión a la base de datos.
 * 
 * IMPORTANTE:
 * 1. Copia este archivo.
 * 2. Renombra la copia como database.php.
 * 3. En database.php pon tu contraseña real de MySQL.
 * 4. No subas database.php a GitHub.
 */

declare(strict_types=1);

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'inmobiliaria_primavera');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Crea y devuelve la conexión PDO.
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        return $pdo;

    } catch (PDOException $e) {
        die('Error al conectar con la base de datos.');
    }
}

/**
 * Escapa texto antes de imprimirlo en HTML.
 */
function e(?string $texto): string
{
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}

/** Ejemplo de como se deberia ve
 * 
*<?php
*declare(strict_types=1);
*
*   define('DB_HOST', '127.0.0.1');
*   define('DB_NAME', 'inmobiliaria_primavera');
*   define('DB_USER', 'root');
*   define('DB_PASS', 'TU_CONTRASEÑA_DE_MYSQL');
*   define('DB_CHARSET', 'utf8mb4');
*
*function db(): PDO
*{
*    static $pdo = null;
*
*    if ($pdo instanceof PDO) {
*        return $pdo;
*    }
*
*    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
*
*    try {
*        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
*            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
*            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
*            PDO::ATTR_EMULATE_PREPARES => false,
*        ]);
*
*        return $pdo;
*
*    } catch (PDOException $e) {
*        die('Error al conectar con la base de datos: ' . $e->getMessage());
*    }
*}
*
*function e(?string $texto): string
*{
*    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
*}
*
*
 */