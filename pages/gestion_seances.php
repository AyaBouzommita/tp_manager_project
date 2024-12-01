<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/seance_operations.php';
require_once '../includes/groupe_operations.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['prof_id'])) {
    header('Location: login.php');
    exit();
}

// Récupérer les séances et les groupes
$seances = getAllSeances();
$groupes = getAllGroupes();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Séances</title>
    <?php include '../includes/header.php'; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .seance-card {
            transition: transform 0.2s, box-shadow 0.2s;
            margin-bottom: 20px;
        }
        .seance-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .seance-header {
            background: linear-gradient(45deg, #0072ff, #00c6ff);
            color: white;
            padding: 15px;
            border-radius: 8px 8px 0 0;
        }
        .seance-body {
            padding: 20px;
        }
        .btn-floating {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(45deg, #0072ff, #00c6ff);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: transform 0.2s;
            z-index: 1000;
        }
        .btn-floating:hover {
            transform: scale(1.1);
            color: white;
        }
        .filters {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100 bg-light">
    <div class="container mt-5 pt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="display-4">Gestion des Séances</h1>
        </div>

        <!-- Filtres -->
        <div class="filters mb-4">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="filterGroupe">Filtrer par groupe</label>
                        <select class="form-select" id="filterGroupe">
                            <option value="">Tous les groupes</option>
                            <?php foreach ($groupes as $groupe): ?>
                                <option value="<?php echo $groupe['id']; ?>">
                                    <?php echo htmlspecialchars($groupe['nom']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="filterDate">Filtrer par date</label>
                        <input type="date" class="form-control" id="filterDate">
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des séances -->
        <div class="row" id="seancesList">
            <?php foreach ($seances as $seance): ?>
                <div class="col-md-4 mb-4">
                    <div class="card seance-card">
                        <div class="seance-header">
                            <h5 class="mb-0">
                                <i class="fas fa-calendar-alt me-2"></i>
                                <?php echo date('d/m/Y', strtotime($seance['date'])); ?>
                            </h5>
                        </div>
                        <div class="seance-body">
                            <p class="mb-2">
                                <i class="fas fa-users me-2"></i>
                                Groupe: <?php echo htmlspecialchars($seance['groupe_nom']); ?>
                            </p>
                            <div class="btn-group w-100 mt-3">
                                <button class="btn btn-outline-primary" onclick="editSeance(<?php echo $seance['id']; ?>)">
                                    <i class="fas fa-edit"></i> Modifier
                                </button>
                                <button class="btn btn-outline-danger" onclick="deleteSeance(<?php echo $seance['id']; ?>)">
                                    <i class="fas fa-trash"></i> Supprimer
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Bouton flottant pour ajouter une séance -->
        <a href="#" class="btn-floating" data-bs-toggle="modal" data-bs-target="#addSeanceModal">
            <i class="fas fa-plus fa-2x"></i>
        </a>
    </div>

    <!-- Modal d'ajout de séance -->
    <div class="modal fade" id="addSeanceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle me-2"></i>Nouvelle Séance
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addSeanceForm">
                        <div class="mb-3">
                            <label class="form-label">Date de la séance</label>
                            <input type="date" class="form-control" name="date" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Groupe</label>
                            <select class="form-select" name="groupe_id" required>
                                <option value="">Sélectionner un groupe</option>
                                <?php foreach ($groupes as $groupe): ?>
                                    <option value="<?php echo $groupe['id']; ?>">
                                        <?php echo htmlspecialchars($groupe['nom']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" onclick="submitSeance()">
                        <i class="fas fa-save me-2"></i>Enregistrer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    
    <!-- Scripts spécifiques -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>
    <script>
        // Initialisation de Flatpickr pour les champs de date
        flatpickr("input[type=date]", {
            locale: "fr",
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d/m/Y"
        });

        // Fonction pour soumettre le formulaire d'ajout de séance
        function submitSeance() {
            const form = document.getElementById('addSeanceForm');
            const formData = new FormData(form);

            fetch('add_seance.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erreur lors de l\'ajout de la séance');
                }
            });
        }

        // Fonction pour éditer une séance
        function editSeance(id) {
            // Rediriger vers la page d'édition
            window.location.href = `edit_seance.php?id=${id}`;
        }

        // Fonction pour supprimer une séance
        function deleteSeance(id) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette séance ?')) {
                fetch(`delete_seance.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Erreur lors de la suppression de la séance');
                    }
                });
            }
        }

        // Filtrage des séances
        document.getElementById('filterGroupe').addEventListener('change', applyFilters);
        document.getElementById('filterDate').addEventListener('change', applyFilters);

        function applyFilters() {
            const groupe_id = document.getElementById('filterGroupe').value;
            const date = document.getElementById('filterDate').value;
            
            window.location.href = `gestion_seances.php?groupe_id=${groupe_id}&date=${date}`;
        }
    </script>
</body>
</html>
