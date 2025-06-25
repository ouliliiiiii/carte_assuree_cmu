<?php
// importerliste.php
//session_start();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importer une liste de bénéficiaires</title>
    <style>
        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border: 1px solid #ccc;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
            width: 500px;
            max-width: 90%;
        }
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }
        .button {
            display: inline-block;
            padding: 10px 15px;
            margin: 10px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            cursor: pointer;
            border: none;
        }
        .button:hover {
            background: #45a049;
        }
        .button.secondary {
            background: #f44336;
        }
        .button.secondary:hover {
            background: #d32f2f;
        }
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .success {
            background: #dff0d8;
            color: #3c763d;
        }
        .error {
            background: #f2dede;
            color: #a94442;
        }
    </style>
</head>
<body>
    <!-- Bouton pour ouvrir le popup -->
    <button class="button" onclick="openPopup()">Importer une liste</button>

    <!-- Overlay -->
    <div id="overlay" class="overlay" onclick="closePopup()"></div>

    <!-- Popup d'importation -->
    <div id="importPopup" class="popup">
        <h2>Importer une liste de bénéficiaires</h2>
        
        <?php if (isset($_SESSION['import_message'])): ?>
            <div class="message <?php echo $_SESSION['import_status']; ?>">
                <?php 
                echo $_SESSION['import_message']; 
                unset($_SESSION['import_message']);
                unset($_SESSION['import_status']);
                ?>
            </div>
        <?php endif; ?>
        
        <div style="margin-bottom: 20px;">
            <a href="download_template.php" class="button">Télécharger le template Excel</a>
            <p style="font-size: 0.9em; color: #666;">Téléchargez notre modèle Excel pré-formaté pour faciliter l'importation.</p>
        </div>
        
        <form method="post" action="import_handler.php" enctype="multipart/form-data">
            <h3>Charger un fichier</h3>
            <input type="file" name="fichier_import" accept=".xls,.xlsx,.csv" required>
            <p style="font-size: 0.9em; color: #666;">Formats acceptés : .xls, .xlsx, .csv (max 2MB)</p>
            
            <div style="margin-top: 20px;">
                <button type="submit" class="button">Importer le fichier</button>
                <button type="button" class="button secondary" onclick="closePopup()">Annuler</button>
            </div>
        </form>
    </div>

    <script>
        function openPopup() {
            document.getElementById('importPopup').style.display = 'block';
            document.getElementById('overlay').style.display = 'block';
        }
        
        function closePopup() {
            document.getElementById('importPopup').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        }
        
        // Fermer le popup si on clique en dehors
        window.onclick = function(event) {
            if (event.target == document.getElementById('overlay')) {
                closePopup();
            }
        }
    </script>
</body>
</html>