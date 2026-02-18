<?php

    session_start();

    // Vérification connexion
    if (!isset($_SESSION['access']) || $_SESSION['access'] !== 'pass') {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script type='text/javascript'>
            Swal.fire({
                icon: 'error',
                title: 'Accès Refusé',
                text: 'Vous n\'êtes pas connecté !',
                confirmButtonColor: '#3085d6'
            }).then((result) => {
                if (result.isConfirmed) {
                window.location.href = '../accueil.php';
                }
            });
        </script>";
        exit();
    }

    // Connexion BDD
    include_once('../../bdd/fonctionConnexionBdd.inc.php');
    $connexion = connectionPDO('../../bdd/configBdd');

    // Gérer l'acceptation/refus d'invitations
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_invitation'])) {
        $idInvitant = (int)$_POST['id_invitant'];
        $action = $_POST['action_invitation'];

        if ($action === 'accepter') {
            // Mettre à jour le statut à accepté
            $stmt = $connexion->prepare("
                UPDATE contact 
                SET statut = 'accepte' 
                WHERE idPossesseur = :invitant AND idDestinataire = :user
            ");
            $stmt->bindValue(':invitant', $idInvitant, PDO::PARAM_INT);
            $stmt->bindValue(':user', $_SESSION['id'], PDO::PARAM_INT);
            $stmt->execute();

            // Créer aussi un contact inverse si nécessaire
            $stmt = $connexion->prepare("
                SELECT COUNT(*) as existe 
                FROM contact 
                WHERE idPossesseur = :user AND idDestinataire = :invitant
            ");
            $stmt->bindValue(':user', $_SESSION['id'], PDO::PARAM_INT);
            $stmt->bindValue(':invitant', $idInvitant, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['existe'] == 0) {
                $stmt = $connexion->prepare("
                    INSERT INTO contact (idPossesseur, idDestinataire, statut)
                    VALUES (:user, :invitant, 'accepte')
                ");
                $stmt->bindValue(':user', $_SESSION['id'], PDO::PARAM_INT);
                $stmt->bindValue(':invitant', $idInvitant, PDO::PARAM_INT);
                $stmt->execute();
            }
        } elseif ($action === 'refuser') {
            $stmt = $connexion->prepare("
                DELETE FROM contact 
                WHERE idPossesseur = :invitant AND idDestinataire = :user
            ");
            $stmt->bindValue(':invitant', $idInvitant, PDO::PARAM_INT);
            $stmt->bindValue(':user', $_SESSION['id'], PDO::PARAM_INT);
            $stmt->execute();
        }

        header("Location: dashboard.php");
        exit;
    }

    // Récupérer les invitations en attente
    $stmt = $connexion->prepare("
        SELECT c.idContact, c.idPossesseur, cp.identifiant
        FROM contact c
        JOIN compte cp ON cp.idCompte = c.idPossesseur
        WHERE c.idDestinataire = :user
        AND c.statut = 'en_attente'
        ORDER BY c.date_creation DESC
    ");
    $stmt->bindValue(':user', $_SESSION['id'], PDO::PARAM_INT);
    $stmt->execute();
    $invitations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les contacts et les afficher
    $stmt = $connexion->prepare("
        SELECT c.idCompte, c.identifiant
        FROM contact ct
        JOIN compte c ON c.idCompte = ct.idDestinataire
        WHERE ct.idPossesseur = :identifiant
        AND ct.statut = 'accepte'");
    $stmt->bindValue(':identifiant', $_SESSION["id"]);
    $stmt->execute();
    $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récucupérer les messages
    if (isset($_GET['contact'])) {
    $idContact = (int) $_GET['contact'];

    $stmt = $connexion->prepare("
        SELECT m.*, c.identifiant AS pseudo_emetteur
        FROM messages m
        JOIN compte c ON c.idCompte = m.idEmetteur
        WHERE
            (m.idEmetteur = :user AND m.idReceveur = :contact)
            OR (m.idEmetteur = :contact AND m.idReceveur = :user)
        ORDER BY m.date_creation ASC
    ");

    $stmt->bindValue(':user', $_SESSION['id'], PDO::PARAM_INT);
    $stmt->bindValue(':contact', $idContact, PDO::PARAM_INT);
    $stmt->execute();
    $msgs = $stmt->fetchAll(PDO::FETCH_ASSOC);


    // Enregistrer les nouveaux messages et PJ dans la bdd

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["envoyer"])) {
        $messageAEnvoyer = trim($_POST["message"]);
        $cheminBDD = null;
        $typeFichier = null;

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $dossier = __DIR__ . '/../../uploads/messages/images/';
            if (!is_dir($dossier)) mkdir($dossier, 0777, true);

            $nomFichier = time() . '_' . basename($_FILES['image']['name']);
            if (move_uploaded_file($_FILES['image']['tmp_name'], $dossier . $nomFichier)) {
                $cheminBDD = $nomFichier;
                $typeFichier = 'image';
            }
        } elseif (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
            $dossier = __DIR__ . '/../../uploads/messages/videos/';
            if (!is_dir($dossier)) mkdir($dossier, 0777, true);

            $nomFichier = time() . '_' . basename($_FILES['video']['name']);
            if (move_uploaded_file($_FILES['video']['tmp_name'], $dossier . $nomFichier)) {
                $cheminBDD = $nomFichier;
                $typeFichier = 'video';
            }
        }

        // --- INSERTION EN BDD ---
        // On insère si le texte n'est pas vide OU si une image a été uploadée
        if ($messageAEnvoyer !== '' || $cheminBDD !== null) {
            if ($messageAEnvoyer === '') {
                $messageAEnvoyer = 'null';
            }

            $stmt = $connexion->prepare("
                INSERT INTO messages (idEmetteur, idReceveur, contenu, chemin) 
                VALUES (:idEmetteur, :idReceveur, :contenu, :chemin)
            ");

            $stmt->bindValue(':idEmetteur', $_SESSION["id"], PDO::PARAM_INT);
            $stmt->bindValue(':idReceveur', (int)$_GET["contact"], PDO::PARAM_INT);
            $stmt->bindValue(':contenu', $messageAEnvoyer);
            $stmt->bindValue(':chemin', $cheminBDD);
            $stmt->execute();
        }

        // Redirection pour éviter le renvoi au rafraîchissement
        header("Location: dashboard.php?contact=" . (int)$_GET["contact"] . "&sent=1");
        exit;
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
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <title>Inscription</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/javascript/dashboard.js" defer></script>
</head>
<body>
    <!-- Alerte d'envoie de message -->
    <?php if (isset($_GET['sent'])) : ?>
        <script>
        Swal.fire({
            icon: 'success',
            title: 'Message envoyé',
            timer: 1500,
            showConfirmButton: false
        });
        </script>
    <?php endif; ?>


    <header>
        <img src="../assets/images/logo.avif" alt="logo">

        <nav>
            <h4>Connecté en tant que <?php echo htmlspecialchars($_SESSION["identifiant"]); ?> </h4>
            <button onclick="deconnexion()" id="deconnexion">Se déconnecter</button>
        </nav>
    </header>

    <main>
        <div id="content">
            <div id="contacts">
                <div class="invitations-section">
                    <h3>Invitations en attente</h3>
                    <?php if (empty($invitations)) : ?>
                        <p class="aucuneInvitation">Aucune invitation</p>
                    <?php else : ?>
                        <?php foreach ($invitations as $invitation) : ?>
                            <div class="invitation-item">
                                <div class="invitation-info">
                                    <img src="../assets/images/avatar.jpg" alt="avatar">
                                    <span><?= htmlspecialchars($invitation['identifiant']) ?></span>
                                </div>
                                <div class="invitation-actions">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="id_invitant" value="<?= $invitation['idPossesseur'] ?>">
                                        <button type="submit" name="action_invitation" value="accepter" class="btn-accepter">✓ Accepter</button>
                                    </form>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="id_invitant" value="<?= $invitation['idPossesseur'] ?>">
                                        <button type="submit" name="action_invitation" value="refuser" class="btn-refuser">✕ Refuser</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <hr>

                <h3>Mes contacts</h3>
                <?php if (empty($contacts)) : ?>
                    <p class="contactVide">Aucun contact pour le moment !</p>
                <?php else : ?>
                    <?php foreach ($contacts as $contact) : ?>
                        <a href="?contact=<?= (int)$contact['idCompte'] ?>" class="contactItem <?= ($idContact ?? null) == $contact['idCompte'] ? 'active' : '' ?>">
                            <img src="../assets/images/avatar.jpg" alt="avatar">
                            <span><?= htmlspecialchars($contact['identifiant']) ?></span>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <div class="nouveauContact">
                    <!-- Modal nouveau contact -->
                    <dialog id="favDialog">
                    <form id="formInvitation">
                        <div class="formNouveauContact">
                            <h3><strong>Nouveau contact</strong></h3>
                            <label for="nomNouveauContact">Pseudo :</label>
                            <input type="text" name="nomNouveauContact" id="nomNouveauContact">
                        </div>
                        <div>
                        <button type="button" id="cancelBtn">Annuler</button>
                        <button type="submit">Envoyer l'invitation</button>
                        </div>
                    </form>
                    </dialog>
                    <p>
                    <button id="showDialog">Nouveau contact</button>
                    </p>
                    <output></output>
                </div>
            </div>

            <div id="discussion">
                <div class="messages">
                    <?php if (empty($msgs)) : ?>
                        <h3 class="messagesVide">Aucun message pour le moment !</h3>
                    <?php else : ?>
                        <h2>Discussion</h2>
                        <?php foreach ($msgs as $msg) : ?>
                            <div class="message <?= $msg['idEmetteur'] == $_SESSION['id'] ? 'emetteur' : 'recepteur' ?>">
                                <div class="message-content">
                                    <?php if (!empty($msg['chemin'])) :
                                        $file = $msg['chemin'];
                                        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                        $imageExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                                        $videoExtensions = ['mp4'];

                                        // On détermine le sous-dossier
                                        $sousDossier = in_array($extension, $imageExtensions) ? 'images' : 'videos';
                                        ?>
                                        <div class="pj-container">
                                            <?php if (in_array($extension, $imageExtensions)) : ?>
                                                <img src="../../uploads/messages/<?= $sousDossier ?>/<?= htmlspecialchars($file) ?>" alt="Image">
                                            <?php elseif (in_array($extension, $videoExtensions)) : ?>
                                                <video controls> <source src="../../uploads/messages/<?= $sousDossier ?>/<?= htmlspecialchars($file) ?>" type="video/mp4"> </video>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($msg['contenu']) && $msg['contenu'] !== 'null') : ?>
                                        <h3 class="messagesRemplis"><?= htmlspecialchars($msg['contenu']) ?></h3>
                                    <?php endif; ?>
                                </div>
                                <h5 class="date"><?= htmlspecialchars($msg['date_creation']) ?></h5>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="rediger">
                    <form action="" method="post" enctype="multipart/form-data">
                        <textarea id="messageRediger" name="message" id="message" placeholder="Écrire un message..." rows="1"></textarea>
                        <div id="piece-jointe-container">
                            <div class="piece-jointe-menu">
                                <label for="uploadImage" class="upload-btn"><img src="../assets/images/image.png" alt="Ajouter une image"></label>
                                <input type="file" id="uploadImage" name="image" accept="image/png, image/jpeg, image/webp">
                                <label for="uploadVideo" class="upload-btn"><img src="../assets/images/video.png" alt="Ajouter une vidéo"></label>
                                <input type="file" id="uploadVideo" name="video" accept="video/mp4">
                            </div>
                            <button type="button" name="piece-jointe" id="piece-jointe"><img src="../assets/images/pieceJointe.png" alt="Ajouter une pièce jointe"></button>
                        </div>
                        <button type="submit" name="envoyer" id="envoyer"><img src="../assets/images/envoyer.png" alt="Envoyer le message"></button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <h5>© 2026 Pierre</h5>
    </footer>
</body>
</html>