<?php
session_start();
require_once 'db.php';

// Vérifie que le paramètre "code" est bien présent et valide
if (!isset($_GET['code']) || !is_numeric($_GET['code'])) {
    header('Location: Historiqueimport.php?error=invalid_id');
    exit;
}

$importId = (int) $_GET['code'];

// Vérifier que l'importation existe
$stmtCheck = $pdo->prepare("SELECT * FROM historique_import WHERE id = ?");
$stmtCheck->execute([$importId]);
$import = $stmtCheck->fetch(PDO::FETCH_ASSOC);

if (!$import) {
    header('Location: Historiqueimport.php?error=not_found');
    exit;
}

try {
    // Démarrer une transaction
    $pdo->beginTransaction();

    // Supprimer les bénéficiaires liés à cette importation (si la table bénéficiaires a un champ `import_id`)
    $stmtBenef = $pdo->prepare("DELETE FROM beneficiaires WHERE import_id = ?");
    $stmtBenef->execute([$importId]);

    // Supprimer l'importation dans l'historique
    $stmtImport = $pdo->prepare("DELETE FROM historique_import WHERE id = ?");
    $stmtImport->execute([$importId]);

    // Valider la transaction
    $pdo->commit();

    header("Location: Historiqueimport.php?success=suppression");
    exit;

} catch (Exception $e) {
    // En cas d’erreur, annuler la transaction
    $pdo->rollBack();
    header("Location: Historiqueimport.php?error=exception");
    exit;
}
?>
