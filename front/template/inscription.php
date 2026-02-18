<?php
    session_start();

    include_once('../../bdd/fonctionConnexionBdd.inc.php');
    $connexion = connectionPDO('../../bdd/configBdd');

    $message = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['valider'])) {
        $identifiant = trim($_POST['identifiant']);
        $motdepasse = $_POST['password'];
        if (!empty($identifiant) && !empty($motdepasse)) {
            // Vérifier si l'identifiant existe déjà
            $verif = $connexion->prepare("SELECT COUNT(*) FROM compte WHERE identifiant = :identifiant");
            $verif->bindValue(':identifiant', $identifiant);
            $verif->execute();
            $existe = $verif->fetchColumn();
            if ($existe > 0) {
                $message = "Cet identifiant existe déjà. Veuillez en choisir un autre.";
            } else {
                // Hachage du mot de passe
                $key = openssl_random_pseudo_bytes(32);
                $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
                $hashedPassword = openssl_encrypt($motdepasse, 'aes-256-cbc', $key, 0, $iv);
                try {
                    $stmt = $connexion->prepare("INSERT INTO compte (identifiant, motdepasse, iv, cle) VALUES (:identifiant, :motdepasse, :iv, :cle)");
                    $stmt->bindValue(':identifiant', $identifiant);
                    $stmt->bindValue(':motdepasse', $hashedPassword, PDO::PARAM_LOB);
                    $stmt->bindValue(':iv', $iv, PDO::PARAM_LOB);
                    $stmt->bindValue(':cle', $key, PDO::PARAM_LOB);
                    $stmt->execute();
                    header("Location: ./login.php");
                    exit();
                } catch (PDOException $e) {
                    $message = "Erreur lors de l'enregistrement : " . $e->getMessage();
                }
            }
        }else {
            $message = "Veuillez remplir tous les champs.";
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
    <title>Inscription</title>
</head>
<body>
    <header>
        <img src="../assets/images/logo.avif" alt="logo">

        <nav>
            <a href="./login.php">Connexion</a>
            <a href="./inscription.php" class="active">Inscription</a>
        </nav>
    </header>

    <main>
        <form action="" method="post" class="form_inscription">
            <fieldset>
                <legend>Inscription</legend>
                
                <label for="identifiant">Identifiant</label>
                <input type="text" id="identifiant" name="identifiant" required  value="<?php if(isset($_POST["identifiant"])) echo $_POST["identifiant"]?>">

                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>

                <?php if (!empty($message)) : ?>
                    <div class="message_alerte"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>

                <button type="submit" id="valider" name="valider">S'inscrire</button>
            </fieldset>
        </form>
    </main>

    <footer>
        <h5>© 2026 Pierre</h5>
    </footer>
</body>
</html>