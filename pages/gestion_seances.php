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

// Traitement des filtres
$selectedGroupe = isset($_GET['groupe_id']) ? $_GET['groupe_id'] : '';
$selectedDate = isset($_GET['date']) ? $_GET['date'] : '';

// Traitement de la modification
if (isset($_POST['action']) && $_POST['action'] === 'edit') {
    $seance_id = $_POST['seance_id'];
    $new_date = $_POST['date'];
    $new_groupe_id = $_POST['groupe_id'];
    
    if (updateSeance($seance_id, $new_date, $new_groupe_id)) {
        $_SESSION['success_message'] = "La séance a été modifiée avec succès.";
    } else {
        $_SESSION['error_message'] = "Erreur lors de la modification de la séance.";
    }
    header('Location: gestion_seances.php');
    exit();
}

// Traitement de l'ajout
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $date = $_POST['date'];
    $groupe_id = $_POST['groupe_id'];
    
    if (addSeance($date, $groupe_id)) {
        $_SESSION['success_message'] = "La séance a été ajoutée avec succès.";
    } else {
        $_SESSION['error_message'] = "Erreur lors de l'ajout de la séance.";
    }
    header('Location: gestion_seances.php');
    exit();
}

// Récupération des séances filtrées
$seances = searchSeances($selectedDate, $selectedGroupe);
$groupes = getAllGroupes();

// Récupération des messages de session
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Séances</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #4CAF50 0%, #2196F3 100%);
            --secondary-gradient: linear-gradient(135deg, #FF9800 0%, #F44336 100%);
        }
        
        .seances-header {
            background: var(--primary-gradient);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 15px 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .filters {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .form-control, .form-select {
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.1);
            border-color: #2196F3;
        }

        .seance-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: none;
            margin-bottom: 2rem;
            transition: transform 0.3s ease;
        }

        .seance-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .seance-header {
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 1rem 1.5rem;
        }

        .seance-header h5 {
            margin: 0;
            font-size: 1.25rem;
        }

        .seance-body {
            padding: 1.5rem;
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
            color: white;
        }

        .btn-outline-danger {
            color: #F44336;
            border-color: #F44336;
        }

        .btn-outline-danger:hover {
            background: var(--secondary-gradient);
            border-color: transparent;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
            color: white;
        }

        .btn-group {
            display: flex;
            gap: 0.5rem;
        }

        .btn-group .btn {
            border-radius: 10px !important;
        }

        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .modal-header {
            background: var(--primary-gradient);
            color: white;
            border-radius: 15px 15px 0 0;
            border: none;
            padding: 1.5rem;
        }

        .modal-header .btn-close {
            filter: brightness(0) invert(1);
            opacity: 0.8;
        }

        .modal-header .btn-close:hover {
            opacity: 1;
        }

        .alert {
            border-radius: 10px;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 1rem 1.5rem;
        }
    </style>
</head>
<body class="bg-light">
    <?php include '../includes/header.php'; ?>

    <div class="seances-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="mb-0">Gestion des Séances</h1>
                <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addSeanceModal">
                    <i class="fas fa-plus"></i> Nouvelle Séance
                </button>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <!-- Filtres -->
        <div class="filters">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="date" class="form-label">Date</label>
                    <input type="date" class="form-control" id="date" name="date" value="<?php echo $selectedDate; ?>">
                </div>
                <div class="col-md-4">
                    <label for="groupe" class="form-label">Groupe</label>
                    <select class="form-select" id="groupe" name="groupe_id">
                        <option value="">Tous les groupes</option>
                        <?php foreach ($groupes as $groupe): ?>
                            <option value="<?php echo $groupe['id']; ?>" <?php echo $selectedGroupe == $groupe['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($groupe['nom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-2"></i>Rechercher
                    </button>
                    <a href="gestion_seances.php" class="btn btn-secondary">
                        <i class="fas fa-undo me-2"></i>Réinitialiser
                    </a>
                </div>
            </form>
        </div>

        <!-- Liste des séances -->
        <div class="row">
            <?php foreach ($seances as $seance): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="seance-card">
                        <div class="seance-header">
                            <h5 class="mb-0">
                                <i class="fas fa-calendar-alt me-2"></i>
                                <?php echo htmlspecialchars(date('d/m/Y', strtotime($seance['date']))); ?>
                            </h5>
                        </div>
                        <div class="seance-body">
                            <p class="mb-3">
                                <strong>Groupe:</strong> <?php echo htmlspecialchars($seance['groupe_nom'] ?? 'Non assigné'); ?>
                            </p>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editSeanceModal<?php echo $seance['id']; ?>">
                                    <i class="fas fa-edit"></i> Éditer
                                </button>
                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteSeanceModal<?php echo $seance['id']; ?>">
                                    <i class="fas fa-trash-alt"></i> Supprimer
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal de modification -->
                <div class="modal fade" id="editSeanceModal<?php echo $seance['id']; ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Modifier la séance</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="gestion_seances.php" method="POST">
                                <input type="hidden" name="action" value="edit">
                                <div class="modal-body">
                                    <input type="hidden" name="seance_id" value="<?php echo $seance['id']; ?>">
                                    <div class="mb-3">
                                        <label for="edit_date<?php echo $seance['id']; ?>" class="form-label">Date</label>
                                        <input type="date" class="form-control" id="edit_date<?php echo $seance['id']; ?>" name="date" value="<?php echo $seance['date']; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_groupe<?php echo $seance['id']; ?>" class="form-label">Groupe</label>
                                        <select class="form-select" id="edit_groupe<?php echo $seance['id']; ?>" name="groupe_id">
                                            <option value="">Sélectionner un groupe</option>
                                            <?php foreach ($groupes as $groupe): ?>
                                                <option value="<?php echo $groupe['id']; ?>" <?php echo $seance['groupe_id'] == $groupe['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($groupe['nom']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                        <i class="fas fa-times me-2"></i>Annuler
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Enregistrer
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal de suppression -->
                <div class="modal fade" id="deleteSeanceModal<?php echo $seance['id']; ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Supprimer la séance</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>Êtes-vous sûr de vouloir supprimer la séance du <?php echo htmlspecialchars(date('d/m/Y', strtotime($seance['date']))); ?> ?</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-2"></i>Annuler
                                </button>
                                <form method="POST" action="delete_seance.php">
                                    <input type="hidden" name="seance_id" value="<?php echo $seance['id']; ?>">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-trash-alt me-2"></i>Supprimer
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Modal d'ajout -->
        <div class="modal fade" id="addSeanceModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Nouvelle séance</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="gestion_seances.php" method="POST">
                        <input type="hidden" name="action" value="add">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="date" class="form-label">Date</label>
                                <input type="date" class="form-control" id="date" name="date" required>
                            </div>
                            <div class="mb-3">
                                <label for="groupe" class="form-label">Groupe</label>
                                <select class="form-select" id="groupe" name="groupe_id">
                                    <option value="">Sélectionner un groupe</option>
                                    <?php foreach ($groupes as $groupe): ?>
                                        <option value="<?php echo $groupe['id']; ?>">
                                            <?php echo htmlspecialchars($groupe['nom']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-2"></i>Annuler
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Ajouter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
