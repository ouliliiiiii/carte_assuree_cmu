<?php
// api/getBeneficiaires.php

header('Content-Type: application/json');
$requirePath = __DIR__ . '/../db.php';
require $requirePath; // connexion PDO

// Session - déterminer le rôle et la région côté serveur pour sécurité
if (session_status() === PHP_SESSION_NONE) session_start();
$region = $_GET['region'] ?? null;
$sessionRole = $_SESSION['role'] ?? null;
$sessionRegion = $_SESSION['region'] ?? null;

// Si l'utilisateur est un agent connecté, forcer la région à celle de la session
if ($sessionRole === 'agent') {
    $region = $sessionRegion ?: $region;
}
$annee  = $_GET['annee'] ?? null;
$semestre = isset($_GET['semestre']) && $_GET['semestre'] !== '' ? intval($_GET['semestre']) : null;
$trimestre = isset($_GET['trimestre']) && $_GET['trimestre'] !== '' ? intval($_GET['trimestre']) : null;

// Déterminer la plage de mois si semestre ou trimestre fourni
$monthStart = null;
$monthEnd = null;
if ($trimestre) {
    switch ($trimestre) {
        case 1: $monthStart = 1; $monthEnd = 3; break;
        case 2: $monthStart = 4; $monthEnd = 6; break;
        case 3: $monthStart = 7; $monthEnd = 9; break;
        case 4: $monthStart = 10; $monthEnd = 12; break;
    }
} elseif ($semestre) {
    if ($semestre === 1) { $monthStart = 1; $monthEnd = 6; }
    elseif ($semestre === 2) { $monthStart = 7; $monthEnd = 12; }
}

try {
    // 4️⃣ Répartition par région (pour diagramme barres)
    // Utilise Date_Enreg (colonne existante) et alias 'region' pour cohérence côté front
    $sqlParRegion = "SELECT Region AS region, COUNT(*) AS total FROM beneficiaires WHERE 1";
    if ($annee) $sqlParRegion .= " AND YEAR(Date_Enreg) = :annee";
    if ($monthStart && $monthEnd) $sqlParRegion .= " AND MONTH(Date_Enreg) BETWEEN :mstart AND :mend";
    $sqlParRegion .= " GROUP BY Region ORDER BY Region";
    $stmtParRegion = $pdo->prepare($sqlParRegion);
    if ($annee) $stmtParRegion->bindValue(':annee', $annee, PDO::PARAM_INT);
    if ($monthStart && $monthEnd) { $stmtParRegion->bindValue(':mstart', $monthStart, PDO::PARAM_INT); $stmtParRegion->bindValue(':mend', $monthEnd, PDO::PARAM_INT); }
    $stmtParRegion->execute();
    $par_region = $stmtParRegion->fetchAll(PDO::FETCH_ASSOC);
    // 1️⃣ Stats globales (hommes, femmes, total, âge moyen)
    // La colonne 'Sexe' stocke 'H' / 'F' dans la base : on mappe en Masculin / Féminin
    // Comptage tolérant des sexes : accepte 'H', 'Homme', 'M', 'Masculin' -> Masculin
    // et 'F', 'Femme', 'Féminin' -> Féminin. Utilise UPPER(TRIM(...)) pour normaliser.
    $sqlStats = "SELECT 
                    SUM(CASE WHEN (UPPER(TRIM(Sexe)) LIKE 'H%' OR UPPER(TRIM(Sexe)) LIKE 'M%') THEN 1 ELSE 0 END) AS Masculin,
                    SUM(CASE WHEN UPPER(TRIM(Sexe)) LIKE 'F%' THEN 1 ELSE 0 END) AS `Féminin`,
                    COUNT(*) AS total";

    // Vérifie si tu as une colonne date_naissance pour calculer l'âge moyen
    // Sinon supprimer la ligne suivante
    if (columnExists($pdo, 'beneficiaires', 'date_naissance')) {
        $sqlStats .= ", AVG(TIMESTAMPDIFF(YEAR, date_naissance, CURDATE())) AS age_moyen";
    } else {
        $sqlStats .= ", 0 AS age_moyen";
    }

    $sqlStats .= " FROM beneficiaires WHERE 1";

    if ($region) {
        $sqlStats .= " AND Region = :region";
    }
    if ($annee) {
        $sqlStats .= " AND YEAR(Date_Enreg) = :annee";
    }
    if ($monthStart && $monthEnd) {
        $sqlStats .= " AND MONTH(Date_Enreg) BETWEEN :mstart AND :mend";
    }

    $stmt = $pdo->prepare($sqlStats);
    if ($region) $stmt->bindValue(':region', $region, PDO::PARAM_STR);
    if ($annee)  $stmt->bindValue(':annee', $annee, PDO::PARAM_INT);
    if ($monthStart && $monthEnd) { $stmt->bindValue(':mstart', $monthStart, PDO::PARAM_INT); $stmt->bindValue(':mend', $monthEnd, PDO::PARAM_INT); }
    $stmt->execute();
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2️⃣ Stats par type de cotisation
    $sqlCot = "SELECT Type_Cotisation, COUNT(*) AS total
               FROM beneficiaires
               WHERE 1";
    if ($region) $sqlCot .= " AND Region = :region";
    if ($annee)  $sqlCot .= " AND YEAR(Date_Enreg) = :annee";
    if ($monthStart && $monthEnd) $sqlCot .= " AND MONTH(Date_Enreg) BETWEEN :mstart AND :mend";
    $sqlCot .= " GROUP BY Type_Cotisation";
    $stmt = $pdo->prepare($sqlCot);
    if ($region) $stmt->bindValue(':region', $region, PDO::PARAM_STR);
    if ($annee)  $stmt->bindValue(':annee', $annee, PDO::PARAM_INT);
    if ($monthStart && $monthEnd) { $stmt->bindValue(':mstart', $monthStart, PDO::PARAM_INT); $stmt->bindValue(':mend', $monthEnd, PDO::PARAM_INT); }
    $stmt->execute();
    $cotisations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3️⃣ Stats par régime
    $sqlReg = "SELECT Regime, COUNT(*) AS total
               FROM beneficiaires
               WHERE 1";
    if ($region) $sqlReg .= " AND Region = :region";
    if ($annee)  $sqlReg .= " AND YEAR(Date_Enreg) = :annee";
    if ($monthStart && $monthEnd) $sqlReg .= " AND MONTH(Date_Enreg) BETWEEN :mstart AND :mend";
    $sqlReg .= " GROUP BY Regime";
    $stmt = $pdo->prepare($sqlReg);
    if ($region) $stmt->bindValue(':region', $region, PDO::PARAM_STR);
    if ($annee)  $stmt->bindValue(':annee', $annee, PDO::PARAM_INT);
    if ($monthStart && $monthEnd) { $stmt->bindValue(':mstart', $monthStart, PDO::PARAM_INT); $stmt->bindValue(':mend', $monthEnd, PDO::PARAM_INT); }
    $stmt->execute();
    $regimes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'Masculin' => (int)$stats['Masculin'],
        'Féminin' => (int)$stats['Féminin'],
        'total' => (int)$stats['total'],
        'age_moyen' => round((float)$stats['age_moyen'], 1),
        'par_type_cotisation' => $cotisations,
        'par_regime' => $regimes,
        'par_region' => $par_region
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

// Fonction pour vérifier si une colonne existe
function columnExists($pdo, $table, $column) {
    $stmt = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE :col");
    $stmt->execute([':col' => $column]);
    return (bool)$stmt->rowCount();
}
