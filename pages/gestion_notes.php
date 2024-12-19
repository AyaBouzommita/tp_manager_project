<?php
require_once '../includes/session_manager.php';
require_once '../includes/db.php';

// Vérifier si l'utilisateur est connecté
checkUserSession();

// Récupérer la liste des groupes
$result_groupes = $conn->query("SELECT * FROM groupes ORDER BY nom");
$groupes = $result_groupes->fetch_all(MYSQLI_ASSOC);

// Récupérer les séances
$selectedGroupe = isset($_POST['groupe_id']) ? $_POST['groupe_id'] : null;
$selectedSeanceId = isset($_POST['seance_id']) ? $_POST['seance_id'] : null;

// Récupérer les séances en fonction du groupe sélectionné
$seances_query = "SELECT s.id, s.date, s.groupe_id,
    (SELECT COUNT(*) FROM notes n 
     INNER JOIN etudiants e ON n.etudiant_id = e.id 
     WHERE n.seance_id = s.id AND e.groupe_id = s.groupe_id) as nb_notes,
    (SELECT COUNT(*) FROM etudiants WHERE groupe_id = s.groupe_id) as total_etudiants,
    (SELECT AVG(note_totale) FROM notes n 
     INNER JOIN etudiants e ON n.etudiant_id = e.id 
     WHERE n.seance_id = s.id AND e.groupe_id = s.groupe_id) as moyenne_seance
    FROM seances s";

if ($selectedGroupe) {
    $seances_query .= " WHERE s.groupe_id = ?";
    $stmt = $conn->prepare($seances_query . " ORDER BY s.date DESC");
    $stmt->bind_param("i", $selectedGroupe);
} else {
    $stmt = $conn->prepare($seances_query . " ORDER BY s.date DESC");
}
$stmt->execute();
$seances = $stmt->get_result();

// Traitement de la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['enregistrer'])) {
        // Enregistrement des notes
        $stmt = $conn->prepare("INSERT INTO notes (etudiant_id, seance_id, travail, compte_rendu, taches_terminees, discipline, note_totale) 
                              VALUES (?, ?, ?, ?, ?, ?, ?) 
                              ON DUPLICATE KEY UPDATE 
                              travail = VALUES(travail),
                              compte_rendu = VALUES(compte_rendu),
                              taches_terminees = VALUES(taches_terminees),
                              discipline = VALUES(discipline),
                              note_totale = VALUES(note_totale)");

        foreach ($_POST['notes'] as $etudiantId => $notes) {
            $travail = !empty($notes['travail']) ? floatval($notes['travail']) : 0;
            $compteRendu = !empty($notes['compte_rendu']) ? floatval($notes['compte_rendu']) : 0;
            $tachesTerminees = !empty($notes['taches_terminees']) ? floatval($notes['taches_terminees']) : 0;
            $discipline = !empty($notes['discipline']) ? floatval($notes['discipline']) : 0;
            $noteTotal = $travail + $compteRendu + $tachesTerminees + $discipline;

            $stmt->bind_param("iiiiiii", 
                $etudiantId, 
                $_POST['seance_id'],
                $travail,
                $compteRendu,
                $tachesTerminees,
                $discipline,
                $noteTotal
            );
            $stmt->execute();
        }
        setFlashMessage('success', "Les notes ont été enregistrées avec succès.");
        
        // Conserver les valeurs de filtres après l'enregistrement
        $selectedSeanceId = $_POST['seance_id'];
        if (isset($_POST['groupe_id'])) {
            $selectedGroupe = $_POST['groupe_id'];
        }
    }
}

// Récupérer les étudiants et leurs notes pour une séance sélectionnée
$etudiants = [];
$notes = [];

