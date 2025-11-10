<?php

require_once 'header.php';
require_once 'db.php';


$userRole = $_SESSION['role'] ?? '';
$userRegion = $_SESSION['region'] ?? '';

// Récupérer toutes les catégories existantes
$categories = $pdo->query("
    SELECT DISTINCT categorie FROM parametres ORDER BY categorie
")->fetchAll(PDO::FETCH_COLUMN);

// Récupérer tous les paramètres avec leur parent_id
$parametres = $pdo->query("
    SELECT id, categorie, valeur, parent_id 
    FROM parametres 
    ORDER BY categorie, valeur
")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Bénéficiaire - SENCSU</title>
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

    <link rel="stylesheet" href="new_style.css">
    
    <style>
       
    </style>
</head>
<body>


   <div class="container mb-5">
        <!-- Section Actions -->
            <div class="card mb-4">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8 mb-3 mb-md-0">
                                    <h2 class="mb-0 section-title"> <i class="bi bi-person-plus-fill me-2"></i>Ajouter un nouveau bénéficiaire</h2>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <div class="d-flex flex-wrap justify-content-md-end">      
                                        <a href="accueil.php" class="btn btn-outline-secondary">
                                            <button class="btn btn-outline-secondary" style="border: none;">
                                                <i class="bi bi-arrow-left-circle-fill me-2"></i>Retour à l'accueil
                                            </button>
                                        </a>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

        <div class="form-container animate__animated animate__fadeIn">
            <form method="post" action="insertion.php" id="beneficiaireForm">
                <div class="row">
                    <!-- Colonne Informations Personnelles -->
                    <div class="col-lg-6">
                        <div class="card-header">
                            <i class="bi bi-person-lines-fill me-2"></i>Informations Personnelles
                        </div>
                        <div class="form-section">
                            <div class="mb-3 text-center mt-3">
                               <input type="file" name="photo" id="photo" accept="image/*" class="d-none" onchange="uploadPhoto(event)">
    
                                <img 
                                    id="photoPreview"
                                    src="<?= htmlspecialchars($beneficiaire['photo'] ?? 'images/avatar.png') ?>" 
                                    data-id="<?= $beneficiaire['id'] ?>"
                                    alt="Photo du bénéficiaire"
                                    class="img-thumbnail"
                                    style="width: 180px; height: 180px; object-fit: cover; cursor: pointer;"
                                    onclick="document.getElementById('photo').click();" 
                                    title="Ajouter photo">
                            </div>
                            <div class="mb-3">
                                <label for="nom" class="form-label required-field">Nom</label>
                                <input type="text" name="nom" id="nom" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="prenom" class="form-label required-field">Prénom</label>
                                <input type="text" name="prenom" id="prenom" class="form-control" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="sexe" class="form-label">Sexe</label>
                                    <select name="sexe" id="sexe" class="form-select">
                                        <option value="">-- Sélectionnez --</option>
                                        <option value="H">Masculin</option>
                                        <option value="F">Féminin</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="date_naissance" class="form-label">Date de naissance</label>
                                    <input type="date" name="date_naissance" id="date_naissance" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="lieu_naissance" class="form-label required-field">Lieu de naissance</label>
                                    <input type="text" name="lieu_naissance" id="lieu_naissance" class="form-control" required>
                                </div>
                              
                            </div>
                            
                            <div class="mb-3">
                                    <label for="type_doc" class="form-label">Type de document</label>
                                    <select id="type_doc" name="type_doc" class="form-select" onchange="afficherChampDoc()">
                                        <option value="">-- Choisissez un type --</option>
                                        <option value="cin">CIN</option>
                                        <option value="passeport">Passeport</option>
                                        <option value="extrait">Extrait</option>
                                    </select>
                            </div>
                            <!-- Champs spécifiques, cachés par défaut -->
                            <div class="mb-3" id="champ_cin" style="display:none;">
                                <label for="cin" class="form-label">Numéro CIN</label>
                                <input  type="text" name="cin" id="cin" class="form-control" placeholder="Ex : 01234567890123456" pattern="\d{17}" inputmode="numeric" maxlength="17" minlength="17" >
                           
                                <div class="form-text">Entrez exactement 17 chiffres (seuls les chiffres sont autorisés).</div>                            
                            </div>

                            <div class="mb-3" id="champ_passeport" style="display:none;">
                                <label for="passeport" class="form-label">Numéro Passeport</label>
                                <input type="text" name="passeport" id="passeport" class="form-control" placeholder="Ex : A1234567" pattern="[A-Z]{1}\d{7}">
                            </div>

                            <div class="mb-3" id="champ_extrait" style="display:none;">
                                <label for="extrait" class="form-label">Numéro Extrait</label>
                                <input type="text" name="extrait" id="extrait" class="form-control" placeholder="Ex : 2023-00123" pattern="\d{4}-\d{5}">
                            </div>
                            
                            <div class="mb-3">
                                <label for="telephone" class="form-label">Téléphone</label>
                                <input type="tel" name="telephone" id="telephone" class="form-control">
                            </div>
                            
                            <div class="mb-3">
                                <label for="adresse" class="form-label">Adresse</label>
                                <textarea name="adresse" id="adresse" class="form-control" rows="2"></textarea>
                            </div>
                            
                            <div class="row">
                                <!-- Région -->
                                <div class="col-md-6 mb-3">
                                    <label for="region" class="form-label">Région</label>
                                     <?php if ($userRole === 'admin'): ?>
                                        <!-- Admin peut choisir -->
                                        <select id="region" name="region" class="form-select" onchange="chargerDepartements()">
                                            <option value="">-- Choisissez une région --</option>
                                        </select>
                                          <?php else: ?>
                                        <!-- Agent : région fixe, non modifiable -->
                                        <input type="text" name="region" id="region" class="form-control" value="<?= htmlspecialchars($userRegion) ?>" readonly>
                                    <?php endif; ?>
                                </div>

                                <!-- Département -->
                                <div class="col-md-6 mb-3">
                                    <label for="departement" class="form-label">Département</label>
                                    <select id="departement" name="departement" class="form-select" onchange="chargerCommunes()">
                                        <option value="">-- Choisissez un département --</option>
                                    </select>
                                </div>

                                <!-- Commune -->
                                <div class="col-md-6 mb-3">
                                    <label for="commune" class="form-label">Commune</label>
                                    <select id="commune" name="commune" class="form-select">
                                        <option value="">-- Choisissez une commune --</option>
                                    </select>
                                </div>


                            </div>
                        </div>
                    </div>
                    
                    <!-- Colonne Informations d'Affiliation -->
                    <div class="col-lg-6">
                        <div class="card-header">
                            <i class="bi bi-person-lines-fill me-2"></i>Informations d'Affiliation
                        </div>
                        <div class="form-section mt-3">
                            <?php foreach($categories as $cat): ?>
                                <div class="mb-3">
                                    <label class="form-label"><?= htmlspecialchars($cat) ?></label>
                                        <select name="<?= strtolower(str_replace(' ', '_', $cat)) ?>" 
                                                id="<?= strtolower(str_replace(' ', '_', $cat)) ?>" 
                                                class="form-select">
                                            <option value="">-- Sélectionnez <?= htmlspecialchars($cat) ?> --</option>
                                            <?php foreach($parametres as $p): ?>
                                                <?php if($p['categorie'] == $cat && !$p['parent_id']): // afficher uniquement les racines ?>
                                                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['valeur']) ?></option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </select>
                                </div>
                            <?php endforeach; ?>
                           

                            <div class="mb-3">
                                <label for="groupe" class="form-label">Groupe d'Appartenance</label>
                                <input type="text" name="groupe" id="groupe" class="form-control">
                            </div>
                            
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="type_cotisation" class="form-label">Type de Cotisation</label>
                                    <select name="type_cotisation" id="type_cotisation" class="form-select">
                                        <option value="">-- Sélectionnez --</option>
                                        <option value="Annuelle">Annuelle</option>
                                        <option value="Semestrielle">Semestrielle</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="date_cotisation" class="form-label">Date de Cotisation</label>
                                    <input type="date" name="date_cotisation" id="date_cotisation" class="form-control">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="date_fin_cotisation" class="form-label">Date de Fin de Cotisation</label>
                                <input type="date" name="date_fin_cotisation" id="date_fin_cotisation" class="form-control readonly-field" readonly>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save-fill me-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>




    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
        const typeCotisation = document.getElementById('type_cotisation');
        const dateCotisation = document.getElementById('date_cotisation');
        const dateFinCotisation = document.getElementById('date_fin_cotisation');

        // 📅 Mise à jour automatique de la date de fin de cotisation
        function updateDateFin() {
            const type = typeCotisation.value;
            const dateStr = dateCotisation.value;

            if (type && dateStr) {
                const date = new Date(dateStr);

                if (type === 'Annuelle') {
                    date.setFullYear(date.getFullYear() + 1);
                    date.setDate(date.getDate() - 1);
                } else if (type === 'Semestrielle') {
                    date.setMonth(date.getMonth() + 6);
                    date.setDate(date.getDate() - 1);
                }

                const yyyy = date.getFullYear();
                const mm = String(date.getMonth() + 1).padStart(2, '0');
                const dd = String(date.getDate()).padStart(2, '0');

                dateFinCotisation.value = `${yyyy}-${mm}-${dd}`;
            } else {
                dateFinCotisation.value = '';
            }
        }

        typeCotisation.addEventListener('change', updateDateFin);
        dateCotisation.addEventListener('change', updateDateFin);

        // 🧾 Validation du formulaire
        document.getElementById('beneficiaireForm').addEventListener('submit', function(e) {
            let isValid = true;

            document.querySelectorAll('[required]').forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            if (!isValid) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Champs obligatoires manquants',
                    text: 'Veuillez remplir tous les champs obligatoires marqués d\'un astérisque (*)',
                    confirmButtonColor: '#2c3e50'
                });
            }
        });
    });
    </script>
        
        <!-- region departement commune -->
    <script>
            let dataSenegal = {};

            window.onload = function () {
            // Charger le fichier JSON
            fetch("regions_departements_communes_senegal.json")
                .then((res) => res.json())
                .then((data) => {
                dataSenegal = data;
                remplirRegions();
                // Si l'utilisateur est un agent, la région est fixe : remplir les départements pour cette région
                const userRole = "<?= $userRole ?>";
                const userRegion = "<?= addslashes($userRegion) ?>";
                if (userRole === 'agent' && userRegion) {
                    // Remplir automatiquement les départements pour la région de l'agent
                    const departementSelect = document.getElementById('departement');
                    departementSelect.innerHTML = '<option value="">-- Choisissez un département --</option>';
                    if (dataSenegal[userRegion]) {
                        const departements = Object.keys(dataSenegal[userRegion]);
                        departements.forEach((dep) => {
                            let option = document.createElement('option');
                            option.value = dep;
                            option.text = dep;
                            departementSelect.appendChild(option);
                        });
                    }
                    // Facultatif : charger automatiquement les communes si un département unique (non fait ici)
                }
                });
            };

            function remplirRegions() {
            const regionSelect = document.getElementById("region");
            for (let region in dataSenegal) {
                let option = document.createElement("option");
                option.value = region;
                option.text = region;
                regionSelect.appendChild(option);
            }
            }

            function chargerDepartements() {
            const region = document.getElementById("region").value;
            const departementSelect = document.getElementById("departement");
            const communeSelect = document.getElementById("commune");

            // Vider les anciennes options
            departementSelect.innerHTML = '<option value="">-- Choisissez un département --</option>';
            communeSelect.innerHTML = '<option value="">-- Choisissez une commune --</option>';

            if (region && dataSenegal[region]) {
                const departements = Object.keys(dataSenegal[region]);
                departements.forEach((dep) => {
                let option = document.createElement("option");
                option.value = dep;
                option.text = dep;
                departementSelect.appendChild(option);
                });
            }
            }

            function chargerCommunes() {
            const region = document.getElementById("region").value;
            const departement = document.getElementById("departement").value;
            const communeSelect = document.getElementById("commune");

            // Vider les anciennes options
            communeSelect.innerHTML = '<option value="">-- Choisissez une commune --</option>';

            if (
                region &&
                departement &&
                dataSenegal[region] &&
                dataSenegal[region][departement]
            ) {
                const communes = dataSenegal[region][departement];
                communes.forEach((commune) => {
                let option = document.createElement("option");
                option.value = commune;
                option.text = commune;
                communeSelect.appendChild(option);
                });
            }
            }


         function uploadPhoto(event) {
            const fileInput = event.target;
            const file = fileInput.files[0];
            const preview = document.getElementById('photoPreview');
            const status = document.getElementById('uploadStatus');
            const id = preview.dataset.id;

            if (!file || !id) return;

            // Affiche l'image localement
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
            };
            reader.readAsDataURL(file);

            // Préparation AJAX
            const formData = new FormData();
            formData.append('photo', file);
            formData.append('id', id);

            fetch('upload_photo.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(result => {
                console.log('Succès :', result);
                status.classList.remove('d-none');
                status.textContent = "Photo mise à jour.";
                setTimeout(() => status.classList.add('d-none'), 3000);
            })
            .catch(error => {
                console.error('Erreur :', error);
                status.classList.remove('d-none');
                status.classList.replace('text-success', 'text-danger');
                status.textContent = "Échec du téléversement.";
            });
         }
    </script>
    <script>
        function afficherChampDoc() {
            const type = document.getElementById("type_doc").value;

            // Cacher tous les champs
            document.getElementById("champ_cin").style.display = "none";
            document.getElementById("champ_passeport").style.display = "none";
            document.getElementById("champ_extrait").style.display = "none";

            // Réinitialiser les champs requis
            document.getElementById("cin").required = false;
            document.getElementById("passeport").required = false;
            document.getElementById("extrait").required = false;

            if (type === "cin") {
                const cinInput = document.getElementById("cin");
                document.getElementById("champ_cin").style.display = "block";
                cinInput.required = true;

                // Ajouter l'événement une seule fois
                if (!cinInput.dataset.listenerAdded) {
                    cinInput.addEventListener('input', function() {
                        this.value = this.value.replace(/\D/g, '');
                    });
                    cinInput.dataset.listenerAdded = "true";
                }
            } else if (type === "passeport") {
                document.getElementById("champ_passeport").style.display = "block";
                document.getElementById("passeport").required = true;
            } else if (type === "extrait") {
                document.getElementById("champ_extrait").style.display = "block";
                document.getElementById("extrait").required = true;
            }
        }

    </script>

    <script>
       const parametres = <?= json_encode($parametres) ?>;

        // Construire une map parent_id → enfants pour filtrage rapide
        const enfantsMap = {};
        parametres.forEach(p => {
            const parentId = p.parent_id ? p.parent_id.toString() : null;
            if (!enfantsMap[parentId]) enfantsMap[parentId] = [];
            enfantsMap[parentId].push(p);
        });

        // Remplir un select selon sa catégorie et son parent_id
        function remplirSelect(selectElem, parentId = null) {
            const categorie = selectElem.getAttribute('data-categorie');
            if (!categorie) return; // sécuriser pour les selects indépendants

            selectElem.innerHTML = `<option value="">-- Sélectionnez ${categorie} --</option>`;

            const options = (enfantsMap[parentId] || []).filter(p => p.categorie === categorie);

            options.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = p.valeur;
                selectElem.appendChild(opt);
            });

            selectElem.disabled = options.length === 0;
        }

        // Mettre à jour uniquement les enfants directs d’un parent
        function updateChildren(parentSelect) {
            const parentId = parentSelect.value || null;
            const parentCategorie = parentSelect.getAttribute('data-categorie');

            // Sélectionner uniquement les selects qui dépendent de ce parent
            const childSelects = Array.from(document.querySelectorAll('select.form-select[data-parent-category]'))
                .filter(s => s.getAttribute('data-parent-category') === parentCategorie);

            childSelects.forEach(child => {
                child.setAttribute('data-parent', parentId);
                remplirSelect(child, parentId);

                // Mettre à jour récursivement les enfants de cet enfant
                updateChildren(child);
            });
        }

        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            const selects = document.querySelectorAll('select.form-select');

            selects.forEach(s => {
                const label = s.previousElementSibling?.textContent.trim();
                if (!label) return;

                // Vérifier si la catégorie existe dans parametres
                const existeDansParam = parametres.some(p => p.categorie === label);
                if (!existeDansParam) return; // ne pas toucher les selects indépendants

                s.setAttribute('data-categorie', label);

                // Vérifier si cette catégorie a des enfants
                const enfants = parametres.filter(p => p.parent_id != null && p.categorie === label);
                if (enfants.length > 0) {
                    const parentCat = parametres.find(p => p.id === enfants[0].parent_id)?.categorie;
                    if (parentCat) {
                        s.setAttribute('data-parent-category', parentCat);
                    }
                }

                // Remplir racines (parent_id = null)
                remplirSelect(s, null);

                // Ajouter événement change uniquement pour les selects liés aux paramètres
                s.addEventListener('change', function() {
                    updateChildren(s);
                });
            });
        });

    </script>

</body>
</html>