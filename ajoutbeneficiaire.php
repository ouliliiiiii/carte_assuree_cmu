<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Ajouter un bénéficiaire</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-5">
    <h1>Ajouter un bénéficiaire</h1>

    <form method="post" action="insertion.php">
        <div class="mb-3">
            <label for="code" class="form-label">Code Immatriculation</label>
            <input type="text" name="code" id="code" class="form-control" required />
        </div>
        <div class="mb-3">
            <label for="nom" class="form-label">Nom</label>
            <input type="text" name="nom" id="nom" class="form-control" required />
        </div>
        <div class="mb-3">
            <label for="prenom" class="form-label">Prénom</label>
            <input type="text" name="prenom" id="prenom" class="form-control" required />
        </div>
        <div class="mb-3">
            <label for="date_naissance" class="form-label">Date de naissance</label>
            <input type="date" name="date_naissance" id="date_naissance" class="form-control" />
        </div>
        <div class="mb-3">
            <label for="sexe" class="form-label">Sexe</label>
            <select name="sexe" id="sexe" class="form-select">
                <option value="">-- Choisir --</option>
                <option value="H">Masculin</option>
                <option value="F">Féminin</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="telephone" class="form-label">Téléphone</label>
            <input type="tel" name="telephone" id="telephone" class="form-control" />
        </div>
        <div class="mb-3">
            <label for="adresse" class="form-label">Adresse</label>
            <textarea name="adresse" id="adresse" class="form-control"></textarea>
        </div>
        <div class="mb-3">
            <label for="regime" class="form-label">Régime</label>
            <input type="text" name="regime" id="regime" class="form-control" />
        </div>
        <div class="mb-3">
            <label for="assureur" class="form-label">Assureur</label>
            <input type="text" name="assureur" id="assureur" class="form-control" />
        </div>
        <div class="mb-3">
            <label for="type_beneficiaire" class="form-label">Type de Bénéficiaire</label>
            <input type="text" name="type_beneficiaire" id="type_beneficiaire" class="form-control" />
        </div>
        <div class="mb-3">
            <label for="date_cotisation" class="form-label">Date de cotisation</label>
            <input type="date" name="date_cotisation" id="date_cotisation" class="form-control" />
        </div>
        <div class="mb-3">
            <label for="date_fin_cotisation" class="form-label">Date de fin de cotisation</label>
            <input type="date" name="date_fin_cotisation" id="date_fin_cotisation" class="form-control" />
        </div>

        <button type="submit" class="btn btn-primary">Ajouter</button>
        <a href="accueil.php" class="btn btn-secondary">Retour</a>
    </form>
</div>
</body>
</html>
