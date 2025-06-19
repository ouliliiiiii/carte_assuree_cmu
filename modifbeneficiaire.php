<?php
require_once 'db.php';

$code = $_GET['code'] ?? '';

if (!$code) {
    die("Code d'immatriculation manquant.");
}

$stmt = $pdo->prepare("SELECT * FROM beneficiaires WHERE Code_Immatriculation = ?");
$stmt->execute([$code]);
$beneficiaire = $stmt->fetch();

if (!$beneficiaire) {
    die("Bénéficiaire non trouvé.");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier un bénéficiaire</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1>Modifier le bénéficiaire</h1>

    <form method="post" action="modification.php">
        <input type="hidden" name="code" value="<?= htmlspecialchars($beneficiaire['Code_Immatriculation']) ?>">

        <div class="mb-3">
            <label class="form-label">Nom</label>
            <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($beneficiaire['Nom']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Prénom</label>
            <input type="text" name="prenom" class="form-control" value="<?= htmlspecialchars($beneficiaire['Prenom']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Date de naissance</label>
            <input type="date" name="date_naissance" class="form-control" value="<?= htmlspecialchars($beneficiaire['Date_Naissance']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Sexe</label>
            <select name="sexe" class="form-select">
                <option value="">-- Choisir --</option>
                <option value="H" <?= $beneficiaire['Sexe'] === 'H' ? 'selected' : '' ?>>Masculin</option>
                <option value="F" <?= $beneficiaire['Sexe'] === 'F' ? 'selected' : '' ?>>Féminin</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Téléphone</label>
            <input type="text" name="telephone" class="form-control" value="<?= htmlspecialchars($beneficiaire['Telephone']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Adresse</label>
            <textarea name="adresse" class="form-control"><?= htmlspecialchars($beneficiaire['Adresse']) ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Régime</label>
            <input type="text" name="regime" class="form-control" value="<?= htmlspecialchars($beneficiaire['Regime']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Assureur</label>
            <input type="text" name="assureur" class="form-control" value="<?= htmlspecialchars($beneficiaire['Assureur']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Type de bénéficiaire</label>
            <input type="text" name="type_beneficiaire" class="form-control" value="<?= htmlspecialchars($beneficiaire['Type_Beneficiaire']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Date de cotisation</label>
            <input type="date" name="date_cotisation" class="form-control" value="<?= htmlspecialchars($beneficiaire['Date_Cotisation']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Date de fin de cotisation</label>
            <input type="date" name="date_fin_cotisation" class="form-control" value="<?= htmlspecialchars($beneficiaire['Date_Fin_Cotisation']) ?>">
        </div>

        <button type="submit" class="btn btn-primary">Modifier</button>
        <a href="accueil.php" class="btn btn-secondary">Annuler</a>
    </form>
</div>
</body>
</html>
