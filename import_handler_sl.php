<?php
// import_handler.php
//session_start();

// Configuration de la base de données
require_once 'db.php';

// Vérifier si le fichier a été uploadé
if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_FILES['fichier_import'])) {
    $_SESSION['import_message'] = "Aucun fichier n'a été uploadé.";
    $_SESSION['import_status'] = 'error';
    header('Location: importerListe.php');
    exit;
}

$fichier = $_FILES['fichier_import']['tmp_name'];
$filename = $_FILES['fichier_import']['name'];

// Vérifier l'extension du fichier
$extension = pathinfo($filename, PATHINFO_EXTENSION);
if (!in_array(strtolower($extension), ['xls', 'xlsx', 'csv'])) {
    $_SESSION['import_message'] = "Erreur : Format de fichier non supporté. Veuillez uploader un fichier Excel (xls, xlsx) ou CSV.";
    $_SESSION['import_status'] = 'error';
    header('Location: importerListe.php');
    exit;
}

// Fonction pour générer le code du bénéficiaire

$ip_pc = '10.100.226.111'; // IP locale
$message = '';

function genererCodeImmatriculation() {
    // Génère une lettre majuscule aléatoire
    $lettre1 = chr(rand(65, 90)); // A-Z
    $lettre2 = chr(rand(65, 90)); // A-Z

    // Génère des groupes de 4 chiffres aléatoires
    $chiffres1 = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    $chiffres2 = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

    // Assemble le code
    return $lettre1 . $chiffres1 . $lettre2 . $chiffres2;
}

// Inclure PHPExcel/PhpSpreadsheet
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

try {
    // Charger le fichier
    $spreadsheet = IOFactory::load($fichier);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();

    // Vérifier les en-têtes (optionnel)
    $headers = array_shift($rows);
    $expectedHeaders = [
       // 'Code_Immatriculation', 
       'Nom', 'Prenom', 'Date_Naissance', 'Sexe', 
        'Telephone', 'Adresse', 'Regime', 'Assureur', 'Type_Beneficiaire', 
        'Date_Cotisation', 'Date_Fin_Cotisation', 
        //'qr_code_url', 
        'Region', 
        'Departement', 'Groupe', 'Type_Adhesion', 'Type_Cotisation',  'CNI'
    ];

    if ($headers != $expectedHeaders) {
        $_SESSION['import_message'] = "Erreur : Les en-têtes du fichier ne correspondent pas au format attendu.";
        $_SESSION['import_status'] = 'error';
        header('Location: importerListe.php');
        exit;
    }

    $dateEnreg = date('Y-m-d H:i:s'); // Date et heure actuelle

// Préparation de la requête
$sql = "INSERT INTO beneficiaires (
    Code_Immatriculation, Nom, Prenom, Date_Naissance, Sexe, Telephone, 
    Adresse, Regime, Assureur, Type_Beneficiaire, Date_Cotisation, 
    Date_Fin_Cotisation, qr_code_url, Region, Departement, Groupe, 
    Type_Adhesion, Type_Cotisation, CNI, Date_Enreg
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $pdo->prepare($sql);
$imported = 0;
$errors = [];

foreach ($rows as $index => $row) {
    try {
        // Validation des données
       // if (empty($row[0])  // Nom
         //  throw new Exception("Nom manquant");
        
       
       
       //  if (empty($row[1])) { // Prénom
         //   throw new Exception("Prénom manquant");
        //}
        
        // Formatage des dates
        $dateNaissance = !empty($row[2]) ? date('Y-m-d', strtotime($row[2])) : null;
        $dateCotisation = !empty($row[9]) ? date('Y-m-d', strtotime($row[9])) : null;
        $dateFinCotisation = !empty($row[10]) ? date('Y-m-d', strtotime($row[10])) : null;
       

        // Génération des valeurs automatiques
        $code = genererCodeImmatriculation();
        $qrCodeUrl = "http://$ip_pc/carte_assur-e_cmu/detail.php?code=" . urlencode($code);

        // Exécution de la requête avec le bon ordre des colonnes
        $stmt->execute([
            $code,          // Code_Immatriculation
            $row[0],        // Nom
            $row[1],        // Prenom
            $dateNaissance, // Date_Naissance
            $row[3],        // Sexe
            $row[4],        // Telephone
            $row[5],        // Adresse
            $row[6],        // Regime
            $row[7],        // Assureur
            $row[8],        // Type_Beneficiaire
            $dateCotisation, // Date_Cotisation
            $dateFinCotisation, // Date_Fin_Cotisation
            $qrCodeUrl,     // qr_code_url
            $row[11],       // Region
            $row[12],       // Departement
            $row[13],       // Groupe
            $row[14],       // Type_Adhesion
            $row[15],       // Type_Cotisation
            $row[16],        // CNI
            $dateEnreg      // DateEnreg
        ]);
        $imported++;
    } catch (Exception $e) {
        $errors[] = "Ligne " . ($index + 2) . ": " . $e->getMessage();
    }
}

    // Préparer le message de résultat
    $message = "Importation terminée : $imported bénéficiaires importés avec succès.";
    if (!empty($errors)) {
        $message .= "<br><br>Erreurs rencontrées :<br>" . implode("<br>", array_slice($errors, 0, 10));
        if (count($errors) > 10) {
            $message .= "<br>... et " . (count($errors) - 10) . " erreurs supplémentaires";
        }
    }

    $_SESSION['import_message'] = $message;
    $_SESSION['import_status'] = empty($errors) ? 'success' : 'error';
    
} catch (Exception $e) {
    $_SESSION['import_message'] = "Erreur lors du traitement du fichier : " . $e->getMessage();
    $_SESSION['import_status'] = 'error';
}

header('Location: accueil.php');
exit;