<?php
require_once 'db.php';

// Récupérer les paramètres de filtre
$filtreType = isset($_GET['type_beneficiaire']) ? $_GET['type_beneficiaire'] : '';
$filtreRegime = isset($_GET['regime']) ? $_GET['regime'] : '';

// Construire la requête SQL de base avec les colonnes souhaitées
$query = "SELECT 
            id,
            Code_Immatriculation,
            Nom,
            Prenom,
            Date_Naissance,
            Sexe,
            Telephone,
            Adresse,
            Regime,
            Assureur,
            Type_Beneficiaire,
            Date_Cotisation,
            Date_Fin_Cotisation,
            qr_code_url,
            Region, 
            Departement, 
            Groupe, 
            Type_Adhesion, 
            Type_Cotisation,  
            CNI
          FROM beneficiaires
          WHERE 1=1";

// Ajouter les conditions de filtre
$params = [];
if (!empty($filtreType)) {
    $query .= " AND Type_Beneficiaire = ?";
    $params[] = $filtreType;
}

if (!empty($filtreRegime)) {
    $query .= " AND Regime = ?";
    $params[] = $filtreRegime;
}

// Préparation et exécution de la requête
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$beneficiaires = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($beneficiaires) === 0) {
    die("Aucun bénéficiaire à exporter avec les critères sélectionnés");
}

// Générer un nom de fichier significatif
$filename = 'beneficiaires_export_' . date('Y-m-d_H-i');
if (!empty($filtreType)) {
    $filename .= '_' . str_replace(' ', '_', $filtreType);
}
if (!empty($filtreRegime)) {
    $filename .= '_' . str_replace(' ', '_', $filtreRegime);
}

// Entêtes HTTP pour forcer le téléchargement
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename . '.csv');

// Création du fichier CSV en sortie
$output = fopen('php://output', 'w');

// Ajout du BOM UTF-8 pour une meilleure compatibilité avec Excel
fwrite($output, "\xEF\xBB\xBF");

// Entêtes du CSV (noms de colonnes)
$entetes = [
    'ID',
    'Code Immatriculation',
    'Nom',
    'Prénom',
    'Date Naissance',
    'Sexe',
    'Téléphone',
    'Adresse',
    'Régime',
    'Assureur',
    'Type Bénéficiaire',
    'Date Cotisation',
    'Date Fin Cotisation',
    'QR Code URL',
    'Region', 
    'Departement', 
    'Groupe', 
    'Type_Adhesion', 
    'Type_Cotisation',  
    'CNI'
];
fputcsv($output, $entetes, ';');

// Données des bénéficiaires
foreach ($beneficiaires as $beneficiaire) {
    // Formatage des dates pour une meilleure lisibilité
    $beneficiaire['Date_Naissance'] = date('d/m/Y', strtotime($beneficiaire['Date_Naissance']));
    $beneficiaire['Date_Cotisation'] = date('d/m/Y', strtotime($beneficiaire['Date_Cotisation']));
    $beneficiaire['Date_Fin_Cotisation'] = date('d/m/Y', strtotime($beneficiaire['Date_Fin_Cotisation']));
    
    fputcsv($output, $beneficiaire, ';');
}

fclose($output);
exit;
?>