<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'] ?? '';
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $date_naissance = $_POST['date_naissance'] ?: null;
    $sexe = $_POST['sexe'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $adresse = $_POST['adresse'] ?? '';
    $regime = $_POST['regime'] ?? '';
    $assureur = $_POST['assureur'] ?? '';
    $type_beneficiaire = $_POST['type_beneficiaire'] ?? '';
    $date_cotisation = $_POST['date_cotisation'] ?: null; // ✅ Convertir '' en NULL
    $date_fin_cotisation = $_POST['date_fin_cotisation'] ?: null; // ✅ Convertir '' en NULL

    if ($code && $nom && $prenom) {
        $stmt = $pdo->prepare("
            UPDATE beneficiaires SET 
                Nom = ?, Prenom = ?, Date_Naissance = ?, Sexe = ?, Telephone = ?, Adresse = ?, 
                Regime = ?, Assureur = ?, Type_Beneficiaire = ?, Date_Cotisation = ?, Date_Fin_Cotisation = ?
            WHERE Code_Immatriculation = ?
        ");

        try {
            $stmt->execute([
                $nom, $prenom, $date_naissance ?: null, $sexe, $telephone, $adresse,
                $regime, $assureur, $type_beneficiaire, $date_cotisation, $date_fin_cotisation,
                $code
            ]);

            header("Location: accueil.php?updated=" . urlencode($code));
            exit;
        } catch (PDOException $e) {
            die("Erreur lors de la mise à jour : " . $e->getMessage());
        }
    } else {
        die("Nom, prénom et code sont obligatoires.");
    }
}
