<?php
require_once 'db.php';
require_once 'audit.php';
require_once 'header.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: admin_users.php');
    exit;
}

$stmt = $pdo->prepare('SELECT u.*, r.name as role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.id = ?');
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    echo "Utilisateur introuvable.";
    exit;
}

// Log consultation
log_action($pdo, $_SESSION['user'] ?? 'system', 'view_user', $user['username'] ?? $id, ['message' => 'Consultation profil utilisateur']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Consulter utilisateur</title>
    <link rel="stylesheet" href="new_style.css">
    <link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4 w-50">
        <h4>Détails de l'utilisateur</h4>
        <form>
            <div class="mb-3">
                <label class="form-label">ID</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($user['id']) ?>" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label">Nom d'utilisateur</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" readonly>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nom</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['nom'] ?? '') ?>" readonly>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Prénom</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['prenom'] ?? '') ?>" readonly>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label">Rôle</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($user['role_name'] ?? '') ?>" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label">Région</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($user['region'] ?? '') ?>" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label">Statut</label>
                <input type="text" class="form-control" value="<?= ($user['statut'] == 1) ? 'Actif' : 'Inactif' ?>" readonly>
            </div>

            <div class="d-flex gap-2">
                <a href="modif_user.php?id=<?= htmlspecialchars($user['id']) ?>" class="btn btn-primary">Modifier</a>
                <a href="admin_users.php" class="btn btn-secondary">Retour</a>
            </div>
        </form>
    </div>
</body>
</html>
