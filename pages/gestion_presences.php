<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['prof_id'])) {
    header("Location: login.php");
    exit();
}

require '../includes/db.php';

// Traitement du formulaire de présence
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['marquer_presence'])) {
    $seance_id = $_POST['seance_id'];
    
    $conn->begin_transaction();
    try {

        // Marquer les nouvelles absences
        if (isset($_POST['absence']) && !empty($_POST['absence'])) {
            // Préparer la requête pour vérifier si l'étudiant est déjà absent à cette séance
            $check_stmt = $conn->prepare("
                SELECT COUNT(*) as already_absent 
                FROM presences 
                WHERE etudiant_id = ? AND seance_id = ? AND presence = 1");

            // Préparer la requête pour insérer une nouvelle absence
            $insert_stmt = $conn->prepare("
                INSERT INTO presences (etudiant_id, seance_id, presence) 
                VALUES (?, ?, 1)");

            foreach ($_POST['absence'] as $etudiant_id => $value) {
                // Vérifier si l'étudiant est déjà absent à cette séance
                $check_stmt->bind_param("ii", $etudiant_id, $seance_id);
                $check_stmt->execute();
                $result = $check_stmt->get_result();
                $row = $result->fetch_assoc();

                // Si l'étudiant n'est pas déjà absent à cette séance
                if ($row['already_absent'] == 0) {
                    // Insérer la nouvelle absence
                    $insert_stmt->bind_param("ii", $etudiant_id, $seance_id);
                    $insert_stmt->execute();
                }
            }
        }

        // Supprimer les absences pour les étudiants non cochés
        $delete_stmt = $conn->prepare("
            DELETE FROM presences 
            WHERE etudiant_id = ? AND seance_id = ? AND presence = 1");

        $get_students_stmt = $conn->prepare("
            SELECT id FROM etudiants WHERE groupe_id = (
                SELECT groupe_id FROM seances WHERE id = ?
            )");
        $get_students_stmt->bind_param("i", $seance_id);
        $get_students_stmt->execute();
        $result_students = $get_students_stmt->get_result();

        while ($student = $result_students->fetch_assoc()) {
            if (!isset($_POST['absence'][$student['id']])) {
                $delete_stmt->bind_param("ii", $student['id'], $seance_id);
                $delete_stmt->execute();
            }
        }

        $conn->commit();
        $success_message = "Présences enregistrées avec succès.";
    } catch (Exception $e) {
        $conn->rollback();
        $error_message = $e->getMessage();
    }
}

// Récupérer la liste des groupes
$result_groupes = $conn->query("SELECT * FROM groupes ORDER BY nom");
$groupes = $result_groupes->fetch_all(MYSQLI_ASSOC);

// Récupérer les séances en fonction du groupe sélectionné
$seances = [];
if (isset($_GET['groupe_id'])) {
    $groupe_id = $_GET['groupe_id'];
    $stmt = $conn->prepare("SELECT * FROM seances WHERE groupe_id = ? ORDER BY date DESC");
    $stmt->bind_param("i", $groupe_id);
    $stmt->execute();
    $result_seances = $stmt->get_result();
    $seances = $result_seances->fetch_all(MYSQLI_ASSOC);
}

// Récupérer les étudiants pour la séance et le groupe sélectionnés
$etudiants = [];
if (isset($_GET['seance_id']) && isset($_GET['groupe_id'])) {
    $seance_id = $_GET['seance_id'];
    $groupe_id = $_GET['groupe_id'];
    
    $stmt = $conn->prepare("SELECT 
        e.id, 
        e.nom, 
        e.prenom, 
        IFNULL(p_current.presence, 0) as is_absent,
        (SELECT COUNT(*) 
         FROM presences p 
         WHERE p.etudiant_id = e.id 
         AND p.presence = 1) as total_absences
        FROM etudiants e
        INNER JOIN groupes g ON e.groupe_id = g.id
        LEFT JOIN presences p_current ON p_current.etudiant_id = e.id AND p_current.seance_id = ?
        WHERE e.groupe_id = ?
        ORDER BY e.nom, e.prenom");
    
    $stmt->bind_param("ii", $seance_id, $groupe_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $etudiants = $result->fetch_all(MYSQLI_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Présences - TP Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #4CAF50 0%, #2196F3 100%);
            --secondary-gradient: linear-gradient(135deg, #FF9800 0%, #F44336 100%);
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .main-content {
            padding-top: 70px;
        }
        
        .presences-header {
            background: var(--primary-gradient);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 15px 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
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
            color: white;
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

        .btn {
            border-radius: 10px;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }

        .alert {
            border-radius: 10px;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
        }

        .form-check-input {
            cursor: pointer;
            width: 1.2em;
            height: 1.2em;
        }

        .absences-count {
            font-weight: 500;
            padding: .25rem .75rem;
            border-radius: 20px;
            background: #e9ecef;
            color: #2c3e50;
        }

        .select-session {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .btn-back {
            color: #6c757d;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            color: #495057;
            transform: translateX(-2px);
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="main-content">
        <div class="presences-header">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="mb-0">Gestion des Présences</h1>
                </div>
            </div>
        </div>

        <div class="container">
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    <?= $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    <?= $error_message; ?>
                </div>
            <?php endif; ?>

            <div class="select-session">
                <h5 class="mb-3">
                    <i class="bi bi-calendar3 me-2"></i>
                    Sélectionner une séance
                </h5>
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="groupe_id" class="form-label">Groupe</label>
                        <select name="groupe_id" id="groupe_id" class="form-select" required onchange="this.form.submit()">
                            <option value="">Choisir un groupe...</option>
                            <?php foreach ($groupes as $groupe): ?>
                                <option value="<?= $groupe['id']; ?>" <?= isset($_GET['groupe_id']) && $_GET['groupe_id'] == $groupe['id'] ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($groupe['nom']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="seance_id" class="form-label">Séance</label>
                        <select name="seance_id" id="seance_id" class="form-select" required>
                            <option value="">Choisir une séance...</option>
                            <?php if (!empty($seances)): ?>
                                <?php foreach ($seances as $seance): ?>
                                    <option value="<?= $seance['id']; ?>" <?= isset($_GET['seance_id']) && $_GET['seance_id'] == $seance['id'] ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars(date('d/m/Y', strtotime($seance['date']))); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-2"></i>
                            Afficher
                        </button>
                    </div>
                </form>
            </div>

            <?php if (isset($seance_id) && isset($groupe_id) && !empty($etudiants)): ?>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-person-check me-2"></i>
                            Liste des étudiants
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <form method="POST">
                            <input type="hidden" name="seance_id" value="<?= $seance_id; ?>">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Nom</th>
                                            <th>Prénom</th>
                                            <th class="text-center">Absence</th>
                                            <th class="text-center">Total des Absences</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($etudiants as $etudiant): ?>
                                            <tr>
                                                <td class="fw-medium"><?= htmlspecialchars($etudiant['nom']); ?></td>
                                                <td><?= htmlspecialchars($etudiant['prenom']); ?></td>
                                                <td class="text-center">
                                                    <div class="form-check d-flex justify-content-center">
                                                        <input type="checkbox" 
                                                               class="form-check-input"
                                                               name="absence[<?= $etudiant['id']; ?>]" 
                                                               value="1"
                                                               <?= $etudiant['is_absent'] ? 'checked' : ''; ?>>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="absences-count">
                                                        <?= $etudiant['total_absences']; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer bg-light p-3 d-flex justify-content-between align-items-center">
                                <a href="dashboard.php" class="btn-back">
                                    <i class="bi bi-arrow-left"></i>
                                    Retour au tableau de bord
                                </a>
                                <button type="submit" name="marquer_presence" class="btn btn-primary">
                                    <i class="bi bi-save me-2"></i>
                                    Enregistrer les absences
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Gérer l'activation/désactivation du bouton Afficher
            $('#seance_id').change(function() {
                var submitButton = $(this).closest('form').find('button[type="submit"]');
                submitButton.prop('disabled', !$(this).val());
            });
        });
    </script>
</body>
</html>
