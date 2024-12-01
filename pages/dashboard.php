<?php
session_start();

// Vérifier si l'utilisateur est connecté (si la session existe)
if (!isset($_SESSION['prof_id'])) {
    // Si l'utilisateur n'est pas connecté, rediriger vers la page de connexion
    header("Location: login.php");
    exit();
}

require '../includes/db.php';  // Connexion à la base de données

// Récupérer les informations du professeur connecté
$prof_id = $_SESSION['prof_id'];
$stmt = $conn->prepare("SELECT * FROM professeurs WHERE id = ?");
$stmt->bind_param("i", $prof_id);
$stmt->execute();
$result = $stmt->get_result();
$prof = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Gestion des TP</title>
    <link rel="stylesheet" href="../assets/css/style.css">  <!-- Lien vers le fichier CSS -->
</head>
<body>
    <div class="dashboard-container">
        <h2>Bienvenue, <?= htmlspecialchars($prof['nom']); ?></h2>
        <p>Vous êtes connecté en tant que professeur.</p>

        <!-- Liens vers les différentes sections de gestion -->
        <nav>
            <ul>
                <li><a href="gestion_groupes.php">Gestion des Groupes</a></li>
                <li><a href="gestion_etudiants.php">Gestion des Étudiants</a></li>
                <li><a href="gestion_seances.php">Gestion des Séances</a></li>
                <li><a href="gestion_presences.php">Gestion des Présences</a></li>
                <li><a href="gestion_notes.php">Gestion des Notes</a></li>
            </ul>
        </nav>

        <!-- Déconnexion -->
        <a href="logout.php">Déconnexion</a>
    </div>
    

    <script src="../assets/js/script.js"></script>  <!-- Lien vers le fichier JS -->
</body>
</html>
