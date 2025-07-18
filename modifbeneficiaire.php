<?php
session_start();
require_once 'db.php';

$code = $_GET['code'] ?? '';

if (!$code) {
    header('Location: accueil.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM beneficiaires WHERE Code_Immatriculation = ?");
$stmt->execute([$code]);
$beneficiaire = $stmt->fetch();

if (!$beneficiaire) {
    header('Location: accueil.php');
    exit;
}

// Formatage des dates pour l'affichage
function formatDateForInput($dateStr) {
    if (empty($dateStr)) return '';
    return date('Y-m-d', strtotime($dateStr));
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Bénéficiaire - SENCSU</title>
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="new_style.css">
 
</head>
<body>
        <?php 
            //On appelle le header de la page
            include 'header.php'; 
        ?>

    <div class="container mb-5">
<!-- Section Actions -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8 mb-3 mb-md-0">
                    <h2 class="mb-0 section-title">  <i class="bi bi-person-gear me-2"></i> Modification bénéficiaire n°: <?= htmlspecialchars($beneficiaire['Code_Immatriculation']) ?> </h2>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div class="d-flex flex-wrap justify-content-md-end">
                            <a href="accueil.php" class="btn btn-outline-secondary">
                                <button class="btn btn-outline-secondary" style="border: none;">
                                     <i class="bi bi-arrow-left-circle-fill me-2"></i>Retour à l'accueil
                                </button>
                                   
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-container animate__animated animate__fadeIn">
            <form method="post" action="modification.php" id="beneficiaireForm"  enctype="multipart/form-data"  >
                <input type="hidden" name="code" value="<?= htmlspecialchars($beneficiaire['Code_Immatriculation']) ?>">
                
                <div class="row">
                    <!-- Colonne Informations Personnelles -->
                    <div class="col-lg-6">
                        <div class="card-header">
                            <i class="bi bi-person-lines-fill me-2"></i>Informations Personnelles
                        </div>
                        <div class="form-section">
                            


           <div class="mb-3 text-center mt-3">
                                <input type="file" name="photo" id="photo" accept="image/*" class="d-none" onchange="previewPhoto(event)">
                                <img id="photoPreview" src="<?= !empty($beneficiaire['photo']) ? htmlspecialchars($beneficiaire['photo']) : 'images/avatar.png' ?>" 
                                    alt="Photo du bénéficiaire"
                                    class="img-thumbnail"
                                    style="width: 180px; height: 180px; object-fit: cover; cursor: pointer;"
                                    onclick="document.getElementById('photo').click();" title="Modifier photo">
                            </div>                 <div class="mb-3">
                                <label for="nom" class="form-label required-field">Nom</label>
                                <input type="text" name="nom" id="nom" class="form-control" 
                                       value="<?= htmlspecialchars($beneficiaire['Nom']) ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="prenom" class="form-label required-field">Prénom</label>
                                <input type="text" name="prenom" id="prenom" class="form-control" 
                                       value="<?= htmlspecialchars($beneficiaire['Prenom']) ?>" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="date_naissance" class="form-label">Date de naissance</label>
                                    <input type="date" name="date_naissance" id="date_naissance" class="form-control" 
                                           value="<?= formatDateForInput($beneficiaire['Date_Naissance']) ?>">
                                </div>
                                 <div class="col-md-6 mb-3">
                                    <label for="lieu_naissance" class="form-label">Lieu de naissance</label>
                                    <input type="text" name="lieu_naissance" id="lieu_naissance" class="form-control" 
                                           value="<?= htmlspecialchars($beneficiaire['lieu_naissance']) ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="sexe" class="form-label">Sexe</label>
                                    <select name="sexe" id="sexe" class="form-select">
                                        <option value="">-- Sélectionnez --</option>
                                        <option value="H" <?= $beneficiaire['Sexe'] === 'H' ? 'selected' : '' ?>>Masculin</option>
                                        <option value="F" <?= $beneficiaire['Sexe'] === 'F' ? 'selected' : '' ?>>Féminin</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="cni" class="form-label">Numéro CNI</label>
                                <input type="text" name="cni" id="cni" class="form-control" 
                                       value="<?= htmlspecialchars($beneficiaire['CNI']) ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="telephone" class="form-label">Téléphone</label>
                                <input type="tel" name="telephone" id="telephone" class="form-control" 
                                       value="<?= htmlspecialchars($beneficiaire['Telephone']) ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="adresse" class="form-label">Adresse</label>
                                <textarea name="adresse" id="adresse" class="form-control" rows="2"><?= htmlspecialchars($beneficiaire['Adresse']) ?></textarea>
                            </div>
                            
                            <div class="row">
                               <div class="col-md-6 mb-3">
                                <label for="region" class="form-label">Région</label>
                                <select name="Region" id="region" class="form-select" onchange="chargerDepartements();">
                                    <option value="">-- Choisissez une région --</option>
                                    <!-- Les options seront ajoutées par JS -->
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="departement" class="form-label">Département</label>
                                <select name="Departement" id="departement" class="form-select" onchange="chargerCommunes()">
                                    <option value="">-- Choisissez un département --</option>
                                    <!-- Options ajoutées par JS -->
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="commune" class="form-label">Commune</label>
                                <select name="Commune" id="commune" class="form-select">
                                    <option value="">-- Choisissez une commune --</option>
                                    <!-- Options ajoutées par JS -->
                                </select>
                            </div>

                            </div>
                        </div>
                    </div>
                    
                    <!-- Colonne Informations d'Affiliation -->
                    <div class="col-lg-6">
                        <div class="card-header">
                             <i class="bi bi-file-earmark-medical-fill me-2"></i>Informations d'Affiliation
                        </div>
                        <div class="form-section">
                            <div class="mb-3">
                                <label for="regime" class="form-label">Régime</label>
                                <select name="regime" id="regime" class="form-select" onchange="mettreAJourTypes()">
                                    <option value="">-- Sélectionnez un régime --</option>
                                    <option value="Contributif" <?= $beneficiaire['Regime'] === 'Contributif' ? 'selected' : '' ?>>Contributif</option>
                                    <option value="Non Contributif" <?= $beneficiaire['Regime'] === 'Non Contributif' ? 'selected' : '' ?>>Non Contributif</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="type_adhesion" class="form-label">Type d'Adhésion</label>
                                <select name="type_adhesion" id="type_adhesion" class="form-select">
                                    <option value="">-- Sélectionnez un type --</option>
                                    <?php
                                    $typesAdhesion = ['Individuelle', 'Familiale', 'Groupe', 'Adhesion Systematique'];
                                    foreach ($typesAdhesion as $type) {
                                        $selected = ($beneficiaire['Type_Adhesion'] === $type) ? 'selected' : '';
                                        echo "<option value=\"".htmlspecialchars($type)."\" $selected>".htmlspecialchars($type)."</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="assureur" class="form-label">Assureur</label>
                                <select name="assureur" id="assureur" class="form-select">
                                    <option value="">-- Sélectionnez un assureur --</option>
                                    <?php
                                    $assureurs = ['SENCSU', 'SOURA', 'MSD'];
                                    foreach ($assureurs as $a) {
                                        $selected = ($beneficiaire['Assureur'] === $a) ? 'selected' : '';
                                        echo "<option value=\"".htmlspecialchars($a)."\" $selected>".htmlspecialchars($a)."</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="type_beneficiaire" class="form-label">Type de Bénéficiaire</label>
                                <select name="type_beneficiaire" id="type_beneficiaire" class="form-select">
                                    <option value="">-- Sélectionnez un type --</option>
                                    <?php
                                    $typesBenef = [
                                        'CLASSIQUE', 'CMU-ELEVE', 'CMU-DAARA', 'PLAN SESAME',
                                        'FEMME ENCEINTE', 'ENFANT 0-5ANS', 'MENAGE BSF', 'TITULAIRE CEC'
                                    ];
                                    foreach ($typesBenef as $type) {
                                        $selected = ($beneficiaire['Type_Beneficiaire'] === $type) ? 'selected' : '';
                                        echo "<option value=\"".htmlspecialchars($type)."\" $selected>".htmlspecialchars($type)."</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="groupe" class="form-label">Groupe d'Appartenance</label>
                                <input type="text" name="groupe" id="groupe" class="form-control" 
                                       value="<?= htmlspecialchars($beneficiaire['Groupe']) ?>">
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="type_cotisation" class="form-label">Type de Cotisation</label>
                                    <select name="type_cotisation" id="type_cotisation" class="form-select">
                                        <option value="">-- Sélectionnez --</option>
                                        <option value="Annuelle" <?= $beneficiaire['Type_Cotisation'] === 'Annuelle' ? 'selected' : '' ?>>Annuelle</option>
                                        <option value="Subventionne" <?= $beneficiaire['Type_Cotisation'] === 'Annuelle' ? 'selected' : '' ?>>Subventionné</option>
                                        <option value="Semestrielle" <?= $beneficiaire['Type_Cotisation'] === 'Semestrielle' ? 'selected' : '' ?>>Semestrielle</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="date_cotisation" class="form-label">Date de Cotisation</label>
                                    <input type="date" name="date_cotisation" id="date_cotisation" class="form-control" 
                                           value="<?= formatDateForInput($beneficiaire['Date_Cotisation']) ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="date_fin_cotisation" class="form-label">Date de Fin de Cotisation</label>
                                <input type="date" name="date_fin_cotisation" id="date_fin_cotisation" class="form-control" 
                                       value="<?= formatDateForInput($beneficiaire['Date_Fin_Cotisation']) ?>">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save-fill me-2"></i>Enregistrer les modifications
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

        // Sélectionne la valeur existante après mise à jour
        const currentValue = "<?= htmlspecialchars($beneficiaire['Type_Beneficiaire']) ?>";
        if (currentValue) {
            selectType.value = currentValue;
        }
    }

    // Initialiser les types en fonction du régime sélectionné
    document.addEventListener('DOMContentLoaded', function() {
        mettreAJourTypes();
        remplirRegions();
        chargerDepartements();
        chargerCommunes();
        
        // Validation du formulaire
        document.getElementById('beneficiaireForm').addEventListener('submit', function(e) {
            let isValid = true;
            
            // Vérification des champs obligatoires
            document.querySelectorAll('[required]').forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                } else {
                    field.classList.remove('is-invalid');
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

                if (type === 'Annuelle' || type === 'Subventionne') {
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

        function previewPhoto(event) {
            const file = event.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('photo', file);
            formData.append('code', '<?= htmlspecialchars($beneficiaire['Code_Immatriculation']) ?>');

            // Envoi AJAX
            fetch('upload_photo.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Affiche la nouvelle photo
                    document.getElementById('photoPreview').src = data.photo_url;
                } else {
                    alert("Erreur : " + data.message);
                }
            })
            .catch(error => {
                console.error('Erreur lors de l’envoi de la photo :', error);
            });

            // Affichage immédiat (en local, avant le retour du serveur)
            const reader = new FileReader();
            reader.onload = function () {
                document.getElementById('photoPreview').src = reader.result;
            };
            reader.readAsDataURL(file);
        }

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

        const beneficiaireRegion = "<?= addslashes($beneficiaire['Region']) ?>";
        const beneficiaireDepartement = "<?= addslashes($beneficiaire['Departement']) ?>";
        const beneficiaireCommune = "<?= addslashes($beneficiaire['Commune']) ?>";
        function remplirRegions() 
        { 
            const regionSelect = document.getElementById("region");
            regionSelect.innerHTML = '<option value="">-- Choisissez une région --</option>';
            for (let region in dataSenegal) {
                let option = document.createElement("option");
                option.value = region;
                option.text = region;
                if(region === beneficiaireRegion) option.selected = true;
                regionSelect.appendChild(option);
            }
        }

        function chargerDepartements() {
            const region = document.getElementById("region").value;
            const departementSelect = document.getElementById("departement");
            const communeSelect = document.getElementById("commune");

            departementSelect.innerHTML = '<option value="">-- Choisissez un département --</option>';
            communeSelect.innerHTML = '<option value="">-- Choisissez une commune --</option>';

            if (region && dataSenegal[region]) {
                const departements = Object.keys(dataSenegal[region]);
                departements.forEach((dep) => {
                    let option = document.createElement("option");
                    option.value = dep;
                    option.text = dep;
                    if(dep === beneficiaireDepartement) option.selected = true;
                    departementSelect.appendChild(option);
                });
            }
        }

        function chargerCommunes() {
            const region = document.getElementById("region").value;
            const departement = document.getElementById("departement").value;
            const communeSelect = document.getElementById("commune");

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
                    if(commune === beneficiaireCommune) option.selected = true;
                    communeSelect.appendChild(option);
                });
            }
        }

    </script>
</body>
</html>