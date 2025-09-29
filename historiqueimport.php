<?php
require_once 'header.php';
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
    
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #dee2e6;
        }
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            .action-buttons {
                width: 100%;
                justify-content: space-between;
            }
        }
    </style>
</head>
<body>

   

    <div class="container mb-5">
        <!-- En-tête de page amélioré -->
        <div class="page-header">
            <div>
                <h1 class="h3 mb-1">Historique des importations</h1>
                <p class="text-muted mb-0">Consultation et gestion des imports précédents</p>
            </div>
            <div class="action-buttons">
                <!-- Bouton de retour bien placé -->
                <a href="accueil.php" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left me-1"></i> Retour au registre
                </a>
                <a href="importerListe.php" class="btn btn-primary">
                    <i class="bi bi-download me-1"></i> Nouvel import
                </a>
            </div>
        </div>

        <!-- Section Filtres -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-funnel"></i> Filtres de recherche
            </div>
            <div class="card-body">
                <form id="filtreForm" method="get" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="search_nom" class="form-label">Nom du fichier</label>
                        <input type="text" class="form-control" id="search_nom" name="search_nom"
                               value="<?= htmlspecialchars($filtrefichier) ?>" placeholder="Rechercher..." />
                    </div>
                    <div class="col-md-4">
                        <label for="search_date" class="form-label">Date d'importation</label>
                        <input type="date" class="form-control" id="search_date" name="search_date"
                               value="<?= htmlspecialchars($filtreDate) ?>" />
                    </div>
                    <div class="col-md-4">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-funnel-fill me-1"></i> Appliquer
                            </button>
                            <a href="<?= basename($_SERVER['PHP_SELF']) ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Réinitialiser
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tableau des historiques -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="bi bi-clock-history me-2"></i>
                    <span>Historique des importations</span>
                </div>
                <span class="badge bg-primary"><?= count($historique) ?> Importation(s)</span>
            </div>
            <div class="card-body">
                <?php if (!empty($historique)): ?>
                <div class="table-responsive">
                    <table id="importTable" class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Nom Fichier</th>
                                <th>Date d'import</th>
                                <th>Lignes importées</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historique as $h) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($h['nom_fichier']) ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($h['date_import'])) ?></td>
                                    <td>
                                        <span class="badge bg-success rounded-pill">
                                            <?= htmlspecialchars($h['nb_lignes_importees']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="detail_import.php?id=<?= (int)$h['id'] ?>" class="btn btn-outline-primary" title='Détails'>
                                                <i class='bi bi-eye'></i>
                                            </a>
                                            <a href='#' class='btn btn-outline-danger delete-btn' 
                                               data-bs-toggle='modal' data-bs-target='#confirmModal'
                                               data-id="<?= (int)$h['id'] ?>" title="Supprimer">
                                                <i class='bi bi-trash'></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox display-4 text-muted"></i>
                    <p class="text-muted mt-3">Aucune importation trouvée</p>
                    <a href="importerListe.php" class="btn btn-primary mt-2">
                        <i class="bi bi-download me-1"></i> Effectuer une importation
                    </a>
                </div>
                <?php endif; ?>
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
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
                lengthMenu: [5, 10, 25, 50, 100],
                order: [[1, 'desc']] // Tri par date décroissante par défaut
            });

            // Gestion suppression
            let deleteId = '';
            $(document).on('click', '.delete-btn', function(e) {
                e.preventDefault();
                deleteId = $(this).data('id');
            });
            
            $('#confirmDelete').click(function() {
                if (deleteId) {
                    window.location.href = 'supprimerimportation.php?id=' + deleteId;
                }
            });
        });
    </script>
</body>
</html>