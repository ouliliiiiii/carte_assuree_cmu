<?php
session_start();
require_once 'db.php';
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

// Récupérer les paramètres de filtre (version simplifiée avec opérateur null coalescing)
$filtreType = $_GET['type_beneficiaire'] ?? '';
$filtreRegime = $_GET['regime'] ?? '';
$filtreNom = $_GET['search_nom'] ?? '';
$filtreCode = $_GET['search_code'] ?? '';
$groupe = $_GET['groupe'] ?? '';
$filtreDateDebut = $_GET['date_debut'] ?? '';
$filtreDateFin = $_GET['date_fin'] ?? '';

// Construire la requête SQL
$query = "SELECT 
            id,
            Date_Enreg,
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
            Commune, 
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
// ... (le reste des conditions de filtre reste identique)

// Exécution de la requête
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$beneficiaires = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($beneficiaires) === 0) {
    die("Aucun bénéficiaire à exporter avec les critères sélectionnés");
}

// Création du spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Entêtes
$entetes = [
    'ID', 'Date Enregistrement', 'Code Immatriculation', 'Nom', 'Prénom',
    'Date Naissance', 'Sexe', 'Téléphone', 'Adresse', 'Régime', 'Assureur',
    'Type Bénéficiaire', 'Date Cotisation', 'Date Fin Cotisation', 'QR Code URL',
    'Region', 'Departement','Commune', 'Groupe', 'Type_Adhesion', 'Type_Cotisation', 'CNI'
];
$sheet->fromArray($entetes, NULL, 'A1');

// Remplissage des données
$row = 2;
foreach ($beneficiaires as $beneficiaire) {
    // Formatage des dates pour Excel (convertir en timestamp Excel)
    $datesToFormat = ['Date_Enreg', 'Date_Naissance', 'Date_Cotisation', 'Date_Fin_Cotisation'];
    foreach ($datesToFormat as $dateField) {
        if (!empty($beneficiaire[$dateField])) {
            $beneficiaire[$dateField] = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(
                strtotime($beneficiaire[$dateField])
            );
        }
    }
    
    // Formatage de la CNI comme texte pour éviter la notation scientifique
    if (!empty($beneficiaire['CNI'])) {
        $beneficiaire['CNI'] = " " . $beneficiaire['CNI']; // Ajoute une apostrophe pour forcer le format texte
    }
    
    $sheet->fromArray($beneficiaire, NULL, 'A' . $row);
    $row++;
}

// Appliquer les formats aux colonnes
$sheet->getStyle('B2:B'.($row-1))->getNumberFormat()->setFormatCode('dd/mm/yyyy'); // Date Enreg
$sheet->getStyle('F2:F'.($row-1))->getNumberFormat()->setFormatCode('dd/mm/yyyy'); // Date Naissance
$sheet->getStyle('M2:M'.($row-1))->getNumberFormat()->setFormatCode('dd/mm/yyyy'); // Date Cotisation
$sheet->getStyle('N2:N'.($row-1))->getNumberFormat()->setFormatCode('dd/mm/yyyy'); // Date Fin Cotisation

// Format de la CNI comme texte
$sheet->getStyle('U2:U'.($row-1))->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

// Format du téléphone
$sheet->getStyle('H2:H'.($row-1))->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

// Ajustement automatique des colonnes
foreach (range('A', 'U') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// En-têtes HTTP pour le téléchargement
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="beneficiaires_export_' . date('Y-m-d_H-i') . '.xls"');
header('Cache-Control: max-age=0');

$writer = new Xls($spreadsheet);
$writer->save('php://output');
exit;
?>