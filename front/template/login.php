<?php
session_start();

include_once('../../bdd/fonctionConnexionBdd.inc.php');
$connexion = connectionPDO('../../bdd/configBdd');

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $identifiant = trim($_POST['identifiant']);
    $motdepasse = $_POST['password'];

    if ($identifiant && $motdepasse) {

        $stmt = $connexion->prepare("
            SELECT idcompte, identifiant, motdepasse, iv, cle
            FROM compte
            WHERE identifiant = :i
        ");

        $stmt->bindValue(':i', $identifiant);
        $stmt->execute();

        $user = $stmt->fetch();

        if ($user) {

            // Conversion BYTEA PostgreSQL
            $encrypted = $user['motdepasse'];
            $iv = $user['iv'];
            $key = $user['cle'];

            if (is_resource($encrypted)) {
                $encrypted = stream_get_contents($encrypted);
            }
            if (is_resource($iv)) {
                $iv = stream_get_contents($iv);
            }
            if (is_resource($key)) {
                $key = stream_get_contents($key);
            }

            $decrypted = openssl_decrypt(
                $encrypted,
                "AES-256-CBC",
                $key,
                0,
                $iv
            );

            if ($decrypted === $motdepasse) {

                $_SESSION['access'] = 'pass';
                $_SESSION['id'] = $user['idcompte'];
                $_SESSION['identifiant'] = $user['identifiant'];

                header("Location: dashboard.php");
                exit;
            }
        }

        $message = "Identifiants incorrects";
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