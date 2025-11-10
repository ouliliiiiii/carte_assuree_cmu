<?php
session_start();
require_once 'db.php';
require_once 'audit.php';

// IP locale pour QR code
$ip_pc = 'localhost/Carte_PROD/';

// Fonction pour générer le code d'immatriculation
function genererCodeImmatriculation() {
    $lettre1 = chr(rand(65, 90));
    $lettre2 = chr(rand(65, 90));
    $chiffres1 = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    $chiffres2 = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    return $lettre1 . $chiffres1 . $lettre2 . $chiffres2;
}

$dateEnreg = date('Y-m-d H:i:s');
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1️⃣ Champs de base du bénéficiaire
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $date_naissance = !empty($_POST['date_naissance']) ? date('Y-m-d', strtotime($_POST['date_naissance'])) : null;
    $lieu_naissance = trim($_POST['lieu_naissance'] ?? '');
    $sexe = $_POST['sexe'] ?? '';
    $telephone = trim($_POST['telephone'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    $region = $_POST['region'] ?? '';
    $departement = $_POST['departement'] ?? '';
    $commune = $_POST['commune'] ?? '';
    $groupe = trim($_POST['groupe'] ?? '');
    $type_cotisation = $_POST['type_cotisation'] ?? '';
    $date_cotisation = !empty($_POST['date_cotisation']) ? date('Y-m-d', strtotime($_POST['date_cotisation'])) : null;
    $date_fin_cotisation = !empty($_POST['date_fin_cotisation']) ? date('Y-m-d', strtotime($_POST['date_fin_cotisation'])) : null;
    $cni = $_POST['cni'] ?? '';

    // Validation minimale
    if (!$nom || !$prenom) {
        $message = "Veuillez renseigner au moins le nom et le prénom.";
    } else {

        // 2️⃣ Générer code immatriculation + QR code
        $code = genererCodeImmatriculation();
        $qr_url = "http://$ip_pc/detail.php?code=" . urlencode($code);

        try {
            $pdo->beginTransaction();

            // 3️⃣ Insérer le bénéficiaire (sans les paramètres dynamiques)
            $stmt = $pdo->prepare("
                INSERT INTO beneficiaires 
                (Code_Immatriculation, Nom, Prenom, Date_Naissance, Lieu_Naissance, Sexe, Telephone, Adresse,
                 Date_Cotisation, Date_Fin_Cotisation, qr_code_url, Region, Departement, Commune, Groupe, Type_Cotisation, CNI, Date_Enreg)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $code, $nom, $prenom, $date_naissance, $lieu_naissance, $sexe, $telephone, $adresse,
                $date_cotisation, $date_fin_cotisation, $qr_url,
                $region, $departement, $commune, $groupe, $type_cotisation, $cni, $dateEnreg
            ]);

            $beneficiaireId = $pdo->lastInsertId();

            // 4️⃣ Insérer les paramètres dynamiques
            // Récupérer toutes les catégories de paramètres existantes
            $stmtParams = $pdo->query("SELECT id, categorie FROM parametres");
            $parametres = $stmtParams->fetchAll(PDO::FETCH_ASSOC);

            // Créer un tableau catégorie => parametre_id envoyé par le formulaire
            $paramIds = [];
            foreach ($parametres as $p) {
                $catKey = strtolower(str_replace(' ', '_', $p['categorie']));
                if (isset($_POST[$catKey]) && $_POST[$catKey] == $p['id']) {
                    $paramIds[] = (int)$p['id'];
                }
            }

            // Insertion dans beneficiaire_parametres
            $stmtInsertParam = $pdo->prepare("
                INSERT INTO beneficiaire_parametres (beneficiaire_id, parametre_id) VALUES (?, ?)
            ");
            foreach ($paramIds as $pid) {
                $stmtInsertParam->execute([$beneficiaireId, $pid]);
            }

            $pdo->commit();

            // 5️⃣ Log et redirection
            $actor = $_SESSION['user'] ?? 'system';
            $details = 'Insertion bénéficiaire ' . $code;
            $target = trim($nom . ' ' . $prenom);
            log_action($pdo, $actor, 'insertion', $target, $details);

            header("Location: accueil.php?added=" . urlencode($code));
            exit;

        } catch (PDOException $e) {
            $pdo->rollBack();
            $message = "Erreur lors de l'ajout : " . $e->getMessage();
        }
    }
}

// 6️⃣ Affichage du message d'erreur simple si besoin
if ($message) {
    echo "<p style='color:red;'>$message</p>";
    echo "<p><a href='ajoutbeneficiaire.php'>Retour au formulaire</a></p>";
}
