<?php

/**
 * Charger les variables d'environnement depuis .env.local
 */
function loadEnv($filePath) {
    if (!file_exists($filePath)) {
        return;
    }
    
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // Ignorer les commentaires
        if (strpos($line, '#') === 0) {
            continue;
        }
        
        // Parser les lignes KEY=VALUE
        if (strpos($line, '=') !== false) {
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Retirer les guillemets si présents
            if (substr($value, 0, 1) === '"' && substr($value, -1) === '"') {
                $value = substr($value, 1, -1);
            }
            
            // Définir la variable d'environnement
            if (!getenv($key)) {
                putenv("$key=$value");
            }
        }
    }
}

// Charger .env.local (fichier local non versionné)
$envPath = __DIR__ . '/.env.local';
loadEnv($envPath);

// Charger .env.example (fichier exemple)
if (!getenv('DATABASE_URL')) {
    $envExamplePath = __DIR__ . '/.env.example';
    loadEnv($envExamplePath);
}

?>
