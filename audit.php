<?php
// audit.php
// Fournit une fonction log_action($pdo, $actor, $action, $target = null, $details = null)
// Crée la table audit_logs si elle n'existe pas.

if (!isset($pdo)) {
    // Lancer une exception légère si $pdo n'est pas disponible
    // Les fichiers qui incluent audit.php doivent inclure db.php avant.
}

function ensure_audit_table(PDO $pdo)
{
    // Créer la table AVEC colonne 'action' (séparée de details)
    $sql = "CREATE TABLE IF NOT EXISTS audit_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        actor VARCHAR(191) DEFAULT NULL,
        action VARCHAR(100) DEFAULT NULL,
        target VARCHAR(191) DEFAULT NULL,
        details TEXT DEFAULT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $pdo->exec($sql);

    // S'assurer que la colonne 'action' existe (ajout si table issue d'une version antérieure)
    try {
        $check = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'audit_logs' AND COLUMN_NAME = 'action'");
        $check->execute();
        $hasAction = (bool) $check->fetchColumn();
        if (!$hasAction) {
            $pdo->exec("ALTER TABLE audit_logs ADD COLUMN action VARCHAR(100) DEFAULT NULL AFTER actor");
        }
    } catch (Exception $e) {
        // Ne pas bloquer l'application si l'opération échoue
        error_log('Audit table ensure action failed: ' . $e->getMessage());
    }
}

function log_action(PDO $pdo, $actor, $action, $target = null, $details = null)
{
    try {
        ensure_audit_table($pdo);
        // Normalize action to simple labels requested by user
        $actionNorm = strtolower((string)$action);
        if (strpos($actionNorm, 'update') !== false || strpos($actionNorm, 'modif') !== false) {
            $action = 'modification';
        } elseif (strpos($actionNorm, 'delete') !== false || strpos($actionNorm, 'suppr') !== false || strpos($actionNorm, 'suppression') !== false) {
            $action = 'suppression';
        } elseif (strpos($actionNorm, 'view') !== false || strpos($actionNorm, 'consult') !== false) {
            $action = 'consultation';
        } elseif (strpos($actionNorm, 'login') !== false || strpos($actionNorm, 'connexion') !== false) {
            $action = 'connexion';
        } elseif (strpos($actionNorm, 'register') !== false || strpos($actionNorm, 'inscr') !== false || strpos($actionNorm, 'insertion') !== false) {
            $action = 'insertion';
        }

        // Construire une chaîne lisible pour details (doit être principalement un message simple)
        $detailsText = '';

        if (is_string($details) && $details !== '') {
            $detailsText = $details;
        } elseif (is_array($details)) {
            // Preferer explicit 'message' key when provided
            if (isset($details['message']) && is_string($details['message'])) {
                $detailsText = $details['message'];
            }

            // If target not provided but we have before/after data, set target to 'Nom Prenom'
            if (empty($target)) {
                $before = $details['before'] ?? null;
                $after = $details['after'] ?? null;
                $candidate = null;
                if (is_array($before) && !empty($before['Nom'])) {
                    $candidate = trim(($before['Nom'] ?? '') . ' ' . ($before['Prenom'] ?? ''));
                } elseif (is_array($after) && !empty($after['Nom'])) {
                    $candidate = trim(($after['Nom'] ?? '') . ' ' . ($after['Prenom'] ?? ''));
                }
                if ($candidate) $target = $candidate;
            }

            // Remove message/before/after from meta dump to keep details readable
            $meta = $details;
            if (is_array($meta)) {
                unset($meta['message'], $meta['before'], $meta['after']);
            }
            if (!empty($meta) && is_array($meta)) {
                $jsonMeta = json_encode($meta, JSON_UNESCAPED_UNICODE);
                if ($detailsText !== '') $detailsText .= ' - ' . $jsonMeta;
                else $detailsText = $jsonMeta;
            }
        } else {
            // Try decode JSON
            $decoded = json_decode((string)$details, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $detailsText = $decoded['message'] ?? '';
            } else {
                $detailsText = is_scalar($details) ? (string)$details : json_encode($details, JSON_UNESCAPED_UNICODE);
            }
        }

        // If detailsText still empty, build a default readable message for beneficiary actions
        if ($detailsText === '') {
            // try to find code in target or meta
            $code = null;
            if (is_string($target) && preg_match('/[A-Z]{1}\d{4}[A-Z]{1}\d{4}/i', $target, $m)) {
                $code = $m[0];
            }
            if ($code) {
                if ($action === 'suppression') $detailsText = 'suppression du beneficiaire ' . $code;
                elseif ($action === 'modification') $detailsText = 'modification beneficiaire ' . $code;
                elseif ($action === 'consultation') $detailsText = 'consultation beneficiaire ' . $code;
                else $detailsText = $action . ' ' . $code;
            } else {
                // Fallback: use action + target
                $detailsText = $action . ($target ? ' ' . $target : '');
            }
        }

        // Ensure lower-case style like user requested
        $detailsText = mb_strtolower($detailsText, 'UTF-8');

        // Insert with colonne action séparée
        $stmt = $pdo->prepare('INSERT INTO audit_logs (actor, action, target, details, created_at) VALUES (?, ?, ?, ?, NOW())');
        $stmt->execute([$actor, $action, $target, $detailsText]);
    } catch (Exception $e) {
        // Ne pas lancer d'erreur critique pour ne pas casser l'application en production
        error_log('Audit log failed: ' . $e->getMessage());
    }
}

?>
