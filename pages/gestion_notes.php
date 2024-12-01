<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['prof_id'])) {
    header("Location: login.php");
    exit();
}

require '../includes/db.php';  // Connexion à la base de données

// Variables pour les notes
$etudiants_notes = []; // Stocker les données soumises pour réaffichage
$notes_totales = []; // Stocker les notes totales calculées

// Récupérer la liste des séances disponibles
$result_seances = $conn->query("SELECT * FROM seances");
$seances = $result_seances->fetch_all(MYSQLI_ASSOC);

// Récupérer les étudiants pour la séance sélectionnée
$etudiants = [];
if (isset($_GET['seance_id']) || isset($_POST['seance_id'])) {
    $seance_id = isset($_GET['seance_id']) ? $_GET['seance_id'] : $_POST['seance_id'];

    // Récupérer les étudiants associés à la séance
    $stmt = $conn->prepare("
        SELECT e.id, e.nom, e.prenom 
        FROM etudiants e
        INNER JOIN groupes g ON e.groupe_id = g.id
        INNER JOIN seances s ON s.groupe_id = g.id
        WHERE s.id = ?
    ");
    $stmt->bind_param("i", $seance_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $etudiants = $result->fetch_all(MYSQLI_ASSOC);
}

// Si le bouton "Calculer les notes totales" est cliqué
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['calculer_notes'])) {
    $etudiants_notes = $_POST['note']; // Stocker les données soumises pour réaffichage

    // Calcul des notes totales
    foreach ($etudiants_notes as $etudiant_id => $notes) {
        $travail = isset($notes['travail']) ? (float)$notes['travail'] : 0;
        $compte_rendu = isset($notes['compte_rendu']) ? (float)$notes['compte_rendu'] : 0;
        $taches_terminees = isset($notes['taches_terminees']) ? (float)$notes['taches_terminees'] : 0;
        $discipline = isset($notes['discipline']) ? (float)$notes['discipline'] : 0;

        $notes_totales[$etudiant_id] = $travail + $compte_rendu + $taches_terminees + $discipline;
    }
}

// Si le bouton "Enregistrer les notes" est cliqué
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['enregistrer_notes'])) {
    $etudiants_notes = $_POST['note']; // Stocker les données soumises pour réaffichage

    foreach ($etudiants_notes as $etudiant_id => $notes) {
        $travail = isset($notes['travail']) ? (float)$notes['travail'] : 0;
        $compte_rendu = isset($notes['compte_rendu']) ? (float)$notes['compte_rendu'] : 0;
        $taches_terminees = isset($notes['taches_terminees']) ? (float)$notes['taches_terminees'] : 0;
        $discipline = isset($notes['discipline']) ? (float)$notes['discipline'] : 0;

        $note_totale = $travail + $compte_rendu + $taches_terminees + $discipline;

        // Insérer ou mettre à jour les notes dans la base de données
        $stmt = $conn->prepare("
            INSERT INTO notes (etudiant_id, seance_id, travail, compte_rendu, taches_terminees, discipline, note_totale) 
            VALUES (?, ?, ?, ?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE 
            travail = VALUES(travail), 
            compte_rendu = VALUES(compte_rendu), 
            taches_terminees = VALUES(taches_terminees), 
            discipline = VALUES(discipline), 
            note_totale = VALUES(note_totale)
        ");
        $stmt->bind_param("iiddddi", $etudiant_id, $seance_id, $travail, $compte_rendu, $taches_terminees, $discipline, $note_totale);
        $stmt->execute();
    }
    $success_message = "Notes enregistrées avec succès.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Notes</title>
    <link rel="stylesheet" href="../assets/css/style.css">  <!-- Lien vers le fichier CSS -->
</head>
<body>
    <div class="gestion-notes-container">
        <h2>Gestion des Notes</h2>

        <!-- Messages de succès ou d'erreur -->
        <?php if (isset($success_message)): ?>
            <p style="color: green;"><?= $success_message; ?></p>
        <?php endif; ?>

        <!-- Sélection de la séance -->
        <h3>Sélectionner une séance</h3>
        <form method="GET">
            <div>
                <label for="seance_id">Séance :</label>
                <select name="seance_id" id="seance_id" required>
                    <option value="">Sélectionner une séance</option>
                    <?php foreach ($seances as $seance): ?>
                        <option value="<?= $seance['id']; ?>" <?= isset($seance_id) && $seance_id == $seance['id'] ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($seance['date']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Sélectionner</button>
            </div>
        </form>

        <!-- Formulaire pour gérer les notes -->
        <?php if (!empty($etudiants)): ?>
            <h3>Attribuer les notes pour la séance</h3>
            <form method="POST">
                <input type="hidden" name="seance_id" value="<?= $seance_id; ?>">
                <table>
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Travail</th>
                            <th>Compte Rendu</th>
                            <th>Tâches Terminées</th>
                            <th>Discipline</th>
                            <th>Note Totale</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($etudiants as $etudiant): ?>
                            <tr>
                                <td><?= htmlspecialchars($etudiant['nom']); ?></td>
                                <td><?= htmlspecialchars($etudiant['prenom']); ?></td>
                                <td>
                                    <input type="number" name="note[<?= $etudiant['id']; ?>][travail]" min="0" max="5" step="0.1" 
                                    value="<?= $etudiants_notes[$etudiant['id']]['travail'] ?? ''; ?>" required>
                                </td>
                                <td>
                                    <input type="number" name="note[<?= $etudiant['id']; ?>][compte_rendu]" min="0" max="5" step="0.1" 
                                    value="<?= $etudiants_notes[$etudiant['id']]['compte_rendu'] ?? ''; ?>" required>
                                </td>
                                <td>
                                    <input type="number" name="note[<?= $etudiant['id']; ?>][taches_terminees]" min="0" max="5" step="0.1" 
                                    value="<?= $etudiants_notes[$etudiant['id']]['taches_terminees'] ?? ''; ?>" required>
                                </td>
                                <td>
                                    <input type="number" name="note[<?= $etudiant['id']; ?>][discipline]" min="0" max="5" step="0.1" 
                                    value="<?= $etudiants_notes[$etudiant['id']]['discipline'] ?? ''; ?>" required>
                                </td>
                                <td>
                                    <?= $notes_totales[$etudiant['id']] ?? '—'; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit" name="calculer_notes">Calculer les notes totales</button>
                <button type="submit" name="enregistrer_notes">Enregistrer les notes</button>
            </form>
        <?php endif; ?>

        <a href="dashboard.php">Retour au tableau de bord</a>
    </div>
    
</body>
</html>
