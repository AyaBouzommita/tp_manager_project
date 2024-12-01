<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['prof_id'])) {
    // Si l'utilisateur n'est pas connecté, rediriger vers la page de connexion
    header("Location: login.php");
    exit();
}

require '../includes/db.php';  // Connexion à la base de données

// Vérification de l'ajout d'une séance
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajouter_seance'])) {
    $date_seance = $_POST['date_seance'];
    $groupe_id = $_POST['groupe_id'];

    // Vérifier si la date et le groupe sont fournis
    if (!empty($date_seance) && !empty($groupe_id)) {
        // Ajouter la séance dans la base de données
        $stmt = $conn->prepare("INSERT INTO seances (date, groupe_id) VALUES (?, ?)");
        $stmt->bind_param("si", $date_seance, $groupe_id);
        $stmt->execute();
        $success_message = "Séance ajoutée avec succès.";
    } else {
        $error_message = "Tous les champs doivent être remplis.";
    }
}

// Vérification de la suppression d'une séance
if (isset($_GET['supprimer_seance_id'])) {
    $seance_id = $_GET['supprimer_seance_id'];

    // Supprimer la séance de la base de données
    $stmt = $conn->prepare("DELETE FROM seances WHERE id = ?");
    $stmt->bind_param("i", $seance_id);
    $stmt->execute();
    $success_message = "Séance supprimée avec succès.";
}

// Récupérer la liste des groupes pour l'affichage du formulaire
$result_groupes = $conn->query("SELECT * FROM groupes");
$groupes = $result_groupes->fetch_all(MYSQLI_ASSOC);

// Récupérer la liste des séances
$seances = $conn->query("SELECT seances.id, seances.date, groupes.nom AS groupe_nom FROM seances INNER JOIN groupes ON seances.groupe_id = groupes.id")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Séances</title>
    <link rel="stylesheet" href="../assets/css/style.css">  <!-- Lien vers le fichier CSS -->
</head>
<body>
    <div class="gestion-seances-container">
        <h2>Gestion des Séances</h2>

        <!-- Affichage des messages de succès ou d'erreur -->
        <?php if (isset($success_message)): ?>
            <p style="color: green;"><?= $success_message; ?></p>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <p style="color: red;"><?= $error_message; ?></p>
        <?php endif; ?>

        <!-- Formulaire pour ajouter une nouvelle séance -->
        <h3>Ajouter une nouvelle séance</h3>
        <form method="POST">
            <div>
                <label for="date_seance">Date de la séance :</label>
                <input type="date" name="date_seance" id="date_seance" required />
            </div>

            <div>
                <label for="groupe_id">Groupe :</label>
                <select name="groupe_id" id="groupe_id" required>
                    <option value="">Sélectionner un groupe</option>
                    <?php foreach ($groupes as $groupe): ?>
                        <option value="<?= $groupe['id']; ?>"><?= htmlspecialchars($groupe['nom']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" name="ajouter_seance">Ajouter Séance</button>
        </form>

        <h3>Liste des Séances</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Groupe</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($seances as $seance): ?>
                    <tr>
                        <td><?= $seance['id']; ?></td>
                        <td><?= $seance['date']; ?></td>
                        <td><?= htmlspecialchars($seance['groupe_nom']); ?></td>
                        <td>
                            <!-- Lien pour supprimer une séance -->
                            <a href="?supprimer_seance_id=<?= $seance['id']; ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette séance ?')">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <a href="dashboard.php">Retour au tableau de bord</a>
    </div>

    <script src="../assets/js/script.js"></script>  <!-- Lien vers le fichier JS -->
</body>
</html>
