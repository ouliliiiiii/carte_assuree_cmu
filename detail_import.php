<?php
require_once 'header.php';
require_once 'db.php';
require 'vendor/autoload.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Importation invalide');
}

$import_id = (int)$_GET['id'];

// Récupération des bénéficiaires liés à cet import
$stmt = $pdo->prepare("SELECT * FROM beneficiaires WHERE import_id = ? ORDER BY Nom, Prenom");
$stmt->execute([$import_id]);
$beneficiaires = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer aussi les infos de l'import pour afficher en titre
$stmt2 = $pdo->prepare("SELECT nom_fichier, date_import FROM historique_import WHERE id = ?");
$stmt2->execute([$import_id]);
$import_info = $stmt2->fetch(PDO::FETCH_ASSOC);
if (!$import_info) {
    die("Importation introuvable");
}

// Récupération des paramètres de filtre
$filtreType = $_GET['type_beneficiaire'] ?? '';
$filtreRegime = $_GET['regime'] ?? '';
$filtreNom = $_GET['search_nom'] ?? '';
$filtreCode = $_GET['search_code'] ?? '';
$filtreGroupe = $_GET['groupe'] ?? '';
$filtreDateDebut = $_GET['date_debut'] ?? '';
$filtreDateFin = $_GET['date_fin'] ?? '';

// Construction dynamique de la requête SQL
$sql = "SELECT * FROM beneficiaires WHERE import_id = :import_id";
$params = ['import_id' => $import_id];

if (!empty($filtreNom)) {
    $sql .= " AND (Nom LIKE :nom OR Prenom LIKE :nom)";
    $params['nom'] = "%$filtreNom%";
}

if (!empty($filtreCode)) {
    $sql .= " AND Code_Immatriculation LIKE :code";
    $params['code'] = "%$filtreCode%";
}

if (!empty($filtreType)) {
    $sql .= " AND Type_Beneficiaire = :type";
    $params['type'] = $filtreType;
}

if (!empty($filtreRegime)) {
    $sql .= " AND Regime = :regime";
    $params['regime'] = $filtreRegime;
}

if (!empty($filtreGroupe)) {
    $sql .= " AND Groupe = :groupe";
    $params['groupe'] = $filtreGroupe;
}

if (!empty($filtreDateDebut)) {
    $sql .= " AND Date_Cotisation >= :date_debut";
    $params['date_debut'] = $filtreDateDebut;
}

if (!empty($filtreDateFin)) {
    $sql .= " AND Date_Cotisation <= :date_fin";
    $params['date_fin'] = $filtreDateFin;
}

$sql .= " ORDER BY Nom, Prenom";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$beneficiaires = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les infos de l'import
$stmt2 = $pdo->prepare("SELECT nom_fichier, date_import FROM historique_import WHERE id = ?");
$stmt2->execute([$import_id]);
$import_info = $stmt2->fetch(PDO::FETCH_ASSOC);
if (!$import_info) {
    die("Importation introuvable");
}

?>

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
  



    <div class="container mb-5"> 
        <!-- Section Actions -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <h5 class="mb-0">Détails de l'importation : <?= htmlspecialchars($import_info['nom_fichier']) ?></h5>
                        <p>Date : <?= $import_info['date_import'] ?></p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="d-flex flex-wrap justify-content-md-end">
                            <a href="Historiqueimport.php" class="btn btn-primary me-2 mb-2"> 
                                   Retour
                            </a>
                           
                           <a href="export_import.php?id=<?= $import_id ?> " class="btn btn-primary me-2 mb-2">
                                <i class="bi bi-upload"></i> Exporter
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
                    <input type="hidden" name="id" value="<?= htmlspecialchars($import_id) ?>">

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
                       <a href="<?= basename($_SERVER['PHP_SELF']) . '?id=' . $import_id ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-counterclockwise"></i> 
                            Réinitialiser
                        </a>

                    </div>
                </form>
            </div>
        </div>


        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-people-fill"></i> Liste des bénéficiaires</span>
                        <?php
                            $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM beneficiaires WHERE import_id = ?");
                            $stmtCount->execute([$import_id]);
                            $nombreBeneficiaires = $stmtCount->fetchColumn();
                        ?>
                        <span class="badge bg-primary"><?= $nombreBeneficiaires ?> bénéficiaires</span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="beneficiairesTable" class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Code_Immatriculation</th>
                                <th>Nom & Prénom</th>
                                <th>Téléphone</th>
                                <th>Adresse</th>
                                <th>CNI</th>
                                <th>Date Cotisation</th>
                                <th>Date Fin Cotisation</th>
                                <!--th>Actions</th-->
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($beneficiaires)) : ?>
                                <?php foreach ($beneficiaires as $b) : ?>
                                    <tr>
                                        <td><?= htmlspecialchars($b['Code_Immatriculation']) ?></td>
                                        <td><?= htmlspecialchars($b['Nom']) ?> <?= htmlspecialchars($b['Prenom']) ?></td>
                                        <td><?= htmlspecialchars($b['Telephone']) ?></td>
                                        <td><?= htmlspecialchars($b['Adresse']) ?></td>
                                        <td><?= htmlspecialchars($b['CNI']) ?></td>
                                        <td><?= htmlspecialchars($b['Date_Cotisation']) ?></td>
                                        <td><?= htmlspecialchars($b['Date_Fin_Cotisation']) ?></td>
                                        <!-- td>
                                            <a href="detail_web.php?code=<?= urlencode($b['Code_Immatriculation']) ?>" class="btn btn-sm btn-success" title="Détails">
                                                <i class="bi bi-eye"></i>
                                            </-a>
                                            <a href="modifbeneficiaire.php?code=<?= urlencode($b['Code_Immatriculation']) ?>" class="btn btn-sm btn-warning" title="Modifier">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="codeqr.php?code=<?= urlencode($b['Code_Immatriculation']) ?>" class="btn btn-sm btn-info" title="QR Code">
                                                <i class="bi bi-qr-code"></i>
                                            </a>
                                            <a href="#" 
                                            class="btn btn-sm btn-danger delete-btn" 
                                            data-bs-toggle="modal"
                                            data-bs-target="#confirmModal"
                                            data-code="<?= htmlspecialchars($b['Code_Immatriculation']) ?>"
                                            title="Supprimer">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td-->
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr><td colspan="10" class="text-center text-muted">Aucun résultat trouvé.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
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
  

</body>
</html>
