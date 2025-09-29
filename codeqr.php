<?php
require_once 'header.php';
require_once 'db.php';

$ip_pc = '10.100.226.172/Carte_PROD/'; // Remplace par l’IP locale de ton PC
//$ip_pc = 'localhost/Carte_PROD/'; // IP locale
$code = $_GET['code'] ?? '';
$url = '';

if ($code !== '') 
{
    $url = "http://$ip_pc/detail.php?code=" . urlencode($code);
    // Dans accueil.php ou une autre page
    echo "<script>window.open('$url', '_blank');</script>";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>QR Code - <?= htmlspecialchars($code) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Style CSS interne -->
    <style>
        .retour-accueil {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
    </style>
     <!-- Lien vers le fichier CSS -->
     <link rel="stylesheet" href="style.css">
</head>
<body>

       
        <div class="container "> 
            <!-- Section Actions -->
            <div class="card ">
                <div class="card-body " >

                    <?php if ($url): ?>
                        <div class="row ">
                            <div class="col-lg-12 d-flex justify-content-center">
                                <h1>QR Code pour le code : <strong><?= htmlspecialchars($code) ?></strong></h1>
                            </div>
                            <div class="col-lg-12 d-flex justify-content-center">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?data=<?= urlencode($url) ?>&size=300x300" alt="QR Code" class="my-3" />
                            </div>
                            <div class="col-lg-12 d-flex justify-content-center">
                                 <p>En scannant ce QR code, vous accéderez à :</p>
                                 <a href="<?= htmlspecialchars($url) ?>" target="_blank"><?= htmlspecialchars($url) ?></a> <br>
                            </div>
                             <div class="col-lg-12 d-flex justify-content-end">
                                 <a href="accueil.php" class="btn btn-secondary">Retour à l'accueil</a>
                            </div>
                            <div class="col-lg-12 d-flex justify-content-center">
                                <?php else: ?>
                                    <div class="alert alert-danger">Aucun code fourni !</div>
                                    <a href="accueil.php" class="btn btn-secondary">Retour à l'accueil</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                </div>
            </div>
        </div>
</body>
</html>
