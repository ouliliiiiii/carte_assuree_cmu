<?php
$pdo = null;
require_once 'db.php';
$totalHommes = $pdo->query("SELECT COUNT(*) FROM beneficiaires WHERE Sexe = 'Masculin'")->fetchColumn();
$totalFemmes = $pdo->query("SELECT COUNT(*) FROM beneficiaires WHERE Sexe = 'Féminin'")->fetchColumn();
session_start();
// Les agents peuvent accéder au tableau de bord mais leurs vues seront restreintes côté front et API
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Bénéficiaires - SENCSU</title>
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css" rel="stylesheet">
	
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="new_style.css">
    
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --light-bg: #f8f9fa;
        }
        
        .dashboard-card {
            transition: transform 0.3s, box-shadow 0.3s;
            border-radius: 10px;
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card {
            background: linear-gradient(135deg, var(--primary-color) 0%, #34495e 100%);
            color: white;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            height: 100%;
        }
        
        .stat-card i {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .stat-card .nombre {
            font-size: 2rem;
            font-weight: bold;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        
        .filter-section {
            background-color: var(--light-bg);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .stat-card .nombre {
                font-size: 1.5rem;
            }
            
            .chart-container {
                height: 250px;
            }
        }
        
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            border-radius: 10px;
        }
        
        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
        
        .error-message {
            color: #e74c3c;
            padding: 10px;
            border-radius: 5px;
            background-color: #ffeaea;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    
    <?php include 'header.php'; ?>

    <script>
        // Variables de session exposées au JS pour restreindre la vue si l'utilisateur est un agent
        const sessionRegion = "<?= addslashes($_SESSION['region'] ?? '') ?>";
        const sessionRole = "<?= addslashes($_SESSION['role'] ?? '') ?>";
    </script>

    <div class="container mb-5 justify-content-md-center">       
        <div class="card dashboard-card mb-4">
            <div class="card-body">
                <h2 class="mt-2 border-bottom pb-3 text-center">Tableau de bord – Statistiques globales et Graphiques analytiques des assurés</h2>

                <div id="errorContainer" class="error-message" style="display: none;">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <span id="errorText"></span>
                </div>

                <!-- Filtres principaux -->
                <div class="row filter-section mb-4">
                    <div class="col-md-3">
                        <label for="globalRegionSelect" class="form-label">Filtrer par région :</label>
                        <select id="globalRegionSelect" class="form-select">
                            <option value="">Toutes les régions</option>
                            <!-- Options ajoutées dynamiquement -->
                        </select>
                    </div>
                        <div class="col-md-3">
                            <label for="globalYearSelect" class="form-label">Filtrer par année :</label>
                            <select id="globalYearSelect" class="form-select">
                                <option value="">Toutes les années</option>
                                <!-- Options ajoutées dynamiquement -->
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="globalSemesterSelect" class="form-label">Filtrer par semestre :</label>
                            <select id="globalSemesterSelect" class="form-select">
                                <option value="">Tous les semestres</option>
                                <option value="1">Semestre 1 (JanVier-Juin)</option>
                                <option value="2">Semestre 2 (Juillet-Decembre)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="globalTrimesterSelect" class="form-label">Filtrer par trimestre :</label>
                            <select id="globalTrimesterSelect" class="form-select">
                                <option value="">Tous les trimestres</option>
                                <option value="1">Trimestre 1 (Janvier-Mars)</option>
                                <option value="2">Trimestre 2 (Avril-Juin)</option>
                                <option value="3">Trimestre 3 (Juillet-Septembre)</option>
                                <option value="4">Trimestre 4 (Octobre-Decembre)</option>
                            </select>
                        </div>
                        <div class="col-12 mt-3 text-end">
                            <button id="resetFilters" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-redo-alt me-1"></i>Réinitialiser les filtres
                            </button>
                        </div>
                </div>

                <!-- Cartes statistiques -->
                <div class="row g-3 mb-4"> 
                    <div class="col-md-3 col-sm-6"> 
                        <div class="stat-card">
                            <i class="fas fa-users"></i>
                            <h5>Total bénéficiaires</h5>
                            <span class="nombre" id="total">0</span>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="stat-card" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);">
                            <i class="fas fa-male"></i>
                            <h5>Hommes</h5>
                            <span class="nombre" id="Masculin">0</span>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="stat-card" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                            <i class="fas fa-female"></i>
                            <h5>Femmes</h5>
                            <span class="nombre" id="Féminin">0</span>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="stat-card" style="background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);">
                            <i class="fas fa-birthday-cake"></i>
                            <h5>Âge moyen</h5>
                            <span class="nombre" id="age_moyen">0</span> ans
                        </div>
                    </div>
                </div>

                <!-- Graphiques -->
                <div class="row">
                    <div class="col-lg-8">
                        <div class="row g-3">
                            <!-- Répartition par région -->
                            <div class="col-12">
                                <div class="card dashboard-card h-100">
                                    <div class="card-body">
                                        <div class="chart-title"><i class="fas fa-map-marked-alt"></i> Répartition par région</div>
                                        <div class="chart-container">
                                            <canvas id="regionChart"></canvas>
                                            <div class="loading-overlay" id="regionLoading">
                                                <div class="spinner-border text-primary" role="status"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Inscriptions par mois -->Vérifier session (dans navigateur) : console.log(sessionRegion, sessionRole); (j'ai exposé ces 
                            <div class="col-12">
                                <div class="card dashboard-card h-100">
                                    <div class="card-header bg-transparent">
                                        <h4 class="mb-0"><i class="fas fa-chart-line me-2"></i>Inscriptions par mois</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="chart-container">
                                            <canvas id="moisChart"></canvas>
                                            <div class="loading-overlay" id="moisLoading">
                                                <div class="spinner-border text-primary" role="status">
                                                    <span class="visually-hidden">Chargement...</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="row g-3">
                            <!-- Répartition H/F -->
                            <div class="col-12">
                                <div class="card dashboard-card h-100">
                                    <div class="card-header bg-transparent">
                                        <h4 class="mb-0"><i class="fas fa-venus-mars me-2"></i>Répartition Hommes / Femmes</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="chart-container">
                                            <canvas id="sexeChart"></canvas>
                                            <div class="loading-overlay" id="sexeLoading">
                                                <div class="spinner-border text-primary" role="status">
                                                    <span class="visually-hidden">Chargement...</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Répartition par type de cotisation -->
                            <div class="col-12">
                                <div class="card dashboard-card h-100">
                                    <div class="card-header bg-transparent">
                                        <h4 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Répartition par type de cotisation</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="chart-container">
                                            <canvas id="cotisationChart"></canvas>
                                            <div class="loading-overlay" id="cotisationLoading">
                                                <div class="spinner-border text-primary" role="status">
                                                    <span class="visually-hidden">Chargement...</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Répartition par régime -->
                            <div class="col-12">
                                <div class="card dashboard-card h-100">
                                    <div class="card-header bg-transparent">
                                        <h4 class="mb-0"><i class="fas fa-heartbeat me-2"></i>Répartition par régime</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="chart-container">
                                            <canvas id="regimeChart"></canvas>
                                            <div class="loading-overlay" id="regimeLoading">
                                                <div class="spinner-border text-primary" role="status">
                                                    <span class="visually-hidden">Chargement...</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
        const colorPalette = {
            primary: ['#3498db', '#2ecc71', '#e74c3c', '#f39c12', '#9b59b6', '#1abc9c', '#34495e', '#e67e22'],
            sexe: ['#3498db', '#e74c3c'], // Hommes, Femmes
            qualitative: ['#3498db', '#2ecc71', '#e74c3c', '#f39c12', '#9b59b6', '#1abc9c', '#34495e', '#e67e22']
        };

        let charts = {};
        let filters = { region: '', year: '' };
        let validRegions = [];

        // Au chargement du DOM
        document.addEventListener("DOMContentLoaded", () => {
            showAllLoaders();

            // Charger la liste des régions
            fetch("regions_departements_communes_senegal.json")
                .then(res => res.json())
                .then(data => {
                    validRegions = Object.keys(data || {});
                    const regionSelect = document.getElementById("globalRegionSelect");

                    // Si l'utilisateur est un agent, on restreint le select à sa région et on le désactive
                    if (sessionRole === 'agent' && sessionRegion) {
                        regionSelect.innerHTML = '';
                        const opt = document.createElement('option');
                        opt.value = sessionRegion;
                        opt.textContent = sessionRegion;
                        regionSelect.appendChild(opt);
                        regionSelect.value = sessionRegion;
                        regionSelect.disabled = true;
                        filters.region = sessionRegion; // préfiltre côté JS
                    } else {
                        validRegions.forEach(region => {
                            const option = document.createElement("option");
                            option.value = region;
                            option.textContent = region;
                            regionSelect.appendChild(option);
                        });
                    }
                })
                .catch(err => console.error("Erreur chargement régions :", err));

            setupEventListeners();

            // Charger les données initiales
            loadData();
        });

        // Charger les données depuis l'API
        function loadData() {
            showAllLoaders();
            let url = `api/getBeneficiaires.php`;
            if (filters.region) url += `?region=${encodeURIComponent(filters.region)}`;
            if (filters.year) url += filters.region ? `&annee=${filters.year}` : `?annee=${filters.year}`;

            // Ajouter semestre / trimestre si fournis
            if (filters.semestre) {
                url += url.includes('?') ? `&semestre=${filters.semestre}` : `?semestre=${filters.semestre}`;
            }
            if (filters.trimestre) {
                url += url.includes('?') ? `&trimestre=${filters.trimestre}` : `?trimestre=${filters.trimestre}`;
            }

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    if (data.error) {
                        showError(data.error);
                        hideAllLoaders();
                        return;
                    }

                    // Si pas de données par mois, on reconstruit depuis les bénéficiaires
                    if (!data.par_mois || data.par_mois.length === 0) {
                        const moisCounts = Array(12).fill(0);
                        (data.beneficiaires || []).forEach(b => {
                            // Certains endpoints utilisent 'Date_Enreg' comme nom de colonne
                            const dateStr = b.date_inscription || b.Date_Enreg || b.date_enreg || null;
                            const date = dateStr ? new Date(dateStr) : null;
                            if ((!filters.year || date.getFullYear() === parseInt(filters.year)) &&
                                (!filters.region || b.region === filters.region)) {
                                moisCounts[date.getMonth()]++;
                            }
                        });
                        data.par_mois = moisCounts.map((total, i) => ({ mois: i + 1, total }));
                    }

                    updateCharts(data);
                    hideAllLoaders();
                })
                .catch(err => {
                    console.error(err);
                    showError("Erreur lors du chargement des données");
                    hideAllLoaders();
                });
        }

        // Mettre à jour les stats globales
        function updateStats(data) {
            document.getElementById("total").textContent = (data.total || 0).toLocaleString();
            document.getElementById("Masculin").textContent = (data.Masculin || 0).toLocaleString();
            document.getElementById("Féminin").textContent = (data.Féminin || 0).toLocaleString();
            document.getElementById("age_moyen").textContent = data.age_moyen ? data.age_moyen.toFixed(1) : "0.0";
        }

        // Écouteurs filtres
        function setupEventListeners() {
            document.getElementById("globalRegionSelect").addEventListener("change", function() {
                filters.region = this.value;
                loadData();
            });
            document.getElementById("globalYearSelect").addEventListener("change", function() {
                filters.year = this.value;
                loadData();
            });

            // Semestre -> quand on sélectionne un semestre, on annule le trimestre
            document.getElementById("globalSemesterSelect").addEventListener("change", function() {
                filters.semestre = this.value;
                filters.trimestre = '';
                const tri = document.getElementById('globalTrimesterSelect');
                if (tri) tri.value = '';
                loadData();
            });

            // Trimestre -> quand on sélectionne un trimestre, on annule le semestre
            document.getElementById("globalTrimesterSelect").addEventListener("change", function() {
                filters.trimestre = this.value;
                filters.semestre = '';
                const sem = document.getElementById('globalSemesterSelect');
                if (sem) sem.value = '';
                loadData();
            });

            document.getElementById("resetFilters").addEventListener("click", function() {
                document.getElementById("globalRegionSelect").value = "";
                document.getElementById("globalYearSelect").value = "";
                const sem = document.getElementById('globalSemesterSelect'); if (sem) sem.value = '';
                const tri = document.getElementById('globalTrimesterSelect'); if (tri) tri.value = '';
                filters.region = '';
                filters.year = '';
                filters.semestre = '';
                filters.trimestre = '';
                loadData();
            });
        }

        // Mettre à jour tous les graphiques
        function updateCharts(data) {
            updateStats(data);

            if (charts.sexe) charts.sexe.destroy();
            createSexeChart(data);

            if (charts.region) charts.region.destroy();
            createRegionChart(data);

            if (charts.mois) charts.mois.destroy();
            createMoisChart(data);

            if (charts.cotisation) charts.cotisation.destroy();
            createCotisationChart(data);

            if (charts.regime) charts.regime.destroy();
            createRegimeChart(data);
        }

        // Graphiques
        function createSexeChart(data) {
            // Ajout du nom du diagramme (évite la multiplication)
            const sexeParent = document.getElementById('sexeChart').parentElement;
            const oldLabel = sexeParent.previousElementSibling;
            if (oldLabel && oldLabel.classList.contains('fw-bold') && oldLabel.textContent.includes('Diagramme en anneau')) {
                oldLabel.remove();
            }
            sexeParent.insertAdjacentHTML('beforebegin', '<div class="fw-bold mb-2">Diagramme en anneau</div>');
            const ctx = document.getElementById('sexeChart').getContext('2d');
            charts.sexe = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Masculin', 'Féminin'],
                    datasets: [{
                        data: [data.Masculin || 0, data.Féminin || 0],
                        backgroundColor: colorPalette.sexe
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '60%' }
            });
        }

        function createRegionChart(data) {
            // Ajout du nom du diagramme (évite la multiplication)
            const regionParent = document.getElementById('regionChart').parentElement;
            const oldLabel = regionParent.previousElementSibling;
            if (oldLabel && oldLabel.classList.contains('fw-bold') && oldLabel.textContent.includes('Diagramme en barres')) {
                oldLabel.remove();
            }
            regionParent.insertAdjacentHTML('beforebegin', '<div class="fw-bold mb-2">Diagramme en barres</div>');
            let regionLabels = [];
            let regionData = [];
            if (filters.region) {
                regionLabels = [filters.region];
                regionData = [data.total || 0];
            } else if (data.par_region && Array.isArray(data.par_region) && data.par_region.length > 0) {
                regionLabels = data.par_region.map(item => item.region);
                regionData = data.par_region.map(item => Math.max(0, parseInt(item.total)));
            } else {
                regionLabels = ['Aucune région'];
                regionData = [0];
            }
            const ctx = document.getElementById('regionChart').getContext('2d');
            charts.region = new Chart(ctx, {
                type: 'bar',
                data: { labels: regionLabels, datasets: [{ label: 'Nombre de bénéficiaires', data: regionData, backgroundColor: colorPalette.primary[0], borderRadius: 5 }] },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: true },
                        title: { display: true, text: 'Répartition des bénéficiaires par région' }
                    },
                    scales: {
                        x: { title: { display: true, text: 'Nombre de bénéficiaires' } },
                        y: { title: { display: true, text: 'Régions' } }
                    }
                }
            });
        }

        function createMoisChart(data) {
            // Ajout du nom du diagramme (évite la multiplication)
            const moisParent = document.getElementById('moisChart').parentElement;
            const oldLabel = moisParent.previousElementSibling;
            if (oldLabel && oldLabel.classList.contains('fw-bold') && oldLabel.textContent.includes('Diagramme en courbe')) {
                oldLabel.remove();
            }
            moisParent.insertAdjacentHTML('beforebegin', '<div class="fw-bold mb-2">Diagramme en courbe</div>');
            // Empêche la courbe d'avoir des valeurs négatives
            const moisLabels = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];
            const moisData = Array(12).fill(0);
            (data.par_mois || []).forEach(item => {
                let val = parseInt(item.total);
                if (isNaN(val) || val < 0) val = 0;
                moisData[item.mois - 1] = val;
            });

            const ctx = document.getElementById('moisChart').getContext('2d');
            charts.mois = new Chart(ctx, {
                type: 'line',
                data: { labels: moisLabels, datasets: [{ label: 'Nombre d’inscriptions', data: moisData, backgroundColor: 'rgba(52,152,219,0.2)', borderColor: colorPalette.primary[0], fill: true, tension: 0.4, pointRadius: 4 }] },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: true },
                        title: { display: true, text: 'Inscriptions par mois' }
                    },
                    scales: {
                        y: { min: 0, title: { display: true, text: 'Nombre d’inscriptions' } },
                        x: { title: { display: true, text: 'Mois' } }
                    }
                }
            });
        }

        function createCotisationChart(data) {
            // Ajout du nom du diagramme (évite la multiplication)
            const cotisationParent = document.getElementById('cotisationChart').parentElement;
            const oldLabel = cotisationParent.previousElementSibling;
            if (oldLabel && oldLabel.classList.contains('fw-bold') && oldLabel.textContent.includes('Diagramme en secteurs')) {
                oldLabel.remove();
            }
            cotisationParent.insertAdjacentHTML('beforebegin', '<div class="fw-bold mb-2">Diagramme en secteurs</div>');
            const labels = data.par_type_cotisation?.map(item => item.Type_Cotisation) || [];
            const values = data.par_type_cotisation?.map(item => parseInt(item.total)) || [];

            const ctx = document.getElementById('cotisationChart').getContext('2d');
            charts.cotisation = new Chart(ctx, {
                type: 'pie',
                data: { labels, datasets: [{ data: values, backgroundColor: colorPalette.qualitative.slice(0, labels.length) }] },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }

        function createRegimeChart(data) {
            // Ajout du nom du diagramme (évite la multiplication)
            const regimeParent = document.getElementById('regimeChart').parentElement;
            const oldLabel = regimeParent.previousElementSibling;
            if (oldLabel && oldLabel.classList.contains('fw-bold') && oldLabel.textContent.includes('Diagramme en barres')) {
                oldLabel.remove();
            }
            regimeParent.insertAdjacentHTML('beforebegin', '<div class="fw-bold mb-2">Diagramme en barres</div>');
            const labels = data.par_regime?.map(item => item.Regime) || [];
            const values = data.par_regime?.map(item => parseInt(item.total)) || [];

            const ctx = document.getElementById('regimeChart').getContext('2d');
            charts.regime = new Chart(ctx, {
                type: 'bar',
                data: { labels, datasets: [{ label: 'Bénéficiaires', data: values, backgroundColor: colorPalette.primary[1], borderRadius: 5 }] },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }

        // Loaders et erreurs
        function showAllLoaders() { document.querySelectorAll('.loading-overlay').forEach(l => l.style.display = 'flex'); }
        function hideAllLoaders() { document.querySelectorAll('.loading-overlay').forEach(l => l.style.display = 'none'); }
        function showError(message) { 
            const container = document.getElementById('errorContainer');
            const text = document.getElementById('errorText');
            if(container && text){ text.textContent = message; container.style.display = 'block'; setTimeout(()=> container.style.display='none',10000);}
        }

</script>



</body>
</html>