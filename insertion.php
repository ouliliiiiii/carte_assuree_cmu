<?php
require_once 'db.php';

$ip_pc = '10.100.226.203'; // IP locale
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $date_naissance = $_POST['date_naissance'] ?? '';
    $sexe = $_POST['sexe'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $adresse = $_POST['adresse'] ?? '';
    

    if ($code && $nom && $prenom) {
        $qr_url = "http://$ip_pc/QR/detail.php?code=" . urlencode($code);
        $stmt = $pdo->prepare("INSERT INTO beneficiaires (Code_Immatriculation, Nom, Prenom, Date_Naissance, Sexe, Telephone, Adresse, qr_code_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        try {
            $stmt->execute([$code, $nom, $prenom, $date_naissance, $sexe, $telephone, $adresse, $qr_url]);
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
