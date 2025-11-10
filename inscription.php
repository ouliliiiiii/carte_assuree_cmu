<?php
session_start();
require_once 'db.php';

// afficher les erreurs provenant de `insertion_inscription.php` si présentes
$errors = $_SESSION['inscription_errors'] ?? [];
$postBack = $_SESSION['inscription_post'] ?? [];
$success = '';
// effacer les variables flash de session
unset($_SESSION['inscription_errors'], $_SESSION['inscription_post']);

// Charger les régions depuis le fichier JSON
$regions = [];
$regionsFile = __DIR__ . '/regions_departements_communes_senegal.json';
if (file_exists($regionsFile)) {
    $json = file_get_contents($regionsFile);
    $data = json_decode($json, true);
    if (is_array($data)) {
        // les clés sont les noms des régions
        $regions = array_keys($data);
        sort($regions);
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body{background:#f8f9fa}</style>
</head>
<body class="d-flex align-items-center justify-content-center vh-100">
    <div class="card p-4" style="width:420px;">
        <h4 class="mb-3 text-center">Inscription Agent</h4>

        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

    <form method="post" action="insertion_inscription.php">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nom</label>
                    <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($postBack['nom'] ?? ($_POST['nom'] ?? '')) ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Prénom</label>
                    <input type="text" name="prenom" class="form-control" value="<?= htmlspecialchars($postBack['prenom'] ?? ($_POST['prenom'] ?? '')) ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Nom d'utilisateur</label>
                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($postBack['username'] ?? ($_POST['username'] ?? '')) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($postBack['email'] ?? ($_POST['email'] ?? '')) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Service régional</label>
                <select name="service_regional" class="form-control" required>
                    <option value="">-- Sélectionner la région --</option>
                    <?php foreach ($regions as $reg): ?>
                            <option value="<?= htmlspecialchars($reg) ?>" <?= (isset($postBack['service_regional']) && $postBack['service_regional'] === $reg) || (isset($_POST['service_regional']) && $_POST['service_regional'] === $reg) ? 'selected' : '' ?>><?= htmlspecialchars($reg) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3 row">
                <div class="col-md-6 mb-2">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="col-md-6 mb-2">
                    <label class="form-label">Confirmer mot de passe</label>
                    <input type="password" name="password_confirm" class="form-control" required>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">S'inscrire</button>
                <a href="login.php" class="btn btn-outline-secondary">Retour à la connexion</a>
            </div>
        </form>
    </div>
</body>
</html>
