<?php
session_start();
require_once 'db.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

function genererCodeImmatriculation($pdo) {
    do {
        $lettre1 = chr(rand(65, 90));
        $lettre2 = chr(rand(65, 90));
        $chiffres1 = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $chiffres2 = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $code = $lettre1 . $chiffres1 . $lettre2 . $chiffres2;

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM beneficiaires WHERE code_immatriculation = ?");
        $stmt->execute([$code]);
        $existe = $stmt->fetchColumn();
    } while ($existe > 0);

    return $code;
}

function calculerDateFinCotisation($dateDebut, $type) {
    $date = DateTime::createFromFormat('Y-m-d', $dateDebut);
    if (!$date) return null;

    if ($type === 'Annuelle') {
        $date->modify('+1 year');
    } elseif ($type === 'Semestrielle') {
        $date->modify('+6 months');
    } else {
        return null;
    }

    return $date->format('Y-m-d');
}

function normaliserDateExcel($val) {
    if ($val === null || $val === '') return false;

    // Si PhpSpreadsheet a détecté une vraie date et l'a convertie en DateTime
    if ($val instanceof DateTime) {
        return $val->format('Y-m-d');
    }

    // Si PhpSpreadsheet a converti une date en string US (m/d/Y)
    $formats = ['d/m/Y', 'j/n/Y', 'm/d/Y', 'n/j/Y'];
    foreach ($formats as $format) {
        $date = DateTime::createFromFormat($format, $val);
        $erreurs = DateTime::getLastErrors();

        if ($date && $erreurs['warning_count'] == 0 && $erreurs['error_count'] == 0) {
            return $date->format('Y-m-d');
        }
    }

    return false;
}

// Vérification du fichier importé
if (!isset($_FILES['fichier_import'])) {
    $_SESSION['import_message'] = "Aucun fichier importé.";
    $_SESSION['import_status'] = 'error';
    header('Location: importerliste.php');
    exit;
}

if (!isset($_SESSION['colonnes_positions'])) {
    $_SESSION['import_message'] = "Veuillez d'abord configurer les paramètres d'importation.";
    $_SESSION['import_status'] = 'error';
    header('Location: importerliste.php');
    exit;
}

$positions = $_SESSION['colonnes_positions'];
$fichier = $_FILES['fichier_import']['tmp_name'];
$extension = pathinfo($_FILES['fichier_import']['name'], PATHINFO_EXTENSION);

if (!in_array(strtolower($extension), ['xls', 'xlsx', 'csv'])) {
    $_SESSION['import_message'] = "Format de fichier non supporté.";
    $_SESSION['import_status'] = 'error';
    header('Location: importerliste.php');
    exit;
}

