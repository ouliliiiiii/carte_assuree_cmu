<?php
session_start();

// Lire le JSON brut envoyé
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

// DEBUG : log les données reçues
if (!$data) {
    http_response_code(400);
    echo "Données JSON non valides : " . $rawInput;
    exit;
}

if (!isset($data['positions']) || !is_array($data['positions'])) {
    http_response_code(400);
    echo "Paramètres manquants pannnnnnnnnnnnnnnnnnnnnnnnnnnnnnn. Contenu reçu : " . json_encode($data);
    exit;
}

$_SESSION['colonnes_positions'] = $data['positions'];

echo "OK";
