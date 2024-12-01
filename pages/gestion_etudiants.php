<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['prof_id'])) {
    header("Location: login.php");
    exit();
}

require '../includes/db.php';

// Récupérer tous les groupes
$groupes_result = $conn->query("SELECT * FROM groupes");
$groupes = $groupes_result->fetch_all(MYSQLI_ASSOC);

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['ajouter_etudiant'])) {
        $nom = $_POST['nom'];
        $prenom = $_POST['prenom'];
        $groupe_id = $_POST['groupe_id'];

        if (!empty($nom) && !empty($prenom) && !empty($groupe_id)) {
            $stmt = $conn->prepare("INSERT INTO etudiants (nom, prenom, groupe_id) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $nom, $prenom, $groupe_id);
            if ($stmt->execute()) {
                $success_message = "Étudiant ajouté avec succès.";
            } else {
                $error_message = "Erreur lors de l'ajout de l'étudiant.";
            }
        }
    } elseif (isset($_POST['modifier_etudiant'])) {
        $id = $_POST['etudiant_id'];
        $nom = $_POST['nom'];
        $prenom = $_POST['prenom'];
        $groupe_id = $_POST['groupe_id'];

        if (!empty($id) && !empty($nom) && !empty($prenom) && !empty($groupe_id)) {
            $stmt = $conn->prepare("UPDATE etudiants SET nom = ?, prenom = ?, groupe_id = ? WHERE id = ?");
            $stmt->bind_param("ssii", $nom, $prenom, $groupe_id, $id);
            if ($stmt->execute()) {
                $success_message = "Étudiant modifié avec succès.";
            } else {
                $error_message = "Erreur lors de la modification de l'étudiant.";
            }
        }
    }
}

// Supprimer un étudiant
if (isset($_GET['supprimer_etudiant_id'])) {
    $id = $_GET['supprimer_etudiant_id'];
    $stmt = $conn->prepare("DELETE FROM etudiants WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $success_message = "Étudiant supprimé avec succès.";
    } else {
        $error_message = "Erreur lors de la suppression de l'étudiant.";
    }
}

// Récupérer la liste des étudiants
$etudiants_result = $conn->query("
    SELECT e.*, g.nom AS groupe_nom 
    FROM etudiants e 
    LEFT JOIN groupes g ON e.groupe_id = g.id 
    ORDER BY e.nom, e.prenom
");
$etudiants = $etudiants_result->fetch_all(MYSQLI_ASSOC);

// Récupérer un étudiant pour modification
if (isset($_GET['get_etudiant'])) {
    $id = $_GET['get_etudiant'];
    $stmt = $conn->prepare("SELECT * FROM etudiants WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $etudiant = $stmt->get_result()->fetch_assoc();
    echo json_encode($etudiant);
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Étudiants</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/gestion_etudiants.css">
</head>
<body class="bg-light">
    <div class="gestion-etudiants-container">
        <div class="page-header">
            <h1 class="page-title">Gestion des Étudiants</h1>
            <div class="header-actions">
                <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                    <i class="fas fa-plus"></i> Nouvel Étudiant
                </button>
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
            </div>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?= $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?= $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="table-container">
            <table class="students-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Groupe</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($etudiants as $etudiant): ?>
                        <tr>
                            <td><?= $etudiant['id']; ?></td>
                            <td><?= htmlspecialchars($etudiant['nom']); ?></td>
                            <td><?= htmlspecialchars($etudiant['prenom']); ?></td>
                            <td><?= htmlspecialchars($etudiant['groupe_nom']); ?></td>
                            <td class="action-buttons">
                                <button class="btn btn-sm btn-primary me-2" onclick="editStudent(<?= $etudiant['id']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteStudent(<?= $etudiant['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Ajout Étudiant -->
    <div class="modal fade" id="addStudentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter un étudiant</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nom</label>
                            <input type="text" class="form-control" name="nom" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Prénom</label>
                            <input type="text" class="form-control" name="prenom" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Groupe</label>
                            <select class="form-select" name="groupe_id" required>
                                <option value="">Sélectionner un groupe</option>
                                <?php foreach ($groupes as $groupe): ?>
                                    <option value="<?= $groupe['id']; ?>">
                                        <?= htmlspecialchars($groupe['nom']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary" name="ajouter_etudiant">
                            <i class="fas fa-plus"></i> Ajouter
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Modification Étudiant -->
    <div class="modal fade" id="editStudentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modifier l'étudiant</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="etudiant_id" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label">Nom</label>
                            <input type="text" class="form-control" name="nom" id="edit_nom" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Prénom</label>
                            <input type="text" class="form-control" name="prenom" id="edit_prenom" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Groupe</label>
                            <select class="form-select" name="groupe_id" id="edit_groupe_id" required>
                                <option value="">Sélectionner un groupe</option>
                                <?php foreach ($groupes as $groupe): ?>
                                    <option value="<?= $groupe['id']; ?>">
                                        <?= htmlspecialchars($groupe['nom']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary" name="modifier_etudiant">
                            <i class="fas fa-save"></i> Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Confirmation Suppression -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmer la suppression</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer cet étudiant ?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <a href="#" id="confirmDeleteBtn" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Supprimer
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editStudent(id) {
            fetch(`?get_etudiant=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_id').value = data.id;
                    document.getElementById('edit_nom').value = data.nom;
                    document.getElementById('edit_prenom').value = data.prenom;
                    document.getElementById('edit_groupe_id').value = data.groupe_id;
                    
                    new bootstrap.Modal(document.getElementById('editStudentModal')).show();
                });
        }

        function deleteStudent(id) {
            const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
            document.getElementById('confirmDeleteBtn').href = `?supprimer_etudiant_id=${id}`;
            modal.show();
        }

        // Fermer automatiquement les alertes après 5 secondes
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });
    </script>
</body>
</html>
