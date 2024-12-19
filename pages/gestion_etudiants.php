<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['prof_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../includes/db.php';
require_once '../includes/etudiant_operations.php';
require_once '../includes/groupe_operations.php';
require_once '../includes/session_manager.php';

// Traitement pour ajouter un étudiant
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajouter_etudiant'])) {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $groupe_id = $_POST['groupe_id'];

    if (addEtudiant($nom, $prenom, $groupe_id)) {
        setFlashMessage('success', "L'étudiant $prenom $nom a été ajouté avec succès.");
        header("Location: " . $_SERVER['PHP_SELF'] . (isset($_GET['groupe_id']) ? "?groupe_id=" . $_GET['groupe_id'] : ""));
        exit();
    } else {
        setFlashMessage('danger', "Erreur lors de l'ajout de l'étudiant.");
    }
}

// Traitement pour modifier un étudiant
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['modifier_etudiant'])) {
    $etudiant_id = $_POST['etudiant_id'];
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $groupe_id = $_POST['groupe_id'];

    if (updateEtudiant($etudiant_id, $nom, $prenom, $groupe_id)) {
        setFlashMessage('success', "L'étudiant $prenom $nom a été modifié avec succès.");
        header("Location: " . $_SERVER['PHP_SELF'] . (isset($_GET['groupe_id']) ? "?groupe_id=" . $_GET['groupe_id'] : ""));
        exit();
    } else {
        setFlashMessage('danger', "Erreur lors de la modification de l'étudiant.");
    }
}

// Traitement pour supprimer un étudiant
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['supprimer_etudiant_id'])) {
    $etudiant_id = $_POST['supprimer_etudiant_id'];
    $etudiant = getEtudiantById($etudiant_id);

    if ($etudiant && deleteEtudiant($etudiant_id)) {
        setFlashMessage('success', "L'étudiant {$etudiant['prenom']} {$etudiant['nom']} a été supprimé avec succès.");
        header("Location: " . $_SERVER['PHP_SELF'] . (isset($_GET['groupe_id']) ? "?groupe_id=" . $_GET['groupe_id'] : ""));
        exit();
    } else {
        setFlashMessage('danger', "Erreur lors de la suppression de l'étudiant.");
    }
}

// Contrôle de l'affichage du formulaire d'ajout
$show_add_form = isset($_POST['show_add_form']);

// Récupération des groupes pour le formulaire
$groupes = getAllGroupes();

// Récupération des étudiants
$groupe_id = isset($_GET['groupe_id']) ? $_GET['groupe_id'] : null;
$search_term = isset($_GET['search']) ? $_GET['search'] : '';
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'asc';

if (!empty($search_term)) {
    $etudiants = searchEtudiants($search_term);
} else if ($groupe_id) {
    $etudiants = getEtudiantsByGroupe($groupe_id, $sort_order);
} else {
    $etudiants = getAllEtudiants($sort_order);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Étudiants - TP Manager</title>
    <!-- Les styles seront inclus via header.php -->
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #4CAF50 0%, #2196F3 100%);
            --secondary-gradient: linear-gradient(135deg, #FF9800 0%, #F44336 100%);
        }
        
        .students-header {
            background: var(--primary-gradient);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 15px 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            height: 100%;
            margin-bottom: 1rem;
        }

        .filter-section {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .filter-section .row {
                flex-direction: column;
            }
            
            .filter-section .col-md-4 {
                margin-bottom: 1rem;
            }
        }

        .stats-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: none;
            margin-bottom: 2rem;
        }

        .card-header {
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 1rem 1.5rem;
        }

        .card-header h2 {
            color: white;
            margin: 0;
            font-size: 1.25rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        .table {
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table thead th {
            background-color: #f8f9fa;
            color: #2c3e50;
            font-weight: 600;
            padding: 1rem;
            border-bottom: 2px solid #e9ecef;
        }

        .table td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #e9ecef;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        .form-control {
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.1);
            border-color: #2196F3;
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

        .btn-outline-success {
            color: #4CAF50;
            border-color: #4CAF50;
        }

        .btn-outline-success:hover {
            background: var(--primary-gradient);
            border-color: transparent;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
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
        }

        .alert {
            border-radius: 15px;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }

        .action-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
        }

        .action-buttons form {
            margin: 0;
        }

        .btn-sm {
            padding: 0.25rem 0.75rem;
            font-size: 0.875rem;
            white-space: nowrap;
        }
    </style>
