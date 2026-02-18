<?php

function connectionPDO($param)
{
    include_once ($param.".inc.php");

    try {
        $host = defined('HOST') ? HOST : 'localhost';
        $port = defined('PORT') ? PORT : '3306';
        $dsn = "mysql:host={$host}:{$port};dbname=" . NAME . ";charset=utf8";
        
        $idcom = new PDO($dsn, USER, PASS);
        //echo "connection réussie";
        return $idcom;
    } catch (PDOException $e) {
        //echo "Erreur lors de la connexion à la base de donnée :".$e->getMessage();
        return false;
    }
}

?>

