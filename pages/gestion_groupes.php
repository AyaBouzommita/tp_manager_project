<?php
session_start();

// Vérifier si l'utilisateur est connecté (si la session existe)
if (!isset($_SESSION['prof_id'])) {
    // Si l'utilisateur n'est pas connecté, rediriger vers la page de connexion
    header("Location: login.php");
    exit();
}

require '../includes/db.php';  // Connexion à la base de données

// Vérifier si un nouveau groupe est ajouté
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajouter_groupe'])) {
    $nom_groupe = $_POST['nom_groupe'];

    // Vérifier si le nom du groupe est vide
    if (!empty($nom_groupe)) {
        // Insertion du groupe dans la base de données
        $stmt = $conn->prepare("INSERT INTO groupes (nom) VALUES (?)");
        $stmt->bind_param("s", $nom_groupe);
        $stmt->execute();
        $success_message = "Groupe ajouté avec succès.";
    } else {
        $error_message = "Le nom du groupe ne peut pas être vide.";
    }
}

// Vérifier si un groupe doit être supprimé
if (isset($_GET['supprimer_groupe_id'])) {
    $groupe_id = $_GET['supprimer_groupe_id'];

    // Supprimer le groupe de la base de données
    $stmt = $conn->prepare("DELETE FROM groupes WHERE id = ?");
    $stmt->bind_param("i", $groupe_id);
    $stmt->execute();
    $success_message = "Groupe supprimé avec succès.";
}

// Récupérer la liste des groupes existants
$result = $conn->query("SELECT * FROM groupes");
$groupes = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Groupes</title>
    <link rel="stylesheet" href="../assets/css/style.css">  <!-- Lien vers le fichier CSS -->
</head>
<body>
    <div class="gestion-groupes-container">
        <h2>Gestion des Groupes</h2>

        <!-- Affichage des messages de succès ou d'erreur -->
        <?php if (isset($success_message)): ?>
            <p style="color: green;"><?= $success_message; ?></p>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <p style="color: red;"><?= $error_message; ?></p>
        <?php endif; ?>

        <!-- Formulaire pour ajouter un nouveau groupe -->
        <h3>Ajouter un nouveau groupe</h3>
        <form method="POST">
            <div>
                <label for="nom_groupe">Nom du groupe :</label>
                <input type="text" name="nom_groupe" id="nom_groupe" required placeholder="Entrez le nom du groupe">
            </div>
            <button type="submit" name="ajouter_groupe">Ajouter</button>
        </form>

        <h3>Liste des Groupes</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom du Groupe</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($groupes as $groupe): ?>
                    <tr>
                        <td><?= $groupe['id']; ?></td>
                        <td><?= htmlspecialchars($groupe['nom']); ?></td>
                        <td>
                            <!-- Lien pour supprimer un groupe -->
                            <a href="?supprimer_groupe_id=<?= $groupe['id']; ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce groupe ?')">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <a href="dashboard.php">Retour au tableau de bord</a>
    </div>

    <?php include '../includes/footer.php'; ?>
    
</body>
</html>
