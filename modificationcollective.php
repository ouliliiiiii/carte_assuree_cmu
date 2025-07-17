<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ids'])) {
    $ids = json_decode($_POST['ids'], true);
    $modification_effectuee = false;

    // === Mise à jour des champs texte ===
    $fields = [];
    $params = [];

    if (!empty($_POST['Region'])) {
        $fields[] = "Region = ?";
        $params[] = $_POST['Region'];
    }
    if (!empty($_POST['Departement'])) {
        $fields[] = "Departement = ?";
        $params[] = $_POST['Departement'];
    }
    if (!empty($_POST['Commune'])) {
        $fields[] = "Commune = ?";
        $params[] = $_POST['Commune'];
    }

    if (!empty($fields) && !empty($ids)) {
        $sql = "UPDATE beneficiaires SET " . implode(', ', $fields) .
               " WHERE id IN (" . implode(',', array_fill(0, count($ids), '?')) . ")";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_merge($params, $ids));

        if ($stmt->rowCount() > 0) {
            $modification_effectuee = true;
        }
    }

    // === Upload des photos ===
    if (!empty($_FILES['photo']['name'][0])) {
        $uploadDir = __DIR__ . '/uploads/photos/'; // chemin absolu
        $webPath = 'uploads/photos/'; // pour enregistrer dans la BDD et afficher dans <img>

        // Créer le dossier s'il n'existe pas
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                die("Erreur : Impossible de créer le dossier $uploadDir");
            }
        }

        foreach ($_FILES['photo']['tmp_name'] as $index => $tmpName) {
            $originalName = $_FILES['photo']['name'][$index];
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $filename = pathinfo($originalName, PATHINFO_FILENAME);

            // Vérifie l'extension
            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'])) {
                continue;
            }

            // Extraire l'ID du nom de fichier
            preg_match('/\d+/', $filename, $matches);
            $idFromFile = isset($matches[0]) ? (int)$matches[0] : null;

            // Vérifie que l'ID est dans la sélection
            if ($idFromFile && in_array($idFromFile, $ids)) {
                $newFileName = $idFromFile . '.' . $extension;
                $fullPath = $uploadDir . $newFileName;
                $relativePath = $webPath . $newFileName;

                if (move_uploaded_file($tmpName, $fullPath)) {
                    $stmtPhoto = $pdo->prepare("UPDATE beneficiaires SET photo = ? WHERE id = ?");
                    $stmtPhoto->execute([$relativePath, $idFromFile]);

                   
                    $modification_effectuee = true;
                    
                }
            }
        }
    }

    // === Redirection finale
    header("Location: accueil.php?modification=" . ($modification_effectuee ? "success" : "none"));
    exit;
}
