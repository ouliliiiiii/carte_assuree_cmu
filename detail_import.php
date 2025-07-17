<?php
session_start();
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
    <?php include 'header.php'; ?>



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
                                <th>Date de Naissance</th>
                                <th>Sexe</th>
                                <th>Téléphone</th>
                                <th>Adresse</th>
                                <th>CNI</th>
                                <th>Date Cotisation</th>
                                <th>Date Fin Cotisation</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($beneficiaires as $b) : ?>
                            <tr>
                                <td><?= htmlspecialchars($b['Code_Immatriculation']) ?></td>
                                <td><?= htmlspecialchars($b['Nom']) ?> <?= htmlspecialchars($b['Prenom']) ?> </td>
                                <td><?= htmlspecialchars($b['Date_Naissance']) ?></td>
                                <td><?= htmlspecialchars($b['Sexe']) ?></td>
                                <td><?= htmlspecialchars($b['Telephone']) ?></td>
                                <td><?= htmlspecialchars($b['Adresse']) ?></td>
                                <td><?= htmlspecialchars($b['CNI']) ?></td>
                                <td><?= htmlspecialchars($b['Date_Cotisation']) ?></td>
                                <td><?= htmlspecialchars($b['Date_Fin_Cotisation']) ?></td>
                            </tr>
                            <?php endforeach; ?>
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
