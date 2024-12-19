<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['prof_id'])) {
    header("Location: login.php");
    exit();
}

require '../includes/db.php';

// Récupérer les informations du professeur
$prof_id = $_SESSION['prof_id'];
$stmt = $conn->prepare("SELECT id, nom, prenom FROM professeurs WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $prof_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $prof = $result->fetch_assoc();
    $stmt->close();
} else {
    $prof = array('nom' => '', 'prenom' => '');
}

// Vérifier si les données du professeur existent
$nom_prof = isset($prof['nom']) ? htmlspecialchars($prof['nom']) : '';
$prenom_prof = isset($prof['prenom']) ? htmlspecialchars($prof['prenom']) : '';

// Récupérer les statistiques de manière sécurisée
try {
    $total_etudiants = $conn->query("SELECT COUNT(*) as count FROM etudiants")->fetch_assoc()['count'] ?? 0;
    $total_groupes = $conn->query("SELECT COUNT(*) as count FROM groupes")->fetch_assoc()['count'] ?? 0;
    $total_seances = $conn->query("SELECT COUNT(*) as count FROM seances")->fetch_assoc()['count'] ?? 0;
} catch (Exception $e) {
    $total_etudiants = 0;
    $total_groupes = 0;
    $total_seances = 0;
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Gestion des TP</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #4CAF50 0%, #2196F3 100%);
            --secondary-gradient: linear-gradient(135deg, #FF9800 0%, #F44336 100%);
        }
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .dashboard-header {
            background: var(--primary-gradient);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 15px 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            height: 100%;
            margin-bottom: 1rem;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .menu-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: none;
            margin-bottom: 2rem;
            transition: transform 0.3s ease;
        }
        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .menu-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .card-link {
            text-decoration: none;
            color: inherit;
        }
        .card-link:hover {
            color: inherit;
        }
        .welcome-name {
            font-weight: 600;
            font-size: 1.8rem;
            color: white;
        }
        .recent-activity {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .btn {
            border-radius: 10px;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-outline-primary {
            color: #2196F3;
            border-color: #2196F3;
        }
        .btn-outline-primary:hover {
            background: var(--primary-gradient);
            border-color: transparent;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="dashboard-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="welcome-name">
                        Bienvenue<?= ($nom_prof || $prenom_prof) ? ', ' . trim($prenom_prof . ' ' . $nom_prof) : '' ?>
                    </h1>
                    <p class="mb-0">Gérez vos travaux pratiques et suivez vos étudiants</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <span class="text-white-50"><?= date('d/m/Y') ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="container pb-5">
        <!-- Statistiques -->
        <div class="row mb-5">
            <div class="col-md-4 mb-4">
                <div class="stat-card p-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-users stat-icon"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="h2 mb-0"><?= $total_etudiants ?></h3>
                            <p class="text-muted mb-0">Étudiants</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="stat-card p-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-layer-group stat-icon"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="h2 mb-0"><?= $total_groupes ?></h3>
                            <p class="text-muted mb-0">Groupes</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="stat-card p-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-calendar-check stat-icon"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="h2 mb-0"><?= $total_seances ?></h3>
                            <p class="text-muted mb-0">Séances</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Menu principal -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-5">
            <div class="col">
                <a href="gestion_groupes.php" class="card-link">
                    <div class="card menu-card h-100">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-layer-group menu-icon"></i>
                            <h5 class="card-title">Gestion des Groupes</h5>
                            <p class="card-text text-muted">Créez et gérez vos groupes d'étudiants</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col">
                <a href="gestion_etudiants.php" class="card-link">
                    <div class="card menu-card h-100">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-user-graduate menu-icon"></i>
                            <h5 class="card-title">Gestion des Étudiants</h5>
                            <p class="card-text text-muted">Gérez la liste de vos étudiants</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col">
                <a href="gestion_seances.php" class="card-link">
                    <div class="card menu-card h-100">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-calendar-alt menu-icon"></i>
                            <h5 class="card-title">Gestion des Séances</h5>
                            <p class="card-text text-muted">Planifiez vos séances de TP</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col">
                <a href="gestion_presences.php" class="card-link">
                    <div class="card menu-card h-100">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-clipboard-check menu-icon"></i>
                            <h5 class="card-title">Gestion des Présences</h5>
                            <p class="card-text text-muted">Suivez les présences aux séances</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col">
                <a href="gestion_notes.php" class="card-link">
                    <div class="card menu-card h-100">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-star menu-icon"></i>
                            <h5 class="card-title">Gestion des Notes</h5>
                            <p class="card-text text-muted">Gérez les notes des étudiants</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Activités récentes -->
        <div class="recent-activity p-4">
            <h4 class="mb-4">
                <i class="fas fa-history me-2 text-primary"></i>
                Activités récentes
            </h4>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Action</th>
                            <th>Détails</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><small class="text-muted"><?= date('d/m/Y H:i', strtotime('+1 hour')); ?></small></td>
                            <td>Connexion</td>
                            <td>Dernière connexion réussie</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
