<?php
session_start();
require_once 'db.php';

// Gestion des messages
$message = '';
if (isset($_GET['added'])) {
    $codeAdded = htmlspecialchars($_GET['added']);
    $message = "Bénéficiaire <strong>$codeAdded</strong> ajouté avec succès !";
}

if (isset($_GET['import_success']) && $_GET['import_success'] == 1) {
    $message = "Importation des bénéficiaires réussie !";
}

// Récupération des paramètres de filtre
$filtreType = $_GET['type_beneficiaire'] ?? '';
$filtreRegime = $_GET['regime'] ?? '';
$filtreNom = $_GET['search_nom'] ?? '';
$filtreCode = $_GET['search_code'] ?? '';
$filtreGroupe = $_GET['groupe'] ?? '';
$filtreDateDebut = $_GET['date_debut'] ?? '';
$filtreDateFin = $_GET['date_fin'] ?? '';

?>
<?php if (isset($_GET['modification'])): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        <?php if ($_GET['modification'] === 'success'): ?>
            Swal.fire({
                icon: 'success',
                title: 'Modifications enregistrées',
                text: 'Les bénéficiaires sélectionnés ont été mis à jour avec succès.',
                confirmButtonColor: '#3085d6'
            });
        <?php elseif ($_GET['modification'] === 'none'): ?>
            Swal.fire({
                icon: 'info',
                title: 'Aucune modification',
                text: 'Aucune donnée n’a été modifiée.',
                confirmButtonColor: '#3085d6'
            });
        <?php endif; ?>
    });
</script>
<?php endif; ?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Bénéficiaires - SENCSU</title>
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
	
	<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
  
    <link rel="stylesheet" href="new_style.css">
 