try {
    $spreadsheet = IOFactory::load($fichier);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();

    $donnees_importees = [];
    $listeErreurs = [];

    foreach ($rows as $i => $row) {
        if ($i === 0) continue;
        $ligne = $i + 1;

        $donnee = [];
        foreach ($positions as $champ => $index) {
            $val = $row[(int)$index - 1] ?? null;
            $donnee[$champ] = trim($val);
        }

        // SEXE
        if (isset($donnee['Sexe'])) {
            $val = strtoupper(trim($donnee['Sexe']));
            if (in_array($val, ['F', 'FEMME'])) {
                $donnee['Sexe'] = 'F';
            } elseif (in_array($val, ['H', 'HOMME'])) {
                $donnee['Sexe'] = 'H';
            } else {
                $listeErreurs[] = "Erreur à la ligne $ligne : Valeur de 'Sexe' invalide ('{$donnee['Sexe']}'). Valeurs acceptées : F, H, Femme, Homme.";
                continue;
            }
        }

        // DATE DE NAISSANCE
        if (!empty($donnee['Date_Naissance'])) {
            $dateNorm = normaliserDateExcel($donnee['Date_Naissance']);
            if (!$dateNorm) {
                $listeErreurs[] = "Erreur à la ligne $ligne : La date de naissance '{$donnee['Date_Naissance']}' est invalide. Format attendu : jj/mm/aaaa";
                continue;
            }
            $donnee['Date_Naissance'] = $dateNorm;
        }

        // DATE DE COTISATION
        if (!empty($donnee['Date_Cotisation'])) {
            $dateNorm = normaliserDateExcel($donnee['Date_Cotisation']);
            if (!$dateNorm) {
                $listeErreurs[] = "Erreur à la ligne $ligne : La date de cotisation '{$donnee['Date_Cotisation']}' est invalide. Format attendu : jj/mm/aaaa";
                continue;
            }
            $donnee['Date_Cotisation'] = $dateNorm;
        }

        // DATE FIN DE COTISATION
        $dateFin = calculerDateFinCotisation($donnee['Date_Cotisation'] ?? null, $donnee['Type_Cotisation'] ?? null);
        if (!$dateFin) {
            $listeErreurs[] = "Erreur à la ligne $ligne : Type de cotisation '{$donnee['Type_Cotisation']}' invalide ou date invalide.";
            continue;
        }
        $donnee['Date_Fin_Cotisation'] = $dateFin;

        if (!empty($donnee['CNI'])) {
                $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM beneficiaires WHERE CNI = ?");
                $stmtCheck->execute([$donnee['CNI']]);
                if ($stmtCheck->fetchColumn() > 0) {
                    $listeErreurs[] = "Ligne $ligne : Un bénéficiaire avec le CNI '{$donnee['CNI']}' existe déjà.";
                    continue;
                }
            }

        $donnee['code_immatriculation'] = genererCodeImmatriculation($pdo);
        $donnees_importees[] = $donnee;
    }

    // ⚠️ AFFICHAGE DES ERREURS AVANT INSERTION
    if (!empty($listeErreurs)) {
        $_SESSION['import_status'] = 'error';
        $_SESSION['import_message'] = implode("<br>", $listeErreurs);
        header('Location: importerliste.php');
        exit;
    }

    //$utilisateur = $_SESSION['username'] ?? 'invité';
    $stmtHist = $pdo->prepare("INSERT INTO historique_import 
        (nom_fichier, date_import, nb_lignes_importees, nb_erreurs, message) 
        VALUES (?, NOW(), ?, ?, ?)");

    $stmtHist->execute([
        $_FILES['fichier_import']['name'],
        0, 0, ''
    ]);
    $import_id = $pdo->lastInsertId();
    // INSERTION EN BASE
    $ip_pc = 'localhost/Carte_PROD/';
    $imported = 0;
    $errors = [];
    $stmt = $pdo->prepare("INSERT INTO beneficiaires (
        Code_Immatriculation, Nom, Prenom, Date_Naissance, Sexe, Telephone, Adresse, Regime,
        Assureur, Type_Beneficiaire, Date_Cotisation, Region, Departement, Commune,
        Groupe, Type_Adhesion, Type_Cotisation, CNI, Date_Fin_Cotisation, qr_code_url, Date_Enreg, import_id
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?
    )");

    $dateEnreg = date('Y-m-d H:i:s');

    foreach ($donnees_importees as $i => $row) {
        try {
            $code = $row['code_immatriculation'];
            $qr = "http://$ip_pc/detail.php?code=" . urlencode($code);

            $stmt->execute([
                $code,
                $row['Nom'] ?? null,
                $row['Prenom'] ?? null,
                $row['Date_Naissance'] ?? null,
                $row['Sexe'] ?? null,
                $row['Telephone'] ?? null,
                $row['Adresse'] ?? null,
                $row['Regime'] ?? null,
                $row['Assureur'] ?? null,
                $row['Type_Beneficiaire'] ?? null,
                $row['Date_Cotisation'] ?? null,
                $row['Region'] ?? null,
                $row['Departement'] ?? null,
                $row['Commune'] ?? null,
                $row['Groupe'] ?? null,
                $row['Type_Adhesion'] ?? null,
                $row['Type_Cotisation'] ?? null,
                $row['CNI'] ?? null,
                $row['Date_Fin_Cotisation'] ?? null,
                $qr,
                $dateEnreg,
                $import_id
            ]);
            $imported++;
        } catch (Exception $e) {
            $errors[] = "Ligne " . ($i + 2) . ": " . $e->getMessage();
        }
    }

    // MESSAGE FINAL
    $message = "$imported lignes importées.";
    if ($errors) {
        $message .= "<br>Erreurs d’insertion :<br>" . implode("<br>", array_slice($errors, 0, 10));
        if (count($errors) > 10) {
            $message .= "<br>... et " . (count($errors) - 10) . " autres erreurs.";
        }
    }

    $stmtUpdate = $pdo->prepare("UPDATE historique_import SET nb_lignes_importees = ?, nb_erreurs = ?, message = ? WHERE id = ?");
    $stmtUpdate->execute([$imported, count($errors), $message, $import_id]);

    $_SESSION['import_status'] = empty($errors) ? 'success' : 'error';
    $_SESSION['import_message'] = $message;

    header('Location: importerliste.php');
    exit;

}
 catch (Exception $e) 
 {
    $_SESSION['import_message'] = "Erreur lors du traitement du fichier : " . $e->getMessage();
    $_SESSION['import_status'] = 'error';
    header('Location: importerliste.php');
    exit;
}
