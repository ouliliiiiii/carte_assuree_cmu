<?php
require_once 'header.php';
require_once 'db.php';

if (isset($_GET['code'])) {
    $code = $_GET['code'];

    $stmt = $pdo->prepare("SELECT * FROM beneficiaires WHERE Code_Immatriculation = :code");
    $stmt->execute([':code' => $code]);
    $beneficiaire = $stmt->fetch(PDO::FETCH_ASSOC);

    // Formatage des dates
    function formatDate($dateStr) {
        if (empty($dateStr) || $dateStr == '0000-00-00') return 'Non renseignée';
        return date('d/m/Y', strtotime($dateStr));
    }

    // Détermination de l'état
    $etat = '';
    $avertissement = '';
    $aujourdhui = new DateTime();

    if ($beneficiaire) {
        $dateDebut = new DateTime($beneficiaire['Date_Cotisation']);
        $dateFin = new DateTime($beneficiaire['Date_Fin_Cotisation']);

        if ($aujourdhui < $dateDebut) {
            // Cotisation n'a pas encore commencé
            $etat = '<span class="badge bg-warning text-dark rounded-pill px-3 py-2">À venir</span>';
        } elseif ($aujourdhui >= $dateDebut && $aujourdhui <= $dateFin) {
            // Cotisation en cours
            $etat = '<span class="badge bg-success rounded-pill px-3 py-2">Actif</span>';
        } else {
            // Cotisation expirée
            $etat = '<span class="badge bg-danger rounded-pill px-3 py-2">Expiré</span>';
        }

        // Calcul du pourcentage de couverture
        $totalDays = $dateFin->diff($dateDebut)->days ?: 1;
        $daysPassed = $aujourdhui->diff($dateDebut)->invert ? $aujourdhui->diff($dateDebut)->days : 0;
        $percentage = min(100, max(0, ($daysPassed / $totalDays) * 100));

        if ($percentage >= 70 && $percentage < 100 && $aujourdhui <= $dateFin) {
            $avertissement = '
                <div class="alert alert-warning mt-3" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    Attention : la période de couverture tire à sa fin !
                </div>';
        }
    }
} else {
    die("Aucun code fourni.");
}

