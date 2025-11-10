<?php
require_once 'db.php';
require_once 'header.php';
require_once 'audit.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: admin_users.php');
    exit;
}

// Récupérer l'utilisateur
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    echo "Utilisateur introuvable.";
    exit;
}

// Récupérer les rôles
$roles = $pdo->query('SELECT * FROM roles')->fetchAll(PDO::FETCH_ASSOC);

// Traitement POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $role_id = $_POST['role_id'] ?? null;
    $region = trim($_POST['region'] ?? '');

    $update = $pdo->prepare('UPDATE users SET username = ?, email = ?, nom = ?, prenom = ?, role_id = ?, region = ? WHERE id = ?');
    $update->execute([$username, $email, $nom, $prenom, $role_id, $region, $id]);

    // Log modification (détails structurés)
    log_action($pdo, $_SESSION['user'] ?? 'system', 'update_user', $username, ['email' => $email, 'role_id' => $role_id, 'region' => $region]);

    header('Location: admin_users.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier utilisateur</title>
    <link rel="stylesheet" href="new_style.css">
    <link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mb-5">
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8 mb-3 mb-md-0">
                    <h2 class="mb-0 section-title">  <i class="bi bi-person-gear me-2"></i> Modification Agent </h2>
                    </div>
                  
                </div>
            </div>
        </div>
        
        <form method="post" action="modif_user.php?id=<?= htmlspecialchars($id) ?>">
            <div class="mb-3">
                <label class="form-label">Nom d'utilisateur</label>
                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nom</label>
                    <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($user['nom'] ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Prénom</label>
                    <input type="text" name="prenom" class="form-control" value="<?= htmlspecialchars($user['prenom'] ?? '') ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Rôle</label>
                <select name="role_id" class="form-control">
                    <?php foreach ($roles as $r): ?>
                        <option value="<?= $r['id'] ?>" <?= ($user['role_id'] == $r['id']) ? 'selected' : '' ?>><?= htmlspecialchars($r['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Région</label>
                <input type="text" name="region" class="form-control" value="<?= htmlspecialchars($user['region'] ?? '') ?>">
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a href="admin_users.php" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>



    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>
