<?php
session_start();
require_once 'db.php';
require_once 'audit.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: inscription.php');
    exit;
}

// Récupérer et nettoyer les champs
$nom = trim($_POST['nom'] ?? '');
$prenom = trim($_POST['prenom'] ?? '');
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$service_regional = trim($_POST['service_regional'] ?? '');
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';

// Vérifier les erreurs
$errors = [];
if ($nom === '') $errors[] = 'Le nom est requis.';
if ($prenom === '') $errors[] = 'Le prénom est requis.';
if ($username === '') $errors[] = 'Le nom d\'utilisateur est requis.';
if ($email === '') $errors[] = 'L\'email est requis.';
if ($service_regional === '') $errors[] = 'La région est requise.';
if ($password === '') $errors[] = 'Le mot de passe est requis.';
if ($password !== $password_confirm) $errors[] = 'Les mots de passe ne correspondent pas.';

if (!empty($errors)) {
    $_SESSION['inscription_errors'] = $errors;
    $_SESSION['inscription_post'] = $_POST;
    header('Location: inscription.php');
    exit;
}

// Log tentative d'inscription
log_action($pdo, ($username ?: 'anonyme'), 'register_attempt', $username, [
    'email' => $email,
    'region' => $service_regional
]);

try {
    // Vérifier unicité username/email
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ? OR email = ?');
    $stmt->execute([$username, $email]);
    if ($stmt->fetchColumn() > 0) {
        $_SESSION['inscription_errors'] = ['Nom d\'utilisateur ou email déjà utilisé.'];
        $_SESSION['inscription_post'] = $_POST;
        header('Location: inscription.php');
        exit;
    }

    // Hasher le mot de passe
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    // Chercher l'id du rôle 'agent'
    $roleId = 2; // fallback
    $roleStmt = $pdo->prepare('SELECT id FROM roles WHERE name = ? LIMIT 1');
    $roleStmt->execute(['agent']);
    $roleFound = $roleStmt->fetchColumn();
    if ($roleFound) $roleId = $roleFound;

    // Insérer l'utilisateur
    $insert = $pdo->prepare('
        INSERT INTO users (username, password, email, nom, prenom, role_id, statut, region) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $insert->execute([$username, $hashed, $email, $nom, $prenom, $roleId, 0, $service_regional]);

    // Log succès inscription
    log_action($pdo, $username, 'register_success', $username, [
        'message' => 'Compte créé',
        'statut' => 0,
        'region' => $service_regional
    ]);

    // Rediriger vers login
    header('Location: login.php?registered=1');
    exit;

} catch (Exception $e) {
    // Gérer toutes les exceptions
    $_SESSION['inscription_errors'] = ['Erreur lors de l\'inscription : ' . $e->getMessage()];
    $_SESSION['inscription_post'] = $_POST;
    header('Location: inscription.php');
    exit;
}
