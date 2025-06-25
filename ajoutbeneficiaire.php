<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Ajouter un bénéficiaire</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

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

    document.addEventListener('DOMContentLoaded', function () {
    const typeCotisation = document.getElementById('type_cotisation');
    const dateCotisation = document.getElementById('date_cotisation');
    const dateFinCotisation = document.getElementById('date_fin_cotisation');

    function updateDateFin() {
        const type = typeCotisation.value;
        const dateStr = dateCotisation.value;

        if (type && dateStr) {
            const date = new Date(dateStr);

            if (type === 'Annuelle') {
                date.setFullYear(date.getFullYear() + 1);
            } else if (type === 'Semestrielle') {
                date.setMonth(date.getMonth() + 6);
            }

            // Format YYYY-MM-DD
            const yyyy = date.getFullYear();
            const mm = String(date.getMonth() + 1).padStart(2, '0');
            const dd = String(date.getDate()).padStart(2, '0');

            dateFinCotisation.value = `${yyyy}-${mm}-${dd}`;
        } else {
            dateFinCotisation.value = '';
        }
    }

    typeCotisation.addEventListener('change', updateDateFin);
    dateCotisation.addEventListener('change', updateDateFin);
});
</script>

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

</head>
<body>

<header class="navbar ">
            <div class="col-lg-4" >
                 <img src="images/Logosen.png" style="width: 300px;">   
            </div>
             
      </header>
<div class="container mt-5">
    <div class="row">
        <div class="row my-4">
            <h1>Ajouter un bénéficiaire</h1>

            <form method="post" action="insertion.php">
    <div class="row">
        <!-- Bloc Informations Personnelles -->
        <div class="col-lg-5 col-12">
            <div class="custom-block">
                <div class="col-12 mb-3">
                    <h3>Informations Personnelles</h3>
                </div>
                <!-- Champs personnels ici -->
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
                    <label for="cni" class="form-label">N° CNI</label>
                    <textarea name="cni" id="cni" class="form-control"></textarea>
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
                    <label for="adresse" class="form-label">Region</label>
                    <textarea name="region" id="region" class="form-control"></textarea>
                </div>
                <div class="mb-3">
                    <label for="departement" class="form-label">Departement</label>
                    <textarea name="departement" id="departement" class="form-control"></textarea>
                </div>
            </div>
        </div>

        <!-- Bloc Informations d’Affiliation -->
        <div class="col-lg-7 col-12">
            <div class="custom-block custom-block-contact">
                <h3 class="mb-4">Informations d’affiliation</h3>

                <div class="mb-3">
                    <label for="regime" class="form-label">Régime</label>
                    <select name="regime" id="regime" class="form-select" onchange="mettreAJourTypes()">
                        <option value="">-- Choisir un régime --</option>
                        <option value="Contributif">Contributif</option>
                        <option value="Non Contributif">Non Contributif</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="type_adhesion" class="form-label">Type d'Adhésion</label>
                    <select name="type_adhesion" id="type_adhesion" class="form-select">
                        <option value="">-- Choisir un type --</option>
                        <option value="Individuelle">Individuelle</option>
                        <option value="Familiale">Familiale</option>
                        <option value="Groupe">Groupe</option>
                        <option value="Adhesion Systematique">Adhesion Systematique</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="assureur" class="form-label">Assureur</label>
                    <select name="assureur" id="assureur" class="form-select">
                        <option value="">-- Choisir un assureur --</option>
                        <option value="SENCSU">SENCSU</option>
                        <option value="SOURA">SOURA</option>
                        <option value="MSD">MSD</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="type_beneficiaire" class="form-label">Type de Bénéficiaire</label>
                    <select name="type_beneficiaire" id="type_beneficiaire" class="form-select">
                        <option value="">-- Choisir un type de bénéficiaire --</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="groupe" class="form-label">Groupe d'Appartenance</label>
                    <textarea name="groupe" id="groupe" class="form-control"></textarea>
                </div>
                <div class="mb-3">
                    <label for="type_cotisation" class="form-label">Type Cotisation</label>
                    <select name="type_cotisation" id="type_cotisation" class="form-select">
                        <option value="">-- Choisir un type --</option>
                        <option value="Annuelle">Annuelle</option>
                        <option value="Semestrielle">Semestrielle</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="date_cotisation" class="form-label">Date de cotisation</label>
                    <input type="date" name="date_cotisation" id="date_cotisation" class="form-control" />
                </div>
                <div class="mb-3">
                    <label for="date_fin_cotisation" class="form-label">Date de fin de cotisation</label>
                    <input type="date" name="date_fin_cotisation" id="date_fin_cotisation" class="form-control" readonly style="background-color: #e9ecef;"/>
                </div>
            </div>
        </div>
    </div>

    <!-- Boutons -->
    <div class="mt-4">
        <button type="submit" class="btn btn-primary">Ajouter</button>
        <a href="accueil.php" class="btn btn-secondary">Retour</a>
    </div>
</form>

        </div>
    </div>
</div>
</body>
</html>
