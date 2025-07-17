<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Bénéficiaire - SENCSU</title>
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

    <link rel="stylesheet" href="new_style.css">
    
    <style>
       
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <img src="images/Logosen.png" class="logo" alt="Logo SENCSU">
                </div>
                <div class="col-md-6 text-end">
                    <span class="text-muted"><?= date('d/m/Y') ?></span>
                </div>
            </div>
        </div>
    </header>

    <div class="container mt-4">
        <div class="form-container animate__animated animate__fadeIn">
            <h2 class="text-center mb-4">
                <i class="bi bi-person-plus-fill me-2"></i>Ajouter un nouveau bénéficiaire
            </h2>
            
            <form method="post" action="insertion.php" id="beneficiaireForm">
                <div class="row">
                    <!-- Colonne Informations Personnelles -->
                    <div class="col-lg-6">
                        <div class="form-section">
                            <h4 class="section-title">
                                <i class="bi bi-person-lines-fill me-2"></i>Informations Personnelles
                            </h4>
                            
                            <div class="mb-3">
                                <label for="nom" class="form-label required-field">Nom</label>
                                <input type="text" name="nom" id="nom" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="prenom" class="form-label required-field">Prénom</label>
                                <input type="text" name="prenom" id="prenom" class="form-control" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="date_naissance" class="form-label">Date de naissance</label>
                                    <input type="date" name="date_naissance" id="date_naissance" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="sexe" class="form-label">Sexe</label>
                                    <select name="sexe" id="sexe" class="form-select">
                                        <option value="">-- Sélectionnez --</option>
                                        <option value="H">Masculin</option>
                                        <option value="F">Féminin</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="cni" class="form-label">Numéro CNI</label>
                                <input type="text" name="cni" id="cni" class="form-control">
                            </div>
                            
                            <div class="mb-3">
                                <label for="telephone" class="form-label">Téléphone</label>
                                <input type="tel" name="telephone" id="telephone" class="form-control">
                            </div>
                            
                            <div class="mb-3">
                                <label for="adresse" class="form-label">Adresse</label>
                                <textarea name="adresse" id="adresse" class="form-control" rows="2"></textarea>
                            </div>
                            
                            <div class="row">
                                <!-- Région -->
                                <div class="col-md-6 mb-3">
                                    <label for="region" class="form-label">Région</label>
                                    <select id="region" class="form-select" onchange="chargerDepartements()">
                                        <option value="">-- Choisissez une région --</option>
                                    </select>
                                </div>

                                <!-- Département -->
                                <div class="col-md-6 mb-3">
                                    <label for="departement" class="form-label">Département</label>
                                    <select id="departement" class="form-select" onchange="chargerCommunes()">
                                        <option value="">-- Choisissez un département --</option>
                                    </select>
                                </div>

                                <!-- Commune -->
                                <div class="col-md-6 mb-3">
                                    <label for="commune" class="form-label">Commune</label>
                                    <select id="commune" class="form-select">
                                        <option value="">-- Choisissez une commune --</option>
                                    </select>
                                </div>


                            </div>
                        </div>
                    </div>
                    
                    <!-- Colonne Informations d'Affiliation -->
                    <div class="col-lg-6">
                        <div class="form-section">
                            <h4 class="section-title">
                                <i class="bi bi-file-earmark-medical-fill me-2"></i>Informations d'Affiliation
                            </h4>
                            
                            <div class="mb-3">
                                <label for="regime" class="form-label">Régime</label>
                                <select name="regime" id="regime" class="form-select" onchange="mettreAJourTypes()">
                                    <option value="">-- Sélectionnez un régime --</option>
                                    <option value="Contributif">Contributif</option>
                                    <option value="Non Contributif">Non Contributif</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="type_adhesion" class="form-label">Type d'Adhésion</label>
                                <select name="type_adhesion" id="type_adhesion" class="form-select">
                                    <option value="">-- Sélectionnez un type --</option>
                                    <option value="Individuelle">Individuelle</option>
                                    <option value="Familiale">Familiale</option>
                                    <option value="Groupe">Groupe</option>
                                    <option value="Adhesion Systematique">Adhésion Systématique</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="assureur" class="form-label">Assureur</label>
                                <select name="assureur" id="assureur" class="form-select">
                                    <option value="">-- Sélectionnez un assureur --</option>
                                    <option value="SENCSU">SENCSU</option>
                                    <option value="SOURA">SOURA</option>
                                    <option value="MSD">MSD</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="type_beneficiaire" class="form-label">Type de Bénéficiaire</label>
                                <select name="type_beneficiaire" id="type_beneficiaire" class="form-select">
                                    <option value="">-- Sélectionnez un type --</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="groupe" class="form-label">Groupe d'Appartenance</label>
                                <input type="text" name="groupe" id="groupe" class="form-control">
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="type_cotisation" class="form-label">Type de Cotisation</label>
                                    <select name="type_cotisation" id="type_cotisation" class="form-select">
                                        <option value="">-- Sélectionnez --</option>
                                        <option value="Annuelle">Annuelle</option>
                                        <option value="Semestrielle">Semestrielle</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="date_cotisation" class="form-label">Date de Cotisation</label>
                                    <input type="date" name="date_cotisation" id="date_cotisation" class="form-control">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="date_fin_cotisation" class="form-label">Date de Fin de Cotisation</label>
                                <input type="date" name="date_fin_cotisation" id="date_fin_cotisation" class="form-control readonly-field" readonly>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between mt-4">
                    <a href="accueil.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left-circle-fill me-2"></i>Retour
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save-fill me-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
    // Mise à jour dynamique des types de bénéficiaires
    function mettreAJourTypes() {
        const regime = document.getElementById("regime").value;
        const selectType = document.getElementById("type_beneficiaire");

        // Nettoie la liste actuelle
        selectType.innerHTML = '<option value="">-- Sélectionnez un type --</option>';

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
                { value: "ENFANT 0-5ANS", text: "ENFANT 0-5 ANS" },
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

    // Calcul automatique de la date de fin de cotisation
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
        
        // Validation du formulaire
        document.getElementById('beneficiaireForm').addEventListener('submit', function(e) {
            let isValid = true;
            
            // Vérification des champs obligatoires
            document.querySelectorAll('[required]').forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('animate-required');
                    setTimeout(() => field.classList.remove('animate-required'), 3000);
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Champs obligatoires manquants',
                    text: 'Veuillez remplir tous les champs obligatoires marqués d\'un astérisque (*)',
                    confirmButtonColor: '#2c3e50'
                });
            }
        });
    });
    </script>
    
    <!-- region departement commune -->
    <script>
        let dataSenegal = {};

        window.onload = function () {
        // Charger le fichier JSON
        fetch("regions_departements_communes_senegal.json")
            .then((res) => res.json())
            .then((data) => {
            dataSenegal = data;
            remplirRegions();
            });
        };

        function remplirRegions() {
        const regionSelect = document.getElementById("region");
        for (let region in dataSenegal) {
            let option = document.createElement("option");
            option.value = region;
            option.text = region;
            regionSelect.appendChild(option);
        }
        }

        function chargerDepartements() {
        const region = document.getElementById("region").value;
        const departementSelect = document.getElementById("departement");
        const communeSelect = document.getElementById("commune");

        // Vider les anciennes options
        departementSelect.innerHTML = '<option value="">-- Choisissez un département --</option>';
        communeSelect.innerHTML = '<option value="">-- Choisissez une commune --</option>';

        if (region && dataSenegal[region]) {
            const departements = Object.keys(dataSenegal[region]);
            departements.forEach((dep) => {
            let option = document.createElement("option");
            option.value = dep;
            option.text = dep;
            departementSelect.appendChild(option);
            });
        }
        }

        function chargerCommunes() {
        const region = document.getElementById("region").value;
        const departement = document.getElementById("departement").value;
        const communeSelect = document.getElementById("commune");

        // Vider les anciennes options
        communeSelect.innerHTML = '<option value="">-- Choisissez une commune --</option>';

        if (
            region &&
            departement &&
            dataSenegal[region] &&
            dataSenegal[region][departement]
        ) {
            const communes = dataSenegal[region][departement];
            communes.forEach((commune) => {
            let option = document.createElement("option");
            option.value = commune;
            option.text = commune;
            communeSelect.appendChild(option);
            });
        }
        }
    </script>
</body>
</html>