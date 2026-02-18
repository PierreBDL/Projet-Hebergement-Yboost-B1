<?php
session_start();
header('Content-Type: application/json');

$response = ['success' => false];

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'error' => 'Non connecté']);
    exit;
}

$pseudo = trim($_POST['pseudoDestinataire'] ?? '');

if ($pseudo === '') {
    echo json_encode(['success' => false, 'error' => 'Pseudo vide']);
    exit;
}

include_once('../../../bdd/fonctionConnexionBdd.inc.php');
$connexion = connectionPDO('../../../bdd/configBdd');

if ($connexion === false) {
    echo json_encode(['success' => false, 'error' => 'Erreur connexion BD']);
    exit;
}

// Chercher le destinataire
$stmt = $connexion->prepare("SELECT idcompte FROM compte WHERE identifiant = :identifiant");
$stmt->bindValue(':identifiant', $pseudo);
$stmt->execute();
$dest = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dest) {
    echo json_encode(['success' => false, 'error' => 'Utilisateur introuvable']);
    exit;
}

// Insertion invitation
$stmt = $connexion->prepare("
    INSERT INTO contact (idpossesseur, iddestinataire, statut)
    VALUES (:me, :dest, 'en_attente')
");

$response['success'] = $stmt->execute([
    ':me'   => $_SESSION['id'],
    ':dest' => $dest['idcompte']
]);

$response['message'] = "Invitation envoyée à $pseudo";

echo json_encode($response);
exit;

?>