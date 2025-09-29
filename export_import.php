<?php
require_once 'header.php';
require_once 'db.php';
//require_once 'db.php';
require 'vendor/autoload.php';

    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xls;
    use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

////exportation
    // Vérification de l'id d'import
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        die("Importation invalide.");
    }

    $export_id = (int)$_GET['id'];

    // Récupération des bénéficiaires liés à l'import avec toutes les colonnes nécessaires
    $stmt = $pdo->prepare("
        SELECT 
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
        WHERE import_id = ?
    ");
    $stmt->execute([$export_id]);
    $beneficiaires = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$beneficiaires) {
        die("Aucun bénéficiaire à exporter pour cet import.");
    }

    // Création du Spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // En-têtes colonne Excel
    $entetes = [
        'ID', 'Date Enregistrement', 'Code Immatriculation', 'Nom', 'Prénom',
        'Date Naissance', 'Sexe', 'Téléphone', 'Adresse', 'Régime', 'Assureur',
        'Type Bénéficiaire', 'Date Cotisation', 'Date Fin Cotisation', 'QR Code URL',
        'Region', 'Departement', 'Commune', 'Groupe', 'Type Adhesion', 'Type Cotisation', 'CNI'
    ];
    $sheet->fromArray($entetes, NULL, 'A1');

    // Préparation des données avec formatage des dates et du texte
    $row = 2;
    foreach ($beneficiaires as $b) {
        // Formatage des dates Excel
        $datesToFormat = ['Date_Enreg', 'Date_Naissance', 'Date_Cotisation', 'Date_Fin_Cotisation'];
        foreach ($datesToFormat as $dateField) {
            if (!empty($b[$dateField])) {
                $b[$dateField] = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(strtotime($b[$dateField]));
            } else {
                $b[$dateField] = null;
            }
        }

        // Forcer CNI et Telephone en texte (pour éviter la notation scientifique)
        if (!empty($b['CNI'])) {
            $b['CNI'] = " " . $b['CNI'];
        }
        if (!empty($b['Telephone'])) {
            $b['Telephone'] = " " . $b['Telephone'];
        }

        // Remplir la ligne
        $sheet->fromArray(array_values($b), NULL, 'A' . $row);
        $row++;
    }

    // Format des colonnes dates
    $sheet->getStyle('B2:B' . ($row - 1))->getNumberFormat()->setFormatCode('dd/mm/yyyy');
    $sheet->getStyle('F2:F' . ($row - 1))->getNumberFormat()->setFormatCode('dd/mm/yyyy');
    $sheet->getStyle('M2:M' . ($row - 1))->getNumberFormat()->setFormatCode('dd/mm/yyyy');
    $sheet->getStyle('N2:N' . ($row - 1))->getNumberFormat()->setFormatCode('dd/mm/yyyy');

    // Format texte pour CNI et Telephone
    $sheet->getStyle('H2:H' . ($row - 1))->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
    $sheet->getStyle('V2:V' . ($row - 1))->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

    // Ajustement auto des colonnes
    foreach (range('A', 'V') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // En-têtes HTTP pour forcer le téléchargement Excel
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="beneficiaires_export_' . date('Y-m-d_H-i') . '.xls"');
    header('Cache-Control: max-age=0');

    // Export vers Excel
    $writer = new Xls($spreadsheet);
    $writer->save('php://output');
    exit;
