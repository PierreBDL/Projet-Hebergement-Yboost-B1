<?php

function connectionPDO($param)
{
    include_once($param . ".inc.php"); // ton configBdd.inc.php avec DATABASE_URL

    try {
        $dbType = defined('DB_TYPE') ? DB_TYPE : 'pgsql';

        if ($dbType === 'pgsql' && defined('DATABASE_URL')) {
            // Parse DATABASE_URL de Supabase
            $url = parse_url(DATABASE_URL);

            if (!$url) {
                throw new Exception("DATABASE_URL invalide");
            }

            $host = $url['host'];
            $port = $url['port'] ?? 5432;
            $user = $url['user'];
            $pass = $url['pass'];
            $dbname = ltrim($url['path'], '/');

            $dsn = "pgsql:host={$host};port={$port};dbname={$dbname};sslmode=require";

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $idcom = new PDO($dsn, $user, $pass, $options);

        } else {
            // Connexion MySQL locale
            $host = HOST;
            $port = defined('PORT') ? PORT : 3306;
            $dsn = "mysql:host={$host};port={$port};dbname=" . NAME . ";charset=utf8mb4";
            $user = defined('USER') ? USER : 'root';
            $pass = defined('PASS') ? PASS : '';

            $idcom = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        }

        return $idcom;

    } catch (PDOException $e) {
        error_log("PDO Error: " . $e->getMessage()); // log pour Render
        return false;
    } catch (Exception $e) {
        error_log("Config Error: " . $e->getMessage());
        return false;
    }
}
