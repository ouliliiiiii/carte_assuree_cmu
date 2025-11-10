<?php
// login.php - Ne pas inclure le header normal
session_start();

// Si déjà connecté, rediriger
if (isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

require_once 'db.php'; // connexion PDO
require_once 'audit.php';

$message = '';
// Message shown after successful registration (account will be inactive by default)
$info = '';

if (isset($_GET['registered']) && $_GET['registered'] == '1') {
    $info = "Inscription envoyée. Votre compte est créé mais inactif. Veuillez contacter l'administrateur pour l'activer.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // 🔹 On récupère aussi le rôle et le statut associé à l'utilisateur
    $sql = "SELECT users.*, roles.name AS role_name
            FROM users
            JOIN roles ON users.role_id = roles.id
            WHERE users.username = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['statut'] == 0) {
        $message = "Votre compte est désactivé. Veuillez contacter l'administrateur.";
        // Log tentative de connexion sur compte inactif
        log_action($pdo, $username, 'login_attempt_inactive', $username, 'Tentative de connexion sur compte inactif');
    }
    // si le mot de passe correspond (hash ou en clair pour compatibilité)
    else if ($user && (password_verify($password, $user['password']) || $user['password'] === $password)) {
    // ✅ Connexion réussie
    $_SESSION['user'] = $user['username'];
    $_SESSION['role'] = $user['role_name'];
    $_SESSION['region'] = $user['region'] ?? '';

    // Log connexion réussie
    log_action($pdo, $user['username'], 'login_success', $user['username'], 'Connexion réussie');

    header("Location: accueil.php");
    exit;
    } else {
        $message = "Nom d'utilisateur ou mot de passe incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">
    <div class="card shadow p-4" style="width: 350px;">
        <h3 class="text-center mb-4">Connexion</h3>

        <?php if ($message): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if ($info): ?>
            <div class="alert alert-info"><?= htmlspecialchars($info) ?></div>
        <?php endif; ?>

        <form method="post" action="login.php">
            <div class="mb-3">
                <label class="form-label">Nom d'utilisateur</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Mot de passe</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Se connecter</button>
        </form>
        <div class="text-center mt-3">
            <a href="inscription.php" class="btn btn-outline-secondary w-100">Inscription</a>
        </div>
    </div>
</body>
</html>
