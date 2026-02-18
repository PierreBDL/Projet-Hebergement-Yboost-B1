<?php
// Redirection vers l'accueil ou login
session_start();

if (isset($_SESSION['access']) && $_SESSION['access'] === 'pass') {
    // Utilisateur connecté, redirect to dashboard
    header('Location: /front/template/dashboard.php');
    exit;
} else {
    // Utilisateur non connecté, redirect to login
    header('Location: /front/template/login.php');
    exit;
}
?>
