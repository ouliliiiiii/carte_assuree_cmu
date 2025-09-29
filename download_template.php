<?php
require_once 'header.php';
require_once 'db.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Créer un nouveau document Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Définir les en-têtes
$headers = [
    'Nom', 'Prenom', 'Date_Naissance', 'Sexe', 
    'Telephone', 'Adresse', 'Regime', 'Assureur', 'Type_Beneficiaire', 
    'Date_Cotisation', 'Date_Fin_Cotisation',
    'Region', 'Departement', 'Groupe', 'Type_Adhesion', 'Type_Cotisation', 'CNI'
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

// Définir les bons en-têtes HTTP pour le téléchargement Excel
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="template_import_beneficiaires.xlsx"');
header('Cache-Control: max-age=0');

// Écrire le fichier Excel dans la sortie
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
