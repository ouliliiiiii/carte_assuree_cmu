<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<header class="header" style="padding: 10px 0; background-color: #f8f9fa; border-bottom: 1px solid #ddd;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <a href="accueil.php">
                    <img src="images/Logosen.png" class="logo" alt="Logo SENCSU" style="max-height: 50px;">
                </a>
                
            </div>
            <div class="col-md-6 text-end">
                <span class="text-muted"><?= date('d/m/Y H:i') ?></span>
                <?php if (isset($_SESSION['user'])): ?>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm ms-3">Se déconnecter</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>
