<?php
require_once 'db.php';

$ip_pc = '10.100.226.203'; // IP locale
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //$code = trim($_POST['code'] ?? '');
    $code = genererCodeImmatriculation();
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $date_naissance = $_POST['date_naissance'] ?? '';
    $sexe = $_POST['sexe'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $adresse = $_POST['adresse'] ?? '';
    $regime = $_POST['regime'] ?? '';
    $assureur = $_POST['assureur'] ?? '';
    $type_beneficiaire = $_POST['type_beneficiaire'] ?? '';
    $date_cotisation = $_POST['date_cotisation'] ?? '';
    $date_fin_cotisation = $_POST['date_fin_cotisation'] ?? '';
    

    if ($code && $nom && $prenom) {
        $qr_url = "http://$ip_pc/QR/detail.php?code=" . urlencode($code);
        $stmt = $pdo->prepare("INSERT INTO beneficiaires (Code_Immatriculation, Nom, Prenom, Date_Naissance, Sexe, Telephone, Adresse, Regime, Assureur, Type_Beneficiaire, Date_Cotisation, Date_Fin_Cotisation, qr_code_url) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        try {
            $stmt->execute([
                $code,
                $nom,
                $prenom,
                $date_naissance,
                $sexe,
                $telephone,
                $adresse,
                $regime,
                $assureur,
                $type_beneficiaire,
                $date_cotisation,
                $date_fin_cotisation,
                $qr_url
            ]);
            // Redirection avec code ajouté pour afficher le pop-up et QR Code
            header("Location: accueil.php?added=" . urlencode($code));
            exit;
        } catch (PDOException $e) {
            // En cas d’erreur, on peut rediriger avec message d’erreur (ou gérer autrement)
            $message = "Erreur lors de l'ajout : " . $e->getMessage();
        }
    } else {
        $message = "Veuillez remplir au moins le code, nom et prénom.";
    }
}

// En cas d’erreur, afficher message simple (sinon tu peux gérer autrement)
if ($message) {
    echo "<p style='color:red;'>$message</p>";
    echo "<p><a href='ajoutbeneficiaire.php'>Retour au formulaire</a></p>";
}
