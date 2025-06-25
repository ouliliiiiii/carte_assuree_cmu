<?php
// download_template.php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

// Créer un nouveau document
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Définir les en-têtes
$headers = [
    'Nom', 'Prenom', 'Date_Naissance', 'Sexe', 
    'Telephone', 'Adresse', 'Regime', 'Assureur', 'Type_Beneficiaire', 
    'Date_Cotisation', 'Date_Fin_Cotisation',
    'Region', 'Departement', 'Groupe', 'Type_Adhesion', 'Type_Cotisation',  'CNI'
];

// Exemple de ligne de données
$exampleRow = [
    'Doe', 'John', '1985-05-15', 'M', 
    '770000000', 'Ouest Foire', 'Contributif', 'SENCSU', 'Classique', 
    '2025-01-01', '2025-12-31',
    'Dakar', 'Pikine', 'Groupe A', 'Individuelle', 'Mensuelle', '1234567890123'
];

// Ajouter les en-têtes et la ligne d'exemple
$sheet->fromArray([$headers, $exampleRow], NULL, 'A1');

// Configurer le writer CSV
$writer = new Csv($spreadsheet);
$writer->setDelimiter(';');
$writer->setEnclosure('"');
$writer->setLineEnding("\r\n");
$writer->setSheetIndex(0);

// En-têtes HTTP pour forcer le téléchargement
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="template_import_beneficiaires.csv"');
header('Cache-Control: max-age=0');

// Ajouter le BOM UTF-8 pour une meilleure compatibilité avec Excel
echo "\xEF\xBB\xBF";

// Écrire le fichier CSV
$writer->save('php://output');
exit;