</head>
<body>
    
    <?php 
        //On appelle le header de la page
        include 'header.php'; 
    ?>

    <div class="container mb-5">
        <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <!-- Section Actions -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-3 mb-3 mb-md-0">
                        <h5 class="mb-0">Gestion des bénéficiaires</h5>
                    </div>
                    <div class="col-md-9 text-md-end">
                        <div class="d-flex flex-wrap justify-content-md-end">
                            <a href="ajoutbeneficiaire.php" class="btn btn-success me-2 mb-2">
                                 <button class="btn btn-success ">
                                   <i class="bi bi-plus-circle"></i> Nouveau bénéficiaire
                                  </button> 
                            </a>
                            <a href="importerListe.php" class="btn btn-primary me-2 mb-2"> 
                                  <i class="bi bi-download"></i> Importer
                            </a>
                           

                            <?php
                                // Construire l'URL d'export avec les filtres actuels
                                $exportUrl = 'exporterliste.php';
                                $queryParams = [];
                                if (!empty($filtreType)) $queryParams['type_beneficiaire'] = $filtreType;
                                if (!empty($filtreRegime)) $queryParams['regime'] = $filtreRegime;
                                if (!empty($filtreNom)) $queryParams['search_nom'] = $filtreNom;
                                if (!empty($filtreCode)) $queryParams['search_code'] = $filtreCode;
                                if (!empty($groupe)) $queryParams['groupe'] = $groupe;
                                if (!empty($filtreDateDebut)) $queryParams['date_debut'] = $filtreDateDebut;
                                if (!empty($filtreDateFin)) $queryParams['date_fin'] = $filtreDateFin;
                                

                                // Ajouter seulement si des filtres sont actifs
                                if (!empty($queryParams)) {
                                    $exportUrl .= '?' . http_build_query($queryParams);
                                }
                            ?>
                            <a href="<?= htmlspecialchars($exportUrl) ?>" class="btn btn-primary me-2 mb-2">
                               <i class="bi bi-upload"></i> Exporter
                            </a>

                            <a href="Historiqueimport.php" class="btn btn-primary  mb-2"> 
                                   Historique des importations
                            </a>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Filtres -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-funnel"></i> Filtres de recherche
            </div>
            <div class="card-body">
                <form id="filtreForm" method="get" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="search_nom" class="form-label">Nom/Prénom</label>
                        <input type="text" class="form-control" id="search_nom" name="search_nom" 
                               value="<?= htmlspecialchars($filtreNom) ?>" placeholder="Rechercher...">
                    </div>
                    <div class="col-md-3">
                        <label for="search_code" class="form-label">Code</label>
                        <input type="text" class="form-control" id="search_code" name="search_code" 
                               value="<?= htmlspecialchars($filtreCode) ?>" placeholder="Code immatriculation">
                    </div>
                    <div class="col-md-3">
                        <label for="type_beneficiaire" class="form-label">Type bénéficiaire</label>
                        <select id="type_beneficiaire" name="type_beneficiaire" class="form-select">
                            <option value="">Tous les types</option>
                            <?php
                            $types = $pdo->query("SELECT DISTINCT Type_Beneficiaire FROM beneficiaires")->fetchAll(PDO::FETCH_COLUMN);
                            foreach ($types as $type) {
                                $selected = $type === $filtreType ? 'selected' : '';
                                echo "<option value=\"".htmlspecialchars($type)."\" $selected>".htmlspecialchars($type)."</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="regime" class="form-label">Régime</label>
                        <select id="regime" name="regime" class="form-select">
                            <option value="">Tous les régimes</option>
                            <?php
                            $regimes = $pdo->query("SELECT DISTINCT Regime FROM beneficiaires")->fetchAll(PDO::FETCH_COLUMN);
                            foreach ($regimes as $regime) {
                                $selected = $regime === $filtreRegime ? 'selected' : '';
                                echo "<option value=\"".htmlspecialchars($regime)."\" $selected>".htmlspecialchars($regime)."</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <!-- Groupe d'appartenance -->
                    <div class="col-md-3">
                        <label for="groupe" class="form-label">Groupe</label>
                        <select id="groupe" name="groupe" class="form-select">
                            <option value="">Tous les groupes</option>
                            <?php
                            $groupes = $pdo->query("SELECT DISTINCT Groupe FROM beneficiaires")->fetchAll(PDO::FETCH_COLUMN);
                            foreach ($groupes as $groupe) {
                                $selected = $groupe === $filtreGroupe ? 'selected' : '';
                                echo "<option value=\"".htmlspecialchars($groupe)."\" $selected>".htmlspecialchars($groupe)."</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Date d'enregistrement : Du -->
                    <div class="col-md-3">
                        <label for="date_debut" class="form-label">Date d'enregistrement (de)</label>
                        <input type="date" class="form-control" name="date_debut" id="date_debut" value="<?= htmlspecialchars($filtreDateDebut ?? '') ?>">
                    </div>

                    <!-- Date d'enregistrement : Au -->
                    <div class="col-md-3">
                        <label for="date_fin" class="form-label">à</label>
                        <input type="date" class="form-control" name="date_fin" id="date_fin" value="<?= htmlspecialchars($filtreDateFin ?? '') ?>">
                    </div>
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-funnel-fill"></i> Appliquer
                        </button>
                        <a href="<?= basename($_SERVER['PHP_SELF']) ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-counterclockwise"></i> 
                            Réinitialiser
                            
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tableau des bénéficiaires -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-people-fill"></i> Liste des bénéficiaires</span>
                    <div class="ms-auto d-flex align-items-center gap-2">
                        <button class="badge bg-secondary" id="btnModifierSelection">Modifier la sélection</button>
                        <span class="badge bg-primary"><?= $pdo->query("SELECT COUNT(*) FROM beneficiaires")->fetchColumn() ?> bénéficiaires</span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="beneficiairesTable" class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th> <input type="checkbox" id="selectAll"> </th>
                                <th>Date Enregistrement</th>
                                <th>Code</th>
                                <th>Nom & Prénom</th>
                                <th>Téléphone</th>
                               <!--  <th>Régime</th> -->
                                <th>Type Bénéficiaire</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT *, 
                                    (CASE 
                                        WHEN Date_Cotisation > CURDATE() THEN 'À venir'
                                            WHEN Date_Cotisation <= CURDATE() AND Date_Fin_Cotisation >= CURDATE() THEN 'Actif'
                                            ELSE 'Expiré'
                                        END) as Etat_Cotisation 
                                    FROM beneficiaires WHERE 1=1";
                            
                            $params = [];
                            if (!empty($filtreType)) {
                                $sql .= " AND Type_Beneficiaire = ?";
                                $params[] = $filtreType;
                            }
                            if (!empty($filtreRegime)) {
                                $sql .= " AND Regime = ?";
                                $params[] = $filtreRegime;
                            }
                            if (!empty($filtreNom)) {
                                $sql .= " AND (Nom LIKE ? OR Prenom LIKE ?)";
                                $params[] = "%$filtreNom%";
                                $params[] = "%$filtreNom%";
                            }
                            if (!empty($filtreCode)) {
                                $sql .= " AND Code_Immatriculation LIKE ?";
                                $params[] = "%$filtreCode%";
                            }
                            if (!empty($filtreGroupe)) {
                                $sql .= " AND Groupe = ?";
                                $params[] = $filtreGroupe;
                            }
                            if (!empty($filtreDateDebut)) {
                                $sql .= " AND Date_Enreg >= ?";
                                $params[] = $filtreDateDebut;
                            }
                            if (!empty($filtreDateFin)) {
                                $sql .= " AND Date_Enreg <= ?";
                                $params[] = $filtreDateFin;
                            }
                            
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute($params);
                            
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                                // Calcul de l'alerte badge
                                    $dateDebut = new DateTime($row['Date_Cotisation']);
                                    $dateFin = new DateTime($row['Date_Fin_Cotisation']);
                                    $aujourdhui = new DateTime();

                                    $totalDays = $dateFin->diff($dateDebut)->days ?: 1;
                                    $daysPassed = $aujourdhui->diff($dateDebut)->invert ? $aujourdhui->diff($dateDebut)->days : 0;
                                    $percentage = min(100, max(0, ($daysPassed / $totalDays) * 100));

                                    $alerteBadge = '';
                                    if ($percentage >= 70 && $percentage < 100 && $aujourdhui <= $dateFin) {
                                        $alerteBadge = '<i class="bi bi-exclamation-triangle-fill text-warning blink"></i>';
                                    }

                                $statusClass = match ($row['Etat_Cotisation']) {
                                    'Actif'    => 'badge-active',
                                    'À venir'  => 'bg-warning',
                                    'Expiré'   => 'badge-expired',
                                    default    => 'badge-secondary' // au cas où
                                };
                                echo "<tr>
                                        <td><input type='checkbox' class='select-beneficiaire' name='beneficiaires[]' value='" . $row['id'] . "'></td>
                                        <td>{$row['Date_Enreg']}</td>
                                        <td>{$row['Code_Immatriculation']}</td>
                                        <td>{$row['Nom']} {$row['Prenom']}</td>
                                        <td>{$row['Telephone']}</td>
                                        <!-- <td>{$row['Regime']}</td> -->
                                        <td>{$row['Type_Beneficiaire']}</td>
                                        <td><span class='badge-status $statusClass'>{$row['Etat_Cotisation']} $alerteBadge</span></td>
                                        <td class='action-buttons'>
                                            <a href='detail_web.php?code={$row['Code_Immatriculation']}' class='btn btn-sm btn-success' title='Détails'>
                                                <i class='bi bi-eye'></i>
                                            </a>
                                            <a href='modifbeneficiaire.php?code={$row['Code_Immatriculation']}' class='btn btn-sm btn-warning' title='Modifier'>
                                                <i class='bi bi-pencil'></i>
                                            </a>
                                            <a href='codeqr.php?code={$row['Code_Immatriculation']}' class='btn btn-sm btn-info' title='QR Code'>
                                                <i class='bi bi-qr-code'></i>
                                            </a>
                                            <a href='#' 
                                                class='btn btn-sm btn-danger delete-btn' 
                                                data-bs-toggle='modal'
                                                data-bs-target='#confirmModal'
                                                data-code='{$row['Code_Immatriculation']}'
                                                title='Supprimer'>
                                                <i class='bi bi-trash'></i>
                                            </a>
                                           
                                        </td>
                                    </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

  


<!-- Modal de modification collective -->
<div class="modal fade" id="modalModification" tabindex="-1" aria-labelledby="modalModificationLabel" aria-hidden="true" >
  <div class="modal-dialog modal-lg">
    <form method="POST" action="modificationcollective.php" id="formModification" enctype="multipart/form-data">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalModificationLabel">Modification collective</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="ids" id="selectedIds">
           <div class="row">
                <!-- Région -->
                <div class="col-md-6 mb-3">
                    <label for="region" class="form-label">Région</label>
                        <select id="region" name="Region" class="form-select" onchange="chargerDepartements()">
                            <option value="">-- Choisissez une région --</option>
                        </select>
                </div>
                <!-- Département -->
                <div class="col-md-6 mb-3">
                    <label for="departement"  class="form-label">Département</label>
                        <select id="departement" class="form-select" name="Departement" onchange="chargerCommunes()">
                            <option value="">-- Choisissez un département --</option>
                        </select>
                </div>
                <!-- Commune -->
                <div class="col-md-6 mb-3">
                    <label for="commune" class="form-label">Commune</label>
                        <select id="commune" name="Commune" class="form-select">
                            <option value="">-- Choisissez une commune --</option>
                        </select>
                </div>

               <div class="col-md-6 mb-3">
                  
                </div>
                            
                <div class="col-md-6 mb-3">
                    <label for="type_adhesion" class="form-label">Type d'Adhésion</label>
                        <select name="type_adhesion" id="type_adhesion" class="form-select">
                            <option value="">-- Sélectionnez un type --</option>
                            <option value="Individuelle">Individuelle</option>
                            <option value="Familiale">Familiale</option>
                            <option value="Groupe">Groupe</option>
                            <option value="Adhesion Systematique">Adhésion Systématique</option>
                        </select>
                </div>
                            
                <div class="col-md-6 mb-3">
                    <label for="assureur" class="form-label">Assureur</label>
                        <select name="assureur" id="assureur" class="form-select">
                            <option value="">-- Sélectionnez un assureur --</option>
                            <option value="SENCSU">SENCSU</option>
                            <option value="SOURA">SOURA</option>
                            <option value="MSD">MSD</option>
                        </select>
                </div>
                            
                
                 <div class=" col-md-6 mb-3">
                    <label for="regime" class="form-label">Régime</label>
                                <select name="regime" id="regimeModal" class="form-select" onchange="mettreAJourTypesModal()">
                                    <option value="">-- Sélectionnez un régime --</option>
                                    <option value="Contributif">Contributif</option>
                                    <option value="Non Contributif">Non Contributif</option>
                                </select>
                </div>
                <div class=" col-md-6 mb-3">
                    <label for="type_beneficiaire" class="form-label">Type de Bénéficiaire</label>
                                <select name="type_beneficiaire" id="type_beneficiaireModal" class="form-select">
                                    <option value="">-- Sélectionnez un type --</option>
                                </select>
                </div>
                            
                <div class="col-md-6 mb-3">
                    <label for="groupe" class="form-label">Groupe d'Appartenance</label>
                            <input type="text" name="groupe" id="groupe" class="form-control">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="type_cotisation" class="form-label">Type de Cotisation</label>
                        <select name="type_cotisation" id="type_cotisation" class="form-select">
                            <option value="">-- Sélectionnez --</option>
                            <option value="Annuelle">Annuelle</option>
                            <option value="Subventionne">Subventionné</option>
                            <option value="Semestrielle">Semestrielle</option>
                        </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="date_cotisation" class="form-label">Date de Cotisation</label>
                        <input type="date" name="date_cotisation" id="date_cotisation" class="form-control" >
                </div>
                           
                <div class="col-md-6 mb-3">
                    <label for="date_fin_cotisation" class="form-label">Date de Fin de Cotisation</label>
                    <input type="date" name="date_fin_cotisation" id="date_fin_cotisation" class="form-control readonly-field" readonly>
                </div>

                <div class="col-md-12 ">
                    <label for="photos" class="form-label">Photos</label>
                    <input type="file" name="photo[]" id="photo" class="form-control" accept="image/*" multiple>
                    <small class="text-muted">
                        Le nom de chaque photo doit correspondre à l'ID du bénéficiaire (ex: <code>15.jpg</code>, <code>42.png</code>)
                    </small>
                </div>

            </div>
          <!-- Ajoute d'autres champs si besoin -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Appliquer les modifications</button>
        </div>
      </div>
    </form>
  </div>
</div>

  

<!-- Modal de confirmation de suppression -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">Confirmation de suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Êtes-vous sûr de vouloir supprimer ce bénéficiaire ? Cette action est irréversible.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Supprimer</button>
            </div>
        </div>
    </div>
</div>

    <!-- Scripts -->
    <!-- Dans votre section scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    


<script>
document.addEventListener('DOMContentLoaded', function () {

    const typeCotisation = document.getElementById('type_cotisation');
    const dateCotisation = document.getElementById('date_cotisation');
    const dateFinCotisation = document.getElementById('date_fin_cotisation');

    const regimeSelect = document.getElementById("regimeModal");
    const typeSelect = document.getElementById("type_beneficiaireModal");

    // Valeur injectée depuis PHP (mode édition si applicable)
    const selectedType = "<?= isset($beneficiaire['type_beneficiaire']) ? htmlspecialchars($beneficiaire['type_beneficiaire']) : '' ?>";
    const selectedRegime = "<?= isset($beneficiaire['regime']) ? htmlspecialchars($beneficiaire['regime']) : '' ?>";

    function mettreAJourTypesModal() {   
        const regimeValue = regimeSelect.value;
        let options = [];

        if (regimeValue === "Contributif") {
            options = ["CLASSIQUE", "CMU-ELEVE", "CMU-DAARA"];
        } else if (regimeValue === "Non Contributif") {
            options = ["PLAN SESAME", "FEMME ENCEINTE", "ENFANT 0-5ANS", "MENAGE BSF", "TITULAIRE CEC"];
        }

        // Vider et ajouter les options
        typeSelect.innerHTML = '<option value="">-- Sélectionnez un type --</option>';
        options.forEach(value => {
            const opt = document.createElement("option");
            opt.value = value;
            opt.textContent = value;
            typeSelect.appendChild(opt);
        });

        // Repositionner si déjà sélectionné
        if (selectedType && regimeValue === selectedRegime) {
            typeSelect.value = selectedType;
        }
    }

    // Auto-remplir si déjà sélectionné
    if (selectedRegime) {
        regimeSelect.value = selectedRegime;
        mettreAJourTypesModal();
    }
    regimeSelect.addEventListener('change', mettreAJourTypesModal);

    // 📅 Mise à jour automatique de la date de fin de cotisation
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

    // 🧾 Validation du formulaire
    document.getElementById('formModification').addEventListener('submit', function(e) {
        let isValid = true;

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



    <script>
   
        $(document).ready(function() {
            // Initialisation DataTable
            $('#beneficiairesTable').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
                },
                responsive: true,
                dom: '<"top"f>rt<"bottom"lip><"clear">',
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50, 100]
            });

            // Gestion de la suppression
            
                    let deleteCode = '';
                    
                    // Lorsqu'on clique sur un bouton de suppression
                    $(document).on('click', '.delete-btn', function() {
                        deleteCode = $(this).data('code');
                    });
                    
                    // Confirmation de suppression
                    $('#confirmDelete').click(function() {
                        if (deleteCode) {
                            window.location.href = 'supprimerbeneficiaire.php?code=' + deleteCode;
                        }
                    });
            
            // Gestion des alertes
            <?php if ($message): ?>
            Swal.fire({
                position: 'top-end',
                icon: 'success',
                title: 'Bénéficiaire ajouté avec succès',
                showConfirmButton: false,
                timer: 1500
            });
            <?php endif; ?>
        });
        
        
        // Export avancé
        document.getElementById('exportBtn').addEventListener('click', function() {
            Swal.fire({
                title: 'Options d\'export',
                html: `
                    <div class="mb-3">
                        <label class="form-label">Format</label>
                        <select class="form-select" id="exportFormat">
                            <option value="excel">Excel</option>
                            <option value="csv">CSV</option>
                            <option value="pdf">PDF</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Colonnes à inclure</label>
                        <select class="form-select" id="exportColumns" multiple>
                            <option value="code" selected>Code</option>
                            <option value="nom" selected>Nom</option>
                            <option value="telephone" selected>Téléphone</option>
                            <option value="regime" selected>Régime</option>
                            <option value="type" selected>Type</option>
                            <option value="adresse">Adresse</option>
                            <option value="date_cotisation">Date cotisation</option>
                            <option value="date_fin">Date fin</option>
                        </select>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Exporter',
                cancelButtonText: 'Annuler',
                preConfirm: () => {
                    const format = document.getElementById('exportFormat').value;
                    const columns = Array.from(document.getElementById('exportColumns').selectedOptions)
                                        .map(opt => opt.value);
                    window.location.href = `exporterliste.php?format=${format}&columns=${columns.join(',')}`;
                }
            });
        });

    </script>

     <!-- selection --->
    <script>
        $(document).ready(function() {
           
            // Gestion du select all
            $('#selectAll').on('click', function() {
                var isChecked = $(this).is(':checked');
                $('.select-beneficiaire').prop('checked', isChecked);
            });

            // Synchronise l'état du "select all" si on décoche un seul checkbox
            $(document).on('change', '.select-beneficiaire', function() {
                const all = $('.select-beneficiaire').length;
                const checked = $('.select-beneficiaire:checked').length;
                $('#selectAll').prop('checked', all === checked);
            });
        });


        document.getElementById('btnModifierSelection').addEventListener('click', function () {
            const ids = Array.from(document.querySelectorAll('.select-beneficiaire:checked'))
                            .map(cb => cb.value);

            if (ids.length === 0) 
            {
                    Swal.fire({
                    icon: 'warning',
                    title: 'Aucun bénéficiaire sélectionné',
                    text: 'Veuillez sélectionner au moins un bénéficiaire.',
                    confirmButtonText: 'OK'
                });
                            
                //alert("Veuillez sélectionner au moins un bénéficiaire.");
                return;
            }

        document.getElementById('selectedIds').value = JSON.stringify(ids);
        const modal = new bootstrap.Modal(document.getElementById('modalModification'));
        modal.show();
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





    <?php if (isset($_SESSION['import_message'])): ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        Swal.fire({
            icon: '<?= $_SESSION['import_status'] === 'success' ? 'success' : 'error' ?>',
            title: 'Résultat de l\'importation',
            html: `<?= addslashes($_SESSION['import_message']) ?>`,
            confirmButtonText: 'OK'
        });
    </script>

    <?php 
    // Nettoyage après affichage
    unset($_SESSION['import_message']);
    unset($_SESSION['import_status']);
    endif; 
    ?>

    <!-- Ajoutez ce code dans votre section existante, après la gestion des alertes d'import -->

    <script> 
        <?php if (isset($_SESSION['delete_message'])): ?>
        Swal.fire({
            icon: '<?= $_SESSION['delete_status'] === 'success' ? 'success' : 'error' ?>',
            title: '<?= $_SESSION['delete_status'] === 'success' ? "Suppression réussie" : "Erreur" ?>',
            html: `<?= addslashes($_SESSION['delete_message']) ?>`,
            confirmButtonText: 'OK'
        });
    </script>
        <?php 
        // Nettoyage après affichage
        unset($_SESSION['delete_message']);
        unset($_SESSION['delete_status']);
        endif; 
        ?>

  


        
</body>
</html>