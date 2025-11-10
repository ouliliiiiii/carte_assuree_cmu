<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
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
    
    <style>
        .header-nav {
            min-height: 70px;
        }
        .user-info {
            background-color: #e9ecef;
            padding: 0.25rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
        }
        .header-btn {
            white-space: nowrap;
            margin: 0 0.2rem;
        }
        @media (max-width: 992px) {
            .header-content {
                flex-direction: column;
                gap: 10px;
            }
            .header-buttons {
                justify-content: center !important;
            }
        }
    </style>
</head>
<body>

<header class="header header-nav" style="background-color: #f8f9fa; border-bottom: 1px solid #ddd;">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center header-content py-2">
            <!-- Logo -->
            <div class="d-flex align-items-center">
                <a href="accueil.php" class="d-flex align-items-center text-decoration-none">
                    <img src="images/Logosen.png" class="logo" alt="Logo SENCSU" style="max-height: 50px;">
                    <span class="ms-2 fw-bold text-success d-none d-md-block">SENCSU</span>
                </a>
            </div>

            <!-- Boutons de navigation -->
            <div class="d-flex align-items-center header-buttons">
                <div class="btn-group me-3" role="group">
                    <a href="accueil.php" class="btn btn-success btn-sm header-btn">
                        <i class="bi bi-people-fill me-1"></i> Registre des bénéficiaires
                    </a>
                    <?php
                    // Afficher le lien vers le tableau de bord pour les administrateurs et pour les agents de région
                    $role = $_SESSION['role'] ?? '';
                    if ($role === 'admin' || $role === 'agent' || $role === ''): ?>
                    <a href="index.php" class="btn btn-outline-secondary btn-sm header-btn">
                        <i class="bi bi-speedometer2 me-1"></i> Tableau de bord
                    </a>
                    <?php endif; ?>
                </div>

                <!-- Informations utilisateur -->
                <div class="d-flex align-items-center ms-3">
                    <?php if (isset($_SESSION['user'])): ?>
                        <span class="user-info me-3 d-none d-md-block">
                            <i class="bi bi-person-circle me-1"></i>
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                <a href="admin_users.php" style="text-decoration:none; color:inherit;">
                                    <strong><?= htmlspecialchars($_SESSION['user']) ?></strong>
                                </a>
                            <?php else: ?>
                                <strong><?= htmlspecialchars($_SESSION['user']) ?></strong>
                            <?php endif; ?>
                            <?php if (!empty($_SESSION['region']) && (isset($_SESSION['role']) && $_SESSION['role'] === 'agent')): ?>
                                <!-- Affichage discret de la région de l'agent pour debug et confirmation -->
                                <span class="ms-2 badge bg-info text-dark" title="Région de l'agent">Région: <?= htmlspecialchars($_SESSION['region']) ?></span>
                            <?php endif; ?>
                        </span>
                    <?php endif; ?>
                    
                    <!-- Date et heure -->
                    <span class="text-muted me-3 d-none d-lg-block">
                        <i class="bi bi-calendar-event me-1"></i><?= date('d/m/Y') ?>
                        <i class="bi bi-clock ms-2 me-1"></i><?= date('H:i') ?>
                    </span>

                    <!-- Bouton déconnexion -->
                    <a href="logout.php" class="btn btn-outline-danger btn-sm header-btn">
                        <i class="bi bi-box-arrow-right"></i>
                        <span class="d-none d-md-inline">Déconnexion</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>