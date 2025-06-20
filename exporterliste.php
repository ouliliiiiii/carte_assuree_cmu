<?php
// Connexion à la base de données
//try {
  //  $conn = new PDO("mysql:host=localhost;dbname=votre_base_de_donnees", "root", "");
    //$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//} catch(PDOException $e) {
  //  die("Erreur de connexion : " . $e->getMessage());
//}

require_once 'db.php';

// Requête pour récupérer les bénéficiaires avec les colonnes spécifiques
$query = "SELECT 
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
            qr_code_url
          FROM beneficiaires";
          
$stmt = $pdo->prepare($query);
$stmt->execute();
$beneficiaires = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($beneficiaires) === 0) {
    die("Aucun bénéficiaire à exporter");
}

// Entêtes HTTP pour forcer le téléchargement
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=beneficiaires_export_'.date('Y-m-d_H-i').'.csv');

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
    'QR Code URL'
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