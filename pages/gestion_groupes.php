<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/groupe_operations.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['prof_id'])) {
    header('Location: login.php');
    exit();
}

// Récupérer le terme de recherche
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

// Récupérer les groupes (filtrés ou tous)
$groupes = !empty($searchTerm) ? searchGroupes($searchTerm) : getAllGroupes();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Groupes</title>
    <?php include '../includes/header.php'; ?>
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #4CAF50 0%, #2196F3 100%);
            --secondary-gradient: linear-gradient(135deg, #FF9800 0%, #F44336 100%);
        }
        
        .groups-header {
            background: var(--primary-gradient);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 15px 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .search-container {
            margin-bottom: 2rem;
        }

        .search-container .input-group {
            max-width: 500px;
        }

        .search-container .form-control {
            border-radius: 10px 0 0 10px;
            border: 1px solid #e0e0e0;
            padding: 0.75rem 1rem;
            box-shadow: none;
        }

        .search-container .btn {
            border-radius: 0 10px 10px 0;
            padding: 0.75rem 1.5rem;
        }

        .card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: none;
            margin-bottom: 2rem;
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .card-header {
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 1rem 1.5rem;
        }

        .card-header h5 {
            margin: 0;
            font-size: 1.25rem;
        }

        .card-body {
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

        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .modal-header {
            border-radius: 15px 15px 0 0;
            border: none;
        }

        .modal-header.bg-primary {
            background: var(--primary-gradient) !important;
        }

        .modal-header.bg-danger {
            background: var(--secondary-gradient) !important;
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

        .alert {
            border-radius: 10px;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="bg-light">
    <div class="groups-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="mb-0">Gestion des Groupes</h1>
                <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addGroupeModal">
                    <i class="fas fa-plus"></i> Nouveau Groupe
                </button>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Barre de recherche -->
        <div class="search-container">
            <form action="" method="GET" class="mb-4">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Rechercher un groupe..." 
                           name="search" value="<?php echo htmlspecialchars($searchTerm); ?>">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                    <?php if (!empty($searchTerm)): ?>
                        <a href="?" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Réinitialiser
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (empty($groupes) && !empty($searchTerm)): ?>
            <div class="alert alert-info">
                Aucun groupe trouvé pour "<?php echo htmlspecialchars($searchTerm); ?>"
            </div>
        <?php endif; ?>

        <div class="row">
            <?php foreach ($groupes as $groupe): ?>
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-users me-2"></i>
                                <?php echo htmlspecialchars($groupe['nom']); ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="btn-group w-100">
                                <button class="btn btn-outline-primary" data-bs-toggle="modal" 
                                        data-bs-target="#editGroupeModal<?php echo $groupe['id']; ?>">
                                    <i class="fas fa-edit"></i> Éditer
                                </button>
                                <button class="btn btn-outline-danger" data-bs-toggle="modal" 
                                        data-bs-target="#deleteGroupeModal<?php echo $groupe['id']; ?>">
                                    <i class="fas fa-trash"></i> Supprimer
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Modal Ajout Groupe -->
    <div class="modal fade" id="addGroupeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Ajouter un Groupe</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="add_groupe.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom du groupe</label>
                            <input type="text" class="form-control" id="nom" name="nom" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modals pour édition et suppression -->
    <?php foreach ($groupes as $groupe): ?>
        <!-- Modal Édition -->
        <div class="modal fade" id="editGroupeModal<?php echo $groupe['id']; ?>" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Modifier le Groupe</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="edit_groupe.php" method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="id" value="<?php echo $groupe['id']; ?>">
                            <div class="mb-3">
                                <label for="nom<?php echo $groupe['id']; ?>" class="form-label">Nom du groupe</label>
                                <input type="text" class="form-control" id="nom<?php echo $groupe['id']; ?>" 
                                       name="nom" value="<?php echo htmlspecialchars($groupe['nom']); ?>" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Suppression -->
        <div class="modal fade" id="deleteGroupeModal<?php echo $groupe['id']; ?>" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Confirmer la suppression</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Êtes-vous sûr de vouloir supprimer le groupe "<?php echo htmlspecialchars($groupe['nom']); ?>" ?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <form action="delete_groupe.php" method="POST" class="d-inline">
                            <input type="hidden" name="id" value="<?php echo $groupe['id']; ?>">
                            <button type="submit" class="btn btn-danger">Supprimer</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <?php include '../includes/footer.php'; ?>
    <?php
    // Si un message de succès ou d'erreur existe, on le supprime après 5 secondes
    if (isset($_SESSION['success']) || isset($_SESSION['error'])) {
        header("Refresh:5; url=" . $_SERVER['PHP_SELF']);
    }
    ?>
</body>
</html>
