<?php
require_once 'header.php';
require_once 'db.php';
require_once 'audit.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $dateEnreg = date('Y-m-d H:i:s');

    // --- 1. Récupération des données POST ---
    $code = $_POST['code'] ?? '';
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $date_naissance = $_POST['date_naissance'] ?: null;
    $sexe = $_POST['sexe'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $adresse = $_POST['adresse'] ?? '';
    $region = $_POST['Region'] ?? '';
    $departement = $_POST['Departement'] ?? '';
    $commune = $_POST['Commune'] ?? '';
    $groupe = $_POST['groupe'] ?? '';
    $date_cotisation = $_POST['date_cotisation'] ?: null;
    $date_fin_cotisation = $_POST['date_fin_cotisation'] ?: null;
    $cni = $_POST['cni'] ?? '';

    $dateNaissance = !empty($date_naissance) ? date('Y-m-d', strtotime($date_naissance)) : null;
    $dateCotisation = !empty($date_cotisation) ? date('Y-m-d', strtotime($date_cotisation)) : null;
    $dateFinCotisation = !empty($date_fin_cotisation) ? date('Y-m-d', strtotime($date_fin_cotisation)) : null;

    if (!$code || !$nom || !$prenom) {
        die("Nom, prénom et code sont obligatoires.");
    }

    // --- 2. Upload photo (hors transaction) ---
    $photo = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['photo']['tmp_name'];
        $fileName = basename($_FILES['photo']['name']);
        $targetDir = 'uploads/photos/';
        $targetPath = $targetDir . uniqid('photo_') . '_' . $fileName;
        if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
        if (move_uploaded_file($fileTmp, $targetPath)) {
            $photo = $targetPath;
        }
    }

    // --- 3. Paramètres dynamiques ---
    $paramsDyn = [];
    $ignored = ['code','nom','prenom','date_naissance','sexe','telephone','adresse',
                'region','departement','commune','groupe','date_cotisation','date_fin_cotisation',
                'cni','photo'];
    foreach ($_POST as $key => $value) {
        if (!in_array(strtolower($key), $ignored)) {
            $paramsDyn[strtolower($key)] = $value;
        }
    }

    $categoriesFixes = ['region','departement','commune','nom','prenom','cni','adresse','telephone','groupe'];

    try {
        // --- 4. Commence la transaction ---
        if (!$pdo->inTransaction()) {
            $pdo->beginTransaction();
        }

        // --- 4a. Récupérer état actuel ---
        $sel = $pdo->prepare('SELECT * FROM beneficiaires WHERE Code_Immatriculation=?');
        $sel->execute([$code]);
        $before = $sel->fetch(PDO::FETCH_ASSOC);

        // --- 4b. Mettre à jour la table beneficiaires ---
        $sql = "UPDATE beneficiaires SET 
            Nom=?, Prenom=?, Date_Naissance=?, Sexe=?, Telephone=?, Adresse=?,
            Region=?, Departement=?, Commune=?, Groupe=?, Date_Cotisation=?, Date_Fin_Cotisation=?, 
            CNI=?, Date_Enreg=?";
        $params = [
            $nom, $prenom, $dateNaissance, $sexe, $telephone, $adresse,
            $region, $departement, $commune, $groupe, $dateCotisation, $dateFinCotisation,
            $cni, $dateEnreg
        ];
        if ($photo) {
            $sql .= ", photo=?";
            $params[] = $photo;
        }
        $sql .= " WHERE Code_Immatriculation=?";
        $params[] = $code;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // --- 4c. Mettre à jour beneficiaire_parametres existants uniquement ---
        $selId = $pdo->prepare('SELECT id_beneficiaire FROM beneficiaires WHERE Code_Immatriculation=?');
        $selId->execute([$code]);
        $idBenef = $selId->fetchColumn();

        $selBpAll = $pdo->prepare("
            SELECT bp.id as bp_id, p.id as param_id, p.categorie, p.valeur
            FROM beneficiaire_parametres bp
            INNER JOIN parametres p ON bp.parametre_id = p.id
            WHERE bp.beneficiaire_id = ?
        ");
        $selBpAll->execute([$idBenef]);
        $benefParamsExistants = $selBpAll->fetchAll(PDO::FETCH_ASSOC);

        $benefParamsMap = [];
        foreach ($benefParamsExistants as $b) {
            $benefParamsMap[strtolower($b['categorie'])] = [
                'bp_id' => $b['bp_id'],
                'param_id' => $b['param_id'],
                'valeur' => $b['valeur']
            ];
        }

        foreach ($paramsDyn as $categorie => $valeur) {
            if (empty($valeur) || in_array($categorie, $categoriesFixes)) continue;
            $catFormated = ucfirst(str_replace('_',' ',$categorie));

            // Vérifier si paramètre existe dans parametres (doit exister)
            $selP = $pdo->prepare("SELECT id FROM parametres WHERE categorie=? AND valeur=?");
            $selP->execute([$catFormated, $valeur]);
            $paramId = $selP->fetchColumn();
            if (!$paramId) continue; // ne jamais créer de nouveau paramètre

            // Mettre à jour le lien existant
            $existing = $benefParamsMap[strtolower($catFormated)] ?? null;
            if ($existing && $existing['param_id'] != $paramId) {
                $upd = $pdo->prepare("UPDATE beneficiaire_parametres SET parametre_id=? WHERE id=?");
                $upd->execute([$paramId, $existing['bp_id']]);
            }
        }

        // --- 4d. Audit ---
        $actor = $_SESSION['user'] ?? 'system';
        $detailsText = 'modification beneficiaire '.$code;
        $target = trim(($nom??'').' '.($prenom??''));
        log_action($pdo, $actor, 'modification', $target, $detailsText);

        // --- 5. Commit ---
        if ($pdo->inTransaction()) {
            $pdo->commit();
        }

        header("Location: detail_web.php?code=".urlencode($code));
        exit;

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        die("Erreur lors de la mise à jour : ".htmlspecialchars($e->getMessage()));
    }
}
?>
