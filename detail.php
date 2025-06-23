<?php
require_once 'db.php';

if (isset($_GET['code'])) 
{
    $code = $_GET['code'];

    $stmt = $pdo->prepare("SELECT * FROM beneficiaires WHERE Code_Immatriculation = :code");
    $stmt->execute([':code' => $code]);
    $beneficiaire = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    die("Aucun code fourni.");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détail bénéficiaire</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
      <!-- CSS Templates --> 
        <link href="css/bootstrap.min.css" rel="stylesheet">

        <link href="css/bootstrap-icons.css" rel="stylesheet">

        <link href="css/tooplate-mini-finance.css" rel="stylesheet">
        <!-- CSS Templates -->  
     

</head>
<body>
        <header class="navbar ">
            <div class="col-lg-4" >
                 <img src="images/Logosen.png" style="width: 350px;">   
            </div>
             <div class="col-lg-4" >
                 <img src="images/Logo.png"style="width: 350px;" >   
            </div>
      </header>

        <div class="container mt-5">
            <div class="row">
              
                    <div class="title-group mb-3">
                        <div class="row">
                            <h1 class=" col-lg-6 h2 mb-0">Détails du bénéficiaire</h1>
                            <h1 class="col-lg-6 h2 mb-0">Etat:Active</h1>
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

       



        <a href="Accueil.php" class="btn btn-secondary mt-3">Retour</a>
    
    
        <?php else: ?>
            <div class="alert alert-danger">Bénéficiaire non trouvé.</div>
            <a href="Accueil.php" class="btn btn-secondary mt-3">Retour</a>
    <?php endif; ?>
       <!-- JAVASCRIPT templates -->
        <script src="js/jquery.min.js"></script>
        <script src="js/bootstrap.bundle.min.js"></script>
        <script src="js/custom.js"></script>
        <!-- JAVASCRIPT templates -->
</body>
</html>
