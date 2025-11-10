<?php
require_once 'db.php';
require_once 'audit.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['ids']) || !is_array($data['ids'])) {
    echo json_encode(["success" => false, "message" => "Aucune sélection reçue"]);
    exit;
}

$ids = array_map('intval', $data['ids']);
$placeholders = implode(',', array_fill(0, count($ids), '?'));

$sql = "DELETE FROM beneficiaires WHERE id IN ($placeholders)";
$stmt = $pdo->prepare($sql);
$success = $stmt->execute($ids);

if ($success) {
    $actor = $_SESSION['user'] ?? 'system';
    $details = 'Suppression multiple beneficiaires ids: ' . implode(',', $ids);
    log_action($pdo, $actor, 'suppression_multiple', null, $details);
}

echo json_encode(["success" => $success]);
