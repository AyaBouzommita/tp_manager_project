<?php
require_once '../includes/session_manager.php';
require_once '../includes/db.php';

// Vérifier si l'utilisateur est connecté
checkUserSession();

// Récupérer toutes les séances
$stmt = $conn->prepare("SELECT id, date FROM seances ORDER BY date DESC");
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
    <link rel="stylesheet" href="../assets/css/gestion_notes.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="notes-container">
        <div class="notes-header">
            <h1><i class="fas fa-graduation-cap"></i> Gestion des Notes</h1>
            
            <?php 
            $flashMessage = getFlashMessage();
            if ($flashMessage): 
            ?>
                <div class="alert alert-<?= $flashMessage['type'] ?>">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($flashMessage['message']) ?>
                </div>
            <?php endif; ?>
        </div>

        <form method="POST" class="seance-selector">
            <label for="seance_id">
                <i class="fas fa-calendar"></i> Sélectionner une séance :
            </label>
            <select name="seance_id" id="seance_id">
                <option value="">Choisir une séance</option>
                <?php while ($seance = $seances->fetch_assoc()): ?>
                    <option value="<?= $seance['id'] ?>" <?= ($selectedSeanceId == $seance['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars(date('d/m/Y', strtotime($seance['date']))) ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <button type="submit" class="btn-select">
                <i class="fas fa-check"></i> Sélectionner
            </button>
        </form>

        <?php if ($selectedSeanceId && !empty($etudiants)): ?>
            <form method="POST" id="notesForm">
                <input type="hidden" name="seance_id" value="<?= $selectedSeanceId ?>">
                
                <table class="notes-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Travail <small>(5 pts)</small></th>
                            <th>Compte Rendu <small>(5 pts)</small></th>
                            <th>Tâches Terminées <small>(5 pts)</small></th>
                            <th>Discipline <small>(5 pts)</small></th>
                            <th>Note Totale <small>(20 pts)</small></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($etudiants as $etudiant): ?>
                            <tr>
                                <td><?= htmlspecialchars($etudiant['nom']) ?></td>
                                <td><?= htmlspecialchars($etudiant['prenom']) ?></td>
                                <td>
                                    <input type="number" min="0" max="5" step="0.5" 
                                           name="notes[<?= $etudiant['id'] ?>][travail]"
                                           value="<?= isset($notes[$etudiant['id']]) ? $notes[$etudiant['id']]['travail'] : '' ?>">
                                </td>
                                <td>
                                    <input type="number" min="0" max="5" step="0.5" 
                                           name="notes[<?= $etudiant['id'] ?>][compte_rendu]"
                                           value="<?= isset($notes[$etudiant['id']]) ? $notes[$etudiant['id']]['compte_rendu'] : '' ?>">
                                </td>
                                <td>
                                    <input type="number" min="0" max="5" step="0.5" 
                                           name="notes[<?= $etudiant['id'] ?>][taches_terminees]"
                                           value="<?= isset($notes[$etudiant['id']]) ? $notes[$etudiant['id']]['taches_terminees'] : '' ?>">
                                </td>
                                <td>
                                    <input type="number" min="0" max="5" step="0.5" 
                                           name="notes[<?= $etudiant['id'] ?>][discipline]"
                                           value="<?= isset($notes[$etudiant['id']]) ? $notes[$etudiant['id']]['discipline'] : '' ?>">
                                </td>
                                <td class="note-totale">
                                    <?= isset($notes[$etudiant['id']]) ? $notes[$etudiant['id']]['note_totale'] : '0' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="action-buttons">
                    <button type="submit" name="calculer" class="btn btn-calculate">
                        <i class="fas fa-calculator"></i> Calculer les notes totales
                    </button>
                    <button type="submit" name="enregistrer" class="btn btn-save">
                        <i class="fas fa-save"></i> Enregistrer les notes
                    </button>
                    <a href="dashboard.php" class="btn btn-return">
                        <i class="fas fa-arrow-left"></i> Retour au tableau de bord
                    </a>
                </div>
            </form>
        <?php elseif ($selectedSeanceId): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                Aucun étudiant trouvé pour cette séance.
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Calcul automatique des notes totales
        document.querySelectorAll('input[type="number"]').forEach(input => {
            input.addEventListener('input', function() {
                const row = this.closest('tr');
                const inputs = row.querySelectorAll('input[type="number"]');
                let total = 0;
                
                inputs.forEach(input => {
                    total += Number(input.value) || 0;
                });
                
                row.querySelector('.note-totale').textContent = total.toFixed(1);
            });
        });

        // Animation de chargement lors de la soumission
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                this.classList.add('loading');
            });
        });
    </script>
</body>
</html>
