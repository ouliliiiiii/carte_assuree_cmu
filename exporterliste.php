<?php 
require_once 'db.php';
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

// Récupérer les paramètres de filtre
$filtreType = $_GET['type_beneficiaire'] ?? '';
$filtreRegime = $_GET['regime'] ?? '';
$filtreNom = $_GET['search_nom'] ?? '';
$filtreCode = $_GET['search_code'] ?? '';
$filtreGroupe = $_GET['groupe'] ?? '';
$filtreDateDebut = $_GET['date_debut'] ?? '';
$filtreDateFin = $_GET['date_fin'] ?? '';
$filtreEtat = $_GET['etat'] ?? '';

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
            CNI,
            photo,
            (CASE 
                WHEN Date_Cotisation > CURDATE() THEN 'À venir'
                WHEN Date_Cotisation <= CURDATE() AND Date_Fin_Cotisation >= CURDATE() THEN 'Actif'
                ELSE 'Expiré'
            END) as Etat_Cotisation
          FROM beneficiaires
          WHERE 1=1";

$params = [];
if (!empty($filtreType)) {
    $query .= " AND Type_Beneficiaire = ?";
    $params[] = $filtreType;
}
if (!empty($filtreRegime)) {
    $query .= " AND Regime = ?";
    $params[] = $filtreRegime;
}
if (!empty($filtreNom)) {
    $query .= " AND (Nom LIKE ? OR Prenom LIKE ?)";
    $params[] = "%$filtreNom%";
    $params[] = "%$filtreNom%";
}
if (!empty($filtreCode)) {
    $query .= " AND Code_Immatriculation LIKE ?";
    $params[] = "%$filtreCode%";
}
if (!empty($filtreGroupe)) {
    $query .= " AND Groupe = ?";
    $params[] = $filtreGroupe;
}
if (!empty($filtreDateDebut)) {
    $query .= " AND Date_Enreg >= ?";
    $params[] = $filtreDateDebut;
}
if (!empty($filtreDateFin)) {
    $query .= " AND Date_Enreg <= ?";
    $params[] = $filtreDateFin;
}

// Exécution de la requête
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$beneficiaires = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Filtrage par état
if (!empty($filtreEtat)) {
    if ($filtreEtat === 'Alerte') {
        $beneficiaires = array_filter($beneficiaires, function($beneficiaire) {
            $dateDebut = new DateTime($beneficiaire['Date_Cotisation']);
            $dateFin = new DateTime($beneficiaire['Date_Fin_Cotisation']);
            $aujourdhui = new DateTime();
            
            $totalDays = $dateFin->diff($dateDebut)->days ?: 1;
            $daysPassed = $aujourdhui->diff($dateDebut)->invert ? $aujourdhui->diff($dateDebut)->days : 0;
            $percentage = min(100, max(0, ($daysPassed / $totalDays) * 100));
            
            return ($percentage >= 70 && $percentage < 100 && $aujourdhui <= $dateFin);
        });
    } else {
        $beneficiaires = array_filter($beneficiaires, function($beneficiaire) use ($filtreEtat) {
            return $beneficiaire['Etat_Cotisation'] === $filtreEtat;
        });
    }
}

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
    'Type Bénéficiaire', 'Date Cotisation', 'Date Fin Cotisation', 'État Cotisation', 'QR Code URL',
    'Region', 'Departement','Commune', 'Groupe', 'Type_Adhesion', 'Type_Cotisation', 'CNI',  'Photo'
];
$sheet->fromArray($entetes, NULL, 'A1');

// Remplissage des données + collecte des photos
$row = 2;
$photos = [];
foreach ($beneficiaires as $beneficiaire) {
    $data = [
        $beneficiaire['id'],
        $beneficiaire['Date_Enreg'],
        $beneficiaire['Code_Immatriculation'],
        $beneficiaire['Nom'],
        $beneficiaire['Prenom'],
        $beneficiaire['Date_Naissance'],
        $beneficiaire['Sexe'],
        $beneficiaire['Telephone'],
        $beneficiaire['Adresse'],
        $beneficiaire['Regime'],
        $beneficiaire['Assureur'],
        $beneficiaire['Type_Beneficiaire'],
        $beneficiaire['Date_Cotisation'],
        $beneficiaire['Date_Fin_Cotisation'],
        $beneficiaire['Etat_Cotisation'],
        $beneficiaire['qr_code_url'],
        $beneficiaire['Region'],
        $beneficiaire['Departement'],
        $beneficiaire['Commune'],
        $beneficiaire['Groupe'],
        $beneficiaire['Type_Adhesion'],
        $beneficiaire['Type_Cotisation'],
        $beneficiaire['CNI'],
        $beneficiaire['photo']
    ];

    $sheet->fromArray($data, NULL, 'A' . $row);

    // Récupération du chemin photo
    $photoPath = $beneficiaire['photo'];
    if (!empty($beneficiaire['photo']) && file_exists($photoPath)) {
        // renommer la photo avec ID et Code_Immatriculation
        $ext = pathinfo($photoPath, PATHINFO_EXTENSION);
        $newName = $beneficiaire['id'] . '_' . $beneficiaire['Code_Immatriculation'] . '.' . $ext;
        $photos[$photoPath] = $newName;
    }

    $row++;
}

// Ajustement automatique des colonnes
foreach (range('A', 'V') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Sauvegarder l’Excel dans un fichier temporaire
$tmpExcel = tempnam(sys_get_temp_dir(), 'export_') . '.xls';
$writer = new Xls($spreadsheet);
$writer->save($tmpExcel);

// Créer l’archive ZIP
$zipFile = 'beneficiaires_export_' . date('Y-m-d_H-i') . '.zip';
$zip = new ZipArchive();
if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
    // Ajouter l’Excel
    $zip->addFile($tmpExcel, 'beneficiaires.xls');

    // Ajouter les photos
    foreach ($photos as $path => $newName) {
        $zip->addFile($path, $newName);
    }

    $zip->close();
}

// Envoyer le ZIP
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . basename($zipFile) . '"');
header('Content-Length: ' . filesize($zipFile));
readfile($zipFile);

// Nettoyage
unlink($tmpExcel);
unlink($zipFile);
exit;
?>
