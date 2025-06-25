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

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
      <!-- CSS Templates --> 
        <link href="css/bootstrap.min.css" rel="stylesheet">

        <link href="css/bootstrap-icons.css" rel="stylesheet">

        <link href="css/tooplate-mini-finance.css" rel="stylesheet">
        <!-- CSS Templates -->  
     
        <!-- Lien vers le fichier CSS -->
     <link rel="stylesheet" href="style.css">

     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    function mettreAJourTypes() {
        const regime = document.getElementById("regime").value;
        const selectType = document.getElementById("type_beneficiaire");

        // Nettoie la liste actuelle
        selectType.innerHTML = '<option value="">-- Choisir un type de bénéficiaire --</option>';

        let options = [];

        if (regime === "Contributif") {
            options = [
                { value: "CLASSIQUE", text: "CLASSIQUE" },
                { value: "CMU-ELEVE", text: "CMU-ELEVE" },
                { value: "CMU-DAARA", text: "CMU-DAARA" }
            ];
        } else if (regime === "Non Contributif") {
            options = [
                { value: "PLAN SESAME", text: "PLAN SESAME" },
                { value: "FEMME ENCEINTE", text: "FEMME ENCEINTE" },
                { value: "ENFANT 0-5ANS", text: "ENFANT 0- 5 ANS" },
                { value: "MENAGE BSF", text: "MENAGE BSF" },
                { value: "TITULAIRE CEC", text: "TITULAIRE CEC" }
            ];
        }

        // Ajoute dynamiquement les nouvelles options
        options.forEach(option => {
            const opt = document.createElement("option");
            opt.value = option.value;
            opt.textContent = option.text;
            selectType.appendChild(opt);
        });
    }
    </script>

</head>
<body>

<header class="navbar ">
            <div class="col-lg-4" >
                 <img src="images/Logosen.png" style="width: 300px;">   
            </div>
             
      </header>
<div class="container mt-5">
    <h1>Modifier le bénéficiaire</h1>

    <form method="post" action="modification.php">
    <input type="hidden" name="code" value="<?= htmlspecialchars($beneficiaire['Code_Immatriculation']) ?>">

    <div class="row">
        <!-- Informations personnelles -->
        <div class="col-md-6">
            <div class="border p-3 mb-4 bg-light">
                <h5 class="mb-3">Informations personnelles</h5>

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
                    <label class="form-label">N° CNI</label>
                    <input type="text" name="cni" class="form-control" value="<?= htmlspecialchars($beneficiaire['CNI']) ?>">
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
                    <label class="form-label">Région</label>
                    <input type="text" name="region" class="form-control" value="<?= htmlspecialchars($beneficiaire['Region']) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Département</label>
                    <input type="text" name="departement" class="form-control" value="<?= htmlspecialchars($beneficiaire['Departement']) ?>">
                </div>
            </div>
        </div>

        <!-- Informations d'affiliation -->
        <div class="col-md-6">
            <div class="border p-3 mb-4 bg-light">
                <h5 class="mb-3">Informations d’affiliation</h5>

                <div class="mb-3">
                    <label class="form-label">Régime</label>
                    <select name="regime" class="form-select" onchange="mettreAJourTypes()">
                        <option value="">-- Choisir un régime --</option>
                        <option value="Contributif" <?= $beneficiaire['Regime'] === 'Contributif' ? 'selected' : '' ?>>Contributif</option>
                        <option value="Non-Contributif" <?= $beneficiaire['Regime'] === 'Non-Contributif' ? 'selected' : '' ?>>Non Contributif</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Type d'Adhésion</label>
                    <select name="type_adhesion" class="form-select">
                        <option value="">-- Choisir un type --</option>
                        <?php
                        $typesAdhesion = ['Individuelle', 'Familiale', 'Groupe', 'Adhesion Systematique'];
                        foreach ($typesAdhesion as $type) {
                            $selected = ($beneficiaire['Type_Adhesion'] === $type) ? 'selected' : '';
                            echo "<option value=\"$type\" $selected>$type</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Assureur</label>
                    <select name="assureur" class="form-select">
                        <option value="">-- Sélectionner --</option>
                        <?php
                        $assureurs = ['SENCSU', 'SURA', 'MSD'];
                        foreach ($assureurs as $a) {
                            $selected = ($beneficiaire['Assureur'] === $a) ? 'selected' : '';
                            echo "<option value=\"$a\" $selected>$a</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Type de bénéficiaire</label>
                    <select name="type_beneficiaire" class="form-select">
                        <option value="">-- Sélectionner --</option>
                        <?php
                        $typesBenef = [
                            'CLASSIQUE', 'CMU-ELEVE', 'CMU-DAARA', 'PLAN SESAME',
                            'FEMME ENCEINTE', 'ENFANT 0-5ANS', 'MENAGE BSF', 'TITULAIRE CEC'
                        ];
                        foreach ($typesBenef as $type) {
                            $selected = ($beneficiaire['Type_Beneficiaire'] === $type) ? 'selected' : '';
                            echo "<option value=\"$type\" $selected>$type</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Groupe d'appartenance</label>
                    <input type="text" name="groupe" class="form-control" value="<?= htmlspecialchars($beneficiaire['Groupe']) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Type de cotisation</label>
                    <select name="type_cotisation" class="form-select">
                        <option value="">-- Choisir un type --</option>
                        <option value="Annuelle" <?= $beneficiaire['Type_Cotisation'] === 'Annuelle' ? 'selected' : '' ?>>Annuelle</option>
                        <option value="Semestrielle" <?= $beneficiaire['Type_Cotisation'] === 'Semestrielle' ? 'selected' : '' ?>>Semestrielle</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Date de cotisation</label>
                    <input type="date" name="date_cotisation" class="form-control" value="<?= htmlspecialchars($beneficiaire['Date_Cotisation']) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Date de fin de cotisation</label>
                    <input type="date" name="date_fin_cotisation" class="form-control" value="<?= htmlspecialchars($beneficiaire['Date_Fin_Cotisation']) ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="text-center">
        <button type="submit" class="btn btn-primary">Modifier</button>
        <a href="accueil.php" class="btn btn-secondary">Annuler</a>
    </div>
</form>

</div>
</body>
</html>