</head>
<body class="bg-light">
    <?php include '../includes/header.php'; ?>

    <div class="container py-4">
        <!-- En-tête de la page -->
        <div class="students-header">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="mb-0"><i class="fas fa-user-graduate me-2"></i>Gestion des Étudiants</h1>
                    </div>
                </div>
            </div>
        </div>

        <?php 
        $flashMessage = getFlashMessage();
        if ($flashMessage): 
        ?>
            <div class="alert alert-<?= $flashMessage['type'] ?> alert-dismissible fade show" role="alert">
                <i class="fas <?= $flashMessage['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> me-2"></i>
                <?= htmlspecialchars($flashMessage['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Messages de succès/erreur -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i><?= $success_message; ?>
            </div>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i><?= $error_message; ?>
            </div>
        <?php endif; ?>

        <!-- Bouton pour afficher le formulaire d'ajout -->
        <?php if (!$show_add_form): ?>
            <div class="mb-4">
                <form method="POST" class="text-end">
                    <button type="submit" name="show_add_form" class="btn btn-success">
                        <i class="fas fa-plus me-2"></i>Ajouter un étudiant
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <!-- Formulaire d'ajout d'étudiant -->
        <?php if ($show_add_form): ?>
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2><i class="fas fa-user-plus me-2"></i>Ajouter un étudiant</h2>
                        <a href="<?= $_SERVER['PHP_SELF']; ?>" class="btn btn-outline-light">
                            <i class="fas fa-times me-2"></i>Annuler
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="nom" class="form-label">Nom</label>
                                <input type="text" class="form-control" id="nom" name="nom" required>
                            </div>
                            <div class="col-md-4">
                                <label for="prenom" class="form-label">Prénom</label>
                                <input type="text" class="form-control" id="prenom" name="prenom" required>
                            </div>
                            <div class="col-md-4">
                                <label for="groupe_id" class="form-label">Groupe</label>
                                <select class="form-control" id="groupe_id" name="groupe_id" required>
                                    <?php foreach ($groupes as $groupe): ?>
                                        <option value="<?= $groupe['id']; ?>"><?= htmlspecialchars($groupe['nom']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="text-end mt-3">
                            <button type="submit" name="ajouter_etudiant" class="btn btn-outline-success">
                                <i class="fas fa-check me-2"></i>Ajouter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <!-- Filtres -->
        <div class="filter-section">
            <form method="GET" class="row align-items-end">
                <div class="col-md-4">
                    <label for="groupe_id" class="form-label">
                        <i class="fas fa-users me-2"></i>Sélectionner un groupe
                    </label>
                    <select name="groupe_id" id="groupe_id" class="form-select">
                        <option value="">Tous les groupes</option>
                        <?php foreach ($groupes as $groupe): ?>
                            <option value="<?php echo $groupe['id']; ?>" <?php echo (isset($_GET['groupe_id']) && $_GET['groupe_id'] == $groupe['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($groupe['nom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="search" class="form-label">
                        <i class="fas fa-search me-2"></i>Rechercher un étudiant
                    </label>
                    <input type="text" name="search" id="search" class="form-control" placeholder="Nom, prénom ou email..." value="<?php echo htmlspecialchars($search_term); ?>">
                </div>
                <div class="col-md-2">
                    <label for="sort_order" class="form-label">
                        <i class="fas fa-sort-alpha-down me-2"></i>Tri
                    </label>
                    <select name="sort_order" id="sort_order" class="form-select">
                        <option value="asc" <?php echo $sort_order === 'asc' ? 'selected' : ''; ?>>A-Z</option>
                        <option value="desc" <?php echo $sort_order === 'desc' ? 'selected' : ''; ?>>Z-A</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-2"></i>Filtrer
                    </button>
                </div>
            </form>
        </div>

        <!-- Liste des étudiants -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h2><i class="fas fa-users me-2"></i>Liste des étudiants</h2>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Groupe</th>
                                <th class="text-end" style="width: 200px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($etudiants)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4">
                                        <i class="fas fa-info-circle me-2"></i>Aucun étudiant trouvé.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($etudiants as $etudiant): ?>
                                    <?php if (isset($_POST['edit']) && $_POST['etudiant_id'] == $etudiant['id']): ?>
                                        <tr>
                                            <td colspan="4">
                                                <form method="POST" action="">
                                                    <input type="hidden" name="etudiant_id" value="<?= $etudiant['id']; ?>">
                                                    <div class="row g-2">
                                                        <div class="col-md-3">
                                                            <input type="text" class="form-control" name="nom" value="<?= htmlspecialchars($etudiant['nom']); ?>" required>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <input type="text" class="form-control" name="prenom" value="<?= htmlspecialchars($etudiant['prenom']); ?>" required>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <select class="form-control" name="groupe_id" required>
                                                                <?php foreach ($groupes as $groupe): ?>
                                                                    <option value="<?= $groupe['id']; ?>" <?= ($etudiant['groupe_id'] == $groupe['id']) ? 'selected' : ''; ?>>
                                                                        <?= htmlspecialchars($groupe['nom']); ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <button type="submit" name="modifier_etudiant" class="btn btn-outline-success me-2">Enregistrer</button>
                                                            <a href="<?= $_SERVER['PHP_SELF']; ?>" class="btn btn-outline-secondary">Annuler</a>
                                                        </div>
                                                    </div>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <tr>
                                            <td><?= htmlspecialchars($etudiant['nom']); ?></td>
                                            <td><?= htmlspecialchars($etudiant['prenom']); ?></td>
                                            <td><?= htmlspecialchars($etudiant['groupe_nom']); ?></td>
                                            <td class="text-end">
                                                <div class="action-buttons">
                                                    <button type="submit" name="edit" form="edit_form_<?= $etudiant['id']; ?>" class="btn btn-outline-info btn-sm">
                                                        <i class="fas fa-edit me-1"></i>Modifier
                                                    </button>
                                                    <button type="submit" form="delete_form_<?= $etudiant['id']; ?>" class="btn btn-outline-danger btn-sm">
                                                        <i class="fas fa-trash me-1"></i>Supprimer
                                                    </button>
                                                </div>
                                                <form id="edit_form_<?= $etudiant['id']; ?>" method="POST" action="" class="d-none">
                                                    <input type="hidden" name="etudiant_id" value="<?= $etudiant['id']; ?>">
                                                </form>
                                                <form id="delete_form_<?= $etudiant['id']; ?>" method="POST" action="" class="d-none">
                                                    <input type="hidden" name="supprimer_etudiant_id" value="<?= $etudiant['id']; ?>">
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html> 