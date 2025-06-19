<?php
$ip_pc = '10.100.226.203'; // Remplace par l’IP locale de ton PC
$code = $_GET['code'] ?? '';
$url = '';

if ($code !== '') {
    $url = "http://$ip_pc/QR/detail.php?code=" . urlencode($code);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>QR Code - <?= htmlspecialchars($code) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container text-center mt-5">

    <?php if ($url): ?>
        <h1>QR Code pour le code : <strong><?= htmlspecialchars($code) ?></strong></h1>
        <img src="https://api.qrserver.com/v1/create-qr-code/?data=<?= urlencode($url) ?>&size=300x300" alt="QR Code" class="my-3" />
        <p>En scannant ce QR code, vous accéderez à :</p>
        <a href="<?= htmlspecialchars($url) ?>" target="_blank"><?= htmlspecialchars($url) ?></a>
    <?php else: ?>
        <div class="alert alert-danger">Aucun code fourni !</div>
        <a href="accueil.php" class="btn btn-secondary">Retour à l'accueil</a>
    <?php endif; ?>

</div>
</body>
</html>
