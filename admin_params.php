<?php
require_once 'db.php';
require_once 'header.php';
require_once 'audit.php';

// --- Vérification de l'accès admin ---
if (!isset($_SESSION['role']) || stripos($_SESSION['role'], 'admin') === false) {
    if (isset($pdo)) {
        log_action($pdo, $_SESSION['user'] ?? 'unknown', 'unauthorized_params_access', null, ['message' => 'Tentative d\'accès à la gestion des paramètres']);
    }
    header('Location: index.php');
    exit;
}

// --- Traitement des actions POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = intval($_POST['id'] ?? 0);
    $categorie = trim($_POST['categorie'] ?? '');
    $valeur = trim($_POST['valeur'] ?? '');
    $parent_id = !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : null;

    try {
        if ($action === 'add') {
            if ($categorie === '' || $valeur === '') {
                $error = 'Veuillez fournir une catégorie et une valeur.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO parametres (categorie, valeur, parent_id) VALUES (:categorie, :valeur, :parent_id)");
                $stmt->execute([
                    ':categorie' => $categorie,
                    ':valeur' => $valeur,
                    ':parent_id' => $parent_id
                ]);
                log_action($pdo, $_SESSION['user'] ?? 'admin', 'param_ajout', $valeur, ['categorie' => $categorie]);
                $message = 'Paramètre ajouté avec succès.';
            }
        } elseif ($action === 'update') {
            if ($id <= 0 || $categorie === '' || $valeur === '') {
                $error = 'Données invalides pour la modification.';
            } else {
                $stmt = $pdo->prepare("UPDATE parametres SET categorie = :categorie, valeur = :valeur, parent_id = :parent_id WHERE id = :id");
                $stmt->execute([
                    ':categorie' => $categorie,
                    ':valeur' => $valeur,
                    ':parent_id' => $parent_id,
                    ':id' => $id
                ]);
                log_action($pdo, $_SESSION['user'] ?? 'admin', 'param_modification', $valeur, ['id' => $id, 'categorie' => $categorie]);
                $message = 'Paramètre modifié avec succès.';
            }
        } elseif ($action === 'delete') {
            if ($id <= 0) {
                $error = 'ID invalide pour suppression.';
            } else {
                $s = $pdo->prepare("SELECT categorie, valeur FROM parametres WHERE id = :id");
                $s->execute([':id' => $id]);
                $row = $s->fetch(PDO::FETCH_ASSOC);

                $stmt = $pdo->prepare("DELETE FROM parametres WHERE id = :id");
                $stmt->execute([':id' => $id]);

                log_action($pdo, $_SESSION['user'] ?? 'admin', 'param_suppression', $row['valeur'] ?? null, ['categorie' => $row['categorie'] ?? null, 'id' => $id]);
                $message = 'Paramètre supprimé avec succès.';
            }
        }

        if (!empty($message)) {
            header('Location: admin_params.php?msg=' . urlencode($message));
            exit;
        }
    } catch (Exception $e) {
        $error = 'Erreur SQL : ' . $e->getMessage();
    }
}

// --- Chargement des paramètres existants ---
try {
    $stmt = $pdo->query("
        SELECT p.id, p.categorie, p.valeur, p.parent_id, parent.valeur AS parent_valeur
        FROM parametres p
        LEFT JOIN parametres parent ON p.parent_id = parent.id
        ORDER BY p.categorie, p.valeur
    ");
    $params = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Charger la liste des parents possibles
    $parentsStmt = $pdo->query("
        SELECT id, CONCAT(categorie, ' - ', valeur) AS label 
        FROM parametres 
        ORDER BY categorie, valeur
    ");
    $parents = $parentsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $params = [];
    $parents = [];
}
?>

<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Gestion des paramètres</title>
    <link rel="stylesheet" href="new_style.css">
</head>
<body>
<div class="container mb-5">

    <!-- En-tête -->
    <div class="card mb-4">
        <div class="card-body d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Gestion des paramètres</h5>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="bi bi-plus-circle"></i> Ajouter un paramètre
            </button>
        </div>
    </div>

    <!-- Tableau -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <span> <i class="bi bi-list"></i> Liste des paramètres</span>
            </div>
        </div> 
        <div class="card-body">
            <div class="table-responsive">
                <table  id="beneficiairesTable" class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Catégorie</th>
                            <th>Valeur</th>
                            <th>Parent</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($params as $p): ?>
                            <tr>
                                <td><?= htmlspecialchars($p['categorie']) ?></td>
                                <td><?= htmlspecialchars($p['valeur']) ?></td>
                                <td><?= htmlspecialchars($p['parent_valeur'] ?? '-') ?></td>
                                <td>
                                    <form method="post" style="display:inline-block;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                        <button class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce paramètre ?');">Supprimer</button>
                                    </form>
                                    <button class="btn btn-sm btn-secondary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editModal"
                                            data-id="<?= (int)$p['id'] ?>"
                                            data-categorie="<?= htmlspecialchars($p['categorie']) ?>"
                                            data-valeur="<?= htmlspecialchars($p['valeur']) ?>"
                                            data-parent-id="<?= (int)$p['parent_id'] ?>">
                                        Modifier
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL AJOUT -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter un paramètre</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Catégorie</label>
                        <input type="text" name="categorie" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Valeur</label>
                        <input type="text" name="valeur" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Parent (facultatif)</label>
                        <select name="parent_id" class="form-select">
                            <option value="">Aucun</option>
                            <?php foreach ($parents as $p): ?>
                                <option value="<?= (int)$p['id'] ?>"><?= htmlspecialchars($p['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL MODIFICATION -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit-id">
                <div class="modal-header">
                    <h5 class="modal-title">Modifier le paramètre</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Catégorie</label>
                        <input type="text" name="categorie" id="edit-categorie" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Valeur</label>
                        <input type="text" name="valeur" id="edit-valeur" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Parent (facultatif)</label>
                        <select name="parent_id" id="edit-parent" class="form-select">
                            <option value="">Aucun</option>
                            <?php foreach ($parents as $p): ?>
                                <option value="<?= (int)$p['id'] ?>"><?= htmlspecialchars($p['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
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
            document.addEventListener('DOMContentLoaded', function() {
                var editModal = document.getElementById('editModal');
                editModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    document.getElementById('edit-id').value = button.getAttribute('data-id');
                    document.getElementById('edit-categorie').value = button.getAttribute('data-categorie');
                    document.getElementById('edit-valeur').value = button.getAttribute('data-valeur');
                    document.getElementById('edit-parent').value = button.getAttribute('data-parent-id') || '';
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
         });
        
    </script>
</body>
</html>
