<?php

// Charger les variables d'environnement depuis .env.local ou .env.example
include_once(__DIR__ . '/loadEnv.inc.php');

// Vérifier si DATABASE_URL est défini (Supabase/Render)
$databaseUrl = getenv('DATABASE_URL');

if ($databaseUrl) {
    // PostgreSQL via URI
    define ('DB_TYPE', 'pgsql');
    define ('DATABASE_URL', $databaseUrl);
} else {
    // MySQL (local/dev)
    define ('DB_TYPE', 'mysql');
    define ('HOST', getenv('DB_HOST') ?: 'localhost');
    define ('NAME', getenv('DB_NAME') ?: 'bdd_messagerie');
    define ('USER', getenv('DB_USER') ?: 'root');
    define ('PASS', getenv('DB_PASS') ?: '');
    define ('PORT', getenv('DB_PORT') ?: '3306');
}

?>