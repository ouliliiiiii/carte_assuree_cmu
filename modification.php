<?php
require_once 'db.php';

$dateEnreg = date('Y-m-d H:i:s'); // Date et heure actuelle

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
    $date_cotisation = $_POST['date_cotisation'] ?: null; //Convertir '' en NULL
    $date_fin_cotisation = $_POST['date_fin_cotisation'] ?: null; // Convertir '' en NULL
    $region = $_POST['region'] ?? '';
    $departement = $_POST['departement'] ?? '';
    $groupe = $_POST['groupe'] ?? '';
    $type_adhesion = $_POST['type_adhesion'] ?? '';
    $type_cotisation = $_POST['type_cotisation'] ?? '';
    $cni = $_POST['cni'] ?? '';
   
    

    $dateNaissance = !empty($date_naissance) ? date('Y-m-d', strtotime($date_naissance)) : null;
    $dateCotisation = !empty($date_cotisation) ? date('Y-m-d', strtotime($date_cotisation)) : null;
    $dateFinCotisation = !empty($date_fin_cotisation) ? date('Y-m-d', strtotime($date_fin_cotisation)) : null;
    

    if ($code && $nom && $prenom) {
        $stmt = $pdo->prepare("
            UPDATE beneficiaires SET 
                Nom = ?, Prenom = ?, Date_Naissance = ?, Sexe = ?, Telephone = ?, Adresse = ?, 
                Regime = ?, Assureur = ?, Type_Beneficiaire = ?, Date_Cotisation = ?, Date_Fin_Cotisation = ?, 
                Region = ?, Departement = ?, Groupe = ?, Type_Adhesion = ?, Type_Cotisation = ?,CNI  = ?, Date_Enreg = ?
            WHERE Code_Immatriculation = ?
        ");

        try {
            $stmt->execute([
                $nom, $prenom, $dateNaissance ?: null, $sexe, $telephone, $adresse,
                $regime, $assureur, $type_beneficiaire, $dateCotisation, $dateFinCotisation,
                $region, $departement, $groupe, $type_adhesion, $type_cotisation, $cni, $dateEnreg, $code
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
