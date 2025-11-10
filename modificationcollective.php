<?php
require 'db.php';
require 'audit.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ids'])) {
    $ids = json_decode($_POST['ids'], true);
    $modification_effectuee = false;

    // Champs à mettre à jour (si remplis)
    $champs_possible = [
        'Region',
        'Departement',
        'Commune',
        'type_adhesion',
        'assureur',
        'regime',
        'type_beneficiaire',
        'groupe',
        'type_cotisation',
        'date_cotisation',
        'date_fin_cotisation'
    ];

    $fields = [];
    $params = [];

    foreach ($champs_possible as $champ) {
        if (isset($_POST[$champ]) && $_POST[$champ] !== '') {
            $fields[] = "$champ = ?";
            $params[] = $_POST[$champ];
        }
    }

    // S'il y a des champs à mettre à jour et des IDs
    if (!empty($fields) && !empty($ids)) {
        $sql = "UPDATE beneficiaires SET " . implode(', ', $fields) .
               " WHERE id IN (" . implode(',', array_fill(0, count($ids), '?')) . ")";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_merge($params, $ids));

        if ($stmt->rowCount() > 0) {
            $modification_effectuee = true;
            // Log modifications collectives
            $actor = $_SESSION['user'] ?? 'system';
            $detailsText = 'modification collective beneficiaires ids: ' . implode(',', $ids);
            log_action($pdo, $actor, 'modification', null, $detailsText);
        }
    }

    // === Upload des photos ===
    if (!empty($_FILES['photo']['name'][0])) {
        $uploadDir = __DIR__ . '/uploads/photos/';
        $webPath = 'uploads/photos/';

        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                die("Erreur : Impossible de créer le dossier $uploadDir");
            }
        }

        foreach ($_FILES['photo']['tmp_name'] as $index => $tmpName) {
            $originalName = $_FILES['photo']['name'][$index];
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $filename = pathinfo($originalName, PATHINFO_FILENAME);

            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'])) {
                continue;
            }

            preg_match('/\d+/', $filename, $matches);
            $idFromFile = isset($matches[0]) ? (int)$matches[0] : null;

            if ($idFromFile && in_array($idFromFile, $ids)) {
                $newFileName = $idFromFile . '.' . $extension;
                $fullPath = $uploadDir . $newFileName;
                $relativePath = $webPath . $newFileName;

                    if (move_uploaded_file($tmpName, $fullPath)) {
                    $stmtPhoto = $pdo->prepare("UPDATE beneficiaires SET photo = ? WHERE id = ?");
                    $stmtPhoto->execute([$relativePath, $idFromFile]);
                    $modification_effectuee = true;
                    // Journaliser l'upload de la photo : récupérer le nom et prénom du bénéficiaire pour construire la cible lisible
                    $actor = $_SESSION['user'] ?? 'system';
                    $s = $pdo->prepare('SELECT Nom, Prenom, Code_Immatriculation FROM beneficiaires WHERE id = ?');
                    $s->execute([$idFromFile]);
                    $b = $s->fetch(PDO::FETCH_ASSOC);
                    $target = $b ? trim(($b['Nom'] ?? '') . ' ' . ($b['Prenom'] ?? '')) : $idFromFile;
                    $code = $b['Code_Immatriculation'] ?? $idFromFile;
                    $detailsText = 'modification photo beneficiaire ' . $code;
                    log_action($pdo, $actor, 'modification', $target, $detailsText);
                }
            }
        }
    }

    // Redirection
    header("Location: accueil.php?modification=" . ($modification_effectuee ? "success" : "none"));
    exit;
}
?>
