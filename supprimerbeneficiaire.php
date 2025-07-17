<?php
session_start();
require_once 'db.php';

if (isset($_GET['code'])) {
    $code = $_GET['code'];
    
    try {
        // Récupérer le code avant suppression pour l'affichage
        $stmt = $pdo->prepare("SELECT Code_Immatriculation FROM beneficiaires WHERE Code_Immatriculation = ?");
        $stmt->execute([$code]);
        $beneficiaire = $stmt->fetch();
        
        if ($beneficiaire) {
            // Suppression du bénéficiaire
            $stmt = $pdo->prepare("DELETE FROM beneficiaires WHERE Code_Immatriculation = ?");
            $stmt->execute([$code]);
            
            $_SESSION['delete_message'] = "Le bénéficiaire <strong>" . htmlspecialchars($beneficiaire['Code_Immatriculation']) . "</strong> a été supprimé avec succès";
            $_SESSION['delete_status'] = "success";
        } else {
            $_SESSION['delete_message'] = "Bénéficiaire introuvable";
            $_SESSION['delete_status'] = "error";
        }
    } catch (PDOException $e) {
        $_SESSION['delete_message'] = "Erreur lors de la suppression : " . $e->getMessage();
        $_SESSION['delete_status'] = "error";
    }
    
    header("Location: accueil.php");
    exit();
}