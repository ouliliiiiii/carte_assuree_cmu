<?php
session_start();
require_once 'db.php';

// Récupération des paramètres de filtre depuis le formulaire
$filtrefichier = $_GET['search_nom'] ?? '';
$filtreDate = $_GET['search_date'] ?? '';

// Construction dynamique de la requête SQL avec filtres
$sql = "SELECT * FROM historique_import WHERE 1=1";
$params = [];

if (!empty($filtrefichier)) {
    $sql .= " AND nom_fichier LIKE ?";
    $params[] = "%$filtrefichier%";
}

if (!empty($filtreDate)) {
    $sql .= " AND DATE(date_import) = ?";
    $params[] = $filtreDate;
}

$sql .= " ORDER BY date_import DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$historique = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Gestion des Bénéficiaires - SENCSU</title>

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="new_style.css" />
</head>
<body>

    <?php include 'header.php'; ?>

    <div class="container mb-5">
        <!-- Section Actions -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-3 mb-3 mb-md-0">
                        <h5 class="mb-0">Historique des importations</h5>
                    </div>
                    <div class="col-md-9 text-md-end">
                        <div class="d-flex flex-wrap justify-content-md-end">
                            <a href="accueil.php" class="btn btn-success  me-2 mb-2">
                                <button class="btn btn-success "> Accueil</button> 
                            </a>
                            <a href="importerListe.php" class="btn btn-primary me-2 mb-2">
                                <i class="bi bi-download"></i> Importer
                            </a>
                            <a href="Historiqueimport.php" class="btn btn-primary mb-2">Historique des importations</a>
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
                        <label for="search_nom" class="form-label">Nom du fichier</label>
                        <input type="text" class="form-control" id="search_nom" name="search_nom"
                               value="<?= htmlspecialchars($filtrefichier) ?>" placeholder="Rechercher..." />
                    </div>
                    <div class="col-md-3">
                        <label for="search_date" class="form-label">Date d'importation</label>
                        <input type="date" class="form-control" id="search_date" name="search_date"
                               value="<?= htmlspecialchars($filtreDate) ?>" />
                    </div>

                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-funnel-fill"></i> Appliquer
                        </button>
                        <a href="<?= basename($_SERVER['PHP_SELF']) ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-counterclockwise"></i> Réinitialiser
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tableau des historiques -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-people-fill"></i> Historique des importations</span>
                <span class="badge bg-primary"><?= count($historique) ?> Importations</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="importTable" class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Nom Fichier</th>
                                <th>Date d'import</th>
                                <th>Lignes importées</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historique as $h) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($h['nom_fichier']) ?></td>
                                    <td><?= htmlspecialchars($h['date_import']) ?></td>
                                    <td><?= htmlspecialchars($h['nb_lignes_importees']) ?></td>
                                    <td>
                                        <a href="detail_import.php?id=<?= (int)$h['id'] ?>" class="btn btn-sm btn-success" title='Détails'>
                                            <i class='bi bi-eye'></i>
                                        </a>
                                        <a href='#' 
                                                class='btn btn-sm btn-danger delete-btn' 
                                                data-bs-toggle='modal'
                                                data-bs-target='#confirmModal'
                                                data-id="<?= (int)$h['id'] ?>"
                                                title="Supprimer">
                                                <i class='bi bi-trash'></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($historique)): ?>
                                <tr><td colspan="4" class="text-center">Aucun résultat trouvé</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
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
                   Êtes-vous sûr de vouloir supprimer cette importation ? Cette action est définitive et entraînera également la suppression de toutes les données associées.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary " data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Supprimer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            $('#importTable').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
                },
                responsive: true,
                dom: '<"top"f>rt<"bottom"lip><"clear">',
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50, 100]
            });

            // Gestion suppression (si tu as modal de confirmation)
            let deleteCode = '';
            $(document).on('click', '.delete-btn', function() {
                deleteCode = $(this).data('id');
            });
            $('#confirmDelete').click(function() {
                if (deleteCode) {
                    window.location.href = 'supprimerimportation.php?code=' + deleteCode;
                }
            });
        });
    </script>
</body>
</html>
