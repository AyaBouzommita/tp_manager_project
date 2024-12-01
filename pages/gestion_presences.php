<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['prof_id'])) {
    header("Location: login.php");
    exit();
}

require '../includes/db.php';

// Récupérer les séances avec les informations de présence
$query = "SELECT s.*, g.nom as nom_groupe,
          (SELECT COUNT(*) FROM presences p WHERE p.seance_id = s.id AND p.presence = 1) as presents_count,
          (SELECT COUNT(*) FROM presences p WHERE p.seance_id = s.id AND p.presence = 0) as absents_count
          FROM seances s 
          LEFT JOIN groupes g ON s.groupe_id = g.id 
          ORDER BY s.date DESC";
$result = $conn->query($query);
$seances = $result->fetch_all(MYSQLI_ASSOC);

// Récupérer la liste des groupes pour le filtre
$query_groupes = "SELECT id, nom FROM groupes ORDER BY nom";
$result_groupes = $conn->query($query_groupes);
$groupes = $result_groupes->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Présences - TP Manager</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        .presence-header {
            background: linear-gradient(135deg, #0072ff 0%, #00c6ff 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 15px 15px;
        }
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            height: 100%;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .stats-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #0072ff 0%, #00c6ff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .presence-table {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .presence-badge {
            padding: 0.5em 1em;
            border-radius: 20px;
            font-size: 0.85em;
        }
        .btn-presence {
            border-radius: 20px;
            padding: 0.4em 1em;
            font-size: 0.9em;
            transition: all 0.3s ease;
        }
        .btn-presence:hover {
            transform: translateY(-2px);
        }
        .dataTables_wrapper {
            padding: 1rem;
        }
        .table > :not(caption) > * > * {
            padding: 1rem;
        }
    </style>
</head>
<body class="bg-light">
    <?php include '../includes/header.php'; ?>

    <div class="presence-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-4 mb-0">Gestion des Présences</h1>
                    <p class="lead mb-0">Suivi des présences aux séances de TP</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <span class="text-white-50"><?= date('d/m/Y') ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="container pb-5">
        <!-- Statistiques -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="stats-card">
                    <i class="fas fa-calendar-check stats-icon"></i>
                    <h3 class="h5 text-muted">Total des Séances</h3>
                    <h2 class="display-6 mb-0"><?= count($seances) ?></h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <i class="fas fa-clock stats-icon"></i>
                    <h3 class="h5 text-muted">Séances Aujourd'hui</h3>
                    <h2 class="display-6 mb-0">
                        <?php
                        $today = date('Y-m-d');
                        echo count(array_filter($seances, function($s) use ($today) {
                            return $s['date'] === $today;
                        }));
                        ?>
                    </h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <i class="fas fa-users stats-icon"></i>
                    <h3 class="h5 text-muted">Groupes Actifs</h3>
                    <h2 class="display-6 mb-0"><?= count($groupes) ?></h2>
                </div>
            </div>
        </div>

        <!-- Tableau des présences -->
        <div class="presence-table">
            <div class="table-responsive">
                <table id="presenceTable" class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Groupe</th>
                            <th>Horaire</th>
                            <th>Présences</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($seances as $seance): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($seance['date'])) ?></td>
                                <td><?= htmlspecialchars($seance['nom_groupe']) ?></td>
                                <td>
                                    <?php 
                                    $debut = $seance['heure_debut'] ?? 'Non définie';
                                    $fin = $seance['heure_fin'] ?? 'Non définie';
                                    echo "$debut - $fin";
                                    ?>
                                </td>
                                <td>
                                    <span class="badge bg-success presence-badge">
                                        <i class="fas fa-check me-1"></i><?= (int)$seance['presents_count'] ?>
                                    </span>
                                    <span class="badge bg-danger presence-badge ms-2">
                                        <i class="fas fa-times me-1"></i><?= (int)$seance['absents_count'] ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $now = time();
                                    $seanceTime = strtotime($seance['date'] . ' ' . $debut);
                                    $seanceFin = strtotime($seance['date'] . ' ' . $fin);
                                    
                                    if ($now < $seanceTime) {
                                        echo '<span class="badge bg-warning text-dark">À venir</span>';
                                    } elseif ($now > $seanceFin) {
                                        echo '<span class="badge bg-secondary">Terminée</span>';
                                    } else {
                                        echo '<span class="badge bg-success">En cours</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <a href="marquer_presences.php?id=<?= $seance['id'] ?>" 
                                       class="btn btn-primary btn-presence">
                                        <i class="fas fa-clipboard-check me-1"></i>Marquer
                                    </a>
                                    <a href="voir_presences.php?id=<?= $seance['id'] ?>" 
                                       class="btn btn-info btn-presence text-white ms-2">
                                        <i class="fas fa-eye me-1"></i>Voir
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#presenceTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/fr-FR.json'
                },
                order: [[0, 'desc']],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Tous"]],
                responsive: true
            });
        });
    </script>
</body>
</html>
