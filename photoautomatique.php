<?php
require 'db.php';
require 'audit.php';

$response = ['success' => false, 'message' => '', 'photo_url' => ''];

$photo = $_FILES['photo'] ?? null;
$id = isset($_POST['id']) ? (int)$_POST['id'] : null;
$code = $_POST['code'] ?? null;

if (!$photo || (!$id && !$code)) {
    $response['message'] = 'Paramètres manquants.';
    echo json_encode($response);
    exit;
}

$uploadDir = 'uploads/photos/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$extension = strtolower(pathinfo($photo['name'], PATHINFO_EXTENSION));
if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'])) {
    $response['message'] = 'Format non autorisé.';
    echo json_encode($response);
    exit;
}

if ($id) {
    $newFilename = $id . '.' . $extension;
} else {
    $newFilename = uniqid('photo_') . '.' . $extension;
}

$destination = $uploadDir . $newFilename;

    if (move_uploaded_file($photo['tmp_name'], $destination)) {
    if ($id) {
        $stmt = $pdo->prepare("UPDATE beneficiaires SET photo = ? WHERE id = ?");
        $stmt->execute([$destination, $id]);
            // Récupérer le bénéficiaire pour construire une cible lisible (nom et prénom)
            $s = $pdo->prepare('SELECT Nom, Prenom, Code_Immatriculation FROM beneficiaires WHERE id = ?');
            $s->execute([$id]);
            $b = $s->fetch(PDO::FETCH_ASSOC);
            $target = $b ? trim(($b['Nom'] ?? '') . ' ' . ($b['Prenom'] ?? '')) : $id;
            $codeFound = $b['Code_Immatriculation'] ?? $id;
            $detailsText = 'modification photo beneficiaire ' . $codeFound;
            log_action($pdo, $_SESSION['user'] ?? 'system', 'modification', $target, $detailsText);
    } else {
        $stmt = $pdo->prepare("UPDATE beneficiaires SET photo = ? WHERE Code_Immatriculation = ?");
        $stmt->execute([$destination, $code]);
            // Récupérer le bénéficiaire pour construire une cible lisible (nom et prénom)
            $s = $pdo->prepare('SELECT Nom, Prenom FROM beneficiaires WHERE Code_Immatriculation = ?');
            $s->execute([$code]);
            $b = $s->fetch(PDO::FETCH_ASSOC);
            $target = $b ? trim(($b['Nom'] ?? '') . ' ' . ($b['Prenom'] ?? '')) : $code;
            $detailsText = 'modification photo beneficiaire ' . $code;
            log_action($pdo, $_SESSION['user'] ?? 'system', 'modification', $target, $detailsText);
    }

    $response['success'] = true;
    $response['photo_url'] = $destination;
} else {
    $response['message'] = 'Erreur lors du téléchargement.';
}

echo json_encode($response);
