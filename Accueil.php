
<!DOCTYPE html>
<?php
require_once 'db.php';

$message = '';
if (isset($_GET['added'])) {
    $codeAdded = htmlspecialchars($_GET['added']);
    $message = "Bénéficiaire <strong>$codeAdded</strong> ajouté avec succès !";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Liste des bénéficiaires</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
     <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div class="container mt-5">

     <?php if ($message): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let timerInterval;
            Swal.fire({
                position:"top-end",
                icon:"success",
                title:"Bien ajouté",
                showConfirmButton:false,
                timer: 1500, 
            });
        });
    </script>
    <?php endif; ?>

   
    <a href="ajoutbeneficiaire.php" class="btn btn-success mb-3">Saisir un bénéficiaire</a>
    <a href="exporterliste.php" class="btn btn-success mb-3">Exporter la liste des benefciaires</a>

    <h1>Liste des bénéficiaires</h1>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Code</th>
                <th>Nom</th>
                <th>Téléphone</th>
                <th>Régime</th>
                <th>Adresse</th>
                <th>action</th>
                <th>QR Code</th>
            </tr>
        </thead>
        <tbody>
           <?php
              $sql = "SELECT * FROM beneficiaires";
              foreach ($pdo->query($sql) as $row) {
                  echo "<tr>";
                  echo "<td>{$row['Code_Immatriculation']}</td>";
                  echo "<td>{$row['Nom']}</td>";
                  echo "<td>{$row['Telephone']}</td>";
                   echo "<td>{$row['Regime']}</td>";
                  echo "<td>{$row['Adresse']}</td>";
                  echo "<td>
                           <a href='detail.php?code={$row['Code_Immatriculation']}' class='btn btn-primary btn-sm'>Détail</a>
                           <a href='modifbeneficiaire.php?code=" . urlencode($row['Code_Immatriculation']) . "' class='btn btn-warning btn-sm'>Modifier</a>
                         </td>";
                  echo "<td><a href='codeqr.php?code=" . urlencode($row['Code_Immatriculation']) . "' class='btn btn-info btn-sm'>Voir</a></td>";
                  echo "</tr>";
              }
        ?>
       </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>