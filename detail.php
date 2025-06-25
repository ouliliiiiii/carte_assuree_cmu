<?php
require_once 'db.php';

if (isset($_GET['code'])) 
{
    $code = $_GET['code'];

    $stmt = $pdo->prepare("SELECT * FROM beneficiaires WHERE Code_Immatriculation = :code");
    $stmt->execute([':code' => $code]);
    $beneficiaire = $stmt->fetch(PDO::FETCH_ASSOC);

    //Changement de l'état en fonction de la cotisation
            $etat = '';
        $aujourdhui = new DateTime();

        if ($beneficiaire) {
            $dateDebut = new DateTime($beneficiaire['Date_Cotisation']);
            $dateFin = new DateTime($beneficiaire['Date_Fin_Cotisation']);

            if ($aujourdhui >= $dateDebut && $aujourdhui <= $dateFin) {
                $etat = '<span class="badge bg-success">Actif</span>';
            } else {
                $etat = '<span class="badge bg-danger">Expiré</span>';
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
    <title>Détails du bénéficiaire - <?= htmlspecialchars($beneficiaire['Nom'] . ' ' .$beneficiaire['Prenom'] ) ?></title>
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
    <style>
        .countdown {
            position: fixed;
            bottom: 15px;
            right: 20px;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
        <header class="navbar ">
            <div class="col-lg-4" >
                 <img src="images/Logosen.png" style="width: 300px;">   
            </div>

      </header>

        <div class="container mt-5">
            <div class="row">
              
                    <div class="title-group mb-3">
                        <div class="row">
                            <h1 class=" col-lg-6 h2 mb-0">Détails du bénéficiaire <?= htmlspecialchars($beneficiaire['Prenom'] . ' ' .$beneficiaire['Nom'] ) ?></h1>
                            <h1 class="col-lg-6 h2 mb-0">État: <?= $etat ?></h1>
                        </div>
                    </div>

                    <?php if ($beneficiaire): ?>
                    <div class="row my-4">
                        <div class="col-lg-5 col-12">
                            <div class="custom-block ">
                                
                                    <div class="col-lg-12 col-12 mb-3">
                                        <h6>Informations Personnelles </h6>
                                    </div>

                                    <div class="col-lg-3 col-12 mb-4 mb-lg-0 mt-4">
                                        <div class="custom-block-profile-image-wrap">
                                            <img src="images/medium-shot-happy-man-smiling.jpg" class="custom-block-profile-image img-fluid" alt="">

                                        </div>
                                    </div>

                                    <div class="col-lg-9 col-12 mt-4">
                                         
                                        <p class="d-flex flex-wrap mb-2">
                                            <strong>Code Bénéficiaire:</strong>
                                            <span class="span"><?= htmlspecialchars($beneficiaire['Code_Immatriculation']) ?></span>
                                        </p>

                                        <p class="d-flex flex-wrap mb-2">
                                            <strong>Nom:</strong> 
                                            <span class="span"><?= htmlspecialchars($beneficiaire['Nom']) ?></span>
                                        </p>

                                        <p class="d-flex flex-wrap mb-2">
                                            <strong>Prénom:</strong>
                                            <span class="span"><?= htmlspecialchars($beneficiaire['Prenom']) ?></span>
                                        </p>

                                        <p class="d-flex flex-wrap mb-2">
                                            <strong>Sexe:</strong>
                                            <span class="span"><?= htmlspecialchars($beneficiaire['Sexe']) ?></span>
                                        </p>
                                        
                                        <p class="d-flex flex-wrap mb-2">
                                            <strong>Téléphone:</strong>
                                            <span class="span"><?= htmlspecialchars($beneficiaire['Telephone']) ?></span>
                                        </p>

                                        <p class="d-flex flex-wrap">
                                            <strong>Adresse:</strong>
                                            <span class="span"><?= htmlspecialchars($beneficiaire['Adresse']) ?></span>
                                        </p>
                                        <p class="d-flex flex-wrap">
                                            <strong>Region:</strong>
                                            <span class="span"><?= htmlspecialchars($beneficiaire['Region']) ?></span>
                                        </p>
                                        <p class="d-flex flex-wrap">
                                            <strong>Departement:</strong>
                                            <span class="span"><?= htmlspecialchars($beneficiaire['Departement']) ?></span>
                                        </p>
                                    </div>
                                
                            </div>
                        </div>

                        <div class="col-lg-7 col-12 ">
                            <div class="custom-block custom-block-contact">
                                <h6 class="mb-4">Informations d’affiliation</h6>

                                <p class="d-flex flex-wrap">
                                    <strong>Régime:</strong> 
                                    <span class="span"><?= htmlspecialchars($beneficiaire['Regime']) ?></span>
                                </p>

                                <p class="d-flex flex-wrap">
                                    <strong>Assureur:</strong>
                                    <span class="span"><?= htmlspecialchars($beneficiaire['Assureur']) ?></span>
                                </p>

                                <p class="d-flex flex-wrap">
                                    <strong>Type de Bénéficiaire:</strong>
                                    <span class="span"><?= htmlspecialchars($beneficiaire['Type_Beneficiaire']) ?></span>
                                </p>
                            </div>
                             <div class="custom-block custom-block-contact">
                                <h6 class="mb-4">Informations de cotisation</h6>

                                <p class="d-flex flex-wrap">
                                    <strong>Date de cotisation:</strong> 
                                    <span class="span"><?= htmlspecialchars($beneficiaire['Date_Cotisation']) ?></span>
                                </p>

                                <p class="d-flex flex-wrap">
                                    <strong>Date de Fin de Cotisation:</strong>
                                    <span class="span"><?= htmlspecialchars($beneficiaire['Date_Fin_Cotisation']) ?></span>
                                </p>
                            </div>
                        </div>
                    </div>

                  
             

            </div>
        </div>

       



     <!--   <a href="Accueil.php" class="btn btn-secondary mt-3">Retour</a>  -->
    
    
        <?php else: ?>
            <div class="alert alert-danger">Bénéficiaire non trouvé.</div>
            <a href="Accueil.php" class="btn btn-secondary mt-3">Retour</a>
    <?php endif; ?>

    <div class="countdown" id="countdown">
        Fermeture automatique dans : <span id="time">60</span>s
    </div>

    <script>
    // Compte à rebours
    let timeLeft = 60;
    const countdown = setInterval(() => {
        timeLeft--;
        document.getElementById('time').textContent = timeLeft;
        
        if (timeLeft <= 0) {
            clearInterval(countdown);
            Swal.fire({
                title: 'Merci de votre visite',
                text: 'Vous avez consulté le dossier <?= $code ?>',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                //window.location.href = 'accueil.php';
                // Fermer l'onglet
            window.close();
            
            // Solution de repli si window.close() ne fonctionne pas
            window.location.href = 'about:blank';
            });
        }
    }, 1000);
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
       <!-- JAVASCRIPT templates -->
        <script src="js/jquery.min.js"></script>
        <script src="js/bootstrap.bundle.min.js"></script>
        <script src="js/custom.js"></script>
        <!-- JAVASCRIPT templates -->
</body>
</html>
