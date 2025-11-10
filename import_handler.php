<?php
require_once 'header.php';
require_once 'db.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

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

function convertirDateExcel($dateValue, $format = 'Y-m-d') {
    if (empty($dateValue)) {
        return null;
    }
    
    // Si c'est déjà un objet DateTime
    if ($dateValue instanceof DateTime) {
        return $dateValue->format($format);
    }
    
    // Si c'est un nombre (format Excel)
    if (is_numeric($dateValue)) {
        try {
            $date = ExcelDate::excelToDateTimeObject($dateValue);
            return $date->format($format);
        } catch (Exception $e) {
            error_log("Erreur conversion date Excel: " . $e->getMessage());
            return null;
        }
    }
    
    // Si c'est une chaîne de caractères
    $dateString = trim($dateValue);
    
    // Essayer différents formats de date
    $formats = [
        'd/m/Y', 'd/m/y', 'd-m-Y', 'd-m-y',
        'm/d/Y', 'm/d/y', 'm-d-Y', 'm-d-y',
        'Y-m-d', 'Y/m/d'
    ];
    
    foreach ($formats as $dateFormat) {
        $date = DateTime::createFromFormat($dateFormat, $dateString);
        if ($date && $date->format($dateFormat) === $dateString) {
            return $date->format('Y-m-d');
        }
    }
    
    // Dernier essai avec la conversion automatique
    try {
        $date = new DateTime($dateString);
        return $date->format('Y-m-d');
    } catch (Exception $e) {
        error_log("Erreur conversion date: " . $e->getMessage() . " - Valeur: " . $dateString);
        return null;
    }
}

