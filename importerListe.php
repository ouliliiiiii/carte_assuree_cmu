<?php
// importerliste.php
session_start();

// On récupère les positions de colonnes si elles ont été enregistrées
$positions = $_SESSION['colonnes_positions'] ?? [];
?>
<?php if (isset($_SESSION['import_message'])): ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const status = <?= json_encode($_SESSION['import_status'] ?? 'info') ?>;
        const rawMessage = <?= json_encode($_SESSION['import_message']) ?>;
        
        if (status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Importation réussie',
                text: rawMessage,
                confirmButtonColor: '#3085d6',
            });
        } else if (status === 'error') {
            // Si le message est une chaîne avec des sauts de ligne => afficher liste
            const lignes = rawMessage.split('\n').filter(l => l.trim() !== '');

            Swal.fire({
                icon: 'error',
                title: 'Erreurs lors de l\'importation',
                html: '<ul style="text-align:left;">' + lignes.map(l => `<li>${l}</li>`).join('') + '</ul>',
                confirmButtonColor: '#d33',
                width: '60%',
            });
        }
    });
</script>
<?php endif; ?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Importer une liste de bénéficiaires</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="new_style.css">
    <style>
        .popup-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        .popup-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-height: 90vh;
            overflow-y: auto;
            width: 80%;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="container mb-5">


    <!-- Section parametre -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6 mb-3 mb-md-0">
                    <h5 class="mb-0">Importer une liste de bénéficiaires</h5>
                </div>
                <div class="col-md-6 text-md-end">
                    <button class="btn btn-secondary" onclick="openPopup('paramPopup')">
                        <i class="bi bi-gear me-2"></i> Paramètres d'importation
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerte -->
    <div class="alert alert-info">
        <i class="bi bi-info-circle me-2"></i>
        Assurez-vous que votre fichier respecte le format requis avant l'importation.
    </div>

    <!-- Formulaire import -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="bi bi-funnel"></i> Sélectionner un fichier
        </div>
        <div class="card-body">
            <form action="import_handler.php" method="post" enctype="multipart/form-data" class="row g-3 align-items-end" id="formImport">
                <div class="col-lg-12">
                    <label class="form-label">Fichier</label>
                    <input class="form-control" type="file" id="fichier_import" name="fichier_import" accept=".xlsx,.xls,.csv" required>
                    <div class="form-text">Formats acceptés: Excel (.xlsx, .xls) ou CSV - Max 5MB</div>
                </div>
                <div class="col-lg-12 d-flex justify-content-end">
                    <a href="accueil.php" type="button" class="btn btn-outline-secondary me-2">Retour</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload me-2"></i> Importer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Popup Paramètres -->
    <div id="paramPopup" class="popup-overlay">
        <div class="popup-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0"><i class="bi bi-gear me-2"></i> Paramètres d'importation</h4>
                <button type="button" class="btn-close" onclick="closePopup('paramPopup')"></button>
            </div>

            <form method="post" onsubmit="event.preventDefault(); submitParamForm(this);">
                <p>Indiquez la position des champs (colonne Excel, ex: 1 pour A, 2 pour B, etc.)</p>
                <div class="row">
                    <?php 
                    $champs = [
                        "Nom", "Prenom", "Date_Naissance", "Sexe", "Telephone", "Adresse", "Regime", 
                        "Assureur", "Type_Beneficiaire", "Date_Cotisation", 
                        "Region", "Departement", "Commune", "Groupe", 
                        "Type_Adhesion", "Type_Cotisation", "CNI"
                    ];
                    foreach ($champs as $champ): ?>
                        <div class="col-md-3 mb-3">
                            <label for="pos_<?= htmlspecialchars($champ) ?>" class="form-label"><?= htmlspecialchars($champ) ?></label>
                            <input
                                type="number"
                                class="form-control"
                                id="pos_<?= htmlspecialchars($champ) ?>"
                                name="positions[<?= htmlspecialchars($champ) ?>]"
                                min="1"
                                placeholder="Num col"
                                value="<?= isset($positions[$champ]) ? (int)$positions[$champ] : '' ?>"
                            >
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <button type="button" class="btn btn-outline-secondary me-2" onclick="closePopup('paramPopup')">Retour</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function openPopup(id) {
        const popup = document.getElementById(id);
        if (popup) {
            popup.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    function closePopup(id) {
        const popup = document.getElementById(id);
        if (popup) {
            popup.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    }

  function submitParamForm(form) {
    const formData = new FormData(form);
    const positions = {};

    formData.forEach((value, key) => {
        const match = key.match(/^positions\[(.+)\]$/);
        if (match) {
            const champ = match[1];
            const num = parseInt(value);
            if (!isNaN(num)) {
                positions[champ] = num;
            }
        }
    });

   console.log("JSON envoyé :", JSON.stringify({ positions: positions }));
// 👈 DEBUG

    fetch('sauvegarde_colonnes.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ positions: positions })
    })
    .then(res => res.text())
    .then(text => {
        console.log("Réponse reçue :", text); // 👈 DEBUG

        if (text.trim() === 'OK') {
            Swal.fire({
                icon: 'success',
                title: 'Succès',
                text: 'Paramètres enregistrés avec succès.',
                confirmButtonColor: '#3085d6',
            }).then(() => {
                closePopup('paramPopup');
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: 'Erreur : ' + text,
                confirmButtonColor: '#d33',
            });
        }
    })
    .catch(err => {
        Swal.fire({
            icon: 'error',
            title: 'Erreur réseau',
            text: err.toString(),
            confirmButtonColor: '#d33',
        });
    });
}


    // Fermeture des popups au clic en dehors
    ['paramPopup'].forEach(id => {
        const popup = document.getElementById(id);
        if (popup) {
            popup.addEventListener('click', function(e) {
                if (e.target === this) closePopup(id);
            });
        }
    });
</script>
</body>
</html>
