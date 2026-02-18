<?php

function connectionPDO($param)
{
    include_once ($param.".inc.php");

    try {
        $dbType = defined('DB_TYPE') ? DB_TYPE : 'mysql';
        
        if ($dbType === 'pgsql' && defined('DATABASE_URL')) {
            // Connexion PostgreSQL via URI (Supabase/Render)
            $dsn = DATABASE_URL;
            
            // Vérification du DSN
            if (empty($dsn)) {
                throw new Exception("DATABASE_URL est vide");
            }
            
            // Options pour SSL et performance
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $idcom = new PDO($dsn, null, null, $options);
        } else {
            // Connexion MySQL (local/docker)
            if (!defined('HOST') || !defined('NAME')) {
                throw new Exception("Variables MySQL non définies : HOST=" . (defined('HOST') ? HOST : 'undefined') . ", NAME=" . (defined('NAME') ? NAME : 'undefined'));
            }
            
            $host = defined('HOST') ? HOST : 'localhost';
            $port = defined('PORT') ? PORT : '3306';
            $dsn = "mysql:host={$host}:{$port};dbname=" . NAME . ";charset=utf8mb4";
            $user = defined('USER') ? USER : 'root';
            $pass = defined('PASS') ? PASS : '';
            
            $idcom = new PDO($dsn, $user, $pass);
            $idcom->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        
        return $idcom;
    } catch (PDOException $e) {
        error_log("PDO Error: " . $e->getMessage());
        trigger_error("Erreur BD: " . $e->getMessage(), E_USER_ERROR);
        return false;
    } catch (Exception $e) {
        error_log("Config Error: " . $e->getMessage());
        trigger_error("Erreur Config: " . $e->getMessage(), E_USER_ERROR);
        return false;
    }
}

?>

