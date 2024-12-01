<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/groupe_operations.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['prof_id'])) {
    header('Location: login.php');
    exit();
}

// Récupérer tous les groupes
$groupes = getAllGroupes();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Groupes</title>
    <?php include '../includes/header.php'; ?>
</head>
<body class="d-flex flex-column min-vh-100">
    <div class="container mt-5 pt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Gestion des Groupes</h1>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addGroupeModal">
                <i class="fas fa-plus"></i> Nouveau Groupe
            </button>
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
    <script>
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                var alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    var bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });
    </script>
</body>
</html>
