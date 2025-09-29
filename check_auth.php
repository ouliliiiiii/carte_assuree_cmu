<?php
// check_auth.php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    // Redirection vers la page de login
    header("Location: login.php");
    exit;
}

// Optionnel : Vérifier les permissions si nécessaire
// if ($_SESSION['user_role'] != 'admin') { ... }
?>