if ($selectedSeanceId) {
    // Récupérer le tri sélectionné
    $sortBy = isset($_POST['sort_by']) ? $_POST['sort_by'] : 'nom_asc';
    
    // Construire la clause ORDER BY en fonction du tri sélectionné
    $orderBy = "ORDER BY ";
    switch ($sortBy) {
        case 'nom_desc':
            $orderBy .= "e.nom DESC, e.prenom DESC";
            break;
        case 'note_asc':
            $orderBy .= "COALESCE(n.note_totale, 0) ASC";
            break;
        case 'note_desc':
            $orderBy .= "COALESCE(n.note_totale, 0) DESC";
            break;
        case 'nom_asc':
        default:
            $orderBy .= "e.nom ASC, e.prenom ASC";
    }

    // Récupérer les étudiants du groupe sélectionné avec leurs notes
    if ($selectedGroupe) {
        $stmt = $conn->prepare(
            "SELECT e.id, e.nom, e.prenom, 
                    n.travail, n.compte_rendu, n.taches_terminees, n.discipline, n.note_totale
             FROM etudiants e 
             LEFT JOIN notes n ON e.id = n.etudiant_id AND n.seance_id = ?
             WHERE e.groupe_id = ? " . $orderBy
        );
        $stmt->bind_param("ii", $selectedSeanceId, $selectedGroupe);
    } else {
        $stmt = $conn->prepare(
            "SELECT e.id, e.nom, e.prenom, 
                    n.travail, n.compte_rendu, n.taches_terminees, n.discipline, n.note_totale
             FROM etudiants e 
             LEFT JOIN notes n ON e.id = n.etudiant_id AND n.seance_id = ? " . $orderBy
        );
        $stmt->bind_param("i", $selectedSeanceId);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $etudiants[] = [
            'id' => $row['id'],
            'nom' => $row['nom'],
            'prenom' => $row['prenom'],
            'travail' => $row['travail'],
            'compte_rendu' => $row['compte_rendu'],
            'taches_terminees' => $row['taches_terminees'],
            'discipline' => $row['discipline'],
            'note_totale' => $row['note_totale']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Notes - TP Manager</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #4CAF50 0%, #2196F3 100%);
            --secondary-gradient: linear-gradient(135deg, #FF9800 0%, #F44336 100%);
        }
        
        .notes-header {
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

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .stats-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .seance-selector {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .notes-table {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .notes-table thead {
            background: var(--primary-gradient);
            color: white;
        }

        .notes-table th {
            padding: 1rem;
            font-weight: 500;
        }

        .notes-table td {
            padding: 0.75rem;
            vertical-align: middle;
        }

        .notes-table tbody tr:hover {
            background-color: rgba(0,0,0,0.02);
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

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
        }

        .btn-secondary {
            background: var(--secondary-gradient);
            border: none;
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }

        .alert {
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            border: none;
        }

        .alert-success {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
        }

        .alert-error {
            background: var(--secondary-gradient);
            color: white;
        }

        .note-input {
            width: 70px;
            text-align: center;
            font-weight: 500;
        }

        .note-totale {
            font-weight: bold;
            font-size: 1.1em;
            color: #2196F3;
        }

        .action-buttons {
            margin-top: 2rem;
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }

        @media (max-width: 768px) {
            .notes-table {
                display: block;
                overflow-x: auto;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body class="bg-light">
    <?php include '../includes/header.php'; ?>

    <div class="container py-4">
        <div class="notes-header">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="display-4 mb-0">Gestion des Notes</h1>
                        <p class="lead mb-0">Évaluez et suivez les performances de vos étudiants</p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <span class="text-white-50"><?= date('d/m/Y') ?></span>
                    </div>
                </div>
            </div>
        </div>

        <?php 
        $flashMessage = getFlashMessage();
        if ($flashMessage): 
        ?>
            <div class="alert alert-<?= $flashMessage['type'] ?> d-flex align-items-center">
                <i class="fas <?= $flashMessage['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> me-2"></i>
                <?= htmlspecialchars($flashMessage['message']) ?>
            </div>
        <?php endif; ?>

        <div class="seance-selector">
            <form method="POST" class="row align-items-end">
                <div class="col-md-3">
                    <label for="groupe_id" class="form-label">
                        <i class="fas fa-users me-2"></i>Sélectionner un groupe
                    </label>
                    <select name="groupe_id" id="groupe_id" class="form-select">
                        <option value="">Tous les groupes</option>
                        <?php foreach ($groupes as $groupe): ?>
                            <option value="<?= $groupe['id'] ?>" <?= ($selectedGroupe == $groupe['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($groupe['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="seance_id" class="form-label">
                        <i class="fas fa-calendar me-2"></i>Sélectionner une séance
                    </label>
                    <select name="seance_id" id="seance_id" class="form-select">
                        <option value="">Choisir une séance</option>
                        <?php while ($seance = $seances->fetch_assoc()): ?>
                            <option value="<?= $seance['id'] ?>" <?= ($selectedSeanceId == $seance['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars(date('d/m/Y', strtotime($seance['date']))) ?> 
                                (<?= $seance['nb_notes'] ?>/<?= $seance['total_etudiants'] ?> notes - 
                                Moyenne: <?= number_format($seance['moyenne_seance'], 2) ?>/20)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="sort_by" class="form-label">
                        <i class="fas fa-sort me-2"></i>Trier par
                    </label>
                    <select name="sort_by" id="sort_by" class="form-select">
                        <option value="nom_asc" <?= (!isset($_POST['sort_by']) || $_POST['sort_by'] == 'nom_asc') ? 'selected' : '' ?>>Nom (A-Z)</option>
                        <option value="nom_desc" <?= (isset($_POST['sort_by']) && $_POST['sort_by'] == 'nom_desc') ? 'selected' : '' ?>>Nom (Z-A)</option>
                        <option value="note_asc" <?= (isset($_POST['sort_by']) && $_POST['sort_by'] == 'note_asc') ? 'selected' : '' ?>>Note (croissant)</option>
                        <option value="note_desc" <?= (isset($_POST['sort_by']) && $_POST['sort_by'] == 'note_desc') ? 'selected' : '' ?>>Note (décroissant)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-check me-2"></i>Appliquer
                    </button>
                </div>
            </form>
        </div>

        <?php if ($selectedSeanceId && !empty($etudiants)): ?>
            <form method="POST" id="notesForm">
                <input type="hidden" name="seance_id" value="<?= $selectedSeanceId ?>">
                <?php if ($selectedGroupe): ?>
                <input type="hidden" name="groupe_id" value="<?= $selectedGroupe ?>">
                <?php endif; ?>
                <?php if (isset($_POST['sort_by'])): ?>
                <input type="hidden" name="sort_by" value="<?= htmlspecialchars($_POST['sort_by']) ?>">
                <?php endif; ?>
                
                <div class="notes-table">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Étudiant</th>
                                <th>Travail <small>(5 pts)</small></th>
                                <th>Compte Rendu <small>(5 pts)</small></th>
                                <th>Tâches <small>(5 pts)</small></th>
                                <th>Discipline <small>(5 pts)</small></th>
                                <th>Total <small>(20 pts)</small></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($etudiants as $etudiant): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($etudiant['nom']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($etudiant['prenom']) ?></small>
                                    </td>
                                    <td>
                                        <input type="number" min="0" max="5" step="0.5" 
                                               class="form-control note-input"
                                               name="notes[<?= $etudiant['id'] ?>][travail]"
                                               value="<?= $etudiant['travail'] ?>">
                                    </td>
                                    <td>
                                        <input type="number" min="0" max="5" step="0.5" 
                                               class="form-control note-input"
                                               name="notes[<?= $etudiant['id'] ?>][compte_rendu]"
                                               value="<?= $etudiant['compte_rendu'] ?>">
                                    </td>
                                    <td>
                                        <input type="number" min="0" max="5" step="0.5" 
                                               class="form-control note-input"
                                               name="notes[<?= $etudiant['id'] ?>][taches_terminees]"
                                               value="<?= $etudiant['taches_terminees'] ?>">
                                    </td>
                                    <td>
                                        <input type="number" min="0" max="5" step="0.5" 
                                               class="form-control note-input"
                                               name="notes[<?= $etudiant['id'] ?>][discipline]"
                                               value="<?= $etudiant['discipline'] ?>">
                                    </td>
                                    <td class="note-totale text-center">
                                        <?= $etudiant['note_totale'] ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="action-buttons">
                
                    <button type="submit" name="enregistrer" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        <?php elseif ($selectedSeanceId): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle me-2"></i>
                Aucun étudiant trouvé pour cette séance.
            </div>
        <?php endif; ?>
    </div>
    <?php include '../includes/footer.php'; ?>
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            // Function to calculate total note
            function calculateTotal(row) {
                let total = 0;
                row.find('input[type="number"]').each(function() {
                    total += parseFloat($(this).val()) || 0;
                });
                row.find('.note-totale').text(total.toFixed(2));
            }

            // Calculate totals when input changes
            $('.note-input').on('input', function() {
                calculateTotal($(this).closest('tr'));
            });

            // Handle group selection change
            $('#groupe_id').change(function() {
                // Submit the form when group changes
                $(this).closest('form').submit();
            });
        });
    </script>
</body>
</html>
