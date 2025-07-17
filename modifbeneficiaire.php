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
    
 
</head>
<body>
        <?php 
            //On appelle le header de la page
            include 'header.php'; 
        ?>

    <div class="container mt-4">
        <div class="form-container animate__animated animate__fadeIn">
            <h2 class="text-center mb-4">
                <i class="bi bi-person-gear me-2"></i>Modifier le bénéficiaire
                <div class="beneficiary-code mt-2"><?= htmlspecialchars($beneficiaire['Code_Immatriculation']) ?></div>
            </h2>
            
            <form method="post" action="modification.php" id="beneficiaireForm">
                <input type="hidden" name="code" value="<?= htmlspecialchars($beneficiaire['Code_Immatriculation']) ?>">
                
                <div class="row">
                    <!-- Colonne Informations Personnelles -->
                    <div class="col-lg-6">
                        <div class="form-section">
                            <h4 class="section-title">
                                <i class="bi bi-person-lines-fill me-2"></i>Informations Personnelles
                            </h4>
                            
                            <div class="mb-3">
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
                                    <input type="text" name="region" id="region" class="form-control" 
                                           value="<?= htmlspecialchars($beneficiaire['Region']) ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="departement" class="form-label">Département</label>
                                    <input type="text" name="departement" id="departement" class="form-control" 
                                           value="<?= htmlspecialchars($beneficiaire['Departement']) ?>">
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
                
                <div class="d-flex justify-content-between mt-4">
                    <a href="accueil.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left-circle-fill me-2"></i>Annuler
                    </a>
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
    </script>
</body>
</html>