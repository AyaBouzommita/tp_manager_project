<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Gestion des TP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
    </main>
    <footer class="bg-dark text-light py-4 mt-auto">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Gestion des TP</h5>
                    <p class="mb-0"> <?php echo date('Y'); ?> Tous droits réservés</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <ul class="list-inline mb-0">
                        <li class="list-inline-item"><a href="#" class="text-light text-decoration-none">Mentions légales</a></li>
                        <li class="list-inline-item">|</li>
                        <li class="list-inline-item"><a href="#" class="text-light text-decoration-none">Contact</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