function validerNomFichierPhoto($nomFichier) {
    if (empty($nomFichier)) {
        return null;
    }
    
    $nomFichier = trim($nomFichier);
    
    // Vérifier les extensions autorisées
    $extensionsAutorisees = ['png', 'jpg', 'jpeg', 'gif', 'bmp'];
    $extension = strtolower(pathinfo($nomFichier, PATHINFO_EXTENSION));
    
    if (!in_array($extension, $extensionsAutorisees)) {
        return false;
    }
    
    // Nettoyer le nom du fichier (enlever les chemins éventuels)
    $nomFichier = basename($nomFichier);
    
    return $nomFichier;
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
        if ($i === 0) continue; // sauter l'en-tête
        $ligne = $i + 1;

        $donnee = [];
        foreach ($positions as $champ => $index) {
            $val = $row[(int)$index - 1] ?? null;
            if ($val !== null) {
                $donnee[$champ] = is_string($val) ? trim($val) : $val;
            } else {
                $donnee[$champ] = null;
            }
        }

        // SEXE
        if (isset($donnee['Sexe'])) {
            $val = strtoupper(trim($donnee['Sexe']));
            if (in_array($val, ['Féminin'])) {
                $donnee['Sexe'] = 'Féminin';
            } elseif (in_array($val, ['Masculin'])) {
                $donnee['Sexe'] = 'Masculin';
            } else {
                $listeErreurs[] = "Erreur à la ligne $ligne : Valeur de 'Sexe' invalide ('{$donnee['Sexe']}'). Valeurs acceptées : F, H, Femme, Homme.";
                continue;
            }
        }

        // REGIME
        $regime = strtoupper(trim($donnee['Regime'] ?? ''));
        if (!in_array($regime, ['CONTRIBUTIF', 'NON CONTRIBUTIF'])) {
            $listeErreurs[] = "Erreur ligne $ligne : Régime invalide ('{$donnee['Regime']}'). Valeurs autorisées : Contributif ou Non Contributif.";
            continue;
        }
        $donnee['Regime'] = ucwords(strtolower($regime));

        // TYPE_BENEFICIAIRE
        $typeBenef = strtoupper(trim($donnee['Type_Beneficiaire'] ?? ''));
        if ($regime === 'CONTRIBUTIF') {
            $typesAcceptes = ['CLASSIQUE', 'ELEVE', 'NDONGO DAARA'];
        } else {
            $typesAcceptes = ['PLAN SESAME', 'FEMME ENCEINTE', 'ENFANT 0-5ANS', 'MENAGE BSF', 'TITULAIRE CEC'];
        }
        if (!in_array($typeBenef, $typesAcceptes)) {
            $listeErreurs[] = "Erreur ligne $ligne : Type de Bénéficiaire invalide ('{$donnee['Type_Beneficiaire']}') pour le régime $regime.";
            continue;
        }
        $donnee['Type_Beneficiaire'] = ucwords(strtolower($typeBenef));

        // ASSUREUR
        $assureur = strtoupper(trim($donnee['Assureur'] ?? ''));
        if (!in_array($assureur, ['SENCSU', 'SOURA', 'MSD'])) {
            $listeErreurs[] = "Erreur ligne $ligne : Assureur invalide ('{$donnee['Assureur']}'). Valeurs autorisées : SENCSU, SOURA, MSD.";
            continue;
        }
        $donnee['Assureur'] = strtoupper($assureur);

        // TYPE_ADHESION
        $typeAdhesion = ucwords(strtolower(trim($donnee['Type_Adhesion'] ?? '')));
        if (!in_array($typeAdhesion, ['Individuelle', 'Familiale', 'Groupe', 'Adhésion Systématique'])) {
            $listeErreurs[] = "Erreur ligne $ligne : Type d'adhésion invalide ('{$donnee['Type_Adhesion']}').";
            continue;
        }
        $donnee['Type_Adhesion'] = $typeAdhesion;

        // DATE DE NAISSANCE
        if (!empty($donnee['Date_Naissance'])) {
            $dateNorm = convertirDateExcel($donnee['Date_Naissance']);
            if (!$dateNorm) {
                $listeErreurs[] = "Erreur à la ligne $ligne : La date de naissance '{$donnee['Date_Naissance']}' est invalide. Format attendu : jj/mm/aaaa";
                continue;
            }
            $donnee['Date_Naissance'] = $dateNorm;
        }

        // DATE DE COTISATION
        if (!empty($donnee['Date_Cotisation'])) {
            $dateNorm = convertirDateExcel($donnee['Date_Cotisation']);
            if (!$dateNorm) {
                $listeErreurs[] = "Erreur à la ligne $ligne : La date de cotisation '{$donnee['Date_Cotisation']}' est invalide. Format attendu : jj/mm/aaaa";
                continue;
            }
            $donnee['Date_Cotisation'] = $dateNorm;
        }

        // TYPE_COTISATION (vérification avant calcul date fin)
        $typeCotisation = ucwords(strtolower(trim($donnee['Type_Cotisation'] ?? '')));
        if (!in_array($typeCotisation, ['Annuelle', 'Semestrielle'])) {
            $listeErreurs[] = "Erreur à la ligne $ligne : Type de cotisation '{$donnee['Type_Cotisation']}' invalide. Valeurs autorisées : Annuelle, Semestrielle.";
            continue;
        }
        $donnee['Type_Cotisation'] = $typeCotisation;

        // DATE FIN DE COTISATION
        if (!empty($donnee['Date_Cotisation'])) {
            $dateFin = calculerDateFinCotisation($donnee['Date_Cotisation'], $donnee['Type_Cotisation']);
            if (!$dateFin) {
                $listeErreurs[] = "Erreur à la ligne $ligne : Type de cotisation '{$donnee['Type_Cotisation']}' invalide ou date invalide.";
                continue;
            }
            $donnee['Date_Fin_Cotisation'] = $dateFin;
        } else {
            $listeErreurs[] = "Erreur à la ligne $ligne : Date de cotisation manquante.";
            continue;
        }

        // PHOTO - Validation du nom de fichier
        if (isset($donnee['Photo']) && !empty($donnee['Photo'])) {
            $nomPhoto = validerNomFichierPhoto($donnee['Photo']);
            if ($nomPhoto === false) {
                $listeErreurs[] = "Erreur à la ligne $ligne : Format de fichier photo invalide ('{$donnee['Photo']}'). Formats acceptés: png, jpg, jpeg, gif, bmp.";
                continue;
            }
            $donnee['Photo'] = $nomPhoto;
        } else {
            $donnee['Photo'] = null;
        }

        // CNI unique
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

    // Si erreurs -> retour
    if (!empty($listeErreurs)) {
        $_SESSION['import_status'] = 'error';
        $_SESSION['import_message'] = implode("<br>", $listeErreurs);
        header('Location: importerliste.php');
        exit;
    }

    // Historique
    $stmtHist = $pdo->prepare("INSERT INTO historique_import 
        (nom_fichier, date_import, nb_lignes_importees, nb_erreurs, message) 
        VALUES (?, NOW(), ?, ?, ?)");
    $stmtHist->execute([$_FILES['fichier_import']['name'], 0, 0, '']);
    $import_id = $pdo->lastInsertId();

    // Insertion en base
    $stmt = $pdo->prepare("INSERT INTO beneficiaires (
        Code_Immatriculation, Nom, Prenom, Date_Naissance, Sexe, Telephone, Adresse, Regime,
        Assureur, Type_Beneficiaire, Date_Cotisation, Region, Departement, Groupe,
        Type_Adhesion, Type_Cotisation, CNI, Date_Fin_Cotisation, Photo, qr_code_url, Date_Enreg, import_id
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
    )");

    $dateEnreg = date('Y-m-d H:i:s');
    $imported = 0;
    $errors = [];

    foreach ($donnees_importees as $i => $row) {
        try {
            $code = $row['code_immatriculation'];
            $qr = "http://localhost/Carte_PROD/detail.php?code=" . urlencode($code);

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
                $row['Groupe'] ?? null,
                $row['Type_Adhesion'] ?? null,
                $row['Type_Cotisation'] ?? null,
                $row['CNI'] ?? null,
                $row['Date_Fin_Cotisation'] ?? null,
                $row['Photo'] ?? null,
                $qr,
                $dateEnreg,
                $import_id
            ]);
            $imported++;
        } catch (Exception $e) {
            $errors[] = "Ligne " . ($i + 2) . ": " . $e->getMessage();
        }
    }

    // Mise à jour historique
    $message = "$imported lignes importées.";
    if ($errors) {
        $message .= "<br>Erreurs d'insertion :<br>" . implode("<br>", array_slice($errors, 0, 10));
        if (count($errors) > 10) {
            $message .= "<br>... et " . (count($errors) - 10) . " autres erreurs.";
        }
    }

    $stmtUpdate = $pdo->prepare("UPDATE historique_import SET nb_lignes_importees = ?, nb_erreurs = ?, message = ? WHERE id = ?");
    $stmtUpdate->execute([$imported, count($errors), $message, $import_id]);

    $_SESSION['import_status'] = empty($errors) ? 'success' : 'error';
    $_SESSION['import_message'] = $message;
    // Log de l'import pour la traçabilité
    $actor = $_SESSION['user'] ?? 'system';
    $logDetails = 'Importation #' . $import_id . ' - ' . $imported . ' lignes importées';
    log_action($pdo, $actor, 'importation', $import_id, $logDetails);

    header('Location: importerliste.php');
    exit;

} catch (Exception $e) {
    $_SESSION['import_message'] = "Erreur lors du traitement : " . $e->getMessage();
    $_SESSION['import_status'] = 'error';
    header('Location: importerliste.php');
    exit;
}