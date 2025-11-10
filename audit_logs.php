<?php
require_once 'db.php';
require_once 'header.php';
require_once 'audit.php';

// seul admin peut consulter
if (!isset($_SESSION['role']) || stripos($_SESSION['role'], 'admin') === false) {
    // Log tentative d'accès non autorisé
    if (isset($pdo)) {
        log_action($pdo, $_SESSION['user'] ?? 'unknown', 'unauthorized_audit_access', $_SESSION['user'] ?? null, ['message' => 'Tentative d\'accès aux journaux d\'audit']);
    }
    header('Location: admin_users.php');
    exit;
}

// Simple pagination
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 50;
$offset = ($page - 1) * $perPage;

ensure_audit_table($pdo);

// Filtres utilisateur
$filterActor = trim($_GET['actor'] ?? '');
$filterTarget = trim($_GET['target'] ?? '');
$filterAction = trim($_GET['action'] ?? '');
$filterDateFrom = trim($_GET['date_from'] ?? ''); 
$filterDateTo = trim($_GET['date_to'] ?? '');     
$filterTimeFrom = trim($_GET['time_from'] ?? ''); 
$filterTimeTo = trim($_GET['time_to'] ?? '');     

$where = [];
$params = [];

if ($filterActor !== '') {
    $where[] = "actor LIKE :actor";
    $params[':actor'] = "%$filterActor%";
}
if ($filterTarget !== '') {
    $where[] = "target LIKE :target";
    $params[':target'] = "%$filterTarget%";
}
if ($filterAction !== '') {
    $where[] = "action LIKE :action";
    $params[':action'] = "%$filterAction%";
}

// Filtrage date/heure
if ($filterDateFrom !== '' || $filterTimeFrom !== '') {
    // composer une borne inférieure
    $from = $filterDateFrom ?: date('1970-01-01');
    $fromTime = $filterTimeFrom ?: '00:00';
    $where[] = "created_at >= :from_dt";
    $params[':from_dt'] = $from . ' ' . $fromTime . ':00';
}
if ($filterDateTo !== '' || $filterTimeTo !== '') {
    $to = $filterDateTo ?: date('Y-m-d');
    $toTime = $filterTimeTo ?: '23:59';
    $where[] = "created_at <= :to_dt";
    $params[':to_dt'] = $to . ' ' . $toTime . ':59';
}

$whereSql = '';
if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

// Compter le total avec les filtres
$countSql = "SELECT COUNT(*) FROM audit_logs $whereSql";
$countStmt = $pdo->prepare($countSql);
foreach ($params as $k => $v) $countStmt->bindValue($k, $v);
$countStmt->execute();
$total = $countStmt->fetchColumn();
$totalPages = max(1, ceil($total / $perPage));

// Récupérer les entrées avec les filtres et la pagination
$sql = "SELECT * FROM audit_logs $whereSql ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Journaux d'audit</title>
    <link rel="stylesheet" href="new_style.css">
</head>
<body>
    <div class="container mb-5">
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-3 mb-3 mb-md-0">
                        <h5 class="mb-0">Journaux d'audit</h5>
                    </div>
                    <div class="col-md-9 text-md-end">
                        <div class="d-flex flex-wrap justify-content-md-end">
                             <a href="admin_users.php" class="btn btn-secondary">Retour</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        

          <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-funnel"></i> Filtres de recherche
                </div>
                <div class="card-body">
                   <!-- Formulaire de filtres -->
                    <form method="get" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Acteur</label>
                            <input type="text" name="actor" class="form-control" value="<?= htmlspecialchars($filterActor) ?>" >
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Cible</label>
                            <input type="text" name="target" class="form-control" value="<?= htmlspecialchars($filterTarget) ?>" >
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Action</label>
                            <input type="text" name="action" class="form-control" value="<?= htmlspecialchars($filterAction) ?>" >
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date de </label>
                            <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($filterDateFrom) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date à</label>
                            <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($filterDateTo) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Heure de (HH:MM)</label>
                            <input type="time" name="time_from" class="form-control" value="<?= htmlspecialchars($filterTimeFrom) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Heure à</label>
                            <input type="time" name="time_to" class="form-control" value="<?= htmlspecialchars($filterTimeTo) ?>">
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-funnel-fill"></i> Appliquer
                            </button>
                            <a href="audit_logs.php" class="btn btn-outline-secondary">
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
                    <span><i class="bi bi-people-fill"></i> Liste des </span>
                </div>
            </div>
            <div class="card-body">
                
                <div class="table-responsive">
                    <table  id="beneficiairesTable" class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Heure</th>
                                <th>Acteur</th>
                                <th>Cible</th>
                                <th>Action</th>
                                <th>Détails</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?= $log['created_at'] ?></td>
                                    <td><?= htmlspecialchars($log['actor']) ?></td>
                                        <td><?= htmlspecialchars($log['target']) ?></td>
                                        <td><?= htmlspecialchars($log['action'] ?? '') ?></td>
                                        <td><?= nl2br(htmlspecialchars($log['details'])) ?></td>
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


</body>
</html>
