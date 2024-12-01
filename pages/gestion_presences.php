<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['prof_id'])) {
    // Si l'utilisateur n'est pas connecté, rediriger vers la page de connexion
    header("Location: login.php");
    exit();
}

require '../includes/db.php';  // Connexion à la base de données

// Vérification de la soumission du formulaire de présence
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['marquer_presence'])) {
    $seance_id = $_POST['seance_id'];
    
    // Vérifier si l'array 'absence' existe et s'il contient des données
    if (isset($_POST['absence']) && !empty($_POST['absence'])) {
        // Parcourir les étudiants et marquer leur absence
        foreach ($_POST['absence'] as $etudiant_id => $absence) {
            // Marquer l'étudiant comme absent (absence = 1)
            $stmt = $conn->prepare("INSERT INTO presences (etudiant_id, seance_id, presence) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE presence = ?");
            $stmt->bind_param("iiii", $etudiant_id, $seance_id, $absence, $absence);
            $stmt->execute();
        }
        $success_message = "Absences enregistrées avec succès.";
    } else {
        $error_message = "Aucune absence n'a été marquée.";
    }
}

// Récupérer la liste des séances disponibles
$result_seances = $conn->query("SELECT * FROM seances");
$seances = $result_seances->fetch_all(MYSQLI_ASSOC);

// Récupérer les étudiants pour la séance sélectionnée
$etudiants = [];
$absences_count = [];
if (isset($_GET['seance_id'])) {
    $seance_id = $_GET['seance_id'];
    
    // Récupérer les étudiants associés à la séance
    $stmt = $conn->prepare("SELECT e.id, e.nom, e.prenom FROM etudiants e
                            INNER JOIN groupes g ON e.groupe_id = g.id
                            INNER JOIN seances s ON s.groupe_id = g.id
                            WHERE s.id = ?");
    $stmt->bind_param("i", $seance_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $etudiants = $result->fetch_all(MYSQLI_ASSOC);
    
    // Calculer le nombre total d'absences pour chaque étudiant pour toutes les séances
    foreach ($etudiants as $etudiant) {
        $etudiant_id = $etudiant['id'];
        
        // Compter le nombre total d'absences de cet étudiant dans toutes les séances
        $absence_stmt = $conn->prepare("SELECT COUNT(*) AS absences_count 
                                       FROM presences 
                                       WHERE etudiant_id = ? AND presence = 0");
        $absence_stmt->bind_param("i", $etudiant_id);
        $absence_stmt->execute();
        $absence_result = $absence_stmt->get_result();
        $absence_row = $absence_result->fetch_assoc();
        $absences_count[$etudiant_id] = $absence_row['absences_count'];
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<?php include '../includes/header.php'; ?>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Absences</title>
    <link rel="stylesheet" href="../assets/css/style.css">  <!-- Lien vers le fichier CSS -->
</head>
<body>
    <div class="gestion-presences-container">
        <h2>Gestion des Absences</h2>

        <!-- Affichage des messages de succès ou d'erreur -->
        <?php if (isset($success_message)): ?>
            <p style="color: green;"><?= $success_message; ?></p>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <p style="color: red;"><?= $error_message; ?></p>
        <?php endif; ?>

        <!-- Formulaire de sélection de la séance -->
        <h3>Sélectionner une séance</h3>
        <form method="GET">
            <div>
                <label for="seance_id">Séance :</label>
                <select name="seance_id" id="seance_id" required>
                    <option value="">Sélectionner une séance</option>
                    <?php foreach ($seances as $seance): ?>
                        <option value="<?= $seance['id']; ?>" <?= isset($_GET['seance_id']) && $_GET['seance_id'] == $seance['id'] ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($seance['date']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Sélectionner</button>
            </div>
        </form>

        <?php if (isset($seance_id) && !empty($etudiants)): ?>
            <h3>Marquer l'absence pour la séance du <?= htmlspecialchars($seance['date']); ?></h3>

            <!-- Formulaire pour marquer les absences des étudiants -->
            <form method="POST">
                <input type="hidden" name="seance_id" value="<?= $seance_id; ?>">
                <table>
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Absence</th>
                            <th>Absences Totales</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($etudiants as $etudiant): ?>
                            <tr>
                                <td><?= htmlspecialchars($etudiant['nom']); ?></td>
                                <td><?= htmlspecialchars($etudiant['prenom']); ?></td>
                                <td>
                                    <!-- Cocher pour marquer comme absent (absence = 1) -->
                                    <input type="checkbox" name="absence[<?= $etudiant['id']; ?>]" value="0">
                                </td>
                                <td>
                                    <!-- Afficher le nombre total d'absences pour cet étudiant -->
                                    <?= isset($absences_count[$etudiant['id']]) ? $absences_count[$etudiant['id']] : 0; ?> Absence(s)
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit" name="marquer_presence">Enregistrer les absences</button>
            </form>
        <?php endif; ?>

        <a href="dashboard.php">Retour au tableau de bord</a>
    </div>
    
    <script src="../assets/js/script.js"></script>  <!-- Lien vers le fichier JS -->
</body>
</html>