if (!$beneficiaire) {
    header('Location: accueil.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fiche Assuré - <?= htmlspecialchars($beneficiaire['Nom'] . ' ' .$beneficiaire['Prenom'] ) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
     <!-- Lien vers le fichier CSS -->
     <link rel="stylesheet" href="new_style.css">
    
</head>
<body>
    

        
    <div class="container mb-5">
        <!-- Section Actions -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-3 mb-3 mb-md-0">
                        <h2 class="mb-0 section-title">Fiche de l'assuré</h2>
                    </div>
                    <div class="col-md-9 text-md-end">
                        <div class="d-flex flex-wrap justify-content-md-end">      
                            <button onclick="window.print()" class="btn btn-primary me-2">
                                <i class="bi bi-printer-fill me-2"></i> Imprimer la fiche
                            </button>
                            <a href="accueil.php" class="btn btn-outline-secondary">
                                <button class="btn btn-outline-secondary" style="border: none;">
                                     <i class="bi bi-arrow-left-circle-fill me-2"></i>Retour à l'accueil
                                </button>
                                   
                            </a>
                            <?php if ($percentage >= 70 && $percentage < 100): ?>
                                <span class="badge bg-warning text-dark blink mx-2 justify-content-center fs-6">
                                    <i class="bi bi-exclamation-circle-fill me-1"></i> Mise à jour nécessaire
                                </span>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
                    <div class="col-lg-12 mb-4 mb-sm-5">
                        <div class="card card-style1 border-0">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <i class="bi bi-person-fill me-2"></i>Identité
                                    </div>
                                    <div class="col-lg-6 d-flex justify-content-end ">
                                        <div class=" mb-4 mb-lg-0 ">
                                            <?= $etat ?>
                                         </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-1-9 p-sm-2-3 p-md-6 p-lg-7">
                                
                                <div class="row align-items-center">
                          
                                    <div class="col-lg-3 mb-4 mb-lg-0 d-flex justify-content-center" >
                                        <?php
                                                $photo = !empty($beneficiaire['photo']) 
                                                    ? htmlspecialchars($beneficiaire['photo']) 
                                                    : 'images/avatar.png';
                                        ?>
                                         <img src="<?= $photo ?>" alt="Photo"  
                                                 style="width: 180px; height: 180px; object-fit: cover; cursor: pointer;">
                                    </div>
                                    <div class="col-lg-9 mb-4 mb-lg-0" >
                                        <div class="d-flex align-items-start">
                                             <h3 class="mb-0">Matricule Numéro: <span class="info-value"><?= htmlspecialchars($beneficiaire['Code_Immatriculation']) ?></span></h3>  
                                        </div>
                                      
                                        <div class="info-item mt-5">
                                            <span class="info-label">Nom:</span>
                                            <span class="info-value"><?= htmlspecialchars($beneficiaire['Nom']) ?></span>
                                        </div>
                                        
                                        <div class="info-item">
                                            <span class="info-label">Prénom:</span>
                                            <span class="info-value"><?= htmlspecialchars($beneficiaire['Prenom']) ?></span>
                                        </div>
                                         <div class="info-item">
                                            <span class="info-label">Sexe:</span>
                                            <span class="info-value">
                                                <?= $beneficiaire['Sexe'] == 'H' ? 'Masculin' : 'Féminin' ?>
                                            </span>
                                        </div>
                                        
                                        <div class="info-item">
                                            <span class="info-label">Téléphone:</span>
                                            <span class="info-value"><?= htmlspecialchars($beneficiaire['Telephone']) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
        </div>
        <div class="row">
            <!-- Colonne de gauche - Informations personnelles -->
            <div class="col-lg-5">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="bi bi-person-fill me-2"></i>Informations personnelles
                    </div>
                    <div class="card-body text-center">
                       <!-- <img src="images/avatar.png" class="profile-img mb-4" alt="Photo profil"> -->
                        
                        <div class="text-start">
                            <div class="info-item">
                                    <span class="info-label">Date Enregistrement:</span>
                                    <span class="info-value"><?= formatDate($beneficiaire['Date_Enreg']) ?></span>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">Date de naissance:</span>
                                <span class="info-value"><?= formatDate($beneficiaire['Date_Naissance']) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Lieu de naissance:</span>
                                <span class="info-value"><?= htmlspecialchars($beneficiaire['lieu_naissance']) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">CNI:</span>
                                <span class="info-value"><?= htmlspecialchars($beneficiaire['CNI']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-geo-alt-fill me-2"></i>Adresse
                    </div>
                    <div class="card-body">
                        <div class="info-item">
                            <span class="info-label">Adresse:</span>
                            <span class="info-value"><?= htmlspecialchars($beneficiaire['Adresse']) ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Région:</span>
                            <span class="info-value"><?= htmlspecialchars($beneficiaire['Region']) ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Département:</span>
                            <span class="info-value"><?= htmlspecialchars($beneficiaire['Departement']) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Commune:</span>
                            <span class="info-value"><?= htmlspecialchars($beneficiaire['Commune']) ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Colonne de droite - Informations d'affiliation -->
            <div class="col-lg-7">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="bi bi-file-earmark-medical-fill me-2"></i>Informations d'affiliation
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <span class="info-label">Régime:</span>
                                    <span class="info-value"><?= htmlspecialchars($beneficiaire['Regime']) ?></span>
                                </div>
                                
                                <div class="info-item">
                                    <span class="info-label">Assureur:</span>
                                    <span class="info-value"><?= htmlspecialchars($beneficiaire['Assureur']) ?></span>
                                </div>
                                
                                <div class="info-item">
                                    <span class="info-label">Type d'adhésion:</span>
                                    <span class="info-value"><?= htmlspecialchars($beneficiaire['Type_Adhesion']) ?></span>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="info-item">
                                    <span class="info-label">Type de bénéficiaire:</span>
                                    <span class="info-value"><?= htmlspecialchars($beneficiaire['Type_Beneficiaire']) ?></span>
                                </div>
                                
                                <div class="info-item">
                                    <span class="info-label">Groupe d'appartenance:</span>
                                    <span class="info-value"><?= htmlspecialchars($beneficiaire['Groupe']) ?></span>
                                </div>
                                
                                <div class="info-item">
                                    <span class="info-label">Type de cotisation:</span>
                                    <span class="info-value"><?= htmlspecialchars($beneficiaire['Type_Cotisation']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="bi bi-calendar-check-fill me-2"></i>Période de couverture
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <span class="info-label">Date de cotisation:</span>
                                    <span class="info-value"><?= formatDate($beneficiaire['Date_Cotisation']) ?></span>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="info-item">
                                    <span class="info-label">Date de fin de cotisation:</span>
                                    <span class="info-value"><?= formatDate($beneficiaire['Date_Fin_Cotisation']) ?></span>
                                </div>
                            </div>
                        </div>
                        
                            <div class="progress mt-4" style="height: 10px;">
                                    <?php
                                    if (!empty($beneficiaire['Date_Cotisation']) && !empty($beneficiaire['Date_Fin_Cotisation'])) {
                                        $dateDebut = new DateTime($beneficiaire['Date_Cotisation']);
                                        $dateFin = new DateTime($beneficiaire['Date_Fin_Cotisation']);
                                        $aujourdhui = new DateTime();

                                        $totalDays = $dateFin->diff($dateDebut)->days ?: 1; // évite division par zéro
                                        $daysPassed = $aujourdhui->diff($dateDebut)->invert ? $aujourdhui->diff($dateDebut)->days : 0;
                                        $percentage = min(100, max(0, ($daysPassed / $totalDays) * 100));

                                        // Choix de la couleur
                                        if ($percentage < 50) {
                                            $barClass = 'bg-success';
                                        } elseif ($percentage < 80) {
                                            $barClass = 'bg-warning';
                                        } else {
                                            $barClass = 'bg-danger';
                                        }
                                    } else {
                                        $percentage = 0;
                                        $barClass = 'bg-secondary';
                                    }
                                    ?>
                                   <div class="progress-bar <?= $barClass ?>" role="progressbar"
                                        style="width: <?= $percentage ?>%"
                                        aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100">
                                    </div>
                            </div>
                                <small class="text-muted">Progression de la période de couverture</small>

                                <?= $avertissement ?>
                    </div>
                </div>
                
                <!-- <div class="card">
                    <div class="card-header">
                        <i class="bi bi-qr-code me-2"></i>Carte d'assuré
                    </div>
                    <div class="card-body text-center">
                        <img src="<?= htmlspecialchars($beneficiaire['qr_code_url']) ?>" class="qr-code img-fluid" alt="QR Code">
                        <p class="mt-3 mb-0 text-muted">Présentez ce code pour accéder à vos services</p>
                    </div> 
                </div> -->
            </div>
        </div>
        
        
    </div>

    <div class="countdown" id="countdown">
        <i class="bi bi-clock-fill me-2"></i>Fermeture dans : <span id="time">60</span>s
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    // Compte à rebours
    let timeLeft = 60;
    <?php if ($percentage >= 70 && $percentage < 100): ?>
        Swal.fire({
            title: 'Alerte couverture',
            html: 'La couverture de <b><?= htmlspecialchars($beneficiaire['Prenom'] . ' ' . $beneficiaire['Nom']) ?></b> approche de son terme.',
            icon: 'warning',
            timer: 8000,
           confirmButtonText: 'OK'
        });
        <?php endif; ?>

    const countdown = setInterval(() => {
        timeLeft--;
        document.getElementById('time').textContent = timeLeft;
        
        if (timeLeft <= 0) {
            clearInterval(countdown);
            Swal.fire({
                title: 'Session terminée',
                html: 'La fiche de <b><?= htmlspecialchars($beneficiaire['Prenom'] . ' ' .$beneficiaire['Nom']) ?></b> sera maintenant fermée',
                icon: 'info',
                confirmButtonText: 'OK'
            }).then(() => {
                //window.location.href = 'accueil.php';
                // Fermer l'onglet
            window.close();
            });
        }
    }, 1000);
    
    // Permet d'annuler la fermeture automatique si l'utilisateur interagit
    document.addEventListener('click', function() {
        timeLeft = 60;
        document.getElementById('time').textContent = timeLeft;
    });
    </script>
</body>
</html>