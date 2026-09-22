<?php
// config/database.php
// ============================================================
// Devuelve la conexión PDO a PostgreSQL.
// Usa el patrón Singleton: solo se crea una conexión por petición.
//
// Las credenciales se leen del fichero .env (nunca hardcodeadas).
// ============================================================

function cargarEnv(string $fichero): void {
    if (!file_exists($fichero)) {
        return; // En el servidor UAB las variables pueden estar ya configuradas
    }
    $lineas = file($fichero, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lineas as $linea) {
        if (str_starts_with(trim($linea), '#')) {
            continue; // Ignorar comentarios
        }
        [$clave, $valor] = explode('=', $linea, 2);
        $_ENV[trim($clave)] = trim($valor);
    }
}

function getConexion(): PDO {
    static $pdo = null; // Se crea solo la primera vez que se llama

    if ($pdo === null) {
        // Cargar variables del fichero .env (si existe)
        cargarEnv(__DIR__ . '/../.env');

        $host   = $_ENV['DB_HOST']     ?? 'localhost';
        $port   = $_ENV['DB_PORT']     ?? '5432';
        $dbname = $_ENV['DB_NAME']     ?? 'tienda_uab';
        $user   = $_ENV['DB_USER']     ?? 'postgres';
        $pass   = $_ENV['DB_PASSWORD'] ?? '';

        try {
            $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            // Registrar el error en el log del servidor (no mostrarlo al usuario)
            error_log('Error de conexión a la BD: ' . $e->getMessage());
            die('No se pudo conectar a la base de datos. Revisa la configuración.');
        }
    }

    return $pdo;
}
