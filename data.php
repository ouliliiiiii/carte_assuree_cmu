<?php
header('Content-Type: application/json');
require_once 'db.php'; // utilise la connexion centralisée

// Initialiser le tableau de données
$data = [
    'total' => 0,
    'hommes' => 0,
    'femmes' => 0,
    'age_moyen' => 0,
    'nouveaux' => 0,
    'dernier_enreg' => null,
    'par_mois' => [],
    'par_region' => [],
    'par_type_cotisation' => [],
    'regions_list' => [],
    'local_stats' => [],
    'par_regime' => []
];

try {
    // ✅ Total bénéficiaires
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM beneficiaires");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $data['total'] = (int)($result['total'] ?? 0);

    // ✅ Répartition Hommes / Femmes
    $stmt = $pdo->query("SELECT COUNT(*) AS hommes FROM beneficiaires WHERE Sexe = 'H'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $data['hommes'] = (int)($result['hommes'] ?? 0);

    $stmt = $pdo->query("SELECT COUNT(*) AS femmes FROM beneficiaires WHERE Sexe = 'F'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $data['femmes'] = (int)($result['femmes'] ?? 0);

    // ✅ Âge moyen
    $stmt = $pdo->query("SELECT AVG(TIMESTAMPDIFF(YEAR, Date_Naissance, CURDATE())) AS age_moyen FROM beneficiaires WHERE Date_Naissance IS NOT NULL");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $data['age_moyen'] = round((float)($result['age_moyen'] ?? 0), 1);

    // ✅ Nouveaux inscrits ce mois-ci
    $stmt = $pdo->query("
        SELECT COUNT(*) AS nouveaux FROM beneficiaires 
        WHERE MONTH(Date_Enreg) = MONTH(CURDATE()) AND YEAR(Date_Enreg) = YEAR(CURDATE())
    ");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $data['nouveaux'] = (int)($result['nouveaux'] ?? 0);

    // ✅ Dernier enregistrement
    $stmt = $pdo->query("SELECT MAX(Date_Enreg) AS dernier_enreg FROM beneficiaires");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $data['dernier_enreg'] = $result['dernier_enreg'] ?? null;

    // ✅ Répartition par mois
    $stmt = $pdo->query("
        SELECT YEAR(Date_Enreg) AS annee, MONTH(Date_Enreg) AS mois, COUNT(*) AS total 
        FROM beneficiaires 
        WHERE Date_Enreg IS NOT NULL
        GROUP BY annee, mois
        ORDER BY annee, mois
    ");
    $data['par_mois'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ✅ Répartition par région (graphique colonnes)
    $stmt = $pdo->query("
        SELECT Region, COUNT(*) AS total
        FROM beneficiaires
        WHERE Region IS NOT NULL AND Region <> ''
        GROUP BY Region
        ORDER BY Region
    ");
    $data['par_region'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ✅ Par type de cotisation
    $stmt = $pdo->query("
        SELECT Type_Cotisation, COUNT(*) AS total
        FROM beneficiaires
        WHERE Type_Cotisation IN ('Annuelle', 'Subventionne', 'Semestrielle')
        GROUP BY Type_Cotisation
        ORDER BY FIELD(Type_Cotisation, 'Annuelle', 'Subventionne', 'Semestrielle')
    ");
    $data['par_type_cotisation'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ✅ Liste des régions
    $stmt = $pdo->query("
        SELECT DISTINCT Region 
        FROM beneficiaires 
        WHERE Region IS NOT NULL AND Region <> ''
        ORDER BY Region
    ");
    $data['regions_list'] = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

    // ✅ Statistiques locales
    $stmt = $pdo->query("
        SELECT Region, Departement, Commune, COUNT(*) AS total
        FROM beneficiaires
        WHERE Region IS NOT NULL AND Region <> ''
        GROUP BY Region, Departement, Commune
    ");
    $data['local_stats'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ✅ Bénéficiaires par régime
    $stmt = $pdo->query("
        SELECT Regime, COUNT(*) as total 
        FROM beneficiaires 
        WHERE Regime IN ('Contributif', 'Non Contributif') 
        GROUP BY Regime
    ");
    $data['par_regime'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Envoyer les données en JSON
    echo json_encode($data);

} catch (PDOException $e) {
    // En cas d'erreur, retourner un message d'erreur
    http_response_code(500);
    echo json_encode([
        'error' => 'Erreur de base de données',
        'message' => $e->getMessage()
    ]);
} catch (Exception $e) {
    // En cas d'autre erreur
    http_response_code(500);
    echo json_encode([
        'error' => 'Erreur inattendue',
        'message' => $e->getMessage()
    ]);
}