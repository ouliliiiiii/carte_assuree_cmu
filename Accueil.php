<!DOCTYPE html>
<?php
require_once 'db.php';

$message = '';
if (isset($_GET['added'])) {
    $codeAdded = htmlspecialchars($_GET['added']);
    $message = "Bénéficiaire <strong>$codeAdded</strong> ajouté avec succès !";
}

// Récupérer les paramètres de filtre
$filtreType = isset($_GET['type_beneficiaire']) ? $_GET['type_beneficiaire'] : '';
$filtreRegime = isset($_GET['regime']) ? $_GET['regime'] : '';


// Afficher le message si un code a été consulté
if (isset($_SESSION['viewed_code'])) {
    $code = $_SESSION['viewed_code'];
    unset($_SESSION['viewed_code']);
    
    echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            title: 'Consultation terminée',
            text: 'Merci d\\'avoir consulté le dossier $code',
            icon: 'success',
            confirmButtonText: 'OK'
        });
    });
    </script>";
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
   
    <title>Liste des bénéficiaires</title>
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

    <?php if ($message): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let timerInterval;
            Swal.fire({
                position:"top-end",
                icon:"success",
                title:"Bien ajouté",
                showConfirmButton:false,
                timer: 1500, 
            });
        });
    </script>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-md-6">
            <a href="Accueil.php" class='btn btn-primary mb-3'>Accueil</a>
            <a href="ajoutbeneficiaire.php" class="btn btn-success mb-3">Saisir un bénéficiaire</a>
            <!-- Bouton pour ouvrir le popup -->
            <button onclick="openPopup()" class="btn btn-success mb-3">Importer la liste</button>

            <!-- Overlay -->
            <div id="overlay" class="overlay"></div>

            <?php
                // Construire l'URL d'export avec les filtres actuels
                $exportUrl = 'exporterliste.php';
                $queryParams = [];
                if (!empty($filtreType)) $queryParams['type_beneficiaire'] = $filtreType;
                if (!empty($filtreRegime)) $queryParams['regime'] = $filtreRegime;

                // Ajouter seulement si des filtres sont actifs
                if (!empty($queryParams)) {
                    $exportUrl .= '?' . http_build_query($queryParams);
                }
            ?>
            <a href="<?= htmlspecialchars($exportUrl) ?>" class="btn btn-success mb-3">Exporter la liste</a>
        </div>
        
        <div class="col-md-6">
            <form id="filtreForm" method="get" class="row g-3">
                <div class="col-md-5">
                    <label for="type_beneficiaire" class="form-label">Type bénéficiaire</label>
                    <select id="type_beneficiaire" name="type_beneficiaire" class="form-select">
                        <option value="">Tous les types</option>
                        <?php
                        $types = $pdo->query("SELECT DISTINCT Type_Beneficiaire FROM beneficiaires")->fetchAll(PDO::FETCH_COLUMN);
                        foreach ($types as $type) {
                            $selected = $type === $filtreType ? 'selected' : '';
                            echo "<option value=\"$type\" $selected>$type</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <label for="regime" class="form-label">Régime</label>
                    <select id="regime" name="regime" class="form-select">
                        <option value="">Tous les régimes</option>
                        <?php
                        $regimes = $pdo->query("SELECT DISTINCT Regime FROM beneficiaires")->fetchAll(PDO::FETCH_COLUMN);
                        foreach ($regimes as $regime) {
                            $selected = $regime === $filtreRegime ? 'selected' : '';
                            echo "<option value=\"$regime\" $selected>$regime</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Filtrer</button>
                    <a href="<?= basename($_SERVER['PHP_SELF']) ?>" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Popup d'importation -->
    <div id="importPopup" class="popup-overlay">
        <div class="popup-content">
            <h2 class="mb-4">Importer une liste de bénéficiaires</h2>
            
            <?php if (isset($_SESSION['import_message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['import_status'] === 'success' ? 'success' : 'danger'; ?>">
                    <?php 
                    echo $_SESSION['import_message']; 
                    unset($_SESSION['import_message']);
                    unset($_SESSION['import_status']);
                    ?>
                </div>
            <?php endif; ?>
            
            <div class="mb-4">
                <a href="download_template.php" class="btn btn-primary mb-2">
                    Télécharger le modèle Excel
                </a>
                <p class="text-muted small">Téléchargez notre modèle Excel pré-formaté pour faciliter l'importation.</p>
            </div>
            
            <form method="post" action="import_handler.php" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="fichier_import" class="form-label">Choisir un fichier</label>
                    <input class="form-control" type="file" id="fichier_import" name="fichier_import" accept=".xls,.xlsx,.csv" required>
                    <div class="form-text">Formats acceptés : .xls, .xlsx, .csv (max 2MB)</div>
                </div>
                
                <div class="d-flex justify-content-end mt-4">
                    <button type="button" class="btn btn-outline-secondary me-2" onclick="closePopup()">Annuler</button>
                    <button type="submit" class="btn btn-primary">Importer le fichier</button>
                </div>
            </form>
        </div>
    </div>
        

    <h2>Liste des bénéficiaires</h2>

    <table id="myTable" class="table table-striped mt-5">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Nom</th>
                    <th>Téléphone</th>
                    <th>Régime</th>
                    <th>Type Bénéficiaire</th>
                    <th>Adresse</th>
                    <th>Action</th>
                    <th>QR Code</th>
                </tr>
            </thead>
        <tbody>
           <?php
              // Construire la requête SQL avec filtres
              $sql = "SELECT * FROM beneficiaires WHERE 1=1";
              $params = [];
              
              if (!empty($filtreType)) {
                  $sql .= " AND Type_Beneficiaire = ?";
                  $params[] = $filtreType;
              }
              
              if (!empty($filtreRegime)) {
                  $sql .= " AND Regime = ?";
                  $params[] = $filtreRegime;
              }
              
              $stmt = $pdo->prepare($sql);
              $stmt->execute($params);
              
              while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                  echo "<tr>";
                  echo "<td>{$row['Code_Immatriculation']}</td>";
                  echo "<td>{$row['Nom']} {$row['Prenom']}</td>";
                  echo "<td>{$row['Telephone']}</td>";
                  echo "<td>{$row['Regime']}</td>";
                  echo "<td>{$row['Type_Beneficiaire']}</td>";
                  echo "<td>{$row['Adresse']}</td>";
                  echo "<td>
                          <a href='detail.php?code={$row['Code_Immatriculation']}' class='btn btn-primary btn-sm'>Détail</a>
                          <a href='modifbeneficiaire.php?code=" . urlencode($row['Code_Immatriculation']) . "' class='btn btn-warning btn-sm'>Modifier</a>
                        </td>";
                  echo "<td><a href='codeqr.php?code=" . urlencode($row['Code_Immatriculation']) . "' class='btn btn-info btn-sm'>Voir</a></td>";
                  echo "</tr>";
              }
           ?>
       </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    $('#myTable').DataTable({
        paging: true,               // pagination activée
        pageLength: 5,              // nombre de lignes par page par défaut
        lengthMenu: [5, 10, 25, 50], // choix du nombre de lignes affichées
        searching: true,            // barre de recherche activée
        ordering: true,             // tri par colonne activé
        info: true ,                 // info sur les pages affichées
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json"
        }
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
   
   function resetFiltres() {
    const form = document.getElementById('filtreForm');
    form.reset(); // Réinitialise les valeurs par défaut du formulaire (balise <select>)
}
   // Fonctions pour gérer le popup
    function openPopup() {
        document.getElementById('importPopup').style.display = 'flex';
        document.getElementById('overlay').style.display = 'block';
    }
    
    function closePopup() {
        document.getElementById('importPopup').style.display = 'none';
        document.getElementById('overlay').style.display = 'none';
    }
    
    // Fermer le popup si on clique en dehors
    document.getElementById('overlay').addEventListener('click', function() {
        closePopup();
    });
</script>
</body>
</html>