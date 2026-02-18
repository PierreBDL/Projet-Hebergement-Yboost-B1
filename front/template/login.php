<?php
session_start();

include_once('../../bdd/fonctionConnexionBdd.inc.php');
$connexion = connectionPDO('../../bdd/configBdd');

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $identifiant = trim($_POST['identifiant']);
    $motdepasse = $_POST['password'];

    if ($identifiant && $motdepasse) {

        // Génération AES
        $key = random_bytes(32);
        $iv = random_bytes(16);

        $encrypted = openssl_encrypt($motdepasse, "AES-256-CBC", $key, 0, $iv);

        $stmt = $connexion->prepare("
            INSERT INTO compte (identifiant, motdepasse, iv, cle)
            VALUES (:i, :m, :iv, :k)
        ");

        $stmt->bindValue(':i', $identifiant);
        $stmt->bindValue(':m', $encrypted, PDO::PARAM_LOB);
        $stmt->bindValue(':iv', $iv, PDO::PARAM_LOB);
        $stmt->bindValue(':k', $key, PDO::PARAM_LOB);

        $stmt->execute();

        $message = "Compte créé";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="../assets/css/commun/header.css">
    <link rel="stylesheet" href="../assets/css/commun/footer.css">
    <link rel="stylesheet" href="../assets/css/inscriptionLogin.css">
    <title>Connexion</title>
</head>
<body>
    <header>
        <img src="../assets/images/logo.avif" alt="logo">

        <nav>
            <a href="./login.php" class="active">Connexion</a>
            <a href="./inscription.php">Inscription</a>
        </nav>
    </header>

    <main>
        <form action="" method="post" class="form_inscription">
            <fieldset>
                <legend>Connexion</legend>
                
                <label for="identifiant">Identifiant</label>
                <input type="text" id="identifiant" name="identifiant" required value="<?php if(isset($_POST["identifiant"])) echo $_POST["identifiant"]?>">

                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>

                <?php if (!empty($message)) : ?>
                    <div class="message_alerte"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>

                <button type="submit" id="valider" name="valider">Se connecter</button>
            </fieldset>
        </form>
    </main>

    <footer>
        <h5>© 2026 Pierre</h5>
    </footer>
</body>
</html>