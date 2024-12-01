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
        .seance-card {
            transition: transform 0.2s, box-shadow 0.2s;
            margin-bottom: 20px;
            border: none;
            border-radius: 15px;
            overflow: hidden;
        }
        .seance-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .seance-header {
            background: linear-gradient(45deg, #0072ff, #00c6ff);
            color: white;
            padding: 15px;
        }
        .seance-body {
            padding: 20px;
            background: white;
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
            border: none;
        }
        .btn-floating:hover {
            transform: scale(1.1);
            color: white;
        }
        .filters {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .alert {
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .form-control, .form-select {
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            padding: 10px 15px;
        }
        .modal-content {
            border-radius: 15px;
            border: none;
        }
        .modal-header {
            background: linear-gradient(45deg, #0072ff, #00c6ff);
            color: white;
            border-radius: 15px 15px 0 0;
        }
        .btn {
            border-radius: 10px;
            padding: 8px 16px;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100 bg-light">
    <?php include '../includes/header.php'; ?>

    <div class="container mt-5 pt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="display-4">Gestion des Séances</h1>
        </div>

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
        <div class="filters mb-4">
            <form method="GET" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label" for="groupe_id">Filtrer par groupe</label>
                    <select class="form-select" name="groupe_id" id="groupe_id" onchange="this.form.submit()">
                        <option value="">Tous les groupes</option>
                        <?php foreach ($groupes as $groupe): ?>
                            <option value="<?php echo $groupe['id']; ?>" <?php echo $selectedGroupe == $groupe['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($groupe['nom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label" for="date">Filtrer par date</label>
                    <input type="date" class="form-control" name="date" id="date" 
                           value="<?php echo $selectedDate; ?>" onchange="this.form.submit()">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-2"></i>Filtrer
                    </button>
                </div>
            </form>
        </div>

        <!-- Liste des séances -->
        <div class="row">
            <?php foreach ($seances as $seance): ?>
                <div class="col-md-4 mb-4">
                    <div class="seance-card">
                        <div class="seance-header">
                            <h5 class="mb-0">
                                <i class="fas fa-calendar-alt me-2"></i>
                                <?php echo date('d/m/Y', strtotime($seance['date'])); ?>
                            </h5>
                        </div>
                        <div class="seance-body">
                            <p class="mb-3">
                                <i class="fas fa-users me-2"></i>
                                Groupe: <?php echo htmlspecialchars($seance['groupe_nom']); ?>
                            </p>
                            <form method="POST" class="d-inline me-2">
                                <input type="hidden" name="seance_id" value="<?php echo $seance['id']; ?>">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" 
                                        data-bs-target="#editModal<?php echo $seance['id']; ?>">
                                    <i class="fas fa-edit me-2"></i>Modifier
                                </button>
                            </form>
                            <form method="POST" action="delete_seance.php" class="d-inline">
                                <input type="hidden" name="seance_id" value="<?php echo $seance['id']; ?>">
                                <button type="submit" class="btn btn-danger" 
                                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette séance ?');">
                                    <i class="fas fa-trash me-2"></i>Supprimer
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Modal de modification -->
                    <div class="modal fade" id="editModal<?php echo $seance['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        <i class="fas fa-edit me-2"></i>Modifier la séance
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST">
                                    <div class="modal-body">
                                        <input type="hidden" name="action" value="edit">
                                        <input type="hidden" name="seance_id" value="<?php echo $seance['id']; ?>">
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Date de la séance</label>
                                            <input type="date" class="form-control" name="date" 
                                                   value="<?php echo $seance['date']; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Groupe</label>
                                            <select class="form-select" name="groupe_id" required>
                                                <?php foreach ($groupes as $groupe): ?>
                                                    <option value="<?php echo $groupe['id']; ?>" 
                                                            <?php echo $seance['groupe_id'] == $groupe['id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($groupe['nom']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Enregistrer
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Modal d'ajout de séance -->
        <div class="modal fade" id="addSeanceModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-plus-circle me-2"></i>Nouvelle Séance
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="action" value="add">
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
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Bouton flottant pour ajouter une séance -->
        <button type="button" class="btn-floating" data-bs-toggle="modal" data-bs-target="#addSeanceModal">
            <i class="fas fa-plus fa-2x"></i>
        </button>
    </div>

    <?php include '../includes/footer.php'; ?>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
