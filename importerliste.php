<?php
session_start(); // Obligatoire, vérifie bien qu'il est au début du script

require_once 'db.php';
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_doublons'])) {
        // Traitement après validation du choix
        $action = $_POST['action_doublons'];
        if (!isset($_SESSION['dataToInsert'])) {
            throw new Exception("Les données d'importation sont manquantes. Veuillez recommencer.");
        }
        $dataToInsert = $_SESSION['dataToInsert'];
        unset($_SESSION['dataToInsert'], $_SESSION['doublons']);

        foreach ($dataToInsert as $d) {
            if ($d['exists']) {
                if ($action === 'ignore') {
                    // on ignore le doublon
                    continue;
                } elseif ($action === 'replace') {
                    $stmtUpdate = $pdo->prepare("UPDATE beneficiaires SET Nom=?, Prenom=?, Date_Naissance=?, Sexe=?, Telephone=?, Adresse=?, Regime=?, Assureur=?, Type_Beneficiaire=?, Date_Cotisation=?, Date_Fin_Cotisation=?, qr_code_url=? WHERE Code_Immatriculation=?");
                    $stmtUpdate->execute([
                        $d['nom'], $d['prenom'], $d['dateNaissance'], $d['sexe'], $d['telephone'], $d['adresse'], $d['regime'], $d['assureur'], $d['type'], $d['dateCotisation'], $d['dateFinCotisation'], $d['qrCodeUrl'], $d['code']
                    ]);
                }
            } else {
                $stmtInsert = $pdo->prepare("INSERT INTO beneficiaires (Code_Immatriculation, Nom, Prenom, Date_Naissance, Sexe, Telephone, Adresse, Regime, Assureur, Type_Beneficiaire, Date_Cotisation, Date_Fin_Cotisation, qr_code_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmtInsert->execute([
                    $d['code'], $d['nom'], $d['prenom'], $d['dateNaissance'], $d['sexe'], $d['telephone'], $d['adresse'], $d['regime'], $d['assureur'], $d['type'], $d['dateCotisation'], $d['dateFinCotisation'], $d['qrCodeUrl']
                ]);
            }
        }

        header("Location: Accueil.php?added=importation");
        exit;
    }

    if (isset($_FILES['fichier_excel']) && $_FILES['fichier_excel']['error'] == 0) {
        $filePath = $_FILES['fichier_excel']['tmp_name'];
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $doublons = [];
        $dataToInsert = [];

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            $code = htmlspecialchars($row[0]);
            // autres champs...
            $nom = htmlspecialchars($row[1]);
            $prenom = htmlspecialchars($row[2]);
            $dateNaissance = htmlspecialchars($row[3]);
            $sexe = htmlspecialchars($row[4]);
            $telephone = htmlspecialchars($row[5]);
            $adresse = htmlspecialchars($row[6]);
            $regime = htmlspecialchars($row[7]);
            $assureur = htmlspecialchars($row[8]);
            $type = htmlspecialchars($row[9]);
            $dateCotisation = htmlspecialchars($row[10]);
            $dateFinCotisation = htmlspecialchars($row[11]);
            $qrCodeUrl = htmlspecialchars($row[12]);

            $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM beneficiaires WHERE Code_Immatriculation = ?");
            $stmtCheck->execute([$code]);
            $exists = $stmtCheck->fetchColumn() > 0;

            $dataToInsert[] = [
                'code' => $code,
                'nom' => $nom,
                'prenom' => $prenom,
                'dateNaissance' => $dateNaissance,
                'sexe' => $sexe,
                'telephone' => $telephone,
                'adresse' => $adresse,
                'regime' => $regime,
                'assureur' => $assureur,
                'type' => $type,
                'dateCotisation' => $dateCotisation,
                'dateFinCotisation' => $dateFinCotisation,
                'qrCodeUrl' => $qrCodeUrl,
                'exists' => $exists,
            ];

            if ($exists) {
                $doublons[] = $code;
            }
        }

        if (count($doublons) > 0) {
            $_SESSION['dataToInsert'] = $dataToInsert;
            $_SESSION['doublons'] = $doublons;

            echo "<h2>Doublons détectés</h2>";
            echo "<p>Les codes suivants existent déjà : " . implode(", ", $doublons) . "</p>";
            echo "<form method='post'>";
            echo "<p>Que souhaitez-vous faire ?</p>";
            echo "<input type='radio' name='action_doublons' value='ignore' checked> Ignorer les doublons<br>";
            echo "<input type='radio' name='action_doublons' value='replace'> Remplacer les doublons<br>";
            echo "<button type='submit'>Valider</button>";
            echo "</form>";
            exit;
        }

        // Si pas de doublons, insertion directe
        foreach ($dataToInsert as $d) {
            $stmtInsert = $pdo->prepare("INSERT INTO beneficiaires (Code_Immatriculation, Nom, Prenom, Date_Naissance, Sexe, Telephone, Adresse, Regime, Assureur, Type_Beneficiaire, Date_Cotisation, Date_Fin_Cotisation, qr_code_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmtInsert->execute([
                $d['code'], $d['nom'], $d['prenom'], $d['dateNaissance'], $d['sexe'], $d['telephone'], $d['adresse'], $d['regime'], $d['assureur'], $d['type'], $d['dateCotisation'], $d['dateFinCotisation'], $d['qrCodeUrl']
            ]);
        }

        header("Location: Accueil.php?added=importation");
        exit;

    } else {
        echo "Erreur lors de l'importation du fichier.";
    }
} catch (Exception $e) {
    echo "<h3>Erreur :</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
