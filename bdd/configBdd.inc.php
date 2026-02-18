<?php

// Vérifier si on est en environnement Docker/Render
$host = getenv('DB_HOST') ?: 'localhost';
$name = getenv('DB_NAME') ?: 'bdd_messagerie';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$port = getenv('DB_PORT') ?: '3306';

define ('HOST', $host);
define('NAME', $name);
define ('USER', $user);
define ('PASS', $pass);
define ('PORT', $port);

?>