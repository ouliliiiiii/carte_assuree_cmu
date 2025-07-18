<?php
require 'db.php';

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
    } else {
        $stmt = $pdo->prepare("UPDATE beneficiaires SET photo = ? WHERE Code_Immatriculation = ?");
        $stmt->execute([$destination, $code]);
    }

    $response['success'] = true;
    $response['photo_url'] = $destination;
} else {
    $response['message'] = 'Erreur lors du téléchargement.';
}

echo json_encode($response);
