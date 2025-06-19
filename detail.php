<?php
require_once 'db.php';

if (isset($_GET['code'])) 
{
    $code = $_GET['code'];

    $stmt = $pdo->prepare("SELECT * FROM beneficiaires WHERE Code_Immatriculation = :code");
    $stmt->execute([':code' => $code]);
    $beneficiaire = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    die("Aucun code fourni.");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détail bénéficiaire</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <?php if ($beneficiaire): ?>
        <h1 class="mb-4">Détail de <strong><?= htmlspecialchars($beneficiaire['Nom']) ?> <?= htmlspecialchars($beneficiaire['Prenom']) ?></strong></h1>

        <div class="card p-4 shadow-sm">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><strong>Code :</strong> <?= htmlspecialchars($beneficiaire['Code_Immatriculation']) ?></li>
                <li class="list-group-item"><strong>Date de naissance :</strong> <?= htmlspecialchars($beneficiaire['Date_Naissance']) ?></li>
                <li class="list-group-item"><strong>Sexe :</strong> <?= htmlspecialchars($beneficiaire['Sexe']) ?></li>
                <li class="list-group-item"><strong>Téléphone :</strong> <?= htmlspecialchars($beneficiaire['Telephone']) ?></li>
                <li class="list-group-item"><strong>Adresse :</strong> <?= htmlspecialchars($beneficiaire['Adresse']) ?></li>
            </ul>
        </div>

        <a href="Accueil.php" class="btn btn-secondary mt-3">Retour</a>
    
    
        <?php else: ?>
            <div class="alert alert-danger">Bénéficiaire non trouvé.</div>
            <a href="Accueil.php" class="btn btn-secondary mt-3">Retour</a>
            <?php endif; ?>
</div>
</body>
</html>
