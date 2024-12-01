<?php
require_once '../includes/session_manager.php';
require_once '../includes/db.php';

// Vérifier si l'utilisateur est connecté
checkUserSession();

// Récupérer toutes les séances
$stmt = $conn->prepare("SELECT s.id, s.date, 
    (SELECT COUNT(*) FROM notes n WHERE n.seance_id = s.id) as nb_notes,
    (SELECT COUNT(*) FROM etudiants) as total_etudiants,
    (SELECT AVG(note_totale) FROM notes n WHERE n.seance_id = s.id) as moyenne_seance
    FROM seances s ORDER BY s.date DESC");
$stmt->execute();
$seances = $stmt->get_result();

// Traitement de la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['calculer'])) {
        // Logique pour calculer les notes totales
        foreach ($_POST['notes'] as $etudiantId => $notes) {
            $noteTotal = 0;
            foreach ($notes as $note) {
                $noteTotal += !empty($note) ? floatval($note) : 0;
            }
            $_POST['notes'][$etudiantId]['note_totale'] = $noteTotal;
        }
    } elseif (isset($_POST['enregistrer'])) {
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
        header("Location: gestion_notes.php");
        exit();
    }
}

// Récupérer les étudiants et leurs notes pour une séance sélectionnée
$selectedSeanceId = isset($_POST['seance_id']) ? $_POST['seance_id'] : null;
$etudiants = [];
$notes = [];

if ($selectedSeanceId) {
    // Récupérer tous les étudiants
    $stmt = $conn->prepare("SELECT id, nom, prenom FROM etudiants ORDER BY nom, prenom");
    $stmt->execute();
    $etudiants = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Récupérer les notes existantes pour cette séance
    $stmt = $conn->prepare("SELECT * FROM notes WHERE seance_id = ?");
    $stmt->bind_param("i", $selectedSeanceId);
    $stmt->execute();
    $notesResult = $stmt->get_result();
    
    while ($note = $notesResult->fetch_assoc()) {
        $notes[$note['etudiant_id']] = $note;
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
                <div class="col-md-8">
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
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-check me-2"></i>Sélectionner
                    </button>
                </div>
            </form>
        </div>

        <?php if ($selectedSeanceId && !empty($etudiants)): ?>
            <form method="POST" id="notesForm">
                <input type="hidden" name="seance_id" value="<?= $selectedSeanceId ?>">
                
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
                                               value="<?= isset($notes[$etudiant['id']]) ? $notes[$etudiant['id']]['travail'] : '' ?>">
                                    </td>
                                    <td>
                                        <input type="number" min="0" max="5" step="0.5" 
                                               class="form-control note-input"
                                               name="notes[<?= $etudiant['id'] ?>][compte_rendu]"
                                               value="<?= isset($notes[$etudiant['id']]) ? $notes[$etudiant['id']]['compte_rendu'] : '' ?>">
                                    </td>
                                    <td>
                                        <input type="number" min="0" max="5" step="0.5" 
                                               class="form-control note-input"
                                               name="notes[<?= $etudiant['id'] ?>][taches_terminees]"
                                               value="<?= isset($notes[$etudiant['id']]) ? $notes[$etudiant['id']]['taches_terminees'] : '' ?>">
                                    </td>
                                    <td>
                                        <input type="number" min="0" max="5" step="0.5" 
                                               class="form-control note-input"
                                               name="notes[<?= $etudiant['id'] ?>][discipline]"
                                               value="<?= isset($notes[$etudiant['id']]) ? $notes[$etudiant['id']]['discipline'] : '' ?>">
                                    </td>
                                    <td class="note-totale text-center">
                                        <?= isset($notes[$etudiant['id']]) ? $notes[$etudiant['id']]['note_totale'] : '0' ?>
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

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        // Calcul automatique des notes totales
        document.querySelectorAll('input[type="number"]').forEach(input => {
            input.addEventListener('input', function() {
                const row = this.closest('tr');
                const inputs = row.querySelectorAll('input[type="number"]');
                let total = 0;
                
                inputs.forEach(input => {
                    total += !isNaN(input.value) && input.value !== '' ? parseFloat(input.value) : 0;
                });
                
                row.querySelector('.note-totale').textContent = total.toFixed(2);
            });
        });

        // Animation des cartes au scroll
        function revealOnScroll() {
            const cards = document.querySelectorAll('.stats-card, .notes-table');
            cards.forEach(card => {
                const cardTop = card.getBoundingClientRect().top;
                const triggerBottom = window.innerHeight * 0.8;
                
                if (cardTop < triggerBottom) {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }
            });
        }

        window.addEventListener('scroll', revealOnScroll);
        revealOnScroll();
    </script>
</body>
</html>
