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
                    <div class="col-md-6">
                        <label for="globalRegionSelect" class="form-label">Filtrer par région :</label>
                        <select id="globalRegionSelect" class="form-select">
                            <option value="">Toutes les régions</option>
                            <!-- Options ajoutées dynamiquement -->
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="globalYearSelect" class="form-label">Filtrer par année :</label>
                        <select id="globalYearSelect" class="form-select">
                            <option value="">Toutes les années</option>
                            <!-- Options ajoutées dynamiquement -->
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
                            <span class="nombre" id="hommes">0</span>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="stat-card" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                            <i class="fas fa-female"></i>
                            <h5>Femmes</h5>
                            <span class="nombre" id="femmes">0</span>
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
                            
                            <!-- Inscriptions par mois -->
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
        // Définition d'une palette de couleurs cohérente
        const colorPalette = {
            primary: ['#3498db', '#2ecc71', '#e74c3c', '#f39c12', '#9b59b6', '#1abc9c', '#34495e', '#e67e22'],
            sexe: ['#3498db', '#e74c3c'], // Homme, Femme
            qualitative: ['#3498db', '#2ecc71', '#e74c3c', '#f39c12', '#9b59b6', '#1abc9c', '#34495e', '#e67e22', '#16a085', '#27ae60', '#2980b9', '#8e44ad', '#f1c40f', '#d35400', '#c0392b', '#7f8c8d']
        };

        let allData = {};
        let charts = {};
        let filters = {
            region: '',
            year: ''
        };

        document.addEventListener("DOMContentLoaded", () => {
            // Afficher tous les indicateurs de chargement
            showAllLoaders();
            
            fetch("data.php")
                .then(res => {
                    if (!res.ok) {
                        throw new Error(`Erreur HTTP: ${res.status}`);
                    }
                    return res.json();
                })
                .then(data => {
                    // Vérifier si la réponse contient une erreur
                    if (data.error) {
                        throw new Error(data.message || 'Erreur inconnue');
                    }
                    
                    allData = data;
                    initializeDashboard(data);
                    hideAllLoaders();
                })
                .catch(err => {
                    console.error("Erreur:", err);
                    hideAllLoaders();
                    showError(`Impossible de charger les données: ${err.message}`);
                });
        });

        function initializeDashboard(data) {
            // Mise à jour des statistiques globales
            updateStats(data);
            
            // Initialisation des filtres
            initializeFilters(data);
            
            // Initialisation des graphiques
            initializeCharts(data);
            
            // Appliquer les écouteurs d'événements
            setupEventListeners();
        }
        
        function updateStats(data) {
            document.getElementById("total").textContent = data.total.toLocaleString();
            document.getElementById("hommes").textContent = data.hommes.toLocaleString();
            document.getElementById("femmes").textContent = data.femmes.toLocaleString();
            document.getElementById("age_moyen").textContent = data.age_moyen.toFixed(1);
        }
        
        function initializeFilters(data) {
            // Filtre des régions
            const regionSelect = document.getElementById("globalRegionSelect");
            data.par_region.forEach(item => {
                const option = document.createElement("option");
                option.value = item.Region;
                option.textContent = item.Region;
                regionSelect.appendChild(option);
            });
            
            // Filtre des années
            const yearSelect = document.getElementById("globalYearSelect");
            const annees = [...new Set(data.par_mois.map(item => item.annee))].sort();
            annees.forEach(annee => {
                const option = document.createElement("option");
                option.value = annee;
                option.textContent = annee;
                yearSelect.appendChild(option);
            });
        }
        
        function initializeCharts(data) {
            createSexeChart(data);
            createRegionChart(data);
            createMoisChart(data);
            createCotisationChart(data);
            createRegimeChart(data);
        }
        
        function setupEventListeners() {
            // Filtre global par région
            document.getElementById("globalRegionSelect").addEventListener("change", function() {
                filters.region = this.value;
                applyFilters();
            });
            
            // Filtre global par année
            document.getElementById("globalYearSelect").addEventListener("change", function() {
                filters.year = this.value;
                applyFilters();
            });
            
            // Réinitialisation des filtres
            document.getElementById("resetFilters").addEventListener("click", function() {
                document.getElementById("globalRegionSelect").value = "";
                document.getElementById("globalYearSelect").value = "";
                filters.region = '';
                filters.year = '';
                applyFilters();
            });
        }
        
        function applyFilters() {
            // Afficher les indicateurs de chargement
            showAllLoaders();
            
            // Simuler un délai de traitement (à remplacer par un vrai appel API si nécessaire)
            setTimeout(() => {
                // Dans une application réelle, vous feriez un appel API ici
                // avec les filtres appliqués pour récupérer les données filtrées
                
                // Pour cette démo, nous allons simplement filtrer les données existantes
                let filteredData = JSON.parse(JSON.stringify(allData));
                
                // Filtrer par région si spécifié
                if (filters.region) {
                    filteredData.par_region = filteredData.par_region.filter(item => item.Region === filters.region);
                }
                
                // Filtrer par année si spécifié
                if (filters.year) {
                    filteredData.par_mois = filteredData.par_mois.filter(item => item.annee == filters.year);
                }
                
                // Mettre à jour les graphiques avec les données filtrées
                updateCharts(filteredData);
                
                // Masquer les indicateurs de chargement
                hideAllLoaders();
            }, 500);
        }
        
        function updateCharts(data) {
            // Mise à jour des statistiques
            updateStats(data);
            
            // Mise à jour de chaque graphique
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
        
        function createSexeChart(data) {
            const ctx = document.getElementById('sexeChart').getContext('2d');
            charts.sexe = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Hommes', 'Femmes'],
                    datasets: [{
                        data: [data.hommes, data.femmes],
                        backgroundColor: colorPalette.sexe,
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: {
                                    size: 12
                                },
                                padding: 20
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((context.parsed / total) * 100);
                                    return `${context.label}: ${context.parsed.toLocaleString()} (${percentage}%)`;
                                }
                            }
                        }
                    },
                    cutout: '60%'
                }
            });
        }
        
        function createRegionChart(data) {
            const regionLabels = data.par_region.map(item => item.Region);
            const regionData = data.par_region.map(item => parseInt(item.total));
            
            const ctx = document.getElementById('regionChart').getContext('2d');
            charts.region = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: regionLabels,
                    datasets: [{
                        label: 'Bénéficiaires',
                        data: regionData,
                        backgroundColor: colorPalette.primary[0],
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString();
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                font: {
                                    size: 12
                                },
                                padding: 15
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `${context.dataset.label}: ${context.parsed.x.toLocaleString()}`;
                                }
                            }
                        }
                    }
                }
            });
        }
        
        function createMoisChart(data) {
            const moisLabels = [
                'Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin',
                'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'
            ];
            
            // Préparer les données pour les 12 mois
            const moisData = Array(12).fill(0);
            data.par_mois.forEach(item => {
                const moisIndex = parseInt(item.mois) - 1;
                moisData[moisIndex] = parseInt(item.total);
            });
            
            const ctx = document.getElementById('moisChart').getContext('2d');
            charts.mois = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: moisLabels,
                    datasets: [{
                        label: 'Inscriptions',
                        data: moisData,
                        backgroundColor: 'rgba(52, 152, 219, 0.2)',
                        borderColor: colorPalette.primary[0],
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: colorPalette.primary[0],
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString();
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top'
                        }
                    }
                }
            });
        }
        
        function createCotisationChart(data) {
            const labels = data.par_type_cotisation.map(item => item.Type_Cotisation);
            const values = data.par_type_cotisation.map(item => parseInt(item.total));
            
            const ctx = document.getElementById('cotisationChart').getContext('2d');
            charts.cotisation = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: colorPalette.qualitative.slice(0, labels.length),
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: {
                                    size: 11
                                },
                                padding: 15
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((context.parsed / total) * 100);
                                    return `${context.label}: ${context.parsed.toLocaleString()} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }
        
        function createRegimeChart(data) {
            const labels = data.par_regime.map(item => item.Regime);
            const values = data.par_regime.map(item => parseInt(item.total));
            
            const ctx = document.getElementById('regimeChart').getContext('2d');
            charts.regime = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Bénéficiaires',
                        data: values,
                        backgroundColor: [colorPalette.primary[1], colorPalette.primary[3]],
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString();
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                font: {
                                    size: 12
                                },
                                padding: 15
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `${context.dataset.label}: ${context.parsed.y.toLocaleString()}`;
                                }
                            }
                        }
                    }
                }
            });
        }
        
        function showAllLoaders() {
            document.querySelectorAll('.loading-overlay').forEach(loader => {
                loader.style.display = 'flex';
            });
        }
        
        function hideAllLoaders() {
            document.querySelectorAll('.loading-overlay').forEach(loader => {
                loader.style.display = 'none';
            });
        }
        
        function showError(message) {
            const errorContainer = document.getElementById('errorContainer');
            const errorText = document.getElementById('errorText');
            
            errorText.textContent = message;
            errorContainer.style.display = 'block';
            
            // Masquer automatiquement après 10 secondes
            setTimeout(() => {
                errorContainer.style.display = 'none';
            }, 10000);
        }
    </script>
</body>
</html>