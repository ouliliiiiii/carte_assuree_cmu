
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
      <!-- CSS Templates --> 
        <link href="css/bootstrap.min.css" rel="stylesheet">

        <link href="css/bootstrap-icons.css" rel="stylesheet">

        <link href="css/tooplate-mini-finance.css" rel="stylesheet">
        <!-- CSS Templates -->  
     <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
     <style>
        .d , .d:hover
        {
            font-size:15px;
            width:80px;
            height:35px;
            color:white;
            background:  #717275; 
        }

         .m, .m:hover
        {
           font-size:15px;
            width:80px;
            height:35px;
            color:white;
            background:rgb(51, 116, 64); 
        }
     </style>
</head>
<body>

 <header class="navbar ">
            <div class="col-lg-4" >
                 <img src="images/Logosen.png" style="width: 350px;">   
            </div>
             <div class="col-lg-4" >
                 <img src="images/Logo.png"style="width: 350px;" >   
            </div>
      </header>

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

    <h2>Liste des bénéficiaires</h2>

    <a href="ajoutbeneficiaire.php" class="btn btn-success mb-3 mt-3" style="float: right;">Saisir un bénéficiaire</a>
     
        <table id="myTable" class="table table-striped mt-5">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Nom</th>
                    <th>Téléphone</th>
                    <th>Régime</th>
                    <th>Adresse</th>
                    <th>Action</th>
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
                            <a href='detail.php?code={$row['Code_Immatriculation']}' class='btn  d mb-3'>Détail</a>
                            <a href='modifbeneficiaire.php?code=" . urlencode($row['Code_Immatriculation']) . "' class='btn  m mb-3'>Modifier</a>
                            </td>";
                    echo "<td><a href='codeqr.php?code=" . urlencode($row['Code_Immatriculation']) . "' class='btn btn-info btn-sm'>Voir</a></td>";
                    echo "</tr>";
                }
            ?>
        </tbody>
        </table>
    
    <div class="container-fluid mt-4">
            <a href="exporterliste.php" class="btn btn-success mb-3">Exporter la liste des benefciaires</a>
            <button type="button" class="btn btn-success mb-3" data-toggle="modal" data-target="#exampleModalCenter">
                Importer un fichier
            </button>
       
    </div>

            
        <!-- Button trigger modal -->
            
            <form action="importerliste.php" method="POST" enctype="multipart/form-data" class="mb-3">
                <input type="file" name="fichier_excel" accept=".xlsx, .xls" required />
                <button type="submit" class="btn btn-primary btn-sm">Importer un fichier Excel</button>
            </form>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    $('#myTable').DataTable({
        paging: true,               // pagination activée
        pageLength: 5,              // nombre de lignes par page par défaut
        lengthMenu: [5, 10, 25, 50], // choix du nombre de lignes affichées
        searching: true,            // barre de recherche activée
        ordering: true,             // tri par colonne activé
        info: true ,                 // info sur les pages affichées
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json"
        }
    });
});
</script>
</body>
</html>
