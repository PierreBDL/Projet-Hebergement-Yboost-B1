<?php

function connectionPDO($param)
{
    include_once ($param.".inc.php");

    try {
        $dbType = defined('DB_TYPE') ? DB_TYPE : 'mysql';
        
        if ($dbType === 'pgsql' && defined('DATABASE_URL')) {
            // Connexion PostgreSQL via URI (Supabase/Render)
            $dsn = DATABASE_URL;
            // Options pour SSL
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $idcom = new PDO($dsn, null, null, $options);
        } else {
            // Connexion MySQL (local/docker)
            $host = defined('HOST') ? HOST : 'localhost';
            $port = defined('PORT') ? PORT : '3306';
            $dsn = "mysql:host={$host}:{$port};dbname=" . NAME . ";charset=utf8mb4";
            
            $idcom = new PDO($dsn, USER, PASS);
            $idcom->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        
        return $idcom;
    } catch (PDOException $e) {
        error_log("Erreur connexion BD: " . $e->getMessage());
        return false;
    }
}

?>

