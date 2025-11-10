<?php
    require_once 'header.php';
    require_once 'db.php';
    require_once 'audit.php';

    // Récupère tous les rôles depuis la table role
    $roles = $pdo->query('SELECT * FROM roles')->fetchAll(PDO::FETCH_ASSOC);

    // Ajout d'un utilisateur
    if (isset($_POST['add_user'])) {
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role_id = $_POST['role_id'];
        $stmt = $pdo->prepare('INSERT INTO users (username, password, role_id) VALUES (?, ?, ?)');
        $stmt->execute([$username, $password, $role_id]);
    }

    // Suppression d'un utilisateur
    if (isset($_GET['delete'])) {
        $id = $_GET['delete'];
        // Récupérer username pour le log
        $u = $pdo->prepare('SELECT username FROM users WHERE id = ?');
        $u->execute([$id]);
        $uname = $u->fetchColumn();
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$id]);
        // Log suppression (détails structurés)
        $details = ['message' => 'Suppression utilisateur via admin_users', 'deleted_id' => $id, 'deleted_username' => $uname];
        log_action($pdo, $_SESSION['user'] ?? 'system', 'delete_user', $uname ?? $id, $details);
    }

    // Modification d'un utilisateur
    if (isset($_POST['edit_user'])) {
        $id = $_POST['id'];
        $username = $_POST['username'];
        $role_id = $_POST['role_id'];
        $stmt = $pdo->prepare('UPDATE users SET username = ?, role_id = ? WHERE id = ?');
        $stmt->execute([$username, $role_id, $id]);
    }

    // Activation/désactivation d'un utilisateur
    if (isset($_GET['toggle_status'])) {
        $id = $_GET['toggle_status'];
        $current = $pdo->query("SELECT statut FROM users WHERE id = " . intval($id))->fetchColumn();
        $newStatut = ($current == 1) ? 0 : 1;
        $stmt = $pdo->prepare('UPDATE users SET statut = ? WHERE id = ?');
        $stmt->execute([$newStatut, $id]);
        // Log activation/désactivation (détails)
        $u = $pdo->prepare('SELECT username FROM users WHERE id = ?');
        $u->execute([$id]);
        $uname = $u->fetchColumn();
        $details = ['message' => 'Statut modifié', 'new_statut' => $newStatut];
        log_action($pdo, $_SESSION['user'] ?? 'system', 'toggle_status', $uname ?? $id, $details);
        header('Location: admin_users.php');
        exit;
    }

    $users = $pdo->query('SELECT u.*, r.name as role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id')->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des utilisateurs</title>
    <link rel="stylesheet" href="new_style.css">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mb-5">

        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-3 mb-3 mb-md-0">
                        <h5 class="mb-0">Gestion des utilisateurs</h5>
                    </div>
                    <div class="col-md-9 text-md-end">
                        <div class="d-flex flex-wrap justify-content-md-end">
                            <a href="admin_params.php" class="btn btn-primary me-2 mb-2"> 
                                    Parametrage
                                </a>
                            <?php if (isset($_SESSION['role']) && stripos($_SESSION['role'], 'admin') !== false): ?>
                                <a href="audit_logs.php" class="btn btn-primary me-2 mb-2">
                                    <i class="bi bi-shield-lock"></i> Traçabilité
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

         <!-- Section Actions -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-people-fill"></i> Liste des Utilisateurs</span>
                </div>
            </div>
            <div class="card-body">
                    <div class="table-responsive">
                        <table id="beneficiairesTable" class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Nom d'utilisateur</th>
                                    <th>Email</th>
                                    <th>Rôle</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user['username']) ?></td>
                                        <td><?= htmlspecialchars($user['email'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($user['role_name'] ?? '') ?></td>
                                        <td>
                                            <?php if ($user['statut'] == 1): ?>
                                                <a href="?toggle_status=<?= $user['id'] ?>" class="btn btn-success" title="Désactiver">
                                                    <i class="bi bi-toggle-on"></i> Actif
                                                </a>
                                            <?php else: ?>
                                                <a href="?toggle_status=<?= $user['id'] ?>" class="btn btn-secondary btn-sm" title="Activer">
                                                    <i class="bi bi-toggle-off"></i> Inactif
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="modif_user.php?id=<?= $user['id'] ?>" class="btn btn-outline-secondary" title="Modifier">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <a href="?delete=<?= $user['id'] ?>" class="btn btn-outline-danger btn-delete" title="Supprimer" data-username="<?= htmlspecialchars($user['username']) ?>">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                            <a href="consult_user.php?id=<?= $user['id'] ?>" class="btn btn-outline-info" title="Consulter">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
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
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        // Confirmation centralisée pour suppression d'utilisateur (avec SweetAlert2)
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.btn-delete').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    var username = btn.getAttribute('data-username') || 'cet utilisateur';
                    var url = btn.getAttribute('href');

                    Swal.fire({
                        title: 'Êtes-vous sûr ?',
                        text: 'Supprimer l\'utilisateur "' + username + '" ? Cette action est irréversible.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Oui, supprimer',
                        cancelButtonText: 'Annuler'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // redirection vers l'URL de suppression
                            window.location.href = url;
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>
