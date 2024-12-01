<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Gestion des TP</title>
</head>
<body>
    <div class="dashboard-container">
        <h2>Bienvenue, <?= htmlspecialchars($prof['nom']); ?> <?= htmlspecialchars($prof['prenom']); ?></h2>
        <p>Vous êtes connecté en tant que professeur.</p>

        <!-- Menu de navigation -->
        <nav>
            <ul>
                <li><a href="gestion_groupes.php">Gestion des Groupes</a></li>
                <li><a href="gestion_etudiants.php">Gestion des Étudiants</a></li>
                <li><a href="gestion_seances.php">Gestion des Séances</a></li>
                <li><a href="gestion_presences.php">Gestion des Présences</a></li>
                <li><a href="gestion_notes.php">Gestion des Notes</a></li>
            </ul>
        </nav>

        <!-- Bouton déconnexion -->
        <a href="logout.php" class="logout">Déconnexion</a>
    </div>
</body>
</html>
