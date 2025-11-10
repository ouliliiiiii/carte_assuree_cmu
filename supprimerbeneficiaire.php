<?php
session_start();
require_once 'db.php';
require_once 'audit.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_GET['code'])) {
    $code = $_GET['code'];
    
    try {
    // Récupérer les informations avant suppression pour l'affichage et l'audit
    $stmt = $pdo->prepare("SELECT Code_Immatriculation, Nom, Prenom FROM beneficiaires WHERE Code_Immatriculation = ?");
        $stmt->execute([$code]);
        $beneficiaire = $stmt->fetch();
        
        if ($beneficiaire) {
            // Suppression du bénéficiaire
            $stmt = $pdo->prepare("DELETE FROM beneficiaires WHERE Code_Immatriculation = ?");
            $stmt->execute([$code]);

            // Log lisible de la suppression
            $actor = $_SESSION['user'] ?? 'system';
            $details = 'suppression du beneficiaire ' . $beneficiaire['Code_Immatriculation'];
            $target = trim(($beneficiaire['Nom'] ?? '') . ' ' . ($beneficiaire['Prenom'] ?? ''));
            log_action($pdo, $actor, 'suppression', $target, $details);

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